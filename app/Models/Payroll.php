<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'salary_month',
        'basic_salary',
        'bonus',
        'deductions',
        'net_salary',
        'status',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'salary_month' => 'date',
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the employee that this payroll record belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if the payroll has been paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->status === 'Paid';
    }

    /**
     * Check if the payroll is pending payment.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Calculate and set the net salary based on basic salary, bonus, and deductions.
     *
     * @return void
     */
    public function calculateNetSalary()
    {
        $this->net_salary = $this->basic_salary + $this->bonus - $this->deductions;
    }
}