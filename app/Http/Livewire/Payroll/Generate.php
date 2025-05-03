<?php

namespace App\Http\Livewire\Payroll;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Department;
use App\Models\Company;
use Livewire\Component;
use Carbon\Carbon;

class Generate extends Component
{
    public $month;
    public $year;
    public $company_id = '';
    public $department_id = '';
    public $employeeId = '';
    
    public $employees = [];
    public $selectedEmployees = [];
    public $generatedPayrolls = [];
    public $processingStatus = '';
    
    public $availableCompanies = [];
    public $availableDepartments = [];
    
    protected $rules = [
        'month' => 'required|numeric|min:1|max:12',
        'year' => 'required|numeric|min:2000|max:2100',
        'company_id' => 'required',
    ];

    public function mount()
    {
        $this->month = Carbon::now()->subMonth()->format('m');
        $this->year = Carbon::now()->subMonth()->format('Y');
        
        $this->availableCompanies = Company::orderBy('name')->get();
        
        if (count($this->availableCompanies) > 0) {
            $this->company_id = $this->availableCompanies->first()->id;
            $this->updatedCompanyId($this->company_id);
        }
    }

    public function updatedCompanyId($value)
    {
        $this->availableDepartments = Department::where('company_id', $value)
            ->orderBy('name')
            ->get();
        
        $this->department_id = '';
        $this->loadEmployees();
    }

    public function updatedDepartmentId()
    {
        $this->loadEmployees();
    }

    protected function loadEmployees()
    {
        if (!$this->company_id) {
            $this->employees = [];
            return;
        }
        
        $query = Employee::with(['user', 'department'])
            ->where('company_id', $this->company_id);
            
        if ($this->department_id) {
            $query->where('department_id', $this->department_id);
        }
        
        $this->employees = $query->get();
        
        // Initialize all employees as selected
        $this->selectedEmployees = $this->employees->pluck('id')->toArray();
    }

    public function selectAll()
    {
        $this->selectedEmployees = $this->employees->pluck('id')->toArray();
    }

    public function deselectAll()
    {
        $this->selectedEmployees = [];
    }

    public function generatePayrolls()
    {
        $this->validate();
        
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Please select at least one employee.');
            return;
        }
        
        $this->processingStatus = 'Processing...';
        $this->generatedPayrolls = [];
        
        $monthYear = $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);
        
        foreach ($this->selectedEmployees as $employeeId) {
            $employee = Employee::with(['user'])->find($employeeId);
            
            if (!$employee) {
                continue;
            }
            
            // Check if payroll already exists for this employee and month
            $existingPayroll = Payroll::where('employee_id', $employeeId)
                ->where('salary_month', $monthYear)
                ->first();
                
            if ($existingPayroll) {
                $this->generatedPayrolls[] = [
                    'employee' => $employee->user->name,
                    'status' => 'Skipped (Already exists)',
                    'payroll' => $existingPayroll,
                ];
                continue;
            }
            
            // Calculate attendance
            $present = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Present')
                ->count();
                
            $absent = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Absent')
                ->count();
                
            $onLeave = Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'On Leave')
                ->count();
                
            // Calculate leave deductions
            $approvedLeaves = Leave::where('employee_id', $employeeId)
                ->where('status', 'Approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->get();
                
            $unpaidLeaveCount = 0;
            foreach ($approvedLeaves as $leave) {
                // Count only unpaid leaves (e.g., beyond allowed limits)
                // This is a simplified calculation - in a real system, you'd need more complex logic
                if ($leave->leave_type === 'Sick' && $leave->getDaysAttribute() > 10) {
                    $unpaidLeaveCount += $leave->getDaysAttribute() - 10;
                }
            }
            
            // Calculate basic salary
            $dailyRate = $employee->salary / $workingDays;
            $deductions = $unpaidLeaveCount * $dailyRate;
            
            // Default bonus as 0
            $bonus = 0;
            
            // Calculate net salary
            $netSalary = $employee->salary + $bonus - $deductions;
            
            // Create payroll record
            $payroll = Payroll::create([
                'employee_id' => $employeeId,
                'salary_month' => $monthYear,
                'basic_salary' => $employee->salary,
                'bonus' => $bonus,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'status' => 'Pending',
            ]);
            
            $this->generatedPayrolls[] = [
                'employee' => $employee->user->name,
                'status' => 'Generated',
                'payroll' => $payroll,
            ];
        }
        
        $this->processingStatus = 'Complete';
        session()->flash('success', count($this->generatedPayrolls) . ' payroll records generated successfully.');
    }

    protected function calculateWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Count only weekdays (Monday to Friday)
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            
            $current->addDay();
        }
        
        return $workingDays;
    }

    public function render()
    {
        return view('livewire.payroll.generate');
    }
}
