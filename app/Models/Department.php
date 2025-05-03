<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Department extends Model
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
        'description',
        'head_id',
    ];

    /**
     * Get the company that this department belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department head (user).
     */
    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Get the users that belong to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the employees that belong to this department.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the projects that belong to this department.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the expenses associated with this department.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the recruitment instances for this department.
     */
    public function recruitments()
    {
        return $this->hasMany(Recruitment::class);
    }
}