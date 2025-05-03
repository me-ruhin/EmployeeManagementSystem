<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PerformanceEvaluation extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'review_date',
        'reviewer_id',
        'rating',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'review_date' => 'date',
        'rating' => 'integer',
    ];

    /**
     * Get the employee that this performance evaluation belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who conducted the review.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the rating description based on the rating value.
     *
     * @return string
     */
    public function getRatingDescriptionAttribute()
    {
        return match($this->rating) {
            1 => 'Poor',
            2 => 'Below Average',
            3 => 'Average',
            4 => 'Good',
            5 => 'Excellent',
            default => 'Not Rated',
        };
    }
}