<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Companies (Admin only)
    Route::resource('companies', CompanyController::class)->middleware('role:admin');
    
    // Users Management (Admin and HR)
    Route::resource('users', UserController::class)->middleware('role:admin|hr');
    
    // Employees
    Route::resource('employees', EmployeeController::class);
    
    // Attendance
    Route::resource('attendance', AttendanceController::class);
    
    // Leaves
    Route::resource('leaves', LeaveController::class);
    
    // Payroll (Admin and HR)
    Route::resource('payroll', PayrollController::class)->middleware('role:admin|hr');
    
    // Projects
    Route::resource('projects', ProjectController::class);
    
    // Tasks
    Route::resource('tasks', TaskController::class);
    
    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // Add admin-specific routes here
    });
    
    // HR routes
    Route::middleware(['role:hr|admin'])->group(function () {
        // Add HR-specific routes here
    });
    
    // Manager routes
    Route::middleware(['role:manager|admin'])->group(function () {
        // Add manager-specific routes here
    });
});