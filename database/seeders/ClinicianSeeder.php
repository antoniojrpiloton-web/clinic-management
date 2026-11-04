<?php

namespace Database\Seeders;

use App\Models\Clinician;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicianSeeder extends Seeder
{
    public function run(): void
    {
        $doctorUser = User::role('Doctor')->first();
        $nurseUser = User::role('Nurse')->first();

        $clinicians = [
            [
                'name' => 'DR. VICENTE S. QUIMPO, MD',
                'type' => 'Doctor',
                'title' => 'School Physician',
                'license_number' => '0052551',
                'ptr_number' => 'PTR-AKL-2026-8910',
                'specialization' => 'General Medicine & School Health',
                'phone' => '+63 917 111 2233',
                'email' => 'vicente.quimpo@nvc.edu.ph',
                'user_id' => $doctorUser?->id,
                'is_active' => true,
            ],
            [
                'name' => 'DR. CARMINA V. DELOS REYES, MD',
                'type' => 'Doctor',
                'title' => 'Consulting Physician',
                'license_number' => '0078124',
                'ptr_number' => 'PTR-AKL-2026-8911',
                'specialization' => 'Pediatrics & Adolescent Health',
                'phone' => '+63 918 222 3344',
                'email' => 'carmina.delosreyes@nvc.edu.ph',
                'user_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'NURSE MARIA THERESA SANTOS, RN',
                'type' => 'Nurse',
                'title' => 'Head Clinic Nurse',
                'license_number' => '0088921',
                'ptr_number' => null,
                'specialization' => 'School Health Nursing',
                'phone' => '+63 920 333 4455',
                'email' => 'maria.santos@nvc.edu.ph',
                'user_id' => $nurseUser?->id,
                'is_active' => true,
            ],
            [
                'name' => 'NURSE JUAN CARLO BAUTISTA, RN',
                'type' => 'Nurse',
                'title' => 'Staff Nurse',
                'license_number' => '0094512',
                'ptr_number' => null,
                'specialization' => 'Emergency Care & First Aid',
                'phone' => '+63 921 444 5566',
                'email' => 'jc.bautista@nvc.edu.ph',
                'user_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'DR. ROBERTO TAN, DMD',
                'type' => 'Dentist',
                'title' => 'University Dentist',
                'license_number' => '0034120',
                'ptr_number' => 'PTR-AKL-2026-8915',
                'specialization' => 'Dental Medicine & Oral Surgery',
                'phone' => '+63 922 555 6677',
                'email' => 'roberto.tan@nvc.edu.ph',
                'user_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($clinicians as $data) {
            Clinician::firstOrCreate(
                ['license_number' => $data['license_number']],
                $data
            );
        }
    }
}
