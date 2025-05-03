<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Livewire\Dashboard\AdminDashboard;
use App\Http\Livewire\Dashboard\HRDashboard;
use App\Http\Livewire\Dashboard\ManagerDashboard;
use App\Http\Livewire\Dashboard\EmployeeDashboard;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return view('dashboard.admin');
        } elseif ($user->isHR()) {
            return view('dashboard.hr');
        } elseif ($user->isManager() || $user->isDepartmentHead()) {
            return view('dashboard.manager');
        } else {
            return view('dashboard.employee');
        }
    }
}