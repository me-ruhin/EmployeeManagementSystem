<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class Edit extends Component
{
    use WithFileUploads;

    public User $user;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $passwordConfirmation;
    public $role;
    public $status;
    public $company_id;
    public $department_id;
    public $profile_photo;
    public $new_profile_photo;

    public $availableCompanies = [];
    public $availableDepartments = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$this->user->id,
            'phone' => 'required|string|max:20|unique:users,phone,'.$this->user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:Admin,HR,Manager,Employee,Department Head',
            'status' => 'required|in:Active,Inactive,Resigned,On Leave',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'new_profile_photo' => 'nullable|image|max:1024',
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->status = $user->status;
        $this->company_id = $user->company_id;
        $this->department_id = $user->department_id;
        $this->profile_photo = $user->profile_photo;

        $this->availableCompanies = Company::orderBy('name')->get();
        
        if ($this->company_id) {
            $this->availableDepartments = Department::where('company_id', $this->company_id)
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedCompanyId($value)
    {
        if ($value) {
            $this->availableDepartments = Department::where('company_id', $value)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableDepartments = [];
            $this->department_id = null;
        }
    }

    public function save()
    {
        $this->validate();

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

        session()->flash('success', 'User updated successfully.');
        
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
