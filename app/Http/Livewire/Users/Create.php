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

class Create extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $password;
    public $passwordConfirmation;
    public $role = 'Employee';
    public $status = 'Active';
    public $company_id;
    public $department_id;
    public $profile_photo;

    public $availableCompanies = [];
    public $availableDepartments = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:Admin,HR,Manager,Employee,Department Head',
            'status' => 'required|in:Active,Inactive,Resigned,On Leave',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'profile_photo' => 'nullable|image|max:1024',
        ];
    }

    public function mount()
    {
        $this->availableCompanies = Company::orderBy('name')->get();
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

        if ($this->role !== 'Admin') {
            // Assign the appropriate role using spatie permissions
            $user->assignRole($this->role);
        } else {
            $user->assignRole('Admin');
        }

        session()->flash('success', 'User created successfully.');
        
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.create');
    }
}
