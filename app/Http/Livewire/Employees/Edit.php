<?php

namespace App\Http\Livewire\Employees;

use App\Models\User;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class Edit extends Component
{
    use WithFileUploads;

    public Employee $employee;
    public User $user;

    // User details
    public $name;
    public $email;
    public $phone;
    public $password;
    public $passwordConfirmation;
    public $role;
    public $status;
    public $profile_photo;
    public $new_profile_photo;

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
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $this->user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:Employee,Manager,Department Head',
            'status' => 'required|in:Active,Inactive,Resigned,On Leave',
            'new_profile_photo' => 'nullable|image|max:1024',
            
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

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->user = $employee->user;

        // Set user fields
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;
        $this->role = $this->user->role;
        $this->status = $this->user->status;
        $this->profile_photo = $this->user->profile_photo;

        // Set employee fields
        $this->company_id = $employee->company_id;
        $this->department_id = $employee->department_id;
        $this->designation = $employee->designation;
        $this->joining_date = $employee->joining_date->format('Y-m-d');
        $this->salary = $employee->salary;
        $this->bank_account_number = $employee->bank_account_number;
        $this->tax_id = $employee->tax_id;
        $this->reporting_manager_id = $employee->reporting_manager_id;

        // Load options
        $this->availableCompanies = Company::orderBy('name')->get();
        $this->loadDepartmentsAndManagers();
    }

    public function updatedCompanyId($value)
    {
        $this->loadDepartmentsAndManagers();
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

    protected function loadDepartmentsAndManagers()
    {
        if ($this->company_id) {
            $this->availableDepartments = Department::where('company_id', $this->company_id)
                ->orderBy('name')
                ->get();
            
            // Update available managers based on company
            $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
                ->where('company_id', $this->company_id)
                ->where('id', '!=', $this->user->id) // Exclude the current user
                ->orderBy('name')
                ->get();
        } else {
            $this->availableDepartments = [];
            $this->department_id = null;
            $this->availableManagers = User::whereIn('role', ['Manager', 'Department Head'])
                ->where('id', '!=', $this->user->id) // Exclude the current user
                ->orderBy('name')
                ->get();
        }
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Update user
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'role' => $this->role,
                'status' => $this->status,
                'company_id' => $this->company_id,
                'department_id' => $this->department_id,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            if ($this->new_profile_photo) {
                // Delete old profile photo if exists
                if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
                    Storage::disk('public')->delete($this->profile_photo);
                }
                
                $profilePhotoPath = $this->new_profile_photo->store('profile-photos', 'public');
                $userData['profile_photo'] = $profilePhotoPath;
            }

            $this->user->update($userData);

            // Update role if changed
            $previousRole = $this->user->getRoleNames()->first();
            if ($previousRole != $this->role) {
                // Remove previous roles
                $this->user->syncRoles([]);
                
                // Assign new role
                $this->user->assignRole($this->role);
            }

            // Update employee
            $this->employee->update([
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

            session()->flash('success', 'Employee updated successfully.');
            
            return redirect()->route('employees.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating employee: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.employees.edit');
    }
}
