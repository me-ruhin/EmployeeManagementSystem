<?php

namespace App\Http\Livewire\Payroll;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $company_id = '';
    public $department_id = '';
    public $status = '';
    public $month = '';
    public $year = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $availableCompanies = [];
    public $availableDepartments = [];
    
    protected $queryString = [
        'company_id' => ['except' => ''],
        'department_id' => ['except' => ''],
        'status' => ['except' => ''],
        'month' => ['except' => ''],
        'year' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->month = Carbon::now()->format('m');
        $this->year = Carbon::now()->format('Y');
        
        $this->availableCompanies = Company::orderBy('name')->get();
        $this->availableDepartments = Department::orderBy('name')->get();
    }

    public function updatedCompanyId($value)
    {
        if ($value) {
            $this->availableDepartments = Department::where('company_id', $value)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableDepartments = Department::orderBy('name')->get();
        }
        $this->department_id = '';
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function processPayment($payrollId)
    {
        $payroll = Payroll::find($payrollId);
        
        if (!$payroll) {
            session()->flash('error', 'Payroll record not found.');
            return;
        }
        
        $payroll->status = 'Paid';
        $payroll->payment_date = now();
        $payroll->save();
        
        session()->flash('success', 'Payment processed successfully.');
    }

    public function processSelected()
    {
        $monthYear = $this->year . '-' . $this->month;
        
        $query = Payroll::where('status', 'Pending')
            ->where('salary_month', 'like', $monthYear . '%');
            
        if ($this->company_id) {
            $query->whereHas('employee', function($q) {
                $q->where('company_id', $this->company_id);
            });
        }
        
        if ($this->department_id) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->department_id);
            });
        }
        
        $count = $query->count();
        
        if ($count === 0) {
            session()->flash('info', 'No pending payrolls found for the selected criteria.');
            return;
        }
        
        $query->update([
            'status' => 'Paid',
            'payment_date' => now(),
        ]);
        
        session()->flash('success', $count . ' payroll records processed successfully.');
    }

    public function render()
    {
        $monthYear = $this->year . '-' . $this->month;
        
        $query = Payroll::with(['employee.user', 'employee.department'])
            ->when($this->company_id, function($q) {
                $q->whereHas('employee', function($query) {
                    $query->where('company_id', $this->company_id);
                });
            })
            ->when($this->department_id, function($q) {
                $q->whereHas('employee', function($query) {
                    $query->where('department_id', $this->department_id);
                });
            })
            ->when($this->status, function($q) {
                $q->where('status', $this->status);
            })
            ->when($monthYear, function($q) use ($monthYear) {
                $q->where('salary_month', 'like', $monthYear . '%');
            });
            
        // Apply sorting
        if ($this->sortField === 'employee_name') {
            $query->join('employees', 'payrolls.employee_id', '=', 'employees.id')
                  ->join('users', 'employees.user_id', '=', 'users.id')
                  ->orderBy('users.name', $this->sortDirection)
                  ->select('payrolls.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        $payrolls = $query->paginate(10);
        
        // Calculate summary statistics
        $summary = [
            'total' => $payrolls->total(),
            'paid' => $query->where('status', 'Paid')->count(),
            'pending' => $query->where('status', 'Pending')->count(),
            'total_amount' => $query->sum('net_salary'),
        ];

        return view('livewire.payroll.index', [
            'payrolls' => $payrolls,
            'summary' => $summary,
        ]);
    }
}
