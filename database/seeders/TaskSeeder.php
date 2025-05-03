<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all projects
        $projects = Project::all();
        
        // Task statuses and priorities
        $statuses = ['Not Started', 'In Progress', 'Under Review', 'Completed'];
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        
        // Task names by project type (based on project name keywords)
        $taskTemplates = [
            'Onboarding' => [
                'Create onboarding checklist',
                'Design welcome package',
                'Create training schedule',
                'Develop orientation presentation',
                'Set up buddy system'
            ],
            'Performance' => [
                'Define performance metrics',
                'Create evaluation forms',
                'Train managers on evaluation process',
                'Schedule review meetings',
                'Develop feedback process'
            ],
            'ERP' => [
                'System requirements analysis',
                'Vendor selection',
                'Data migration planning',
                'Test environment setup',
                'User training development',
                'Go-live strategy'
            ],
            'Security' => [
                'Security audit',
                'Vulnerability assessment',
                'Policy documentation',
                'Employee training preparation',
                'Security monitoring setup'
            ],
            'Marketing' => [
                'Market research',
                'Campaign strategy development',
                'Content creation',
                'Social media planning',
                'Analytics setup',
                'Performance tracking'
            ],
            'Financial' => [
                'Financial data analysis',
                'Process documentation',
                'Automation requirements',
                'Report template design',
                'User acceptance testing'
            ],
            'Supply Chain' => [
                'Current process mapping',
                'Vendor analysis',
                'Improvement identification',
                'Implementation planning',
                'Performance metric definition'
            ]
        ];
        
        // Default task list for any project type not matched
        $defaultTasks = [
            'Project kickoff meeting',
            'Requirements gathering',
            'Resource allocation',
            'Development phase',
            'Quality assurance',
            'Stakeholder review',
            'Final implementation',
            'Project documentation'
        ];
        
        foreach ($projects as $project) {
            // Determine which task template to use based on project name keywords
            $selectedTemplate = $defaultTasks;
            
            foreach ($taskTemplates as $keyword => $tasks) {
                if (stripos($project->name, $keyword) !== false) {
                    $selectedTemplate = $tasks;
                    break;
                }
            }
            
            // Get employees from the same department
            $employees = Employee::where('department_id', $project->department_id)->get();
            
            // Skip if no employees found
            if ($employees->isEmpty()) {
                continue;
            }
            
            // Create 4-8 tasks for each project
            $numberOfTasks = count($selectedTemplate);
            for ($i = 0; $i < $numberOfTasks; $i++) {
                // Skip some tasks randomly for variety
                if (rand(1, 10) > 8) {
                    continue;
                }
                
                // Determine dates relative to project timeline
                $projectStartDate = Carbon::parse($project->start_date);
                $projectEndDate = $project->actual_end_date 
                    ? Carbon::parse($project->actual_end_date) 
                    : Carbon::parse($project->estimated_end_date);
                
                // Task dates - spread across project timeline
                $taskStartOffset = ($i / $numberOfTasks) * $projectStartDate->diffInDays($projectEndDate);
                $taskStartDate = $projectStartDate->copy()->addDays($taskStartOffset);
                
                // Due date is between start date and a portion of the way to project end
                $maxDuration = max(5, ceil(($projectEndDate->diffInDays($taskStartDate)) / 2));
                $dueDate = $taskStartDate->copy()->addDays(rand(3, $maxDuration));
                
                // Ensure due date is not after project end date
                if ($dueDate->gt($projectEndDate)) {
                    $dueDate = $projectEndDate->copy()->subDays(rand(0, 5));
                }
                
                // Assign random employee from department
                $assignedEmployeeId = $employees->random()->id;
                
                // Determine status based on dates
                $status = '';
                $completionDate = null;
                
                if ($dueDate->lt(Carbon::now())) {
                    // Past due date
                    $statusRoll = rand(1, 10);
                    if ($statusRoll <= 7) {
                        // 70% chance it's completed
                        $status = 'Completed';
                        $completionDate = $dueDate->copy()->subDays(rand(0, 5))->format('Y-m-d');
                    } elseif ($statusRoll <= 9) {
                        // 20% it's still in progress (overdue)
                        $status = 'In Progress';
                    } else {
                        // 10% not even started (problem!)
                        $status = 'Not Started';
                    }
                } else {
                    // Future due date
                    $statusRoll = rand(1, 10);
                    if ($statusRoll <= 3) {
                        $status = 'Not Started';
                    } elseif ($statusRoll <= 7) {
                        $status = 'In Progress';
                    } elseif ($statusRoll <= 9) {
                        $status = 'Under Review';
                    } else {
                        $status = 'Completed';
                        $completionDate = Carbon::now()->subDays(rand(1, 5))->format('Y-m-d');
                    }
                }
                
                Task::create([
                    'id' => Str::uuid(),
                    'project_id' => $project->id,
                    'title' => $selectedTemplate[$i],
                    'description' => 'Task details for ' . $selectedTemplate[$i],
                    'assigned_to' => $assignedEmployeeId,
                    'start_date' => $taskStartDate->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => $status,
                    'priority' => $priorities[array_rand($priorities)],
                    'completion_date' => $completionDate,
                ]);
            }
        }
    }
}