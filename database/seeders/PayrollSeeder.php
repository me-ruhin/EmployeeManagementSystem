<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();
        
        // Create payroll records for the last 3 months
        for ($month = 3; $month >= 1; $month--) {
            $payrollDate = Carbon::now()->subMonths($month);
            $payrollMonth = $payrollDate->format('F Y');
            $startDate = $payrollDate->copy()->startOfMonth();
            $endDate = $payrollDate->copy()->endOfMonth();
            
            foreach ($employees as $employee) {
                // Basic salary from employee record
                $basicSalary = $employee->salary;
                
                // Calculate allowances (10-15% of basic salary)
                $allowances = $basicSalary * (rand(10, 15) / 100);
                
                // Calculate deductions (5-10% of basic salary)
                $deductions = $basicSalary * (rand(5, 10) / 100);
                
                // Calculate tax (15-20% of basic salary)
                $tax = $basicSalary * (rand(15, 20) / 100);
                
                // Calculate net salary
                $netSalary = $basicSalary + $allowances - $deductions - $tax;
                
                // Create payroll record
                Payroll::create([
                    'id' => Str::uuid(),
                    'employee_id' => $employee->id,
                    'payroll_date' => $endDate->format('Y-m-d'),
                    'month' => $payrollMonth,
                    'basic_salary' => $basicSalary,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'tax' => $tax,
                    'net_salary' => $netSalary,
                    'payment_status' => $month === 1 ? 'Processing' : 'Paid',
                    'payment_method' => 'Bank Transfer',
                    'payment_reference' => 'REF-' . strtoupper(Str::random(8)),
                ]);
            }
        }
    }
}