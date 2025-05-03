<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with 'Employee' or 'Department Head' roles
        $users = User::whereIn('role', ['Employee', 'Department Head'])->get();
        
        // Create employee profiles for each user
        foreach ($users as $user) {
            $department = Department::find($user->department_id);
            $reportingManagerId = $user->role === 'Employee' ? $department->head_id : null;
            
            // Determine salary based on role
            $baseSalary = $user->role === 'Department Head' ? 85000 : 55000;
            // Add some variation
            $salary = $baseSalary + (rand(-5000, 5000));
            
            Employee::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'department_id' => $user->department_id,
                'designation' => $user->role === 'Department Head' ? "{$department->name} Manager" : "{$department->name} Specialist",
                'joining_date' => now()->subMonths(rand(1, 36))->format('Y-m-d'), // Random joining date in the past 3 years
                'salary' => $salary,
                'bank_account_number' => 'ACCT-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'tax_id' => 'TAX-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'reporting_manager_id' => $reportingManagerId,
            ]);
        }
    }
}