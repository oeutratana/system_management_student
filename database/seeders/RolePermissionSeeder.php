<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * The full set of permissions used by the application.
     *
     * view_classes and view_courses were added beyond the core list so that
     * teachers can keep browsing their own classes/courses (existing behavior).
     */
    protected array $permissions = [
        'manage_users',
        'manage_students',
        'manage_teachers',
        'manage_parents',
        'manage_classes',
        'manage_courses',
        'view_students',
        'manage_exams',
        'manage_grades',
        'view_grades',
        'manage_attendance',
        'view_attendance',
        'manage_fees',
        'view_fees',
        'manage_payments',
        'view_payments',
        'manage_announcements',
        'view_announcements',
        'view_reports',
        'view_classes',
        'view_courses',
    ];

    /**
     * Role name => permissions granted to that role.
     *
     * @var array<string, list<string>>
     */
    protected array $roles = [
        'admin' => [], // filled with every permission below
        'teacher' => [
            'view_students',
            'view_classes',
            'view_courses',
            'manage_exams',
            'manage_grades',
            'view_grades',
            'manage_attendance',
            'view_attendance',
            'view_announcements',
            'view_reports',
        ],
        'student' => [
            'view_grades',
            'view_attendance',
            'view_fees',
            'view_payments',
            'view_announcements',
        ],
    ];

    public function run(): void
    {
        $this->seedRoles();
        $this->seedPermissions();
        $this->assignPermissionsToRoles();
        $this->linkExistingUsersToRoles();
    }

    protected function seedRoles(): void
    {
        foreach (array_keys($this->roles) as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }

    protected function seedPermissions(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }

    protected function assignPermissionsToRoles(): void
    {
        $allPermissionIds = Permission::pluck('id');

        foreach ($this->roles as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();

            $permissionIds = $roleName === 'admin'
                ? $allPermissionIds
                : Permission::whereIn('name', $permissionNames)->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * Map the legacy `role` string column to the new roles table so existing
     * users keep their role without being deleted or guessed.
     */
    protected function linkExistingUsersToRoles(): void
    {
        foreach (User::query()->get() as $user) {
            if ($user->role_id !== null) {
                continue;
            }

            $role = Role::where('name', $user->role)->first();

            if ($role !== null) {
                $user->forceFill(['role_id' => $role->id])->save();
            }
        }
    }
}
