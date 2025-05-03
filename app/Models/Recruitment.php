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
     * Get the department associated with the recruitment.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all candidates for the recruitment.
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    /**
     * Check if recruitment is open
     */
    public function isOpen()
    {
        return $this->status === 'Open';
    }

    /**
     * Check if recruitment is closed
     */
    public function isClosed()
    {
        return $this->status === 'Closed';
    }

    /**
     * Get the count of candidates by status
     */
    public function getCandidateStatusCountAttribute()
    {
        return [
            'pending' => $this->candidates()->where('status', 'Pending')->count(),
            'shortlisted' => $this->candidates()->where('status', 'Shortlisted')->count(),
            'rejected' => $this->candidates()->where('status', 'Rejected')->count(),
            'hired' => $this->candidates()->where('status', 'Hired')->count(),
        ];
    }
}
