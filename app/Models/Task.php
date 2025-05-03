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
     * Get the project associated with the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the employee assigned to the task.
     */
    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Check if task is pending
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if task is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'In Progress';
    }

    /**
     * Check if task is completed
     */
    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if task is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'Cancelled';
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue()
    {
        return now() > $this->deadline && !$this->isCompleted();
    }

    /**
     * Get days remaining until deadline
     */
    public function getDaysRemainingAttribute()
    {
        $today = now()->startOfDay();
        $deadline = $this->deadline->startOfDay();
        
        if ($today > $deadline) {
            return 0;
        }
        
        return $today->diffInDays($deadline);
    }
}
