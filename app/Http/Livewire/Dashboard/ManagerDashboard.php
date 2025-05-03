<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\Leave;
use Illuminate\Support\Facades\Auth;

class ManagerDashboard extends Component
{
    public $teamMembers = 0;
    public $activeProjects = 0; 
    public $pendingTasks = 0;
    public $pendingApprovals = 0;
    
    public $teamMembersList = [];
    public $projectsList = [];
    public $pendingTasksList = [];
    
    public function mount()
    {
        $user = Auth::user();
        $department = $user->department;
        
        // Count metrics
        $this->teamMembers = Employee::where('department_id', $department->id)->count();
        $this->activeProjects = Project::where('department_id', $department->id)
            ->where('status', 'Active')
            ->count();
        $this->pendingTasks = Task::whereHas('project', function($query) use ($department) {
            $query->where('department_id', $department->id);
        })
        ->where('status', 'Pending')
        ->count();
        $this->pendingApprovals = Leave::whereHas('employee', function($query) use ($department) {
            $query->where('department_id', $department->id);
        })
        ->where('status', 'Pending')
        ->count();
        
        // Get team members
        $this->teamMembersList = Employee::with('user')
            ->where('department_id', $department->id)
            ->get();
            
        // Get active projects
        $this->projectsList = Project::where('department_id', $department->id)
            ->where('status', 'Active')
            ->take(5)
            ->get();
            
        // Get pending tasks
        $this->pendingTasksList = Task::with('employee.user')
            ->whereHas('project', function($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->where('status', 'Pending')
            ->orderBy('priority', 'desc')
            ->take(10)
            ->get();
    }
    
    public function render()
    {
        return view('livewire.dashboard.manager-dashboard');
    }
}