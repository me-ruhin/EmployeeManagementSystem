<?php

namespace App\Http\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $date;
    public $department_id = '';
    public $status = '';
    public $downloadingReport = false;
    public $reportType = 'daily';
    public $startDate;
    public $endDate;
    public $month;
    public $year;

    public $viewingDepartments = [];

    public function mount()
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->month = Carbon::now()->format('Y-m');
        $this->year = Carbon::now()->format('Y');
        
        // Determine which departments the user can view
        $user = Auth::user();
        
        if ($user->isAdmin() || $user->isHR()) {
            // Admin and HR can see all departments
            $this->viewingDepartments = Department::orderBy('name')->get();
        } elseif ($user->isDepartmentHead()) {
            // Department head can see their department
            $this->viewingDepartments = Department::where('head_id', $user->id)->get();
            if ($this->viewingDepartments->count() > 0) {
                $this->department_id = $this->viewingDepartments->first()->id;
            }
        } elseif ($user->isManager()) {
            // Manager can see their department
            if ($user->department_id) {
                $this->viewingDepartments = Department::where('id', $user->department_id)->get();
                $this->department_id = $user->department_id;
            }
        } else {
            // Regular employee can see only their attendance
            $this->viewingDepartments = collect();
        }
    }

    public function generateReport()
    {
        $this->downloadingReport = true;
        
        // In a real implementation, this would generate a downloadable report
        // For this example, we'll just show a success message
        
        session()->flash('success', 'Attendance report generated successfully.');
        
        $this->downloadingReport = false;
    }

    public function markPresent($employeeId)
    {
        $this->updateAttendance($employeeId, 'Present');
    }

    public function markAbsent($employeeId)
    {
        $this->updateAttendance($employeeId, 'Absent');
    }

    public function markLate($employeeId)
    {
        $this->updateAttendance($employeeId, 'Late');
    }

    public function markOnLeave($employeeId)
    {
        $this->updateAttendance($employeeId, 'On Leave');
    }

    protected function updateAttendance($employeeId, $status)
    {
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employeeId,
            'date' => Carbon::parse($this->date),
        ]);
        
        $attendance->status = $status;
        
        if ($status === 'Present' && !$attendance->check_in_time) {
            $attendance->check_in_time = Carbon::now();
        }
        
        $attendance->save();
        
        session()->flash('success', 'Attendance updated successfully.');
    }

    public function render()
    {
        $user = Auth::user();
        $query = Attendance::with(['employee.user', 'employee.department'])
            ->whereDate('date', Carbon::parse($this->date));
            
        // Apply filters based on user role
        if ($user->isEmployee() && !$user->isManager() && !$user->isDepartmentHead()) {
            // Regular employees can only see their own attendance
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            } else {
                $query->where('employee_id', 0); // Force no results if no employee record
            }
        } elseif ($this->department_id) {
            // Filter by department if selected
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->department_id);
            });
        } elseif ($user->isManager()) {
            // Managers can see their department's attendance
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('reporting_manager_id', $user->id);
            });
        }
        
        // Apply status filter if selected
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        $attendances = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get all employees who should have attendance for today
        // This is used to show employees who don't have an attendance record yet
        $employeesQuery = Employee::with(['user', 'department']);
        
        if ($user->isEmployee() && !$user->isManager() && !$user->isDepartmentHead()) {
            $employeesQuery->where('user_id', $user->id);
        } elseif ($this->department_id) {
            $employeesQuery->where('department_id', $this->department_id);
        } elseif ($user->isManager()) {
            $employeesQuery->where('reporting_manager_id', $user->id);
        }
        
        $employees = $employeesQuery->get();
        
        // Create a map of employee IDs that already have attendance records
        $attendanceEmployeeIds = $attendances->pluck('employee_id')->toArray();
        
        // Filter to find employees without attendance records
        $employeesWithoutAttendance = $employees->filter(function ($employee) use ($attendanceEmployeeIds) {
            return !in_array($employee->id, $attendanceEmployeeIds);
        });

        return view('livewire.attendance.index', [
            'attendances' => $attendances,
            'employeesWithoutAttendance' => $employeesWithoutAttendance,
        ]);
    }
}
