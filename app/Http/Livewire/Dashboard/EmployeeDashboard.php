<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Task;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeDashboard extends Component
{
    public $checkedInToday;
    public $checkedOutToday;
    public $todayAttendance;
    public $pendingTasks;
    public $completedTasks;
    public $leaveBalance;
    public $recentLeaves;
    public $upcomingDeadlines;
    
    public function mount()
    {
        $this->loadDashboardData();
    }
    
    public function loadDashboardData()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return;
        }
        
        // Check today's attendance
        $today = Carbon::today();
        $this->todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();
            
        $this->checkedInToday = $employee->hasCheckedInToday();
        $this->checkedOutToday = $employee->hasCheckedOutToday();
        
        // Get task statistics
        $this->pendingTasks = Task::where('assigned_to', $employee->id)
            ->whereIn('status', ['Pending', 'In Progress'])
            ->count();
            
        $this->completedTasks = Task::where('assigned_to', $employee->id)
            ->where('status', 'Completed')
            ->count();
        
        // Get leave statistics (simple implementation - can be enhanced)
        $this->leaveBalance = [
            'annual' => 20, // Default values (can be made dynamic based on company policy)
            'sick' => 10,
            'casual' => 5,
        ];
        
        // Get leave history
        $this->recentLeaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get upcoming deadlines
        $this->upcomingDeadlines = Task::where('assigned_to', $employee->id)
            ->whereIn('status', ['Pending', 'In Progress'])
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->take(5)
            ->get();
    }
    
    public function checkIn()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return;
        }
        
        $today = Carbon::today();
        $now = Carbon::now();
        
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);
        
        if (!$attendance->check_in_time) {
            $attendance->check_in_time = $now;
            
            // Check if late (assuming 9 AM is the start time)
            $startTime = Carbon::createFromTimeString('09:00:00');
            if ($now->isAfter($startTime)) {
                $attendance->status = 'Late';
            } else {
                $attendance->status = 'Present';
            }
            
            $attendance->save();
            
            $this->loadDashboardData();
            session()->flash('message', 'Checked in successfully at ' . $now->format('h:i A'));
        }
    }
    
    public function checkOut()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return;
        }
        
        $today = Carbon::today();
        $now = Carbon::now();
        
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        if ($attendance && !$attendance->check_out_time) {
            $attendance->check_out_time = $now;
            $attendance->save();
            
            $this->loadDashboardData();
            session()->flash('message', 'Checked out successfully at ' . $now->format('h:i A'));
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.employee-dashboard');
    }
}
