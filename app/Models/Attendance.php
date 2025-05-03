<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    /**
     * Get the employee that this attendance record belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate the total working hours for this attendance record.
     *
     * @return float|null
     */
    public function getWorkingHoursAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_out_time->diffInHours($this->check_in_time);
        }
        
        return null;
    }

    /**
     * Check if the employee was late.
     *
     * @return bool
     */
    public function isLate()
    {
        return $this->status === 'Late';
    }

    /**
     * Check if the employee was present.
     *
     * @return bool
     */
    public function isPresent()
    {
        return $this->status === 'Present';
    }

    /**
     * Check if the employee was absent.
     *
     * @return bool
     */
    public function isAbsent()
    {
        return $this->status === 'Absent';
    }

    /**
     * Check if the employee was on leave.
     *
     * @return bool
     */
    public function isOnLeave()
    {
        return $this->status === 'On Leave';
    }
}