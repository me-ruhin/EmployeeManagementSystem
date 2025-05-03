<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            // Create HR Department
            Department::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Human Resources',
                'description' => 'Responsible for recruiting, onboarding, training, and employee relations.',
                'head_id' => null, // Will be updated after users are created
            ]);

            // Create IT Department
            Department::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Information Technology',
                'description' => 'Manages infrastructure, software development, and technical support.',
                'head_id' => null,
            ]);

            // Create Finance Department
            Department::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Finance',
                'description' => 'Manages accounting, budgeting, and financial reporting.',
                'head_id' => null,
            ]);

            // Create Marketing Department
            Department::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Marketing',
                'description' => 'Handles advertising, promotions, and brand management.',
                'head_id' => null,
            ]);

            // Create Operations Department
            Department::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Operations',
                'description' => 'Oversees daily business activities and process improvements.',
                'head_id' => null,
            ]);
        }
    }
}