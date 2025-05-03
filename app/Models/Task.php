<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Task extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'deadline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
    ];

    /**
     * Get the project that this task belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the employee who is assigned to this task.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Check if the task is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if the task is in progress.
     *
     * @return bool
     */
    public function isInProgress()
    {
        return $this->status === 'In Progress';
    }

    /**
     * Check if the task is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if the task is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'Cancelled';
    }

    /**
     * Check if the task has high priority.
     *
     * @return bool
     */
    public function isHighPriority()
    {
        return $this->priority === 'High' || $this->priority === 'Critical';
    }

    /**
     * Check if the task is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return $this->deadline < now() && !$this->isCompleted();
    }
}