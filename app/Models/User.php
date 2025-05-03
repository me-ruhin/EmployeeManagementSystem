<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'company_id',
        'department_id',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the company that the user belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employee profile associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'Admin' || $this->hasRole('admin');
    }

    /**
     * Check if the user is an HR.
     */
    public function isHR()
    {
        return $this->role === 'HR' || $this->hasRole('hr');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager()
    {
        return $this->role === 'Manager' || $this->hasRole('manager');
    }

    /**
     * Check if the user is a department head.
     */
    public function isDepartmentHead()
    {
        return $this->role === 'Department Head' || $this->hasRole('department_head');
    }

    /**
     * Check if the user is a regular employee.
     */
    public function isEmployee()
    {
        return $this->role === 'Employee' || $this->hasRole('employee');
    }
}