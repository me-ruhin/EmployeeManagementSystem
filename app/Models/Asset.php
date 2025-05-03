<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Asset extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'status',
        'assigned_to',
    ];

    /**
     * Get the company that this asset belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee who is assigned to this asset.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Check if the asset is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->status === 'Available';
    }

    /**
     * Check if the asset is assigned.
     *
     * @return bool
     */
    public function isAssigned()
    {
        return $this->status === 'Assigned';
    }

    /**
     * Check if the asset is under maintenance.
     *
     * @return bool
     */
    public function isUnderMaintenance()
    {
        return $this->status === 'Under Maintenance';
    }

    /**
     * Check if the asset is disposed.
     *
     * @return bool
     */
    public function isDisposed()
    {
        return $this->status === 'Disposed';
    }
}