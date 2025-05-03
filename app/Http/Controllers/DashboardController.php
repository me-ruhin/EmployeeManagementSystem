<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Livewire\Dashboard\AdminDashboard;
use App\Http\Livewire\Dashboard\HRDashboard;
use App\Http\Livewire\Dashboard\ManagerDashboard;
use App\Http\Livewire\Dashboard\DepartmentHeadDashboard;
use App\Http\Livewire\Dashboard\EmployeeDashboard;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('Admin')) {
            return view('dashboard', ['component' => 'admin-dashboard']);
        } elseif ($user->hasRole('HR')) {
            return view('dashboard', ['component' => 'hr-dashboard']);
        } elseif ($user->hasRole('Manager')) {
            return view('dashboard', ['component' => 'manager-dashboard']);
        } elseif ($user->hasRole('Department Head')) {
            return view('dashboard', ['component' => 'department-head-dashboard']);
        } else {
            return view('dashboard', ['component' => 'employee-dashboard']);
        }
    }
}