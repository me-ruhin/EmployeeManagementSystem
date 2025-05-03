<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Recruitment extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'position',
        'department_id',
        'job_description',
        'status',
    ];

    /**
     * Get the department that this recruitment belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the candidates for this recruitment.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    /**
     * Check if the recruitment is open.
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->status === 'Open';
    }

    /**
     * Check if the recruitment is closed.
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->status === 'Closed';
    }

    /**
     * Get the number of candidates for this recruitment.
     *
     * @return int
     */
    public function getCandidateCountAttribute()
    {
        return $this->candidates()->count();
    }

    /**
     * Get the number of shortlisted candidates for this recruitment.
     *
     * @return int
     */
    public function getShortlistedCountAttribute()
    {
        return $this->candidates()->where('status', 'Shortlisted')->count();
    }

    /**
     * Get the number of hired candidates for this recruitment.
     *
     * @return int
     */
    public function getHiredCountAttribute()
    {
        return $this->candidates()->where('status', 'Hired')->count();
    }
}