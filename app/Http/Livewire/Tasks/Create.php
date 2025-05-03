<?php

namespace App\Http\Livewire\Tasks;

use App\Models\Task;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $title;
    public $description;
    public $project_id;
    public $assigned_to;
    public $status = 'Pending';
    public $priority = 'Medium';
    public $deadline;
    
    public $department_id;
    public $availableProjects = [];
    public $availableEmployees = [];
    public $availableDepartments = [];

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:employees,id',
            'status' => 'required|in:Pending,In Progress,Completed,Cancelled',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'deadline' => 'required|date|after_or_equal:today',
        ];
    }

    public function mount()
    {
        $this->deadline = now()->format('Y-m-d');
        
        $user = Auth::user();
        
        // Load departments based on user role
        if ($user->isAdmin() || $user->isHR()) {
            // Admin and HR can see all departments
            $this->availableDepartments = Department::orderBy('name')->get();
        } elseif ($user->isDepartmentHead() || $user->isManager()) {
            // Department head and manager can only see their department
            if ($user->department_id) {
                $this->department_id = $user->department_id;
                $this->availableDepartments = Department::where('id', $user->department_id)->get();
            } else {
                $this->availableDepartments = Department::orderBy('name')->get();
            }
        }
        
        $this->loadProjectsAndEmployees();
    }

    public function updatedDepartmentId($value)
    {
        $this->loadProjectsAndEmployees();
    }

    public function updatedProjectId($value)
    {
        if ($value) {
            $project = Project::find($value);
            if ($project) {
                $this->department_id = $project->department_id;
                $this->loadProjectsAndEmployees();
            }
        }
    }

    protected function loadProjectsAndEmployees()
    {
        // Load projects based on selected department
        if ($this->department_id) {
            $this->availableProjects = Project::where('department_id', $this->department_id)
                ->orderBy('name')
                ->get();
                
            $this->availableEmployees = Employee::with('user')
                ->where('department_id', $this->department_id)
                ->whereHas('user', function($query) {
                    $query->where('status', 'Active');
                })
                ->get();
        } else {
            $this->availableProjects = [];
            $this->availableEmployees = [];
        }
    }

    public function save()
    {
        $this->validate();
        
        Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to,
            'status' => $this->status,
            'priority' => $this->priority,
            'deadline' => $this->deadline,
        ]);
        
        session()->flash('success', 'Task created successfully.');
        
        return redirect()->route('tasks.index');
    }

    public function render()
    {
        return view('livewire.tasks.create');
    }
}
