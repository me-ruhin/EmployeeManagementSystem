<?php

namespace App\Http\Livewire\Payroll;

use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Leave;
use Livewire\Component;
use Carbon\Carbon;

class View extends Component
{
    public Payroll $payroll;
    public $attendanceSummary = [];
    public $leavesSummary = [];

    public function mount(Payroll $payroll)
    {
        $this->payroll = $payroll->load(['employee.user', 'employee.department', 'employee.company']);
        
        // Calculate attendance summary for the payroll month
        $this->calculateAttendanceSummary();
        
        // Calculate leaves taken during the payroll month
        $this->calculateLeavesSummary();
    }

    protected function calculateAttendanceSummary()
    {
        // Parse month from salary_month (format: YYYY-MM)
        $parts = explode('-', $this->payroll->salary_month);
        if (count($parts) >= 2) {
            $year = $parts[0];
            $month = $parts[1];
            
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            // Get attendance records for the month
            $attendanceRecords = Attendance::where('employee_id', $this->payroll->employee_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            // Calculate summary
            $this->attendanceSummary = [
                'total_working_days' => $this->calculateWorkingDays($startDate, $endDate),
                'present' => $attendanceRecords->where('status', 'Present')->count(),
                'absent' => $attendanceRecords->where('status', 'Absent')->count(),
                'late' => $attendanceRecords->where('status', 'Late')->count(),
                'on_leave' => $attendanceRecords->where('status', 'On Leave')->count(),
            ];
        }
    }

    protected function calculateLeavesSummary()
    {
        // Parse month from salary_month (format: YYYY-MM)
        $parts = explode('-', $this->payroll->salary_month);
        if (count($parts) >= 2) {
            $year = $parts[0];
            $month = $parts[1];
            
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            // Get leaves for the month
            $leaves = Leave::where('employee_id', $this->payroll->employee_id)
                ->where('status', 'Approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->get();
            
            // Group leaves by type
            $leavesByType = [];
            foreach ($leaves as $leave) {
                $type = $leave->leave_type;
                if (!isset($leavesByType[$type])) {
                    $leavesByType[$type] = 0;
                }
                
                // Calculate days within the month
                $leaveStart = max($startDate, Carbon::parse($leave->start_date));
                $leaveEnd = min($endDate, Carbon::parse($leave->end_date));
                $days = $leaveStart->diffInDays($leaveEnd) + 1;
                
                $leavesByType[$type] += $days;
            }
            
            $this->leavesSummary = $leavesByType;
        }
    }

    protected function calculateWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Count only weekdays (Monday to Friday)
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            
            $current->addDay();
        }
        
        return $workingDays;
    }

    public function processPayment()
    {
        if ($this->payroll->status === 'Paid') {
            session()->flash('info', 'This payroll has already been paid.');
            return;
        }
        
        $this->payroll->status = 'Paid';
        $this->payroll->payment_date = now();
        $this->payroll->save();
        
        session()->flash('success', 'Payment processed successfully.');
    }

    public function render()
    {
        return view('livewire.payroll.view');
    }
}
