<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $hrRole = Role::create(['name' => 'HR']);
        $managerRole = Role::create(['name' => 'Manager']);
        $employeeRole = Role::create(['name' => 'Employee']);
        $departmentHeadRole = Role::create(['name' => 'Department Head']);

        // Create permissions
        $permissions = [
            // User management permissions
            'view users', 'create users', 'edit users', 'delete users',
            
            // Company management permissions
            'view companies', 'create companies', 'edit companies', 'delete companies',
            
            // Department management permissions
            'view departments', 'create departments', 'edit departments', 'delete departments',
            
            // Employee management permissions
            'view employees', 'create employees', 'edit employees', 'delete employees',
            
            // Attendance management permissions
            'view attendances', 'create attendances', 'edit attendances', 'delete attendances',
            
            // Leave management permissions
            'view leaves', 'create leaves', 'edit leaves', 'delete leaves', 'approve leaves',
            
            // Payroll management permissions
            'view payrolls', 'create payrolls', 'edit payrolls', 'delete payrolls',
            
            // Project management permissions
            'view projects', 'create projects', 'edit projects', 'delete projects',
            
            // Task management permissions
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            
            // Performance evaluation permissions
            'view evaluations', 'create evaluations', 'edit evaluations', 'delete evaluations',
            
            // Recruitment management permissions
            'view recruitments', 'create recruitments', 'edit recruitments', 'delete recruitments',
            
            // Candidate management permissions
            'view candidates', 'create candidates', 'edit candidates', 'delete candidates',
            
            // Asset management permissions
            'view assets', 'create assets', 'edit assets', 'delete assets',
            
            // Expense management permissions
            'view expenses', 'create expenses', 'edit expenses', 'delete expenses', 'approve expenses',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to Admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to HR role
        $hrRole->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view companies',
            'view departments', 'edit departments',
            'view employees', 'create employees', 'edit employees',
            'view attendances', 'create attendances', 'edit attendances',
            'view leaves', 'approve leaves',
            'view payrolls', 'create payrolls', 'edit payrolls',
            'view recruitments', 'create recruitments', 'edit recruitments',
            'view candidates', 'create candidates', 'edit candidates', 'delete candidates',
            'view assets', 'create assets', 'edit assets',
            'view expenses', 'approve expenses',
            'view evaluations', 'create evaluations',
        ]);

        // Assign permissions to Manager role
        $managerRole->givePermissionTo([
            'view employees',
            'view attendances',
            'view leaves', 'approve leaves',
            'view projects', 'create projects', 'edit projects',
            'view tasks', 'create tasks', 'edit tasks',
            'view evaluations', 'create evaluations',
            'view expenses', 'approve expenses',
        ]);

        // Assign permissions to Department Head role
        $departmentHeadRole->givePermissionTo([
            'view employees',
            'view attendances',
            'view leaves', 'approve leaves',
            'view projects',
            'view tasks', 'create tasks', 'edit tasks',
            'view evaluations', 'create evaluations',
            'view expenses', 'approve expenses',
        ]);

        // Assign permissions to Employee role
        $employeeRole->givePermissionTo([
            'view attendances', 'create attendances',
            'view leaves', 'create leaves',
            'view tasks', 'edit tasks',
            'view expenses', 'create expenses',
        ]);
    }
}
