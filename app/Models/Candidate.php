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
     * Get the recruitment associated with the candidate.
     */
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    /**
     * Check if candidate is pending
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if candidate is shortlisted
     */
    public function isShortlisted()
    {
        return $this->status === 'Shortlisted';
    }

    /**
     * Check if candidate is rejected
     */
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }

    /**
     * Check if candidate is hired
     */
    public function isHired()
    {
        return $this->status === 'Hired';
    }
}
