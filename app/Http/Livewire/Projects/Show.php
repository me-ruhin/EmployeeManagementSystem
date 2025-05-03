<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithPagination;

    public Project $project;
    public $activeTab = 'overview';
    public $showTaskModal = false;
    
    // Task form fields
    public $title;
    public $description;
    public $assigned_to;
    public $status = 'Pending';
    public $priority = 'Medium';
    public $deadline;
    
    public $editingTask = null;
    public $taskStatusFilter = '';
    
    public $availableEmployees = [];

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
        'taskStatusFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:employees,id',
            'status' => 'required|in:Pending,In Progress,Completed,Cancelled',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'deadline' => 'required|date|after_or_equal:today',
        ];
    }

    public function mount(Project $project)
    {
        $this->project = $project->load(['department', 'company']);
        $this->deadline = now()->format('Y-m-d');
        
        // Load employees that can be assigned tasks (from the same department)
        $this->availableEmployees = Employee::with('user')
            ->where('department_id', $project->department_id)
            ->whereHas('user', function($query) {
                $query->where('status', 'Active');
            })
            ->get();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openTaskModal()
    {
        $this->resetTaskForm();
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->resetTaskForm();
    }

    public function editTask(Task $task)
    {
        $this->editingTask = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->assigned_to = $task->assigned_to;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->deadline = $task->deadline->format('Y-m-d');
        
        $this->showTaskModal = true;
    }

    public function resetTaskForm()
    {
        $this->editingTask = null;
        $this->title = '';
        $this->description = '';
        $this->assigned_to = '';
        $this->status = 'Pending';
        $this->priority = 'Medium';
        $this->deadline = now()->format('Y-m-d');
    }

    public function saveTask()
    {
        $this->validate();
        
        if ($this->editingTask) {
            $task = Task::find($this->editingTask);
            
            if (!$task) {
                session()->flash('error', 'Task not found.');
                return;
            }
            
            $task->update([
                'title' => $this->title,
                'description' => $this->description,
                'assigned_to' => $this->assigned_to,
                'status' => $this->status,
                'priority' => $this->priority,
                'deadline' => $this->deadline,
            ]);
            
            session()->flash('success', 'Task updated successfully.');
        } else {
            Task::create([
                'project_id' => $this->project->id,
                'title' => $this->title,
                'description' => $this->description,
                'assigned_to' => $this->assigned_to,
                'status' => $this->status,
                'priority' => $this->priority,
                'deadline' => $this->deadline,
            ]);
            
            session()->flash('success', 'Task created successfully.');
        }
        
        $this->closeTaskModal();
    }

    public function updateTaskStatus(Task $task, $newStatus)
    {
        $task->status = $newStatus;
        $task->save();
        
        session()->flash('success', 'Task status updated successfully.');
    }

    public function updateProjectStatus($newStatus)
    {
        $this->project->status = $newStatus;
        $this->project->save();
        
        session()->flash('success', 'Project status updated to ' . $newStatus);
    }

    public function render()
    {
        $tasksQuery = Task::with(['assignee.user'])
            ->where('project_id', $this->project->id);
            
        if ($this->taskStatusFilter) {
            $tasksQuery->where('status', $this->taskStatusFilter);
        }
        
        $tasks = $tasksQuery->orderBy('deadline')->paginate(10);
        
        // Calculate task statistics
        $taskStats = [
            'total' => Task::where('project_id', $this->project->id)->count(),
            'completed' => Task::where('project_id', $this->project->id)
                ->where('status', 'Completed')
                ->count(),
            'in_progress' => Task::where('project_id', $this->project->id)
                ->where('status', 'In Progress')
                ->count(),
            'pending' => Task::where('project_id', $this->project->id)
                ->where('status', 'Pending')
                ->count(),
            'cancelled' => Task::where('project_id', $this->project->id)
                ->where('status', 'Cancelled')
                ->count(),
        ];
        
        // Calculate completion percentage
        $completionPercentage = $taskStats['total'] > 0
            ? round(($taskStats['completed'] / $taskStats['total']) * 100)
            : 0;

        return view('livewire.projects.show', [
            'tasks' => $tasks,
            'taskStats' => $taskStats,
            'completionPercentage' => $completionPercentage,
        ]);
    }
}
