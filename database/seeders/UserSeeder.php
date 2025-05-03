<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first company and its departments
        $company = Company::first();
        $departments = Department::where('company_id', $company->id)->get();
        
        // Create Admin User
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '+1-555-111-0001',
            'role' => 'Admin',
            'status' => 'Active',
            'company_id' => $company->id,
            'department_id' => null,
            'profile_photo' => null,
        ]);
        // Assign Spatie role to user
        $adminUser->assignRole('Admin');
        
        // Create HR Manager
        $hrDepartment = $departments->where('name', 'Human Resources')->first();
        $hrManager = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '+1-555-111-0002',
            'role' => 'HR',
            'status' => 'Active',
            'company_id' => $company->id,
            'department_id' => $hrDepartment->id,
            'profile_photo' => null,
        ]);
        // Assign Spatie role to user
        $hrManager->assignRole('HR');
        
        // Update HR department head
        $hrDepartment->update(['head_id' => $hrManager->id]);
        
        // Create Department Heads for other departments
        foreach ($departments as $department) {
            if ($department->name != 'Human Resources') {
                $departmentHead = User::create([
                    'name' => "{$department->name} Manager",
                    'email' => strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'phone' => '+1-555-111-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                    'role' => 'Department Head',
                    'status' => 'Active',
                    'company_id' => $company->id,
                    'department_id' => $department->id,
                    'profile_photo' => null,
                ]);
                // Assign Spatie role to user
                $departmentHead->assignRole('Department Head');
                
                // Update department head
                $department->update(['head_id' => $departmentHead->id]);
            }
        }
        
        // Create regular employees (3 for each department)
        foreach ($departments as $department) {
            for ($i = 1; $i <= 3; $i++) {
                $employee = User::create([
                    'name' => "Employee {$department->name} {$i}",
                    'email' => "employee{$i}." . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'phone' => '+1-555-222-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                    'role' => 'Employee',
                    'status' => 'Active',
                    'company_id' => $company->id,
                    'department_id' => $department->id,
                    'profile_photo' => null,
                ]);
                // Assign Spatie role to user
                $employee->assignRole('Employee');
            }
        }
    }
}