<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            DepartmentSeeder::class,
            RoleSeeder::class, // Add RoleSeeder before UserSeeder
            UserSeeder::class,
            EmployeeSeeder::class,
            AttendanceSeeder::class,
            LeaveSeeder::class,
            PayrollSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            PerformanceEvaluationSeeder::class,
            RecruitmentSeeder::class,
            CandidateSeeder::class,
            AssetSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}