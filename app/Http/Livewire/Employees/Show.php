<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Task;
use App\Models\PerformanceEvaluation;
use Livewire\Component;
use Carbon\Carbon;

class Show extends Component
{
    public Employee $employee;
    public $activeTab = 'profile';
    public $viewMonth;
    public $attendanceData = [];
    public $leaveData = [];
    public $taskData = [];
    public $evaluations = [];

    protected $queryString = [
        'activeTab' => ['except' => 'profile'],
    ];

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->viewMonth = Carbon::now()->format('Y-m');
        $this->loadTabData($this->activeTab);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTabData($tab);
    }

    public function updatedViewMonth()
    {
        $this->loadTabData($this->activeTab);
    }

    protected function loadTabData($tab)
    {
        switch ($tab) {
            case 'attendance':
                $this->loadAttendanceData();
                break;
            case 'leaves':
                $this->loadLeaveData();
                break;
            case 'tasks':
                $this->loadTaskData();
                break;
            case 'evaluations':
                $this->loadEvaluationData();
                break;
        }
    }

    protected function loadAttendanceData()
    {
        $month = Carbon::createFromFormat('Y-m', $this->viewMonth);
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        
        // Get all attendance records for the month
        $attendanceRecords = Attendance::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $this->attendanceData = [];
        
        // Populate calendar days
        $currentDay = $startOfMonth->copy();
        while ($currentDay <= $endOfMonth) {
            $dateStr = $currentDay->format('Y-m-d');
            $record = $attendanceRecords->get($dateStr);
            
            $this->attendanceData[] = [
                'date' => $dateStr,
                'day' => $currentDay->format('d'),
                'day_name' => $currentDay->format('D'),
                'is_weekend' => $currentDay->isWeekend(),
                'status' => $record ? $record->status : null,
                'check_in' => $record && $record->check_in_time ? $record->check_in_time->format('H:i') : null,
                'check_out' => $record && $record->check_out_time ? $record->check_out_time->format('H:i') : null,
                'hours_worked' => $record ? $record->getHoursWorkedAttribute() : 0,
            ];
            
            $currentDay->addDay();
        }
    }

    protected function loadLeaveData()
    {
        $this->leaveData = Leave::where('employee_id', $this->employee->id)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    protected function loadTaskData()
    {
        $this->taskData = Task::with('project')
            ->where('assigned_to', $this->employee->id)
            ->orderBy('deadline')
            ->get();
    }

    protected function loadEvaluationData()
    {
        $this->evaluations = PerformanceEvaluation::with('reviewer')
            ->where('employee_id', $this->employee->id)
            ->orderBy('review_date', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.employees.show');
    }
}
