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
     * Get the company that this project belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department that this project belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the tasks associated with this project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Check if the project is ongoing.
     *
     * @return bool
     */
    public function isOngoing()
    {
        return $this->status === 'Ongoing';
    }

    /**
     * Check if the project is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if the project is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'Cancelled';
    }

    /**
     * Get the project's progress percentage based on completed tasks.
     *
     * @return float
     */
    public function getProgressPercentageAttribute()
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->tasks()->where('status', 'Completed')->count();
        
        return ($completedTasks / $totalTasks) * 100;
    }
}