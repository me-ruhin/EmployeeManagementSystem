<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\Leave;
use App\Models\Attendance;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerDashboard extends Component
{
    public $teamMembers;
    public $teamCount;
    public $onLeaveToday;
    public $pendingLeaves;
    public $projects;
    public $upcomingDeadlines;
    public $attendanceSummary;
    
    public function mount()
    {
        $this->loadDashboardData();
    }
    
    public function loadDashboardData()
    {
        $user = Auth::user();
        
        // Get team members (direct reports)
        $employeeIds = Employee::where('reporting_manager_id', $user->id)->pluck('id')->toArray();
        $userIds = Employee::where('reporting_manager_id', $user->id)->pluck('user_id')->toArray();
        
        $this->teamMembers = Employee::with('user')
            ->where('reporting_manager_id', $user->id)
            ->get();
            
        $this->teamCount = $this->teamMembers->count();
        
        // Get on leave team members today
        $today = Carbon::today();
        $this->onLeaveToday = Attendance::whereIn('employee_id', $employeeIds)
            ->where('date', $today)
            ->where('status', 'On Leave')
            ->count();
            
        // Get pending leave requests
        $this->pendingLeaves = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get associated projects
        $departmentId = $user->department_id;
        $this->projects = Project::where('department_id', $departmentId)
            ->orderBy('end_date')
            ->get();
            
        // Get upcoming deadlines
        $this->upcomingDeadlines = Task::whereIn('assigned_to', $employeeIds)
            ->whereIn('status', ['Pending', 'In Progress'])
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->take(5)
            ->get();
            
        // Get attendance summary for today
        $this->attendanceSummary = [
            'present' => Attendance::whereIn('employee_id', $employeeIds)
                ->where('date', $today)
                ->where('status', 'Present')
                ->count(),
            'absent' => Attendance::whereIn('employee_id', $employeeIds)
                ->where('date', $today)
                ->where('status', 'Absent')
                ->count(),
            'late' => Attendance::whereIn('employee_id', $employeeIds)
                ->where('date', $today)
                ->where('status', 'Late')
                ->count(),
            'on_leave' => Attendance::whereIn('employee_id', $employeeIds)
                ->where('date', $today)
                ->where('status', 'On Leave')
                ->count(),
        ];
    }
    
    public function render()
    {
        return view('livewire.dashboard.manager-dashboard');
    }
}
