<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'contact_email',
        'contact_phone',
    ];

    /**
     * Get the users that belong to this company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the departments that belong to this company.
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the employees that belong to this company.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the projects that belong to this company.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the assets that belong to this company.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}