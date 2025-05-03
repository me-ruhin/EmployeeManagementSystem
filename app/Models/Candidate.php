<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Candidate extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recruitment_id',
        'name',
        'email',
        'phone',
        'resume',
        'status',
    ];

    /**
     * Get the recruitment that this candidate belongs to.
     */
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    /**
     * Check if the candidate's application is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if the candidate is shortlisted.
     *
     * @return bool
     */
    public function isShortlisted()
    {
        return $this->status === 'Shortlisted';
    }

    /**
     * Check if the candidate is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }

    /**
     * Check if the candidate is hired.
     *
     * @return bool
     */
    public function isHired()
    {
        return $this->status === 'Hired';
    }

    /**
     * Get the job position applied for by this candidate.
     *
     * @return string
     */
    public function getPositionAppliedAttribute()
    {
        return $this->recruitment ? $this->recruitment->position : 'Unknown';
    }
}