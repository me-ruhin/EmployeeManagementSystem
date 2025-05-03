<?php

namespace App\Http\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\Employee;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckInOut extends Component
{
    public $message = '';
    public $status = '';
    public $lastCheckIn = null;
    public $lastCheckOut = null;
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $attendance = null;
    public $employee = null;
    public $todayAttendance = null;

    public function mount()
    {
        $this->loadEmployeeData();
    }

    protected function loadEmployeeData()
    {
        $user = Auth::user();
        $this->employee = $user->employee;
        
        if (!$this->employee) {
            $this->message = 'No employee record found. Please contact HR.';
            $this->status = 'error';
            return;
        }
        
        $today = Carbon::today();
        $this->todayAttendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $today)
            ->first();
            
        if ($this->todayAttendance) {
            $this->lastCheckIn = $this->todayAttendance->check_in_time;
            $this->lastCheckOut = $this->todayAttendance->check_out_time;
            
            // Determine if check-in/check-out is possible
            $this->canCheckIn = !$this->lastCheckIn;
            $this->canCheckOut = $this->lastCheckIn && !$this->lastCheckOut;
        } else {
            $this->canCheckIn = true;
            $this->canCheckOut = false;
        }
    }

    public function checkIn()
    {
        if (!$this->employee) {
            $this->message = 'No employee record found. Please contact HR.';
            $this->status = 'error';
            return;
        }
        
        $now = Carbon::now();
        $today = Carbon::today();
        
        // Check if already checked in
        if ($this->todayAttendance && $this->todayAttendance->check_in_time) {
            $this->message = 'You have already checked in today at ' . $this->todayAttendance->check_in_time->format('h:i A');
            $this->status = 'info';
            return;
        }
        
        // Create or update attendance record
        $attendance = Attendance::firstOrNew([
            'employee_id' => $this->employee->id,
            'date' => $today,
        ]);
        
        $attendance->check_in_time = $now;
        
        // Determine if late (assuming work starts at 9 AM)
        $workStartTime = Carbon::createFromTimeString('09:00:00');
        if ($now->copy()->setDate($today->year, $today->month, $today->day) > $workStartTime) {
            $attendance->status = 'Late';
            $this->message = 'You have checked in late at ' . $now->format('h:i A');
        } else {
            $attendance->status = 'Present';
            $this->message = 'You have successfully checked in at ' . $now->format('h:i A');
        }
        
        $attendance->save();
        $this->status = 'success';
        
        $this->loadEmployeeData();
    }

    public function checkOut()
    {
        if (!$this->employee) {
            $this->message = 'No employee record found. Please contact HR.';
            $this->status = 'error';
            return;
        }
        
        $now = Carbon::now();
        $today = Carbon::today();
        
        // Check if checked in
        if (!$this->todayAttendance || !$this->todayAttendance->check_in_time) {
            $this->message = 'You must check in before checking out.';
            $this->status = 'error';
            return;
        }
        
        // Check if already checked out
        if ($this->todayAttendance->check_out_time) {
            $this->message = 'You have already checked out today at ' . $this->todayAttendance->check_out_time->format('h:i A');
            $this->status = 'info';
            return;
        }
        
        // Update attendance record
        $this->todayAttendance->check_out_time = $now;
        $this->todayAttendance->save();
        
        $this->message = 'You have successfully checked out at ' . $now->format('h:i A');
        $this->status = 'success';
        
        $this->loadEmployeeData();
    }

    public function render()
    {
        return view('livewire.attendance.check-in-out');
    }
}
