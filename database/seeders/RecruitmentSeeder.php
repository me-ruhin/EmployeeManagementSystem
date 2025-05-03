<?php

namespace Database\Seeders;

use App\Models\Recruitment;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RecruitmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $departments = Department::all();
        
        // Job positions by department
        $positionsByDepartment = [
            'Human Resources' => [
                'HR Specialist',
                'Talent Acquisition Specialist',
                'Employee Relations Manager',
                'Compensation Analyst'
            ],
            'Information Technology' => [
                'Software Developer',
                'System Administrator',
                'Network Engineer',
                'Data Analyst',
                'IT Project Manager'
            ],
            'Finance' => [
                'Financial Analyst',
                'Accountant',
                'Payroll Specialist',
                'Audit Manager'
            ],
            'Marketing' => [
                'Marketing Specialist',
                'Digital Marketing Manager',
                'Content Writer',
                'Brand Manager',
                'SEO Specialist'
            ],
            'Operations' => [
                'Operations Manager',
                'Quality Assurance Specialist',
                'Logistics Coordinator',
                'Supply Chain Analyst',
                'Facility Manager'
            ]
        ];
        
        // Status options
        $statuses = [
            'Open',
            'Interviewing',
            'On Hold',
            'Filled',
            'Cancelled'
        ];
        
        // Create recruitment records
        foreach ($departments as $department) {
            // Skip if no positions defined for this department
            if (!isset($positionsByDepartment[$department->name])) {
                continue;
            }
            
            // Get a random HR employee to be the hiring manager
            $hrDepartment = Department::where('name', 'Human Resources')->first();
            $hiringManager = null;
            
            if ($hrDepartment) {
                $hrEmployees = Employee::where('department_id', $hrDepartment->id)->get();
                if ($hrEmployees->isNotEmpty()) {
                    $hiringManager = $hrEmployees->random();
                }
            }
            
            // If no HR employee found, use department head
            if (!$hiringManager) {
                $departmentEmployees = Employee::where('department_id', $department->id)->get();
                $hiringManager = $departmentEmployees->isNotEmpty() ? $departmentEmployees->first() : null;
            }
            
            // Skip if no hiring manager
            if (!$hiringManager) {
                continue;
            }
            
            // Create 2-4 job positions per department
            $positions = collect($positionsByDepartment[$department->name])->shuffle()->take(rand(2, 4));
            
            foreach ($positions as $position) {
                // Job details
                $postedDate = Carbon::now()->subDays(rand(30, 120));
                $closingDate = $postedDate->copy()->addDays(rand(30, 60));
                $status = $statuses[array_rand($statuses)];
                
                // If status is filled, ensure dates make sense
                if ($status === 'Filled') {
                    $closingDate = min($closingDate, Carbon::now()->subDays(rand(5, 20)));
                }
                
                // Salary range
                $minSalary = rand(40, 80) * 1000;
                $maxSalary = $minSalary + rand(10, 30) * 1000;
                
                // Position requirements and responsibilities
                $requirements = 'Bachelor\'s degree in relevant field; ' . rand(2, 7) . '+ years experience; Strong communication skills; Ability to work in a team environment.';
                $responsibilities = 'Collaborate with cross-functional teams; Manage day-to-day operations; Report directly to department head; Develop and implement strategies.';
                
                // Create recruitment record
                Recruitment::create([
                    'id' => Str::uuid(),
                    'job_title' => $position,
                    'department_id' => $department->id,
                    'hiring_manager_id' => $hiringManager->id,
                    'job_description' => "We are seeking an experienced {$position} to join our {$department->name} team. This role reports directly to the {$department->name} Manager.",
                    'requirements' => $requirements,
                    'responsibilities' => $responsibilities,
                    'position_type' => rand(1, 10) <= 8 ? 'Full-time' : 'Contract',
                    'location' => rand(1, 10) <= 7 ? 'On-site' : 'Remote',
                    'min_salary' => $minSalary,
                    'max_salary' => $maxSalary,
                    'posted_date' => $postedDate->format('Y-m-d'),
                    'closing_date' => $closingDate->format('Y-m-d'),
                    'status' => $status,
                    'number_of_openings' => rand(1, 3),
                ]);
            }
        }
    }
}