<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard' => ['view'],
        'users' => ['view', 'create', 'edit', 'delete'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'permissions' => ['view', 'create', 'edit', 'delete'],
        'audit-logs' => ['view'],
        'settings' => ['manage'],
        'backups' => ['view', 'create', 'download', 'delete'],
        'academic-years' => ['view', 'create', 'edit', 'delete'],
        'colleges' => ['view', 'create', 'edit', 'delete'],
        'programs' => ['view', 'create', 'edit', 'delete'],
        'clinicians' => ['view', 'create', 'edit', 'delete'],
        'patients' => ['view', 'create', 'edit', 'delete', 'import'],
        'students' => ['view', 'create', 'edit', 'delete', 'import'],
        'employees' => ['view', 'create', 'edit', 'delete', 'import'],
        'physical-examinations' => ['view', 'create', 'edit', 'delete', 'print'],
        'medical-profiles' => ['view', 'edit'],
        'medical-certificates' => ['view', 'create', 'print'],
        'medicines' => ['view', 'create', 'edit', 'delete'],
        'medicine-categories' => ['view', 'create', 'edit', 'delete'],
        'inventory' => ['view', 'stock-in', 'stock-out', 'dispense', 'reports'],
    ];

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
            'academic-years.view', 'academic-years.create', 'academic-years.edit', 'academic-years.delete',
            'colleges.view', 'colleges.create', 'colleges.edit', 'colleges.delete',
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',
            'clinicians.view', 'clinicians.create', 'clinicians.edit', 'clinicians.delete',
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete', 'patients.import',
            'students.view', 'students.create', 'students.edit', 'students.delete', 'students.import',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete', 'employees.import',
            'physical-examinations.view', 'physical-examinations.create', 'physical-examinations.edit', 'physical-examinations.delete', 'physical-examinations.print',
            'medical-profiles.view', 'medical-profiles.edit',
            'medical-certificates.view', 'medical-certificates.create', 'medical-certificates.print',
            'medicines.view', 'medicines.create', 'medicines.edit', 'medicines.delete',
            'medicine-categories.view', 'medicine-categories.create', 'medicine-categories.edit', 'medicine-categories.delete',
            'inventory.view', 'inventory.stock-in', 'inventory.stock-out', 'inventory.dispense', 'inventory.reports',
        ]);

        foreach (['Doctor', 'Nurse'] as $role) {
            Role::findOrCreate($role, 'web')->syncPermissions([
                'dashboard.view',
                'clinicians.view', 'clinicians.create', 'clinicians.edit',
                'patients.view', 'patients.create', 'patients.edit',
                'students.view', 'students.create', 'students.edit',
                'employees.view', 'employees.create', 'employees.edit',
                'physical-examinations.view', 'physical-examinations.create', 'physical-examinations.edit', 'physical-examinations.print',
                'medical-profiles.view', 'medical-profiles.edit',
                'medical-certificates.view', 'medical-certificates.create', 'medical-certificates.print',
                'medicines.view', 'medicines.create', 'medicines.edit',
                'inventory.view', 'inventory.stock-in', 'inventory.stock-out', 'inventory.dispense', 'inventory.reports',
            ]);
        }

        Role::findOrCreate('Receptionist', 'web')->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit',
            'students.view', 'students.create', 'students.edit',
            'employees.view', 'employees.create', 'employees.edit',
            'physical-examinations.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
