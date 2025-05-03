<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Task;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboard extends Component
{
    public $pendingTasks = 0;
    public $completedTasks = 0;
    public $leaveBalance = 0;
    public $activeProjects = 0;
    
    public $recentTasks = [];
    public $leaveHistory = [];
    public $attendanceHistory = [];
    public $projectsList = [];
    
    public function mount()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if ($employee) {
            // Count metrics
            $this->pendingTasks = Task::where('employee_id', $employee->id)
                ->whereIn('status', ['Pending', 'In Progress'])
                ->count();
                
            $this->completedTasks = Task::where('employee_id', $employee->id)
                ->where('status', 'Completed')
                ->count();
                
            $this->leaveBalance = 20 - Leave::where('employee_id', $employee->id)
                ->whereYear('start_date', date('Y'))
                ->where('status', 'Approved')
                ->sum('total_days');
                
            $this->activeProjects = Project::whereHas('tasks', function($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                    ->whereIn('status', ['Pending', 'In Progress']);
            })->count();
            
            // Recent tasks
            $this->recentTasks = Task::where('employee_id', $employee->id)
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();
                
            // Leave history
            $this->leaveHistory = Leave::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            // Attendance history
            $this->attendanceHistory = Attendance::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->take(7)
                ->get();
                
            // Projects
            $this->projectsList = Project::whereHas('tasks', function($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })
            ->where('status', 'Active')
            ->take(4)
            ->get();
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.employee-dashboard');
    }
}