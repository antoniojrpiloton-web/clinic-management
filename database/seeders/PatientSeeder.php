<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Clinician;
use App\Models\College;
use App\Models\MedicalProfile;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $ay = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        $ccis = College::where('code', 'CCIS')->first();
        $bsit = Program::where('code', 'BSIT')->first();
        $bsn = Program::where('code', 'BSN')->first();
        $chs = College::where('code', 'CHS')->first();

        $studentsData = [
            [
                'patient_number' => 'PAT-2026-00001',
                'school_id_number' => '2023-01045',
                'type' => 'student',
                'first_name' => 'Krystal',
                'middle_name' => 'Mae',
                'last_name' => 'Bernardino',
                'suffix' => null,
                'gender' => 'Female',
                'date_of_birth' => '2004-05-14',
                'civil_status' => 'Single',
                'blood_type' => 'O+',
                'email' => 'krystal.bernardino@snsu.edu.ph',
                'phone' => '+63 912 345 6789',
                'address' => 'Brgy. Washington, Surigao City',
                'emergency_contact_name' => 'Elena Bernardino',
                'emergency_contact_relationship' => 'Mother',
                'emergency_contact_phone' => '+63 918 111 2233',
                'allergies' => 'Penicillin, Shellfish',
                'medical_history' => 'Mild Asthma (controlled)',
                'status' => 'active',
                'college_id' => $ccis?->id,
                'program_id' => $bsit?->id,
                'academic_year_id' => $ay?->id,
                'year_level' => '2nd Year',
                'section' => 'BSIT 2-A',
                'enrollment_status' => 'enrolled',
            ],
            [
                'patient_number' => 'PAT-2026-00002',
                'school_id_number' => '2022-00812',
                'type' => 'student',
                'first_name' => 'Joshua',
                'middle_name' => 'Paul',
                'last_name' => 'Alcantara',
                'suffix' => 'Jr.',
                'gender' => 'Male',
                'date_of_birth' => '2003-11-20',
                'civil_status' => 'Single',
                'blood_type' => 'A+',
                'email' => 'joshua.alcantara@snsu.edu.ph',
                'phone' => '+63 920 456 7890',
                'address' => 'Brgy. San Juan, Surigao City',
                'emergency_contact_name' => 'Joshua Alcantara Sr.',
                'emergency_contact_relationship' => 'Father',
                'emergency_contact_phone' => '+63 921 222 3344',
                'allergies' => 'None',
                'medical_history' => 'No prior chronic conditions',
                'status' => 'active',
                'college_id' => $chs?->id ?? $ccis?->id,
                'program_id' => $bsn?->id ?? $bsit?->id,
                'academic_year_id' => $ay?->id,
                'year_level' => '3rd Year',
                'section' => 'BSN 3-B',
                'enrollment_status' => 'enrolled',
            ],
        ];

        foreach ($studentsData as $item) {
            Patient::firstOrCreate(
                ['patient_number' => $item['patient_number']],
                $item
            );
        }

        $employeesData = [
            [
                'patient_number' => 'PAT-2026-00003',
                'school_id_number' => 'EMP-2016-0089',
                'type' => 'employee',
                'first_name' => 'Ramon',
                'middle_name' => 'Carlos',
                'last_name' => 'Villanueva',
                'suffix' => null,
                'gender' => 'Male',
                'date_of_birth' => '1985-03-25',
                'civil_status' => 'Married',
                'blood_type' => 'B+',
                'email' => 'ramon.villanueva@snsu.edu.ph',
                'phone' => '+63 917 555 4321',
                'address' => 'Poblacion, Surigao City',
                'emergency_contact_name' => 'Carla Villanueva',
                'emergency_contact_relationship' => 'Spouse',
                'emergency_contact_phone' => '+63 917 888 9900',
                'allergies' => 'Ibuprofen',
                'medical_history' => 'Hypertension Stage 1 (on Maintenance)',
                'status' => 'active',
                'college_id' => $ccis?->id,
                'office_department' => 'Department of Computer Science',
                'employment_classification' => 'Permanent',
                'category' => 'Faculty',
                'position' => 'Assistant Professor IV',
                'employment_status' => 'active',
            ],
            [
                'patient_number' => 'PAT-2026-00004',
                'school_id_number' => 'EMP-2020-0145',
                'type' => 'employee',
                'first_name' => 'Maria',
                'middle_name' => 'Corazon',
                'last_name' => 'Dela Torre',
                'suffix' => null,
                'gender' => 'Female',
                'date_of_birth' => '1990-09-12',
                'civil_status' => 'Single',
                'blood_type' => 'AB+',
                'email' => 'maria.delatorre@snsu.edu.ph',
                'phone' => '+63 928 333 4455',
                'address' => 'Brgy. Taft, Surigao City',
                'emergency_contact_name' => 'Teresa Dela Torre',
                'emergency_contact_relationship' => 'Sister',
                'emergency_contact_phone' => '+63 929 444 5566',
                'allergies' => 'Dust mites',
                'medical_history' => 'Allergic rhinitis',
                'status' => 'active',
                'college_id' => null,
                'office_department' => 'Office of the University Registrar',
                'employment_classification' => 'Permanent',
                'category' => 'Non-Teaching / Staff',
                'position' => 'Administrative Officer II',
                'employment_status' => 'active',
            ],
        ];

        foreach ($employeesData as $item) {
            Patient::firstOrCreate(
                ['patient_number' => $item['patient_number']],
                $item
            );
        }

        $doctor = User::role('Doctor')->first() ?? User::first();
        $chiefClinician = Clinician::where('license_number', '0052551')->first() ?? Clinician::first();

        foreach (Patient::all() as $p) {
            MedicalProfile::firstOrCreate(
                ['patient_id' => $p->id],
                [
                    'past_medical_history' => $p->allergies ? ['Asthma / Wheezing', 'Allergy / Hay Fever'] : ['None'],
                    'family_medical_history' => ['Hypertension'],
                    'allergies_drugs' => str_contains((string) $p->allergies, 'Penicillin') ? 'Penicillin' : null,
                    'allergies_food' => str_contains((string) $p->allergies, 'Shellfish') ? 'Shellfish' : null,
                    'allergies_others' => str_contains((string) $p->allergies, 'Dust') ? 'Dust mites' : null,
                    'existing_conditions' => $p->medical_history,
                    'current_medications' => str_contains((string) $p->medical_history, 'Asthma') ? 'Salbutamol MDI PRN' : null,
                    'smoking_status' => 'non-smoker',
                    'alcohol_status' => 'non-drinker',
                    'updated_by' => $doctor?->id,
                ]
            );

            PhysicalExamination::firstOrCreate(
                ['patient_id' => $p->id],
                [
                    'pe_number' => PhysicalExamination::generatePeNumber(),
                    'academic_year_id' => $ay?->id,
                    'examiner_id' => $doctor?->id,
                    'clinician_id' => $chiefClinician?->id,
                    'examiner_name' => $chiefClinician?->name ?? 'DR. VICENTE S. QUIMPO, MD',
                    'examiner_license_number' => $chiefClinician?->license_number ?? '0052551',
                    'exam_date' => now()->subDays(rand(1, 15)),
                    'purpose' => 'Annual Physical Exam',
                    'systolic' => 120,
                    'diastolic' => 80,
                    'pulse_rate' => 74,
                    'respiratory_rate' => 18,
                    'temperature' => 36.6,
                    'oxygen_saturation' => 99,
                    'height_cm' => 165.0,
                    'weight_kg' => 58.5,
                    'bmi' => 21.49,
                    'bmi_category' => 'Normal',
                    'visual_acuity_right' => '20/20',
                    'visual_acuity_left' => '20/20',
                    'visual_acuity_is_corrected' => false,
                    'color_vision' => 'Normal',
                    'hearing' => 'Normal',
                    'general_appearance' => 'Conscious, coherent, ambulatory, well-nourished',
                    'skin' => ['status' => 'normal', 'remarks' => 'Good skin turgor, no jaundice, no pallor'],
                    'heent' => ['status' => 'normal', 'remarks' => 'Anicteric sclerae, pink palpebral conjunctivae'],
                    'neck' => ['status' => 'normal', 'remarks' => 'Supple, no palpable lymphadenopathy'],
                    'chest_lungs' => ['status' => 'normal', 'remarks' => 'Clear breath sounds, symmetrical chest expansion'],
                    'heart' => ['status' => 'normal', 'remarks' => 'Normal rate, regular rhythm, no murmurs'],
                    'abdomen' => ['status' => 'normal', 'remarks' => 'Flat, soft, non-tender, no organomegaly'],
                    'musculoskeletal' => ['status' => 'normal', 'remarks' => 'Full range of motion, normal gait'],
                    'neurological' => ['status' => 'normal', 'remarks' => 'Grossly intact motor and sensory function'],
                    'dental' => ['status' => 'normal', 'remarks' => 'Good oral hygiene'],
                    'chest_xray_findings' => 'Normal chest radiograph, no active lung infiltrates',
                    'chest_xray_date' => now()->subDays(20),
                    'laboratory_notes' => 'CBC, Urinalysis, and routine screening normal',
                    'clinical_findings' => 'Essentially normal physical examination findings',
                    'medical_recommendations' => 'Maintain balanced diet, regular exercise, adequate hydration',
                    'fitness_assessment' => 'Class A',
                    'fitness_activities' => [
                        'physical_activities' => 'fit',
                        'on_the_job_training' => 'fit',
                        'work_immersion' => 'fit',
                        'to_work' => 'fit',
                        'to_travel' => 'fit',
                        'other_requirements' => 'fit',
                    ],
                    'status' => 'completed',
                ]
            );
        }
    }
}
