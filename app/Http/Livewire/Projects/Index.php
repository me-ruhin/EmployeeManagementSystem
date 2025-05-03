<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $department_id = '';
    public $company_id = '';
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    
    public $availableCompanies = [];
    public $availableDepartments = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'department_id' => ['except' => ''],
        'company_id' => ['except' => ''],
        'sortField' => ['except' => 'start_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->availableCompanies = Company::orderBy('name')->get();
        $this->loadDepartments();
    }

    public function updatedCompanyId($value)
    {
        $this->loadDepartments();
    }

    protected function loadDepartments()
    {
        if ($this->company_id) {
            $this->availableDepartments = Department::where('company_id', $this->company_id)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableDepartments = Department::orderBy('name')->get();
        }
        $this->department_id = '';
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

    public function render()
    {
        $user = Auth::user();
        
        $query = Project::with(['department', 'company'])
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->when($this->department_id, function ($query) {
                return $query->where('department_id', $this->department_id);
            })
            ->when($this->company_id, function ($query) {
                return $query->where('company_id', $this->company_id);
            });
            
        // Apply filters based on user role
        if ($user->isEmployee() && !$user->isManager() && !$user->isDepartmentHead() && !$user->isAdmin() && !$user->isHR()) {
            // Regular employees can see projects they are assigned to (via tasks)
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->whereHas('tasks', function($q) use ($employeeId) {
                    $q->where('assigned_to', $employeeId);
                });
            } else {
                $query->where('id', 0); // Force no results if no employee record
            }
        } elseif ($user->isDepartmentHead()) {
            // Department heads can see projects from their department
            $query->whereHas('department', function($q) use ($user) {
                $q->where('head_id', $user->id);
            });
        } elseif ($user->isManager() && $user->department_id) {
            // Managers can see projects from their department
            $query->where('department_id', $user->department_id);
        }
        
        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $projects = $query->paginate(10);

        return view('livewire.projects.index', [
            'projects' => $projects,
        ]);
    }
}
