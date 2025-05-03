<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'department_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the company associated with the project.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department associated with the project.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->tasks()->where('status', 'Completed')->count();
        
        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        
        $today = now()->startOfDay();
        $endDate = $this->end_date->startOfDay();
        
        if ($today > $endDate) {
            return 0;
        }
        
        return $today->diffInDays($endDate);
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue()
    {
        if (!$this->end_date) {
            return false;
        }
        
        return now() > $this->end_date && $this->status !== 'Completed';
    }
}
