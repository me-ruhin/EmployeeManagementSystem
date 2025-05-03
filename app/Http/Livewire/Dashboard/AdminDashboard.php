<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Project;
use App\Models\Leave;
use App\Models\Attendance;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboard extends Component
{
    public $totalEmployees;
    public $totalDepartments;
    public $activeProjects;
    public $pendingLeaves;
    public $todayAttendance;
    public $departmentEmployeeCount;
    public $projectsStatusCount;
    public $recentLeaves;
    
    public function mount()
    {
        $this->loadDashboardData();
    }
    
    public function loadDashboardData()
    {
        // Get counts for summary cards
        $this->totalEmployees = Employee::count();
        $this->totalDepartments = Department::count();
        $this->activeProjects = Project::where('status', 'Ongoing')->count();
        $this->pendingLeaves = Leave::where('status', 'Pending')->count();
        
        // Get today's attendance statistics
        $today = Carbon::today();
        $this->todayAttendance = [
            'present' => Attendance::whereDate('date', $today)->where('status', 'Present')->count(),
            'absent' => Attendance::whereDate('date', $today)->where('status', 'Absent')->count(),
            'late' => Attendance::whereDate('date', $today)->where('status', 'Late')->count(),
            'on_leave' => Attendance::whereDate('date', $today)->where('status', 'On Leave')->count(),
        ];
        
        // Get employee count by department for chart
        $this->departmentEmployeeCount = Department::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($department) {
                return [
                    'name' => $department->name,
                    'count' => $department->employees_count
                ];
            });
        
        // Get projects by status for chart
        $this->projectsStatusCount = [
            'ongoing' => Project::where('status', 'Ongoing')->count(),
            'completed' => Project::where('status', 'Completed')->count(),
            'cancelled' => Project::where('status', 'Cancelled')->count(),
        ];
        
        // Get recent leave requests
        $this->recentLeaves = Leave::with(['employee.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }
    
    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
