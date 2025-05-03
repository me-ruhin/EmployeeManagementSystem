<?php

namespace App\Http\Livewire\Tasks;

use App\Models\Task;
use App\Models\Project;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $priority = '';
    public $project_id = '';
    public $sortField = 'deadline';
    public $sortDirection = 'asc';
    
    public $availableProjects = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'priority' => ['except' => ''],
        'project_id' => ['except' => ''],
        'sortField' => ['except' => 'deadline'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $user = Auth::user();
        
        // Load projects based on user role
        if ($user->isAdmin() || $user->isHR()) {
            // Admin and HR can see all projects
            $this->availableProjects = Project::orderBy('name')->get();
        } elseif ($user->isDepartmentHead() && $user->department_id) {
            // Department head can see department projects
            $this->availableProjects = Project::where('department_id', $user->department_id)
                ->orderBy('name')
                ->get();
        } elseif ($user->isManager() && $user->department_id) {
            // Manager can see department projects
            $this->availableProjects = Project::where('department_id', $user->department_id)
                ->orderBy('name')
                ->get();
        } else {
            // Regular employees can see projects they have tasks in
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $this->availableProjects = Project::whereHas('tasks', function($query) use ($employeeId) {
                    $query->where('assigned_to', $employeeId);
                })
                ->orderBy('name')
                ->get();
            } else {
                $this->availableProjects = collect();
            }
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updateTaskStatus(Task $task, $newStatus)
    {
        $task->status = $newStatus;
        $task->save();
        
        session()->flash('success', 'Task status updated successfully.');
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Task::with(['project', 'assignee.user'])
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->when($this->priority, function ($query) {
                return $query->where('priority', $this->priority);
            })
            ->when($this->project_id, function ($query) {
                return $query->where('project_id', $this->project_id);
            });
            
        // Apply filters based on user role
        if ($user->isEmployee() && !$user->isManager() && !$user->isDepartmentHead() && !$user->isAdmin() && !$user->isHR()) {
            // Regular employees can only see their assigned tasks
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->where('assigned_to', $employeeId);
            } else {
                $query->where('id', 0); // Force no results if no employee record
            }
        } elseif ($user->isDepartmentHead() && $user->department_id) {
            // Department heads can see tasks for their department
            $query->whereHas('project', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isManager() && $user->department_id) {
            // Managers can see tasks for their department
            $query->whereHas('project', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }
        
        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $tasks = $query->paginate(10);

        return view('livewire.tasks.index', [
            'tasks' => $tasks,
        ]);
    }
}
