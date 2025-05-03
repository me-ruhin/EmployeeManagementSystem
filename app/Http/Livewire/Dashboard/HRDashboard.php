<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Recruitment;

class HRDashboard extends Component
{
    public $totalEmployees;
    public $totalDepartments;
    public $pendingLeaves;
    public $activeRecruitments;
    
    public function mount()
    {
        $this->totalEmployees = Employee::count();
        $this->totalDepartments = Department::count();
        $this->pendingLeaves = Leave::where('status', 'Pending')->count();
        $this->activeRecruitments = Recruitment::where('status', 'Open')->count();
    }
    
    public function render()
    {
        $recentEmployees = Employee::with('user', 'department')
            ->latest()
            ->take(5)
            ->get();
            
        $pendingLeaveRequests = Leave::with('employee.user')
            ->where('status', 'Pending')
            ->latest()
            ->take(5)
            ->get();
            
        $openRecruitments = Recruitment::with('department')
            ->where('status', 'Open')
            ->latest()
            ->take(5)
            ->get();
        
        return view('livewire.dashboard.hr-dashboard', [
            'recentEmployees' => $recentEmployees,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'openRecruitments' => $openRecruitments,
        ]);
    }
}