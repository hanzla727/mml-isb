<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Super Admin only
            'manage-nas',
            'manage-users',
            'manage-departments',
            'manage-settings',
            'manage-permissions',
            'manage-announcements',
            'manage-targets',
            'manage-projects',
            'manage-forms',
            // Super Admin + Admin + NA Head
            'view-reports',
            'view-analytics',
            'export-reports',
            'view-announcements',
            'view-targets',
            // Super Admin + Admin + NA Head (meeting/task workflow management)
            'manage-meetings',
            'manage-tasks',
            'review-task-reports',
            // Super Admin + Admin + NA Head + UC Head (org hierarchy)
            'review-reports',
            'mark-attendance',
            'manage-leave-requests',
            'manage-expense-claims',
            // All roles
            'submit-reports',
            'view-own-reports',
            'view-own-targets',
            'view-own-tasks',
            'submit-task-reports',
            'submit-leave-requests',
            'submit-expense-claims',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            Permission::findOrCreate($permission, 'sanctum');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions($permissions);
        Role::findOrCreate('super_admin', 'sanctum')->syncPermissions($permissions);

        // Admin: operational control over every NA they're assigned to
        // (App\Services\HierarchyScope narrows the actual rows they see —
        // this list is "what they're allowed to do", not "where").
        $adminPermissions = [
            'view-reports', 'view-analytics', 'export-reports',
            'view-announcements', 'view-targets',
            'manage-meetings', 'manage-tasks', 'review-task-reports',
            'manage-projects', 'manage-forms',
            'review-reports', 'mark-attendance',
            'manage-leave-requests', 'manage-expense-claims',
        ];
        Role::findOrCreate('admin', 'web')->syncPermissions($adminPermissions);
        Role::findOrCreate('admin', 'sanctum')->syncPermissions($adminPermissions);

        // NA Head: identical operational scope to Admin, but always
        // narrowed to exactly the one NA they head.
        Role::findOrCreate('na_head', 'web')->syncPermissions($adminPermissions);
        Role::findOrCreate('na_head', 'sanctum')->syncPermissions($adminPermissions);

        // UC Head: sits between NA Head and its volunteers — same
        // operational scope as NA Head/Admin, but HierarchyScope narrows it
        // to exactly the UC(s) they've been assigned (User::ucsHeaded()),
        // which can be more than one.
        Role::findOrCreate('uc_head', 'web')->syncPermissions($adminPermissions);
        Role::findOrCreate('uc_head', 'sanctum')->syncPermissions($adminPermissions);

        $userPermissions = [
            'submit-reports', 'view-own-reports', 'view-own-targets', 'view-announcements',
            'view-own-tasks', 'submit-task-reports',
            'submit-leave-requests', 'submit-expense-claims',
        ];
        Role::findOrCreate('user', 'web')->syncPermissions($userPermissions);
        Role::findOrCreate('user', 'sanctum')->syncPermissions($userPermissions);
    }
}
