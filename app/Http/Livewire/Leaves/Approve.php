<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use App\Models\Attendance;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Approve extends Component
{
    use WithPagination;

    public $leaveId = null;
    public $leaveDetails = null;
    public $rejectReason = '';
    public $selectedLeaveIds = [];
    public $bulkAction = '';

    public function mount()
    {
        $this->bulkAction = '';
    }

    public function viewLeave($id)
    {
        $this->leaveId = $id;
        $this->leaveDetails = Leave::with(['employee.user', 'employee.department'])
            ->find($id);
    }

    public function closeModal()
    {
        $this->leaveId = null;
        $this->leaveDetails = null;
        $this->rejectReason = '';
    }

    public function approveLeave()
    {
        if (!$this->leaveId) {
            return;
        }
        
        $leave = Leave::find($this->leaveId);
        
        if (!$leave) {
            session()->flash('error', 'Leave request not found.');
            $this->closeModal();
            return;
        }
        
        // Update leave status
        $leave->status = 'Approved';
        $leave->approved_by = Auth::id();
        $leave->save();
        
        // Create attendance records for the leave period
        $this->createAttendanceRecords($leave);
        
        session()->flash('success', 'Leave request approved successfully.');
        $this->closeModal();
    }

    public function rejectLeave()
    {
        if (!$this->leaveId) {
            return;
        }
        
        $this->validate([
            'rejectReason' => 'required|min:5',
        ]);
        
        $leave = Leave::find($this->leaveId);
        
        if (!$leave) {
            session()->flash('error', 'Leave request not found.');
            $this->closeModal();
            return;
        }
        
        // Update leave status
        $leave->status = 'Rejected';
        $leave->reason = $leave->reason . "\n\nRejection reason: " . $this->rejectReason;
        $leave->approved_by = Auth::id();
        $leave->save();
        
        session()->flash('success', 'Leave request rejected successfully.');
        $this->closeModal();
    }

    protected function createAttendanceRecords($leave)
    {
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Skip weekends if needed
            if (!$currentDate->isWeekend()) {
                // Check if attendance record already exists
                $attendance = Attendance::firstOrNew([
                    'employee_id' => $leave->employee_id,
                    'date' => $currentDate->toDateString(),
                ]);
                
                $attendance->status = 'On Leave';
                $attendance->save();
            }
            
            $currentDate->addDay();
        }
    }

    public function toggleSelect($leaveId)
    {
        $index = array_search($leaveId, $this->selectedLeaveIds);
        
        if ($index !== false) {
            unset($this->selectedLeaveIds[$index]);
            $this->selectedLeaveIds = array_values($this->selectedLeaveIds);
        } else {
            $this->selectedLeaveIds[] = $leaveId;
        }
    }

    public function toggleSelectAll($leaves)
    {
        if (count($this->selectedLeaveIds) === count($leaves)) {
            $this->selectedLeaveIds = [];
        } else {
            $this->selectedLeaveIds = collect($leaves)->pluck('id')->toArray();
        }
    }

    public function applyBulkAction()
    {
        if (empty($this->selectedLeaveIds) || $this->bulkAction === '') {
            return;
        }
        
        if ($this->bulkAction === 'approve') {
            $leaves = Leave::whereIn('id', $this->selectedLeaveIds)->get();
            
            foreach ($leaves as $leave) {
                $leave->status = 'Approved';
                $leave->approved_by = Auth::id();
                $leave->save();
                
                $this->createAttendanceRecords($leave);
            }
            
            session()->flash('success', count($leaves) . ' leave requests approved successfully.');
        } elseif ($this->bulkAction === 'reject') {
            Leave::whereIn('id', $this->selectedLeaveIds)
                ->update([
                    'status' => 'Rejected',
                    'approved_by' => Auth::id(),
                ]);
                
            session()->flash('success', count($this->selectedLeaveIds) . ' leave requests rejected successfully.');
        }
        
        $this->selectedLeaveIds = [];
        $this->bulkAction = '';
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = Leave::with(['employee.user', 'employee.department'])
            ->where('status', 'Pending');
            
        // Filter based on user role
        if ($user->isManager() || $user->isDepartmentHead()) {
            // Managers and Department Heads can only approve leaves for their team
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('reporting_manager_id', $user->id);
            });
        }
        
        $pendingLeaves = $query->orderBy('start_date')->paginate(10);

        return view('livewire.leaves.approve', [
            'pendingLeaves' => $pendingLeaves,
        ]);
    }
}
