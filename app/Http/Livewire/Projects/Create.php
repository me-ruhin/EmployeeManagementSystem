<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $name;
    public $description;
    public $company_id;
    public $department_id;
    public $start_date;
    public $end_date;
    public $status = 'Ongoing';
    
    public $availableCompanies = [];
    public $availableDepartments = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Ongoing,Completed,Cancelled',
        ];
    }

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        
        $user = Auth::user();
        
        // Load companies
        if ($user->isAdmin() || $user->isHR()) {
            $this->availableCompanies = Company::orderBy('name')->get();
        } else {
            // For managers and department heads, only show their company
            if ($user->company_id) {
                $this->company_id = $user->company_id;
                $this->availableCompanies = Company::where('id', $user->company_id)->get();
            } else {
                $this->availableCompanies = Company::orderBy('name')->get();
            }
        }
        
        $this->loadDepartments();
    }

    public function updatedCompanyId($value)
    {
        $this->loadDepartments();
    }

    protected function loadDepartments()
    {
        $user = Auth::user();
        
        if ($this->company_id) {
            // For manager or department head, only show their department
            if (($user->isManager() || $user->isDepartmentHead()) && $user->department_id) {
                $this->department_id = $user->department_id;
                $this->availableDepartments = Department::where('id', $user->department_id)->get();
            } else {
                $this->availableDepartments = Department::where('company_id', $this->company_id)
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $this->availableDepartments = [];
            $this->department_id = null;
        }
    }

    public function save()
    {
        $this->validate();
        
        Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'company_id' => $this->company_id,
            'department_id' => $this->department_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ]);
        
        session()->flash('success', 'Project created successfully.');
        
        return redirect()->route('projects.index');
    }

    public function render()
    {
        return view('livewire.projects.create');
    }
}
