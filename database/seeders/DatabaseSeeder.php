<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
            AcademicStructureSeeder::class,
            DiseaseCategorySeeder::class,
            ClinicianSeeder::class,
        ]);

        $superAdmins = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@clinic.test',
            ],
            [
                'name' => 'Super Admin (SNSU)',
                'email' => 'apiloton1@snsu.edu.ph',
            ],
            [
                'name' => 'Super Admin (ADSSU)',
                'email' => 'alpiloton@adssu.edu.ph',
            ],
        ];

        foreach ($superAdmins as $adminData) {
            $user = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => 'password',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->hasRole(User::SUPER_ADMIN_ROLE)) {
                $user->assignRole(User::SUPER_ADMIN_ROLE);
            }
        }

        $this->call([
            PatientSeeder::class,
            MedicineAndInventorySeeder::class,
            ClinicOperationsSeeder::class,
            SimulationSeeder::class,
        ]);
    }
}
