<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $status = '';
    public $type = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $startDate = '';
    public $endDate = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'type' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Leave::with(['employee.user', 'employee.department', 'approver'])
            ->select('leaves.*');
            
        // Apply filters based on user role
        if ($user->isEmployee() && !$user->isManager() && !$user->isDepartmentHead() && !$user->isHR() && !$user->isAdmin()) {
            // Regular employees can only see their own leaves
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            } else {
                $query->where('employee_id', 0); // Force no results if no employee record
            }
        } elseif ($user->isManager() || $user->isDepartmentHead()) {
            // Managers can see their team's leaves
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('reporting_manager_id', $user->id)
                  ->orWhere('employee_id', $user->employee->id ?? 0); // Include manager's own leaves
            });
        }
        
        // Apply status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        // Apply leave type filter
        if ($this->type) {
            $query->where('leave_type', $this->type);
        }
        
        // Apply date filters
        if ($this->startDate) {
            $query->where('start_date', '>=', Carbon::parse($this->startDate));
        }
        
        if ($this->endDate) {
            $query->where('end_date', '<=', Carbon::parse($this->endDate));
        }
            
        // Apply sorting
        if ($this->sortField === 'employee_name') {
            $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                  ->join('users', 'employees.user_id', '=', 'users.id')
                  ->orderBy('users.name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        $leaves = $query->paginate(10);

        return view('livewire.leaves.index', [
            'leaves' => $leaves,
        ]);
    }
}
