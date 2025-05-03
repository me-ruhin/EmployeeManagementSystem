<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $department_id = '';
    public $company_id = '';
    public $sortField = 'joining_date';
    public $sortDirection = 'desc';

    public $availableCompanies = [];
    public $availableDepartments = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'department_id' => ['except' => ''],
        'company_id' => ['except' => ''],
        'sortField' => ['except' => 'joining_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->availableCompanies = Company::orderBy('name')->get();
        $this->availableDepartments = Department::orderBy('name')->get();
    }

    public function updatedCompanyId($value)
    {
        if ($value) {
            $this->availableDepartments = Department::where('company_id', $value)
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
        $employees = Employee::with(['user', 'department', 'company'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                })
                ->orWhere('designation', 'like', '%' . $this->search . '%');
            })
            ->when($this->department_id, function ($query) {
                $query->where('department_id', $this->department_id);
            })
            ->when($this->company_id, function ($query) {
                $query->where('company_id', $this->company_id);
            });

        // Apply sorting based on the selected field
        if ($this->sortField === 'name') {
            $employees = $employees->join('users', 'employees.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->sortDirection)
                ->select('employees.*');
        } else {
            $employees = $employees->orderBy($this->sortField, $this->sortDirection);
        }

        $employees = $employees->paginate(10);

        return view('livewire.employees.index', [
            'employees' => $employees,
        ]);
    }
}
