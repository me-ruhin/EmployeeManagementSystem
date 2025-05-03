<?php

namespace App\Http\Livewire\Employees;

use App\Models\User;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class Create extends Component
{
    use WithFileUploads;

    // User details
    public $name;
    public $email;
    public $phone;
    public $password;
    public $passwordConfirmation;
    public $role = 'Employee';
    public $status = 'Active';
    public $profile_photo;

    // Employee details
    public $company_id;
    public $department_id;
    public $designation;
    public $joining_date;
    public $salary;
    public $bank_account_number;
    public $tax_id;
    public $reporting_manager_id;

    // Options for selects
    public $availableCompanies = [];
    public $availableDepartments = [];
    public $availableManagers = [];

    protected function rules()
    {
        return [
            // User rules
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:Employee,Manager,Department Head',
            'status' => 'required|in:Active,Inactive,Resigned,On Leave',
            'profile_photo' => 'nullable|image|max:1024',
            
            // Employee rules
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'bank_account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'reporting_manager_id' => 'nullable|exists:users,id',
        ];
    }

    public function mount()
    {
        $this->joining_date = now()->format('Y-m-d');
        $this->availableCompanies = Company::orderBy('name')->get();
        $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
            ->orderBy('name')
            ->get();
    }

    public function updatedCompanyId($value)
    {
        if ($value) {
            $this->availableDepartments = Department::where('company_id', $value)
                ->orderBy('name')
                ->get();
            
            // Update available managers based on company
            $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
                ->where('company_id', $value)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableDepartments = [];
            $this->department_id = null;
            $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedDepartmentId($value)
    {
        if ($value) {
            // Update available managers based on department
            $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
                ->where(function($query) use ($value) {
                    $query->where('department_id', $value)
                        ->orWhere('role', 'Department Head');
                })
                ->orderBy('name')
                ->get();
        }
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Create user
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'status' => $this->status,
                'company_id' => $this->company_id,
                'department_id' => $this->department_id,
            ];

            if ($this->profile_photo) {
                $profilePhotoPath = $this->profile_photo->store('profile-photos', 'public');
                $userData['profile_photo'] = $profilePhotoPath;
            }

            $user = User::create($userData);

            // Assign role
            $user->assignRole($this->role);

            // Create employee
            Employee::create([
                'user_id' => $user->id,
                'company_id' => $this->company_id,
                'department_id' => $this->department_id,
                'designation' => $this->designation,
                'joining_date' => $this->joining_date,
                'salary' => $this->salary,
                'bank_account_number' => $this->bank_account_number,
                'tax_id' => $this->tax_id,
                'reporting_manager_id' => $this->reporting_manager_id,
            ]);

            DB::commit();

            session()->flash('success', 'Employee created successfully.');
            
            return redirect()->route('employees.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.employees.create');
    }
}
