<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $departments = Department::all();
        
        // Project names and descriptions for each department
        $projectsByDepartment = [
            'Human Resources' => [
                ['name' => 'Employee Onboarding Process Improvement', 'description' => 'Streamline and enhance the employee onboarding process to improve efficiency and new hire experience'],
                ['name' => 'Performance Review System Implementation', 'description' => 'Implement a comprehensive performance review system with clear metrics and feedback mechanisms'],
                ['name' => 'Employee Benefits Analysis', 'description' => 'Analyze current benefits package and propose improvements based on competitive market analysis']
            ],
            'Information Technology' => [
                ['name' => 'ERP System Implementation', 'description' => 'Deploy and configure a new enterprise resource planning system to streamline business operations'],
                ['name' => 'Cybersecurity Enhancement Initiative', 'description' => 'Improve organizational cybersecurity posture through infrastructure upgrades and employee training'],
                ['name' => 'Cloud Migration Strategy', 'description' => 'Develop and execute a strategy to migrate on-premises applications to cloud infrastructure']
            ],
            'Finance' => [
                ['name' => 'Cost Reduction Analysis', 'description' => 'Identify opportunities for cost reduction across all business units'],
                ['name' => 'Financial Reporting Automation', 'description' => 'Automate financial reporting processes to improve accuracy and reduce time investment'],
                ['name' => 'Investment Portfolio Optimization', 'description' => 'Review and optimize the company investment portfolio for better returns']
            ],
            'Marketing' => [
                ['name' => 'Digital Marketing Campaign', 'description' => 'Design and implement a comprehensive digital marketing strategy across multiple platforms'],
                ['name' => 'Brand Refresh Initiative', 'description' => 'Update brand identity and messaging to align with current market position'],
                ['name' => 'Customer Engagement Platform', 'description' => 'Implement a new customer engagement platform to improve interactions and loyalty']
            ],
            'Operations' => [
                ['name' => 'Supply Chain Optimization', 'description' => 'Analyze and improve the efficiency of the supply chain process'],
                ['name' => 'Quality Management System', 'description' => 'Implement a comprehensive quality management system to ensure product consistency'],
                ['name' => 'Facility Expansion Planning', 'description' => 'Plan and oversee the expansion of production facilities to meet growing demand']
            ]
        ];
        
        // Project statuses
        $statuses = ['Not Started', 'In Progress', 'On Hold', 'Completed'];
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        
        foreach ($departments as $department) {
            // Skip if no projects defined for this department
            if (!isset($projectsByDepartment[$department->name])) {
                continue;
            }
            
            // Get employees in this department
            $employees = Employee::where('department_id', $department->id)->get();
            
            // Skip if no employees in this department
            if ($employees->isEmpty()) {
                continue;
            }
            
            // Get department manager
            $manager = $employees->first(function ($employee) use ($department) {
                return $employee->user_id == $department->head_id;
            });
            
            foreach ($projectsByDepartment[$department->name] as $projectInfo) {
                // Determine random dates
                $startDate = Carbon::now()->subMonths(rand(1, 6));
                $estimatedEndDate = $startDate->copy()->addMonths(rand(3, 8));
                
                // Determine status
                $status = $statuses[array_rand($statuses)];
                
                // If status is completed, set actual end date
                $actualEndDate = null;
                if ($status === 'Completed') {
                    $actualEndDate = $startDate->copy()->addMonths(rand(2, 6));
                }
                
                Project::create([
                    'id' => Str::uuid(),
                    'name' => $projectInfo['name'],
                    'description' => $projectInfo['description'],
                    'start_date' => $startDate->format('Y-m-d'),
                    'estimated_end_date' => $estimatedEndDate->format('Y-m-d'),
                    'actual_end_date' => $actualEndDate ? $actualEndDate->format('Y-m-d') : null,
                    'status' => $status,
                    'priority' => $priorities[array_rand($priorities)],
                    'budget' => rand(10000, 100000),
                    'department_id' => $department->id,
                    'project_manager_id' => $manager ? $manager->id : ($employees->isNotEmpty() ? $employees->random()->id : null),
                ]);
            }
        }
    }
}