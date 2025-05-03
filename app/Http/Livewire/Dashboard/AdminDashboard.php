<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\Recruitment;
use Illuminate\Support\Facades\DB;

class AdminDashboard extends Component
{
    public $totalUsers = 0;
    public $totalEmployees = 0;
    public $totalDepartments = 0;
    public $activeProjects = 0;
    
    public $usersByRole = [];
    public $recentUsers = [];
    public $pendingLeaves = [];
    public $departmentCounts = [];
    
    public function mount()
    {
        // Count metrics
        $this->totalUsers = User::count();
        $this->totalEmployees = Employee::count();
        $this->totalDepartments = Department::count();
        $this->activeProjects = Project::where('status', 'Active')->count();
        
        // Users by role
        $this->usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();
            
        // Recent users
        $this->recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Department counts
        $this->departmentCounts = Department::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->get();
    }
    
    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}