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
     * Get the company associated with the asset.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee the asset is assigned to.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Check if asset is available
     */
    public function isAvailable()
    {
        return $this->status === 'Available';
    }

    /**
     * Check if asset is assigned
     */
    public function isAssigned()
    {
        return $this->status === 'Assigned';
    }

    /**
     * Check if asset is under maintenance
     */
    public function isUnderMaintenance()
    {
        return $this->status === 'Under Maintenance';
    }

    /**
     * Check if asset is disposed
     */
    public function isDisposed()
    {
        return $this->status === 'Disposed';
    }
}
