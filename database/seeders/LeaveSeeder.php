<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees and their managers
        $employees = Employee::with('user')->get();
        
        // Leave types
        $leaveTypes = [
            'Annual Leave', 
            'Sick Leave', 
            'Personal Leave', 
            'Emergency Leave',
            'Maternity Leave',
            'Paternity Leave'
        ];
        
        // Create leave requests for each employee (1-3 per employee)
        foreach ($employees as $employee) {
            $numLeaves = rand(1, 3);
            
            for ($i = 0; $i < $numLeaves; $i++) {
                // Random start date (between 90 days ago and 30 days in future)
                $startDate = Carbon::now()->subDays(rand(0, 90));
                
                // Random duration (1-7 days)
                $duration = rand(1, 7);
                $endDate = (clone $startDate)->addDays($duration - 1);
                
                // Determine status based on date
                $status = '';
                $approved_by_id = null;
                
                if ($startDate->isFuture()) {
                    // Leave in the future
                    $statusProbability = rand(1, 100);
                    if ($statusProbability <= 70) {
                        $status = 'Pending';
                    } else {
                        $status = 'Approved';
                        $approved_by_id = $employee->reporting_manager_id;
                    }
                } else {
                    // Leave in the past
                    $statusProbability = rand(1, 100);
                    if ($statusProbability <= 5) {
                        $status = 'Rejected';
                    } else {
                        $status = 'Approved';
                        $approved_by_id = $employee->reporting_manager_id;
                    }
                }
                
                Leave::create([
                    'id' => Str::uuid(),
                    'employee_id' => $employee->id,
                    'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'reason' => "Leave request for " . $duration . " days",
                    'status' => $status,
                    'submitted_at' => $startDate->subDays(rand(5, 15))->format('Y-m-d H:i:s'),
                    'approved_by_id' => $approved_by_id,
                    'approved_at' => $status === 'Approved' ? $startDate->addDays(rand(1, 3))->format('Y-m-d H:i:s') : null,
                ]);
            }
        }
    }
}