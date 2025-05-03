<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'designation',
        'joining_date',
        'salary',
        'bank_account_number',
        'tax_id',
        'reporting_manager_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that this employee belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department that this employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the manager (user) that this employee reports to.
     */
    public function reportingManager()
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    /**
     * Get the employees that report to this employee.
     */
    public function reportingEmployees()
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id', 'user_id');
    }

    /**
     * Get the attendance records for this employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave requests for this employee.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the payroll records for this employee.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the tasks assigned to this employee.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the performance evaluations for this employee.
     */
    public function performanceEvaluations()
    {
        return $this->hasMany(PerformanceEvaluation::class);
    }

    /**
     * Get the assets assigned to this employee.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    /**
     * Get the expenses submitted by this employee.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}