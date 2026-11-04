<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Application permissions grouped by module.
     *
     * @var array<string, list<string>>
     */
    public const PERMISSIONS = [
        'dashboard' => ['view'],
        'users' => ['view', 'create', 'edit', 'delete'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'permissions' => ['view', 'create', 'edit', 'delete'],
        'audit-logs' => ['view'],
        'settings' => ['manage'],
        'backups' => ['view', 'create', 'download', 'delete'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$module}.{$action}", 'web');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate(User::SUPER_ADMIN_ROLE, 'web');

        Role::findOrCreate('Administrator', 'web')->syncPermissions([
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view',
            'audit-logs.view',
            'settings.manage',
            'backups.view', 'backups.create', 'backups.download',
        ]);

        foreach (['Doctor', 'Nurse', 'Receptionist'] as $role) {
            Role::findOrCreate($role, 'web')->syncPermissions(['dashboard.view']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
