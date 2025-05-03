<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();
        
        // For each employee, create 30 days of attendance records
        foreach ($employees as $employee) {
            // Generate attendance for the past 30 days (excluding weekends)
            for ($day = 30; $day >= 1; $day--) {
                $date = Carbon::now()->subDays($day);
                
                // Skip weekends (Saturday and Sunday)
                if ($date->isWeekend()) {
                    continue;
                }
                
                // Determine attendance status randomly
                $statusProbability = rand(1, 100);
                if ($statusProbability <= 5) {
                    // 5% chance of absence
                    $status = 'Absent';
                    $checkInTime = null;
                    $checkOutTime = null;
                } elseif ($statusProbability <= 10) {
                    // 5% chance of being on leave
                    $status = 'On Leave';
                    $checkInTime = null;
                    $checkOutTime = null;
                } elseif ($statusProbability <= 25) {
                    // 15% chance of being late
                    $status = 'Late';
                    $checkInTime = $date->copy()->setHour(9)->setMinute(rand(30, 59));
                    $checkOutTime = $date->copy()->setHour(17)->addMinutes(rand(0, 60));
                } else {
                    // 75% chance of being present
                    $status = 'Present';
                    $checkInTime = $date->copy()->setHour(8)->addMinutes(rand(45, 59));
                    $checkOutTime = $date->copy()->setHour(17)->addMinutes(rand(0, 30));
                }
                
                Attendance::create([
                    'id' => Str::uuid(),
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'status' => $status,
                ]);
            }
        }
    }
}