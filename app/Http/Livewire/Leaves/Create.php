<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Create extends Component
{
    public $leave_type = 'Sick';
    public $start_date;
    public $end_date;
    public $reason;
    public $available_leave_types = [
        'Sick', 'Casual', 'Annual', 'Maternity'
    ];

    protected $rules = [
        'leave_type' => 'required|in:Sick,Casual,Annual,Maternity',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|min:5|max:500',
    ];

    public function mount()
    {
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $this->validate();
        
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            session()->flash('error', 'No employee record found. Cannot apply for leave.');
            return;
        }
        
        // Check for overlapping leaves
        $existingLeave = Leave::where('employee_id', $employee->id)
            ->where(function($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function($q) {
                        $q->where('start_date', '<=', $this->start_date)
                          ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->where('status', '!=', 'Rejected')
            ->first();
            
        if ($existingLeave) {
            session()->flash('error', 'You already have a leave request for the selected dates.');
            return;
        }
        
        // Create leave request
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'status' => 'Pending',
        ]);
        
        session()->flash('success', 'Leave request submitted successfully.');
        
        // Reset form
        $this->reset(['reason']);
        $this->leave_type = 'Sick';
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        // Get basic leave statistics for the user
        $user = Auth::user();
        $employee = $user->employee;
        
        $leaveStats = null;
        
        if ($employee) {
            $currentYear = Carbon::now()->year;
            
            // Count used leaves by type for the current year
            $usedLeaves = Leave::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->whereYear('start_date', $currentYear)
                ->selectRaw('leave_type, sum(datediff(end_date, start_date) + 1) as days')
                ->groupBy('leave_type')
                ->pluck('days', 'leave_type')
                ->toArray();
                
            // Define leave limits (these could come from a settings table in a real application)
            $leaveLimits = [
                'Sick' => 10,
                'Casual' => 5,
                'Annual' => 20,
                'Maternity' => 90,
            ];
            
            // Calculate remaining leaves
            $leaveStats = collect($leaveLimits)->map(function ($limit, $type) use ($usedLeaves) {
                $used = $usedLeaves[$type] ?? 0;
                return [
                    'type' => $type,
                    'limit' => $limit,
                    'used' => $used,
                    'remaining' => max(0, $limit - $used)
                ];
            });
        }
        
        return view('livewire.leaves.create', [
            'leaveStats' => $leaveStats,
        ]);
    }
}
