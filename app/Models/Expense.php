<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Expense extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'department_id',
        'expense_type',
        'amount',
        'description',
        'status',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the employee who submitted this expense.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the department that this expense belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who approved this expense.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if the expense is pending approval.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if the expense is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === 'Approved';
    }

    /**
     * Check if the expense is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }
}