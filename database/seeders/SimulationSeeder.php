<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\DentalConsultation;
use App\Models\DiseaseCategory;
use App\Models\EmergencyRecord;
use App\Models\FirstAidRecord;
use App\Models\InventoryTransaction;
use App\Models\MedicalConsultation;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulationSeeder extends Seeder
{
    public static ?int $customCount = null;

    public static ?string $customArea = null;

    private const CHUNK_SIZE = 250;

    public static function availableAreas(): array
    {
        return [
            'all' => 'All Tables & Clinical Documents (Complete Simulation)',
            'clinic_visits' => 'Clinic Visit Logs Only (clinic_visits)',
            'medical_consultations' => '1. Medical Consultations (medical_consultations + clinic_visits)',
            'dental_consultations' => '2. Dental Consultations (dental_consultations + clinic_visits)',
            'emergency_records' => '3. Emergency Records (emergency_records + clinic_visits)',
            'first_aid_records' => '4. First Aid Records (first_aid_records + clinic_visits)',
            'patients' => 'Patients & Medical Profiles (patients + medical_profiles)',
            'physical_exams' => 'Annual Physical Examinations (physical_examinations)',
            'schedules' => 'Scheduled Physical Exams & Attendees (physical_exam_schedules)',
            'pharmacy' => 'Pharmacy Dispensings & Inventory Ledger (medicine_dispensings + inventory_transactions)',
            'audit_logs' => 'System Audit Logs (audit_logs)',
        ];
    }

    public static function normalizeArea(?string $area): string
    {
        $clean = strtolower(trim((string) $area));

        return match ($clean) {
            '', 'all', 'everything', 'full' => 'all',
            'visit', 'visits', 'clinic_visit', 'clinic_visits', 'clinicvisit', 'clinicvisits' => 'clinic_visits',
            'medical', 'consultation', 'consultations', 'medical_consultation', 'medical_consultations' => 'medical_consultations',
            'dental', 'dental_consultation', 'dental_consultations' => 'dental_consultations',
            'emergency', 'er', 'emergency_record', 'emergency_records' => 'emergency_records',
            'first_aid', 'firstaid', 'first_aid_record', 'first_aid_records' => 'first_aid_records',
            'patient', 'patients', 'profile', 'profiles', 'medical_profiles' => 'patients',
            'pe', 'physical_exam', 'physical_exams', 'physical_examination', 'physical_examinations' => 'physical_exams',
            'schedule', 'schedules', 'pe_schedule', 'pe_schedules', 'physical_exam_schedules' => 'schedules',
            'pharmacy', 'dispensing', 'dispensings', 'medicine', 'medicines', 'inventory', 'medicine_dispensings' => 'pharmacy',
            'audit', 'logs', 'audit_log', 'audit_logs' => 'audit_logs',
            default => 'all',
        };
    }

    public function run(): void
    {
        $area = $this->resolveTargetArea();
        $countToAdd = $this->resolveRecordCount();

        if ($countToAdd <= 0) {
            return;
        }

        $this->call([
            AcademicStructureSeeder::class,
            ClinicianSeeder::class,
        ]);

        $ay = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::first();

        $colleges = College::all();
        $programs = Program::all();
        $doctors = Clinician::where('type', 'Doctor')->get();
        $nurses = Clinician::where('type', 'Nurse')->get();
        $adminUser = User::first();

        if ($colleges->isEmpty() || $programs->isEmpty() || $doctors->isEmpty() || $nurses->isEmpty() || ! $adminUser || ! $ay) {
            $this->call([
                RolesAndPermissionsSeeder::class,
                SettingsSeeder::class,
                AcademicStructureSeeder::class,
                ClinicianSeeder::class,
                MedicineAndInventorySeeder::class,
            ]);

            $ay = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
            $colleges = College::all();
            $programs = Program::all();
            $doctors = Clinician::where('type', 'Doctor')->get();
            $nurses = Clinician::where('type', 'Nurse')->get();
            $adminUser = User::first() ?? User::create([
                'name' => 'System Administrator',
                'email' => 'admin@clinic.test',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $dentists = Clinician::where('specialization', 'like', '%Dent%')->get();
        if ($dentists->isEmpty()) {
            $dentist = Clinician::firstOrCreate(
                ['license_number' => '0098412'],
                [
                    'name' => 'Dr. Clarisse M. Montemayor, DMD',
                    'type' => 'Doctor',
                    'title' => 'University Dentist II',
                    'ptr_number' => 'PTR-8841209',
                    'specialization' => 'General & Preventive Dentistry',
                    'phone' => '+63 917 889 4410',
                    'email' => 'clarisse.montemayor@nvc.edu.ph',
                    'is_active' => true,
                ]
            );
            $dentists = collect([$dentist]);
        }

        $this->ensureMedicinesAndBatches();

        $medicinesWithBatches = Medicine::with('batches')->get()->filter(
            fn (Medicine $med): bool => $med->batches->isNotEmpty()
        )->values();

        $uniquePrefix = strtoupper(Str::random(5));

        if ($area === 'all' || $area === 'patients') {
            $targetPatients = $this->seedPatients($countToAdd, $ay, $colleges, $programs, $uniquePrefix);
            $this->seedMedicalProfiles($targetPatients, $adminUser);
        } else {
            $targetPatients = Patient::with(['college', 'program'])->limit(max($countToAdd, 100))->get();
            if ($targetPatients->isEmpty()) {
                $targetPatients = $this->seedPatients(min($countToAdd, 50), $ay, $colleges, $programs, $uniquePrefix);
                $this->seedMedicalProfiles($targetPatients, $adminUser);
            }
        }

        if ($area === 'all' || $area === 'physical_exams') {
            $this->seedPhysicalExaminations($countToAdd, $ay, $targetPatients, $doctors, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'schedules') {
            $this->seedPhysicalExamSchedulesAndAttendees($countToAdd, $ay, $colleges, $programs, $doctors, $targetPatients, $adminUser, $uniquePrefix);
        }

        if ($area === 'clinic_visits') {
            $this->seedGeneralClinicVisits($countToAdd, $targetPatients, $doctors, $nurses, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'medical_consultations') {
            $this->seedMedicalConsultations($countToAdd, $targetPatients, $doctors, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'dental_consultations') {
            $this->seedDentalConsultations($countToAdd, $targetPatients, $dentists, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'emergency_records') {
            $this->seedEmergencyRecords($countToAdd, $targetPatients, $doctors, $nurses, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'first_aid_records') {
            $this->seedFirstAidRecords($countToAdd, $targetPatients, $nurses, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'pharmacy') {
            $this->seedMedicineDispensingsAndTransactions($countToAdd, $targetPatients, $doctors, $medicinesWithBatches, $adminUser, $uniquePrefix);
        }

        if ($area === 'all' || $area === 'audit_logs') {
            $this->seedAuditLogs($countToAdd, $adminUser, $targetPatients);
        }

        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.charts');

        if ($this->command) {
            $areaLabel = self::availableAreas()[$area] ?? $area;
            $this->command->info("Added +{$countToAdd} new records for [{$areaLabel}]!");
        }
    }

    private function resolveTargetArea(): string
    {
        if (self::$customArea !== null) {
            return self::normalizeArea(self::$customArea);
        }

        if (app()->runningUnitTests()) {
            return 'all';
        }

        if ($this->command && $this->command->getInput()->isInteractive()) {
            $areas = self::availableAreas();
            $selected = $this->command->choice(
                'Which clinic area / module would you like to seed?',
                $areas,
                'all'
            );

            $key = array_search($selected, $areas, true);

            return is_string($key) ? $key : self::normalizeArea((string) $selected);
        }

        return 'all';
    }

    private function resolveRecordCount(): int
    {
        if (self::$customCount !== null) {
            return max(0, self::$customCount);
        }

        if (app()->runningUnitTests()) {
            return 15;
        }

        if ($this->command && $this->command->getInput()->isInteractive()) {
            $answer = $this->command->ask(
                'Enter the number of records to ADD (e.g. 100, 500, 1000)',
                '100'
            );

            return max(0, min(50000, (int) $answer));
        }

        return 100;
    }

    private function ensureMedicinesAndBatches(): void
    {
        if (Medicine::count() === 0) {
            $this->call(MedicineAndInventorySeeder::class);
        }

        $nearExpiryMed = Medicine::where('code', 'MED-0007')->first() ?? Medicine::first();
        if ($nearExpiryMed) {
            MedicineBatch::firstOrCreate(
                [
                    'medicine_id' => $nearExpiryMed->id,
                    'batch_number' => 'LOT-EXPIRING-2026-SOON',
                ],
                [
                    'supplier' => 'NVC Medical & Pharma Distributors',
                    'expiration_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                    'quantity_received' => 5000,
                    'quantity_remaining' => 45,
                    'unit_cost' => 5.50,
                    'received_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                ]
            );
        }

        MedicineBatch::where('quantity_remaining', '>', 50)->update([
            'quantity_received' => 50000,
            'quantity_remaining' => 45000,
        ]);
    }

    private function seedPatients(
        int $countToAdd,
        AcademicYear $ay,
        Collection $colleges,
        Collection $programs,
        string $uniquePrefix
    ): Collection {
        $existing = Patient::count();

        $maleFirstNames = ['Juan', 'Joshua', 'Mark', 'Rafael', 'Gabriel', 'Christian', 'Daniel', 'Kenneth', 'Jerome', 'Vincent', 'Ramon', 'Paolo', 'Carlo', 'Adrian', 'Francis', 'Miguel', 'John', 'Patrick', 'Kelvin', 'Bryan'];
        $femaleFirstNames = ['Maria', 'Krystal', 'Angela', 'Camille', 'Clarisse', 'Patricia', 'Jasmine', 'Bea', 'Christine', 'Althea', 'Bianca', 'Denise', 'Eleanor', 'Hannah', 'Marites', 'Sofia', 'Therese', 'Valerie', 'Nicole', 'Grace'];
        $lastNames = ['Dela Cruz', 'Reyes', 'Santos', 'Villanueva', 'Bautista', 'Mendoza', 'Alcantara', 'Quimpo', 'Montemayor', 'Tolentino', 'Bernardino', 'Ramos', 'Morales', 'Aquino', 'Fernandez', 'Garcia', 'Lopez', 'Gonzales', 'Castillo', 'Rivera'];
        $barangays = ['Poblacion', 'Andagaw', 'New Buswang', 'Old Buswang', 'Estancia', 'Tigayon', 'Linabuan Norte', 'Bakhaw Sur', 'Caano', 'Mabilo'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $sections = ['A', 'B', 'C'];
        $departments = [
            'Office of the University Registrar',
            'Human Resource Management Office',
            'Finance and Accounting Services',
            'Library and Information Services',
            'General Services and Campus Security',
            'Academic Affairs Division',
        ];

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $isStudent = ($seq % 5 !== 0);
            $gender = ($seq % 2 === 0) ? 'Female' : 'Male';
            $program = $programs[$seq % $programs->count()];
            $college = $colleges->firstWhere('id', $program->college_id) ?? $colleges[$seq % $colleges->count()];
            $yearLevel = $yearLevels[$seq % count($yearLevels)];
            $sectionLetter = $sections[$seq % count($sections)];

            $firstName = $gender === 'Male'
                ? $maleFirstNames[$seq % count($maleFirstNames)]
                : $femaleFirstNames[$seq % count($femaleFirstNames)];
            $middleName = $lastNames[($seq + 3) % count($lastNames)];
            $lastName = $lastNames[$seq % count($lastNames)];

            $hasAsthma = ($seq % 9 === 0);
            $hasHypertension = (! $isStudent && $seq % 5 === 0);
            $hasAllergy = ($seq % 7 === 0);

            $birthYear = $isStudent ? (2003 + ($seq % 4)) : (1972 + ($seq % 26));
            $birthMonth = ($seq % 12) + 1;
            $birthDay = ($seq % 27) + 1;

            $rows[] = [
                'patient_number' => sprintf('PAT-%s-%06d', $uniquePrefix, $i),
                'school_id_number' => $isStudent
                    ? sprintf('2026-%s-%06d', $uniquePrefix, $i)
                    : sprintf('EMP-%s-%06d', $uniquePrefix, $i),
                'type' => $isStudent ? 'student' : 'employee',
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => ($gender === 'Male' && $seq % 15 === 0) ? 'Jr.' : null,
                'gender' => $gender,
                'date_of_birth' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
                'civil_status' => $isStudent ? 'Single' : (($seq % 3 === 0) ? 'Single' : 'Married'),
                'blood_type' => $bloodTypes[$seq % count($bloodTypes)],
                'email' => strtolower(sprintf('patient.%s.%d@nvc.edu.ph', $uniquePrefix, $i)),
                'phone' => sprintf('+63 917 %03d %04d', ($seq % 900) + 100, ($seq % 9000) + 1000),
                'address' => 'Brgy. '.$barangays[$seq % count($barangays)].', Kalibo, Aklan',
                'emergency_contact_name' => 'Roberto '.$lastName,
                'emergency_contact_relationship' => $isStudent ? 'Parent' : 'Spouse',
                'emergency_contact_phone' => sprintf('+63 918 %03d %04d', ($seq % 900) + 100, ($seq % 9000) + 1000),
                'allergies' => $hasAllergy ? 'Crustaceans (Shrimp, Crab), Penicillin' : 'None known',
                'medical_history' => $hasHypertension
                    ? 'Hypertension Stage 1 (on Maintenance)'
                    : ($hasAsthma ? 'Mild Intermittent Bronchial Asthma' : 'No prior chronic conditions'),
                'status' => 'active',
                'college_id' => $college->id,
                'program_id' => $isStudent ? $program->id : null,
                'academic_year_id' => $ay->id,
                'year_level' => $isStudent ? $yearLevel : null,
                'section' => $isStudent ? ($program->code.' '.substr($yearLevel, 0, 1).'-'.$sectionLetter) : null,
                'enrollment_status' => $isStudent ? 'enrolled' : null,
                'office_department' => $isStudent ? null : $departments[$seq % count($departments)],
                'employment_classification' => $isStudent ? null : 'Permanent',
                'category' => $isStudent ? null : (($seq % 10 === 0) ? 'Non-Teaching / Staff' : 'Faculty'),
                'position' => $isStudent ? null : (($seq % 10 === 0) ? 'Administrative Officer II' : 'Associate Professor I'),
                'employment_status' => $isStudent ? null : 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('patients')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('patients')->insert($rows);
        }

        return Patient::with(['college', 'program'])
            ->where('patient_number', 'like', "PAT-{$uniquePrefix}-%")
            ->orderBy('id')
            ->get();
    }

    private function seedMedicalProfiles(Collection $newPatients, User $adminUser): void
    {
        if ($newPatients->isEmpty()) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];

        foreach ($newPatients as $idx => $patient) {
            $hasAsthma = str_contains((string) $patient->medical_history, 'Asthma');
            $hasHypertension = str_contains((string) $patient->medical_history, 'Hypertension');
            $hasAllergy = ! str_contains((string) $patient->allergies, 'None');

            $rows[] = [
                'patient_id' => $patient->id,
                'past_medical_history' => json_encode($hasAsthma
                    ? ['Asthma / Wheezing', 'Chickenpox']
                    : ($hasHypertension ? ['Hypertension', 'Dengue Fever'] : ['None'])),
                'past_medical_history_notes' => null,
                'family_medical_history' => json_encode(($idx % 2 === 0) ? ['Hypertension', 'Diabetes Mellitus'] : ['None']),
                'family_medical_history_notes' => null,
                'allergies_food' => $hasAllergy ? 'Crustaceans (Shrimp, Crab)' : null,
                'allergies_drugs' => $hasAllergy ? 'Penicillin / Amoxicillin' : null,
                'allergies_others' => $hasAsthma ? 'House dust mites, pollen' : null,
                'existing_conditions' => $patient->medical_history,
                'current_medications' => $hasHypertension
                    ? 'Amlodipine 5mg OD'
                    : ($hasAsthma ? 'Salbutamol 100mcg MDI PRN' : 'Ascorbic Acid + Zinc OD'),
                'hospitalization_history' => ($idx % 11 === 0) ? json_encode(['Admitted for Dengue Fever (2023)']) : null,
                'surgical_history' => ($idx % 14 === 0) ? json_encode(['Appendectomy (2022)']) : null,
                'smoking_status' => ($patient->type === 'employee' && $idx % 15 === 0) ? 'occasional' : 'non-smoker',
                'alcohol_status' => ($idx % 4 === 0) ? 'occasional' : 'non-drinker',
                'immunizations' => json_encode(['COVID-19 Primary & Booster', 'Hepatitis B', 'Tetanus Toxoid', 'MMR']),
                'notes' => 'Baseline medical profile verified at NVC University Clinic.',
                'updated_by' => $adminUser->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('medical_profiles')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('medical_profiles')->insert($rows);
        }
    }

    private function seedGeneralClinicVisits(
        int $countToAdd,
        Collection $patients,
        Collection $doctors,
        Collection $nurses,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $patients->isEmpty()) {
            return;
        }

        $existing = ClinicVisit::count();
        $now = now()->format('Y-m-d H:i:s');
        $patientCount = $patients->count();
        $doctorCount = $doctors->count();
        $nurseCount = $nurses->count();

        $complaints = [
            'Routine Blood Pressure Monitoring & Vital Signs Check',
            'Pre-OJT & Internship Medical Clearance Request',
            'Varsity & Intramural Sports Fitness Checkup',
            'Follow-up Checkup after Acute Viral Illness',
            'Mild Fatigue and Dizziness after Laboratory Class',
            'Health Counseling and Nutrition Assessment',
        ];

        $rows = [];
        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $patients[($i - 1) % $patientCount];
            $clinician = ($seq % 2 === 0) ? $nurses[$seq % $nurseCount] : $doctors[$seq % $doctorCount];
            $visitDate = $this->resolveVisitDate($i);

            $rows[] = array_merge($this->patientSnapshot($patient), [
                'visit_number' => sprintf('VIS-GN-%s-%06d', $uniquePrefix, $i),
                'patient_id' => $patient->id,
                'visit_type' => ClinicVisit::TYPE_GENERAL,
                'visit_category' => ($seq % 4 === 0) ? 'scheduled' : 'walk_in',
                'visit_date' => $visitDate->format('Y-m-d H:i:s'),
                'time_out' => $visitDate->copy()->addMinutes(20)->format('Y-m-d H:i:s'),
                'chief_complaint' => $complaints[$seq % count($complaints)],
                'systolic' => 112 + ($seq % 16),
                'diastolic' => 72 + ($seq % 10),
                'pulse_rate' => 70 + ($seq % 16),
                'respiratory_rate' => 16 + ($seq % 4),
                'temperature' => 36.6,
                'oxygen_saturation' => 99,
                'pain_scale' => 0,
                'clinician_id' => $clinician->id,
                'recorded_by' => $adminUser->id,
                'disposition' => 'back_to_class',
                'status' => 'completed',
                'remarks' => 'General clinic checkup and vital signs evaluation completed.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('clinic_visits')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('clinic_visits')->insert($rows);
        }
    }

    private function seedPhysicalExaminations(
        int $countToAdd,
        AcademicYear $ay,
        Collection $newPatients,
        Collection $doctors,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        $existing = PhysicalExamination::count();

        $fitnessPool = [
            'Class A',
            'Class A',
            'Class A',
            'Class A',
            'Class A',
            'Class B',
            'Class B',
            'Class C',
            'Class D',
            'Pending / Incomplete',
        ];

        $normalSkin = json_encode(['status' => 'normal', 'remarks' => 'Good skin turgor, warm, no active lesions']);
        $normalHeent = json_encode(['status' => 'normal', 'remarks' => 'Anicteric sclerae, pink palpebral conjunctivae, non-hyperemic pharynx']);
        $normalNeck = json_encode(['status' => 'normal', 'remarks' => 'Supple neck, trachea midline, no cervical lymphadenopathy']);
        $normalLungs = json_encode(['status' => 'normal', 'remarks' => 'Clear breath sounds, symmetrical chest expansion']);
        $abnormalLungs = json_encode(['status' => 'abnormal', 'remarks' => 'Occasional end-expiratory wheezes; clears after bronchodilator']);
        $normalHeart = json_encode(['status' => 'normal', 'remarks' => 'Adynamic precordium, normal rate and regular rhythm']);
        $abnormalHeart = json_encode(['status' => 'abnormal', 'remarks' => 'Sustained elevated BP; regular rhythm, no murmurs']);
        $normalAbdomen = json_encode(['status' => 'normal', 'remarks' => 'Flat, normoactive bowel sounds, soft, non-tender']);
        $normalMusculo = json_encode(['status' => 'normal', 'remarks' => 'Full range of motion across all joints, normal gait']);
        $normalNeuro = json_encode(['status' => 'normal', 'remarks' => 'Cranial nerves II-XII intact, no motor or sensory deficits']);
        $normalDental = json_encode(['status' => 'normal', 'remarks' => 'Good oral hygiene']);
        $abnormalDental = json_encode(['status' => 'abnormal', 'remarks' => 'Mild dental calculus / caries for dental prophylaxis']);

        $fitActivities = json_encode([
            'physical_activities' => 'fit',
            'on_the_job_training' => 'fit',
            'work_immersion' => 'fit',
            'to_work' => 'fit',
            'to_travel' => 'fit',
            'other_requirements' => 'fit',
        ]);
        $notFitActivities = json_encode([
            'physical_activities' => 'not_fit',
            'on_the_job_training' => 'not_fit',
            'work_immersion' => 'not_fit',
            'to_work' => 'not_fit',
            'to_travel' => 'not_fit',
            'other_requirements' => 'not_fit',
        ]);

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];
        $patientCount = $newPatients->count();
        $doctorCount = $doctors->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $newPatients[($i - 1) % $patientCount];
            $doctor = $doctors[$seq % $doctorCount];
            $fitnessClass = $fitnessPool[$seq % count($fitnessPool)];
            $isFit = in_array($fitnessClass, ['Class A', 'Class B'], true);

            $height = $patient->gender === 'Male' ? (162 + ($seq % 20)) : (152 + ($seq % 16));
            $weight = $patient->gender === 'Male' ? (56 + ($seq % 28)) : (46 + ($seq % 24));
            $bmi = round($weight / (($height / 100) ** 2), 2);
            $examinedDate = $this->resolveVisitDate($seq);
            $pePurpose = $patient->type === 'student'
                ? (($seq % 8 === 0) ? 'Athletes / Varsity Sports Clearance' : 'Scheduled Physical Examination')
                : 'Annual Faculty/Staff Physical Exam';

            $rows[] = [
                'pe_number' => sprintf('PE-%s-%06d', $uniquePrefix, $i),
                'patient_id' => $patient->id,
                'academic_year_id' => $ay->id,
                'examiner_id' => $adminUser->id,
                'clinician_id' => $doctor->id,
                'examiner_name' => $doctor->name,
                'examiner_license_number' => $doctor->license_number,
                'exam_date' => $examinedDate->format('Y-m-d'),
                'purpose' => $pePurpose,
                'systolic' => ($fitnessClass === 'Class C') ? 140 : (110 + ($seq % 14)),
                'diastolic' => ($fitnessClass === 'Class C') ? 90 : (70 + ($seq % 12)),
                'pulse_rate' => 68 + ($seq % 20),
                'respiratory_rate' => 16 + ($seq % 4),
                'temperature' => 36.5,
                'oxygen_saturation' => 98 + ($seq % 2),
                'height_cm' => $height,
                'weight_kg' => $weight,
                'bmi' => $bmi,
                'bmi_category' => match (true) {
                    $bmi < 18.5 => 'Underweight',
                    $bmi < 25.0 => 'Normal',
                    $bmi < 30.0 => 'Overweight',
                    default => 'Obese',
                },
                'visual_acuity_right' => ($seq % 6 === 0) ? '20/40' : '20/20',
                'visual_acuity_left' => ($seq % 6 === 0) ? '20/40' : '20/20',
                'visual_acuity_is_corrected' => ($seq % 6 === 0),
                'color_vision' => 'Normal',
                'hearing' => 'Normal',
                'general_appearance' => 'Conscious, coherent, ambulatory, well-nourished, in no acute distress',
                'skin' => $normalSkin,
                'heent' => $normalHeent,
                'neck' => $normalNeck,
                'chest_lungs' => ($fitnessClass === 'Class C' && $seq % 2 === 0) ? $abnormalLungs : $normalLungs,
                'heart' => ($fitnessClass === 'Class C' && $seq % 2 !== 0) ? $abnormalHeart : $normalHeart,
                'abdomen' => $normalAbdomen,
                'musculoskeletal' => $normalMusculo,
                'neurological' => $normalNeuro,
                'dental' => ($seq % 4 === 0) ? $abnormalDental : $normalDental,
                'chest_xray_findings' => 'Normal chest radiograph, no active cardiopulmonary infiltrates',
                'chest_xray_date' => $examinedDate->copy()->subDays(3)->format('Y-m-d'),
                'laboratory_notes' => 'CBC, Urinalysis, Fecalysis, and HBsAg screening within normal parameters',
                'clinical_findings' => match ($fitnessClass) {
                    'Class A' => 'Essentially normal physical examination findings',
                    'Class B' => 'Physically fit with minor correctable visual/dental condition',
                    'Class C' => 'With controlled chronic condition requiring periodic clinic monitoring',
                    'Class D' => 'Requires modified physical education activities; exempted from strenuous drills',
                    default => 'Pending specialist medical clearance',
                },
                'medical_recommendations' => match ($fitnessClass) {
                    'Class A' => 'Continue balanced diet, regular exercise, and adequate hydration.',
                    'Class B' => 'Advised optical refraction / routine dental cleaning at NVC Dental Clinic.',
                    'Class C' => 'Bring maintenance medications to campus; monthly BP/respiratory monitoring.',
                    'Class D' => 'Assign alternative/light PE modules; avoid contact sports.',
                    default => 'Submit Internal Medicine clearance prior to full physical activity.',
                },
                'fitness_assessment' => $fitnessClass,
                'fitness_activities' => $isFit ? $fitActivities : $notFitActivities,
                'status' => $fitnessClass === 'Pending / Incomplete' ? 'pending' : 'completed',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('physical_examinations')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('physical_examinations')->insert($rows);
        }
    }

    private function seedPhysicalExamSchedulesAndAttendees(
        int $countToAdd,
        AcademicYear $ay,
        Collection $colleges,
        Collection $programs,
        Collection $doctors,
        Collection $newPatients,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        $existingSchedules = PhysicalExamSchedule::count();
        $now = now()->format('Y-m-d H:i:s');
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        $rows = [];
        $collegeCount = $colleges->count();
        $programCount = $programs->count();
        $doctorCount = $doctors->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existingSchedules + $i;
            $college = $colleges[$seq % $collegeCount];
            $program = $programs->where('college_id', $college->id)->first() ?? $programs[$seq % $programCount];
            $isCompleted = ($seq % 3 !== 0);
            $scheduleDate = $isCompleted
                ? Carbon::now()->subDays(($seq % 120) + 1)
                : Carbon::now()->addDays(($seq % 30) + 1);

            $rows[] = [
                'schedule_number' => sprintf('PES-%s-%06d', $uniquePrefix, $i),
                'title' => sprintf('%s %s Annual PE Screening Batch #%d', $college->code, $program->code, $seq),
                'purpose' => 'Annual Physical Exam',
                'target_classification' => 'student',
                'academic_year_id' => $ay->id,
                'college_id' => $college->id,
                'program_id' => $program->id,
                'year_level' => $yearLevels[$seq % count($yearLevels)],
                'office_department' => null,
                'scheduled_date' => $scheduleDate->format('Y-m-d'),
                'start_time' => '08:00:00',
                'end_time' => '15:00:00',
                'venue' => 'NVC Main University Clinic - Examination Hall',
                'capacity' => 50,
                'clinician_id' => $doctors[$seq % $doctorCount]->id,
                'status' => $isCompleted ? 'completed' : 'scheduled',
                'notes' => 'Bring validated Student ID, Chest X-Ray film/result, CBC, and Urinalysis results.',
                'created_by' => $adminUser->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('physical_exam_schedules')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('physical_exam_schedules')->insert($rows);
        }

        $newScheduleIds = DB::table('physical_exam_schedules')
            ->where('schedule_number', 'like', "PES-{$uniquePrefix}-%")
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $newPatientIds = $newPatients->pluck('id')->all();
        $peByPatient = DB::table('physical_examinations')
            ->whereIn('patient_id', $newPatientIds)
            ->pluck('id', 'patient_id')
            ->all();

        $sCount = count($newScheduleIds);
        $pCount = count($newPatientIds);

        if ($sCount > 0 && $pCount > 0) {
            $attRows = [];
            for ($i = 0; $i < $countToAdd; $i++) {
                $schedId = $newScheduleIds[$i % $sCount];
                $patId = $newPatientIds[$i % $pCount];
                $status = ($i % 5 === 0) ? 'pending' : (($i % 9 === 0) ? 'no_show' : 'attended');

                $attRows[] = [
                    'physical_exam_schedule_id' => $schedId,
                    'patient_id' => $patId,
                    'status' => $status,
                    'physical_examination_id' => ($status === 'attended') ? ($peByPatient[$patId] ?? null) : null,
                    'clinic_visit_id' => null,
                    'attended_at' => ($status === 'attended') ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($attRows) >= self::CHUNK_SIZE) {
                    DB::table('physical_exam_schedule_attendees')->insert($attRows);
                    $attRows = [];
                }
            }

            if ($attRows !== []) {
                DB::table('physical_exam_schedule_attendees')->insert($attRows);
            }
        }
    }

    private function seedMedicalConsultations(
        int $countToAdd,
        Collection $newPatients,
        Collection $doctors,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        if (DiseaseCategory::count() === 0) {
            $this->call(DiseaseCategorySeeder::class);
        }
        $categoryIdByCode = DiseaseCategory::pluck('id', 'code')->all();

        $existing = MedicalConsultation::count();

        $cases = [
            [
                'category_code' => 'COMM-URTI',
                'chief_complaint' => 'Fever, productive cough, and sore throat for 3 days',
                'diagnosis' => 'Upper Respiratory Tract Infection (URTI)',
                'history' => 'Patient developed low-grade fever (38.2°C), nasal congestion, and sore throat 3 days prior to consultation.',
                'findings' => 'Hyperemic posterior pharyngeal wall, non-exudative tonsils, clear breath sounds.',
                'treatment' => 'Supportive therapy, warm saline gargles, antipyretics, and oral hydration.',
                'prescription' => 'Paracetamol 500mg 1 tab q6h PRN for fever; Cetirizine 10mg 1 tab OD HS for 5 days.',
                'advice' => 'Wear face mask on campus, increase oral fluids, and rest.',
                'icd' => 'J06.9',
                'temp' => 38.2,
                'bp_sys' => 118,
                'bp_dia' => 76,
            ],
            [
                'category_code' => 'COMM-URTI',
                'chief_complaint' => 'High-grade fever, body malaise, myalgia, and frontal headache',
                'diagnosis' => 'Acute Viral Influenza Syndrome',
                'history' => 'Sudden onset of chills, fever (38.6°C), generalized body aches, and fatigue starting 2 days ago.',
                'findings' => 'Febrile, flushed skin, mildly congested nasal turbinates, lungs clear to auscultation.',
                'treatment' => 'Administered initial dose of Paracetamol 500mg PO; advised home isolation and rest.',
                'prescription' => 'Paracetamol 500mg 1 tab q4-6h PRN for fever >= 37.8°C; Ascorbic Acid + Zinc OD.',
                'advice' => 'Home respiratory isolation for 48 hours until afebrile.',
                'icd' => 'J11.1',
                'temp' => 38.6,
                'bp_sys' => 114,
                'bp_dia' => 72,
            ],
            [
                'category_code' => 'NCD-GI',
                'chief_complaint' => 'Loose watery stools (4 episodes) with mild periumbilical cramps',
                'diagnosis' => 'Acute Gastroenteritis with Mild Dehydration',
                'history' => 'Started having watery non-bloody stools after eating outside campus yesterday evening.',
                'findings' => 'Slightly dry oral mucosa, hyperactive bowel sounds, soft abdomen with mild periumbilical tenderness.',
                'treatment' => 'Oral rehydration therapy initiated in clinic (1 sachet ORS in 200mL water).',
                'prescription' => 'Oral Rehydration Salts (Hydrite) 1 sachet per loose stool; BRAT diet.',
                'advice' => 'Avoid dairy, oily, and spicy foods for 3 days.',
                'icd' => 'A09.0',
                'temp' => 37.3,
                'bp_sys' => 106,
                'bp_dia' => 68,
            ],
            [
                'category_code' => 'COMM-URTI',
                'chief_complaint' => 'Persistent productive cough for 5 days with mild chest tightness',
                'diagnosis' => 'Acute Bronchitis',
                'history' => 'Cough initially dry now productive of yellowish sputum; no hemoptysis or night sweats.',
                'findings' => 'Coarse rhonchi over bilateral mid-lung fields that clear partially with coughing; SpO2 98%.',
                'treatment' => 'Steam inhalation advice, mucolytic therapy, and antibacterial coverage.',
                'prescription' => 'Amoxicillin 500mg 1 cap TID for 7 days; increase oral fluid intake.',
                'advice' => 'Complete full 7-day antibiotic course and return for lung re-auscultation.',
                'icd' => 'J20.9',
                'temp' => 37.6,
                'bp_sys' => 120,
                'bp_dia' => 78,
            ],
            [
                'category_code' => 'COMM-SVI',
                'chief_complaint' => 'Redness, tearing, and gritty sensation in both eyes',
                'diagnosis' => 'Acute Bacterial Conjunctivitis',
                'history' => 'Woke up with mucopurulent discharge crusting on eyelashes of right eye, spreading to left eye.',
                'findings' => 'Bilateral bulbar and palpebral conjunctival injection with yellowish discharge; visual acuity preserved.',
                'treatment' => 'Sterile eye irrigation; strict hand hygiene counseling.',
                'prescription' => 'Tobramycin 0.3% Ophthalmic Drops 1-2 drops q4h OU for 7 days.',
                'advice' => 'Avoid rubbing eyes or sharing towels; temporary class excuse issued.',
                'icd' => 'H10.3',
                'temp' => 36.8,
                'bp_sys' => 116,
                'bp_dia' => 74,
            ],
            [
                'category_code' => 'NCD-CARDIO',
                'chief_complaint' => 'Nape heaviness, dizziness, and elevated blood pressure',
                'diagnosis' => 'Essential Hypertension (Uncontrolled)',
                'history' => 'Known hypertensive patient who missed maintenance dose this morning; experienced occipital heaviness.',
                'findings' => 'BP 154/96 mmHg, HR 86 bpm regular, lungs clear, no focal neurological deficits.',
                'treatment' => 'Placed on bed rest in quiet clinic bay; administered Amlodipine 5mg PO.',
                'prescription' => 'Amlodipine Besilate 5mg 1 tab OD daily; strict low-sodium diet and daily BP diary.',
                'advice' => 'Monitor blood pressure daily at NVC Clinic for 1 week.',
                'icd' => 'I10',
                'temp' => 36.7,
                'bp_sys' => 154,
                'bp_dia' => 96,
            ],
            [
                'category_code' => 'NCD-NEURO',
                'chief_complaint' => 'Throbbing unilateral headache with photophobia',
                'diagnosis' => 'Tension-Type Headache / Acute Migraine',
                'history' => 'Throbbing right frontotemporal headache triggered by sleep deprivation during examinations.',
                'findings' => 'Alert, oriented, cranial nerves intact, mild pericranial muscle tenderness, no meningeal signs.',
                'treatment' => 'Rested in darkened clinic observation room for 1 hour; given analgesic.',
                'prescription' => 'Ibuprofen 200mg 1-2 caps PRN after meals for severe headache.',
                'advice' => 'Maintain regular sleep schedule and screen breaks.',
                'icd' => 'G44.2',
                'temp' => 36.6,
                'bp_sys' => 118,
                'bp_dia' => 76,
            ],
            [
                'category_code' => 'NCD-GI',
                'chief_complaint' => 'Epigastric burning pain and acid regurgitation',
                'diagnosis' => 'Acid Peptic Disease / Acute Gastritis',
                'history' => 'Skipped breakfast and drank strong coffee; developed gnawing epigastric pain.',
                'findings' => 'Normoactive bowel sounds, mild direct tenderness over epigastrium, Murphy sign negative.',
                'treatment' => 'Given Kremil-S chewable antacid stat; dietary counseling on small frequent meals.',
                'prescription' => 'Antacid (Kremil-S) 1 tab chewable TID 1 hour after meals and at bedtime.',
                'advice' => 'Avoid skipping meals, carbonated drinks, coffee, and spicy foods.',
                'icd' => 'K29.7',
                'temp' => 36.5,
                'bp_sys' => 112,
                'bp_dia' => 72,
            ],
            [
                'category_code' => 'NCD-REPRO',
                'chief_complaint' => 'Severe lower abdominal cramping on Day 1 of menses',
                'diagnosis' => 'Primary Dysmenorrhea',
                'history' => 'Recurrent crampy suprapubic pain radiating to lower back on first day of menstrual period.',
                'findings' => 'Soft abdomen, mild suprapubic tenderness, no guarding or rebound.',
                'treatment' => 'Warm compress applied over lower abdomen; given Mefenamic Acid 500mg PO.',
                'prescription' => 'Mefenamic Acid 500mg 1 cap TID PC PRN for menstrual cramps.',
                'advice' => 'Apply warm compress and take analgesic with meals.',
                'icd' => 'N94.4',
                'temp' => 36.7,
                'bp_sys' => 110,
                'bp_dia' => 70,
            ],
            [
                'category_code' => 'NCD-RESP',
                'chief_complaint' => 'Episodic wheezing and shortness of breath after climbing stairs',
                'diagnosis' => 'Bronchial Asthma in Mild Acute Exacerbation',
                'history' => 'Known asthmatic exposed to dust during campus cleanup; experienced chest tightness and wheezing.',
                'findings' => 'Diffuse expiratory wheezes bilateral lung fields, RR 22 cpm, SpO2 96% on room air.',
                'treatment' => 'Salbutamol 2.5mg/2.5mL nebulization x 1 dose; post-neb lungs clear, SpO2 99%.',
                'prescription' => 'Salbutamol Sulfate Nebule PRN for acute bronchospasm; Cetirizine 10mg OD.',
                'advice' => 'Avoid dust triggers and carry rescue bronchodilator inhaler at all times.',
                'icd' => 'J45.901',
                'temp' => 36.8,
                'bp_sys' => 122,
                'bp_dia' => 78,
            ],
            [
                'category_code' => 'COMM-TB',
                'chief_complaint' => 'Chronic productive cough for 3 weeks with low-grade afternoon fever',
                'diagnosis' => 'Pulmonary Tuberculosis (Presumptive TB for DOTS Referral)',
                'history' => 'Chronic cough for 3 weeks associated with night sweats and mild weight loss.',
                'findings' => 'Decreased breath sounds right upper lung zone; referred for Chest X-Ray and GeneXpert.',
                'treatment' => 'Respiratory isolation precautions, N95 mask provided, referred to TB-DOTS Center.',
                'prescription' => 'Ascorbic Acid + Zinc OD; diagnostic sputum AFB and GeneXpert request.',
                'advice' => 'Report immediately to Municipal Health Office TB-DOTS facility.',
                'icd' => 'A15.0',
                'temp' => 37.8,
                'bp_sys' => 112,
                'bp_dia' => 70,
            ],
            [
                'category_code' => 'NCD-GU',
                'chief_complaint' => 'Dysuria, urinary frequency, and suprapubic discomfort for 2 days',
                'diagnosis' => 'Acute Uncomplicated Urinary Tract Infection (UTI)',
                'history' => 'Painful burning urination and frequent small-volume voiding after low oral water intake.',
                'findings' => 'Mild suprapubic tenderness, negative costovertebral angle tenderness bilaterally.',
                'treatment' => 'Requested routine urinalysis; initiated empirical oral antibiotic and hydration.',
                'prescription' => 'Cefuroxime 500mg 1 tab BID for 7 days; increase water intake to 2.5L/day.',
                'advice' => 'Avoid holding urine and carbonated or caffeinated beverages.',
                'icd' => 'N39.0',
                'temp' => 37.1,
                'bp_sys' => 114,
                'bp_dia' => 74,
            ],
            [
                'category_code' => 'NCD-SKIN',
                'chief_complaint' => 'Itchy erythematous skin rashes on both forearms',
                'diagnosis' => 'Acute Contact Dermatitis / Allergic Skin Rashes',
                'history' => 'Developed pruritic erythematous maculopapular rashes on forearms after laboratory activity.',
                'findings' => 'Well-demarcated erythematous papules on bilateral volar forearms, no secondary infection.',
                'treatment' => 'Cool compress applied; given oral antihistamine STAT.',
                'prescription' => 'Cetirizine 10mg 1 tab OD HS for 5 days; Hydrocortisone 1% cream BID thinly.',
                'advice' => 'Avoid scratching and use mild hypoallergenic soap.',
                'icd' => 'L23.9',
                'temp' => 36.6,
                'bp_sys' => 116,
                'bp_dia' => 74,
            ],
            [
                'category_code' => 'NCD-MSK',
                'chief_complaint' => 'Lower back pain and right ankle sprain after PE class',
                'diagnosis' => 'Acute Musculoskeletal Strain / Ankle Sprain',
                'history' => 'Twisted right ankle and strained lower back during physical education basketball drill.',
                'findings' => 'Mild swelling over right lateral malleolus, tender paraspinal muscles, neurovascular intact.',
                'treatment' => 'RICE therapy (Rest, Ice compress, Compression elastic bandage, Elevation).',
                'prescription' => 'Mefenamic Acid 500mg 1 cap TID PC PRN for pain.',
                'advice' => 'Exempted from strenuous PE drills for 5 days.',
                'icd' => 'S93.4',
                'temp' => 36.7,
                'bp_sys' => 120,
                'bp_dia' => 78,
            ],
            [
                'category_code' => 'NCD-MENTAL',
                'chief_complaint' => 'Palpitations,hyperventilation, and acute academic anxiety episode',
                'diagnosis' => 'Acute Situational Anxiety Episode (Mental and Behavioral)',
                'history' => 'Experienced sudden chest tightness, trembling, and hyperventilation prior to thesis defense.',
                'findings' => 'Alert, tachycardic HR 104 bpm, SpO2 99%, clear breath sounds, no focal neurologic deficit.',
                'treatment' => 'Guided paced breathing exercises in quiet clinic room; supportive counseling.',
                'prescription' => 'Supportive care; referred to University Guidance & Counseling Office.',
                'advice' => 'Practice grounding techniques and follow up with Guidance Counselor.',
                'icd' => 'F41.9',
                'temp' => 36.7,
                'bp_sys' => 126,
                'bp_dia' => 82,
            ],
            [
                'category_code' => 'NCD-DISABILITY',
                'chief_complaint' => 'Routine wellness check and mobility assistance for PWD student',
                'diagnosis' => 'Illness of Student with Disability (PWD Supportive Care)',
                'history' => 'Enrolled student with orthopedic disability presenting for fatigue and assistive monitoring.',
                'findings' => 'Stable vital signs, mild muscle fatigue in lower extremities.',
                'treatment' => 'Rest in accessible clinic bay, hydration, and class coordination.',
                'prescription' => 'Multivitamins + B-Complex 1 cap OD.',
                'advice' => 'Coordinate classroom accessibility accommodation with Dean.',
                'icd' => 'Z74.0',
                'temp' => 36.6,
                'bp_sys' => 112,
                'bp_dia' => 72,
            ],
            [
                'category_code' => 'NCD-ENDO',
                'chief_complaint' => 'Dizziness and general weakness due to skipped meals and underweight status',
                'diagnosis' => 'Nutritional Deficiency / Mild Malnutrition (Endocrine & Metabolic)',
                'history' => 'Student reported skipping meals for 2 days with BMI 16.8 kg/m2 and mild orthostatic dizziness.',
                'findings' => 'Pale palpebral conjunctivae, BMI 16.8 (Underweight), lungs clear.',
                'treatment' => 'Provided caloric oral nutrition drink in clinic and dietary counseling.',
                'prescription' => 'Ferrous Sulfate + Folic Acid 1 tab OD; Multivitamins OD.',
                'advice' => 'Follow balanced high-protein diet and return for weight monitoring in 2 weeks.',
                'icd' => 'E44.1',
                'temp' => 36.5,
                'bp_sys' => 102,
                'bp_dia' => 66,
            ],
        ];

        $now = now()->format('Y-m-d H:i:s');
        $visitRows = [];
        $visitCodes = [];
        $patientCount = $newPatients->count();
        $doctorCount = $doctors->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $newPatients[($i - 1) % $patientCount];
            $doctor = $doctors[$seq % $doctorCount];
            $case = $cases[$seq % count($cases)];
            $visitDate = $this->resolveVisitDate($i);
            $visitNumber = sprintf('VIS-MC-%s-%06d', $uniquePrefix, $i);
            $visitCodes[] = $visitNumber;

            $issueCert = ($seq % 3 === 0);
            $issueReferral = ($seq % 5 === 0);

            $visitRows[] = array_merge($this->patientSnapshot($patient), [
                'visit_number' => $visitNumber,
                'patient_id' => $patient->id,
                'visit_type' => ClinicVisit::TYPE_MEDICAL,
                'visit_category' => 'walk_in',
                'visit_date' => $visitDate->format('Y-m-d H:i:s'),
                'time_out' => $visitDate->copy()->addMinutes(28)->format('Y-m-d H:i:s'),
                'chief_complaint' => $case['chief_complaint'],
                'systolic' => $case['bp_sys'],
                'diastolic' => $case['bp_dia'],
                'pulse_rate' => 72 + ($seq % 18),
                'respiratory_rate' => 16 + ($seq % 4),
                'temperature' => $case['temp'],
                'oxygen_saturation' => 98,
                'pain_scale' => ($seq % 5) + 1,
                'clinician_id' => $doctor->id,
                'recorded_by' => $adminUser->id,
                'disposition' => $issueReferral ? 'referred' : ($issueCert ? 'sent_home' : 'back_to_class'),
                'status' => 'completed',
                'remarks' => 'Medical consultation completed and logged.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($visitRows) >= self::CHUNK_SIZE) {
                DB::table('clinic_visits')->insert($visitRows);
                $visitRows = [];
            }
        }

        if ($visitRows !== []) {
            DB::table('clinic_visits')->insert($visitRows);
        }

        $visitIdMap = DB::table('clinic_visits')
            ->where('visit_number', 'like', "VIS-MC-{$uniquePrefix}-%")
            ->pluck('id', 'visit_number')
            ->all();

        $consultRows = [];
        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $visitNumber = $visitCodes[$i - 1];
            $visitId = $visitIdMap[$visitNumber] ?? null;

            if (! $visitId) {
                continue;
            }

            $case = $cases[$seq % count($cases)];
            $visitDate = $this->resolveVisitDate($i);
            $issueCert = ($seq % 3 === 0);
            $issueReferral = ($seq % 5 === 0);

            $consultRows[] = [
                'clinic_visit_id' => $visitId,
                'disease_category_id' => $categoryIdByCode[$case['category_code']] ?? null,
                'history_of_present_illness' => $case['history'],
                'physical_findings' => $case['findings'],
                'diagnosis' => $case['diagnosis'],
                'icd_code' => $case['icd'],
                'treatment_plan' => $case['treatment'],
                'medications_prescribed' => $case['prescription'],
                'advice' => $case['advice'],
                'medical_certificate_issued' => $issueCert,
                'rest_days' => $issueCert ? (($seq % 3) + 1) : null,
                'referred_to' => $issueReferral ? 'Dr. Rafael S. Tumbokon Memorial Hospital - Outpatient Specialty Dept.' : null,
                'referral_reason' => $issueReferral ? 'For further diagnostic workup and specialist co-management' : null,
                'follow_up_date' => $visitDate->copy()->addDays(5)->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($consultRows) >= self::CHUNK_SIZE) {
                DB::table('medical_consultations')->insert($consultRows);
                $consultRows = [];
            }
        }

        if ($consultRows !== []) {
            DB::table('medical_consultations')->insert($consultRows);
        }
    }

    private function seedDentalConsultations(
        int $countToAdd,
        Collection $newPatients,
        Collection $dentists,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        $existing = DentalConsultation::count();

        $dentalCases = [
            [
                'chief_complaint' => 'Routine oral examination and dental prophylaxis',
                'diagnosis' => 'Moderate Dental Calculus with Localized Marginal Gingivitis; Caries on #36 and #46',
                'procedures' => json_encode(['Oral Examination / Consultation', 'Oral Prophylaxis (Cleaning)', 'Fluoride Application']),
                'teeth' => '#36, #46, Lower Anterior Linguals',
                'anesthesia' => 'None',
                'treatment_rendered' => 'Performed full-mouth ultrasonic scaling, coronal polishing, and topical fluoride gel application.',
                'treatment_plan' => 'Schedule permanent composite restoration for teeth #36 and #46 next visit.',
                'recommendations' => 'Brush twice daily using modified Bass technique and dental floss nightly.',
                'decayed' => 2,
                'missing' => 0,
                'filled' => 3,
                'gingivitis' => true,
                'calculus' => true,
                'priority' => 'routine',
                'chart' => json_encode(['16' => 'F', '26' => 'F', '36' => 'C', '37' => 'F', '46' => 'C']),
            ],
            [
                'chief_complaint' => 'Sensitivity to cold drinks and sweet food on lower left molar',
                'diagnosis' => 'Reversible Pulpitis secondary to Class I Dental Caries on Tooth #36',
                'procedures' => json_encode(['Oral Examination / Consultation', 'Permanent Filling (Restoration)']),
                'teeth' => '#36 (Lower Left 1st Molar)',
                'anesthesia' => 'None',
                'treatment_rendered' => 'Caries excavation on tooth #36, glass ionomer liner placement, and light-cured composite resin restoration.',
                'treatment_plan' => 'Re-evaluate restoration margins after 6 months.',
                'recommendations' => 'Avoid chewing hard foods for 2 hours; maintain twice-daily fluoridated brushing.',
                'decayed' => 1,
                'missing' => 1,
                'filled' => 4,
                'gingivitis' => false,
                'calculus' => false,
                'priority' => 'soon',
                'chart' => json_encode(['16' => 'F', '26' => 'F', '36' => 'F', '38' => 'M', '46' => 'C']),
            ],
            [
                'chief_complaint' => 'Severe throbbing toothache on upper right first molar (#16)',
                'diagnosis' => 'Symptomatic Irreversible Pulpitis / Grossly Carious Tooth #16 for Extraction',
                'procedures' => json_encode(['Oral Examination / Consultation', 'Tooth Extraction', 'Prescription Only']),
                'teeth' => '#16 (Upper Right 1st Molar)',
                'anesthesia' => 'Lidocaine 2% with 1:100,000 Epinephrine 1.8mL',
                'treatment_rendered' => 'Aseptic technique, posterior superior alveolar nerve block with 2% Lidocaine, atraumatic extraction of #16, hemostasis achieved.',
                'treatment_plan' => 'Follow up in 5 days for socket healing inspection.',
                'recommendations' => 'Bite firmly on sterile gauze for 30 minutes; soft cold diet today; no vigorous rinsing for 24 hours.',
                'decayed' => 3,
                'missing' => 2,
                'filled' => 2,
                'gingivitis' => true,
                'calculus' => true,
                'priority' => 'urgent',
                'chart' => json_encode(['16' => 'X', '26' => 'F', '36' => 'C', '37' => 'F', '38' => 'M', '46' => 'C']),
            ],
        ];

        $now = now()->format('Y-m-d H:i:s');
        $visitRows = [];
        $visitCodes = [];
        $patientCount = $newPatients->count();
        $dentistCount = $dentists->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $newPatients[($i + 2) % $patientCount];
            $dentist = $dentists[$seq % $dentistCount];
            $dCase = $dentalCases[$seq % count($dentalCases)];
            $visitDate = $this->resolveVisitDate($i + 2);
            $visitNumber = sprintf('VIS-DC-%s-%06d', $uniquePrefix, $i);
            $visitCodes[] = $visitNumber;

            $visitRows[] = array_merge($this->patientSnapshot($patient), [
                'visit_number' => $visitNumber,
                'patient_id' => $patient->id,
                'visit_type' => ClinicVisit::TYPE_DENTAL,
                'visit_category' => 'scheduled',
                'visit_date' => $visitDate->format('Y-m-d H:i:s'),
                'time_out' => $visitDate->copy()->addMinutes(40)->format('Y-m-d H:i:s'),
                'chief_complaint' => $dCase['chief_complaint'],
                'systolic' => 118,
                'diastolic' => 76,
                'pulse_rate' => 74,
                'respiratory_rate' => 18,
                'temperature' => 36.6,
                'oxygen_saturation' => 99,
                'pain_scale' => ($dCase['priority'] === 'urgent') ? 6 : 1,
                'clinician_id' => $dentist->id,
                'recorded_by' => $adminUser->id,
                'disposition' => 'back_to_class',
                'status' => 'completed',
                'remarks' => 'Oral health examination and dental procedure completed.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($visitRows) >= self::CHUNK_SIZE) {
                DB::table('clinic_visits')->insert($visitRows);
                $visitRows = [];
            }
        }

        if ($visitRows !== []) {
            DB::table('clinic_visits')->insert($visitRows);
        }

        $visitIdMap = DB::table('clinic_visits')
            ->where('visit_number', 'like', "VIS-DC-{$uniquePrefix}-%")
            ->pluck('id', 'visit_number')
            ->all();

        $emptyJson = json_encode([]);
        $dentalRows = [];

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $visitNumber = $visitCodes[$i - 1];
            $visitId = $visitIdMap[$visitNumber] ?? null;

            if (! $visitId) {
                continue;
            }

            $dCase = $dentalCases[$seq % count($dentalCases)];
            $visitDate = $this->resolveVisitDate($i + 2);

            $dentalRows[] = [
                'clinic_visit_id' => $visitId,
                'oral_examination_findings' => 'Permanent dentition evaluated; occlusal pits, fissures, and gingival margins inspected.',
                'tooth_chart' => $dCase['chart'],
                'primary_tooth_chart' => $emptyJson,
                'procedures' => $dCase['procedures'],
                'teeth_involved' => $dCase['teeth'],
                'anesthesia_used' => $dCase['anesthesia'],
                'diagnosis' => $dCase['diagnosis'],
                'treatment_rendered' => $dCase['treatment_rendered'],
                'recommendations' => $dCase['recommendations'],
                'oral_hygiene_status' => $dCase['calculus'] ? 'Fair' : 'Good',
                'follow_up_date' => $visitDate->copy()->addMonths(6)->format('Y-m-d'),
                'previous_dental_treatment' => 'Oral Prophylaxis & Composite Filling',
                'dental_treatment_frequency' => 'regular',
                'last_dental_visit' => $visitDate->copy()->subMonths(7)->format('Y-m-d'),
                'dental_fears' => false,
                'dental_concerns' => $dCase['chief_complaint'],
                'has_toothache' => ($dCase['priority'] === 'urgent'),
                'has_bleeding_gums' => $dCase['gingivitis'],
                'has_bad_breath' => false,
                'has_tooth_sensitivity' => true,
                'has_jaw_pain' => false,
                'has_loose_teeth' => false,
                'has_swelling' => ($dCase['priority'] === 'urgent'),
                'systemic_diabetes' => false,
                'systemic_hypertension' => false,
                'systemic_heart_disease' => false,
                'systemic_bleeding_disorder' => false,
                'systemic_allergies' => false,
                'systemic_allergy_details' => null,
                'soft_tissue_lips' => 'normal',
                'soft_tissue_cheeks' => 'normal',
                'soft_tissue_tongue' => 'normal',
                'soft_tissue_palate' => 'normal',
                'soft_tissue_gingiva' => $dCase['gingivitis'] ? 'inflamed' : 'normal',
                'soft_tissue_floor_of_mouth' => 'normal',
                'periodontal_status' => $dCase['gingivitis'] ? 'gingivitis' : 'healthy',
                'calculus_index' => $dCase['calculus'] ? 'moderate' : 'none',
                'gingival_index' => $dCase['gingivitis'] ? 'mild' : 'healthy',
                'plaque_index' => $dCase['calculus'] ? 'fair' : 'good',
                'dmft_decayed' => $dCase['decayed'],
                'dmft_missing' => $dCase['missing'],
                'dmft_filled' => $dCase['filled'],
                'dmfs_decayed' => $dCase['decayed'] * 2,
                'dmfs_missing' => $dCase['missing'] * 5,
                'dmfs_filled' => $dCase['filled'] * 2,
                'treatment_plan' => $dCase['treatment_plan'],
                'priority_level' => $dCase['priority'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($dentalRows) >= self::CHUNK_SIZE) {
                DB::table('dental_consultations')->insert($dentalRows);
                $dentalRows = [];
            }
        }

        if ($dentalRows !== []) {
            DB::table('dental_consultations')->insert($dentalRows);
        }
    }

    private function seedEmergencyRecords(
        int $countToAdd,
        Collection $newPatients,
        Collection $doctors,
        Collection $nurses,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        $existing = EmergencyRecord::count();

        $emergencyCases = [
            [
                'chief_complaint' => 'Sudden syncope and heat exhaustion during outdoor ROTC/PE drill',
                'nature' => 'Syncope / Loss of Consciousness',
                'triage' => 'yellow',
                'location' => 'NVC Main Quadrangle / Athletic Oval',
                'description' => 'Student experienced lightheadedness and brief syncope after prolonged standing in warm weather.',
                'interventions' => json_encode(['Oxygen Therapy', 'Recovery Position', 'Monitoring of Vital Signs']),
                'notes' => 'Placed in Trendelenburg position, loosened uniform, applied cool compress, gave O2 at 2 LPM via nasal cannula.',
                'transfer' => false,
                'outcome' => 'Stabilized and Discharged',
            ],
            [
                'chief_complaint' => 'Acute severe bronchospasm and dyspnea (SpO2 91%)',
                'nature' => 'Severe Asthma / Difficulty of Breathing',
                'triage' => 'red',
                'location' => 'Academic Building 1 - 3rd Floor Lecture Room',
                'description' => 'Patient developed severe shortness of breath and audible wheezing with SpO2 dropping to 91% on room air.',
                'interventions' => json_encode(['Oxygen Therapy', 'Nebulization', 'Monitoring of Vital Signs']),
                'notes' => 'Administered high-flow O2 at 4 LPM and back-to-back Salbutamol nebulization; transferred to hospital ER.',
                'transfer' => true,
                'outcome' => 'Transferred to Hospital',
            ],
            [
                'chief_complaint' => 'Acute allergic urticaria and facial angioedema after food ingestion',
                'nature' => 'Severe Allergic Reaction / Anaphylaxis',
                'triage' => 'yellow',
                'location' => 'NVC University Canteen',
                'description' => 'Developed generalized pruritic wheals and mild periorbital swelling 20 minutes after eating lunch.',
                'interventions' => json_encode(['Emergency Medication Given', 'Airway Management', 'Monitoring of Vital Signs']),
                'notes' => 'Airway patent, no stridor. Administered Cetirizine 10mg PO and monitored vital signs every 15 minutes.',
                'transfer' => false,
                'outcome' => 'Stabilized and Sent Home',
            ],
        ];

        $now = now()->format('Y-m-d H:i:s');
        $visitRows = [];
        $visitCodes = [];
        $patientCount = $newPatients->count();
        $doctorCount = $doctors->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $newPatients[($i + 5) % $patientCount];
            $doctor = $doctors[$seq % $doctorCount];
            $eCase = $emergencyCases[$seq % count($emergencyCases)];
            $visitDate = $this->resolveVisitDate($i + 4);
            $visitNumber = sprintf('VIS-ER-%s-%06d', $uniquePrefix, $i);
            $visitCodes[] = $visitNumber;

            $visitRows[] = array_merge($this->patientSnapshot($patient), [
                'visit_number' => $visitNumber,
                'patient_id' => $patient->id,
                'visit_type' => ClinicVisit::TYPE_EMERGENCY,
                'visit_category' => 'emergency_call',
                'visit_date' => $visitDate->format('Y-m-d H:i:s'),
                'time_out' => $visitDate->copy()->addMinutes(55)->format('Y-m-d H:i:s'),
                'chief_complaint' => $eCase['chief_complaint'],
                'systolic' => 94,
                'diastolic' => 62,
                'pulse_rate' => 110,
                'respiratory_rate' => 24,
                'temperature' => 37.6,
                'oxygen_saturation' => 94,
                'pain_scale' => 4,
                'clinician_id' => $doctor->id,
                'recorded_by' => $adminUser->id,
                'disposition' => $eCase['transfer'] ? 'referred' : 'observation',
                'status' => 'completed',
                'remarks' => 'Emergency triage and stabilization protocol executed.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($visitRows) >= self::CHUNK_SIZE) {
                DB::table('clinic_visits')->insert($visitRows);
                $visitRows = [];
            }
        }

        if ($visitRows !== []) {
            DB::table('clinic_visits')->insert($visitRows);
        }

        $visitIdMap = DB::table('clinic_visits')
            ->where('visit_number', 'like', "VIS-ER-{$uniquePrefix}-%")
            ->pluck('id', 'visit_number')
            ->all();

        $erRows = [];
        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $visitNumber = $visitCodes[$i - 1];
            $visitId = $visitIdMap[$visitNumber] ?? null;

            if (! $visitId) {
                continue;
            }

            $patient = $newPatients[($i + 5) % $patientCount];
            $eCase = $emergencyCases[$seq % count($emergencyCases)];
            $visitDate = $this->resolveVisitDate($i + 4);

            $erRows[] = [
                'clinic_visit_id' => $visitId,
                'incident_datetime' => $visitDate->copy()->subMinutes(15)->format('Y-m-d H:i:s'),
                'incident_location' => $eCase['location'],
                'emergency_nature' => $eCase['nature'],
                'triage_level' => $eCase['triage'],
                'level_of_consciousness' => 'alert',
                'gcs_score' => 15,
                'incident_description' => $eCase['description'],
                'interventions' => $eCase['interventions'],
                'intervention_notes' => $eCase['notes'],
                'ambulance_called' => $eCase['transfer'],
                'transferred_to_hospital' => $eCase['transfer'],
                'hospital_name' => $eCase['transfer'] ? 'Dr. Rafael S. Tumbokon Memorial Hospital' : null,
                'transport_mode' => $eCase['transfer'] ? 'NVC Emergency Response Vehicle' : null,
                'transferred_at' => $eCase['transfer'] ? $visitDate->copy()->addMinutes(40)->format('Y-m-d H:i:s') : null,
                'guardian_notified' => true,
                'notified_person' => $patient->emergency_contact_name ?? 'Parent / Guardian',
                'notified_at' => $visitDate->copy()->addMinutes(10)->format('Y-m-d H:i:s'),
                'incident_report_number' => sprintf('IR-%s-%06d', $uniquePrefix, $i),
                'outcome' => $eCase['outcome'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($erRows) >= self::CHUNK_SIZE) {
                DB::table('emergency_records')->insert($erRows);
                $erRows = [];
            }
        }

        if ($erRows !== []) {
            DB::table('emergency_records')->insert($erRows);
        }
    }

    private function seedFirstAidRecords(
        int $countToAdd,
        Collection $newPatients,
        Collection $nurses,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty()) {
            return;
        }

        $existing = FirstAidRecord::count();

        $firstAidCases = [
            [
                'chief_complaint' => 'Superficial abrasion and laceration on right knee after slipping on walkway',
                'injury_type' => 'Abrasion / Scratch',
                'body_part' => 'Right knee and anterior shin',
                'location' => 'Covered Walkway near Engineering Building',
                'cause' => 'Slipped on wet pavement during afternoon rain',
                'given' => json_encode(['Wound Cleaning', 'Antiseptic Applied', 'Dressing / Bandage Applied']),
                'notes' => 'Irrigated wound with sterile normal saline, cleansed with Povidone-Iodine 10%, covered with sterile gauze pad.',
                'supplies' => 'Sterile Normal Saline 50mL, Betadine 10% solution, Sterile Gauze Pad 4x4 (2 pcs), Micropore Tape',
            ],
            [
                'chief_complaint' => 'Right ankle inversion sprain during intramural basketball game',
                'injury_type' => 'Sprain / Strain',
                'body_part' => 'Right lateral ankle (lateral malleolus)',
                'location' => 'NVC University Gymnasium Basketball Court',
                'cause' => 'Awkward landing during intramural basketball match',
                'given' => json_encode(['Cold Compress / Ice Pack', 'Elastic Bandage Immobilization', 'Limb Elevation']),
                'notes' => 'Applied RICE protocol (Rest, Ice pack for 20 minutes, Compression with 3-inch elastic bandage, Elevation).',
                'supplies' => 'Cold Pack, Elastic Bandage (3-inch), Ibuprofen 200mg cap',
            ],
            [
                'chief_complaint' => 'Minor first-degree thermal burn on left forearm from laboratory hotplate',
                'injury_type' => 'Minor Burn / Scald',
                'body_part' => 'Left volar forearm (2cm x 1.5cm)',
                'location' => 'Science & Chemistry Laboratory Room 204',
                'cause' => 'Brief accidental contact with heated glass beaker',
                'given' => json_encode(['Cool Running Water Irrigation', 'Burn Ointment Applied', 'Sterile Non-Adherent Dressing']),
                'notes' => 'Cooled under gentle running water for 15 minutes, applied burn ointment and non-adherent sterile dressing.',
                'supplies' => 'Sterile Water, Silver Sulfadiazine Ointment, Non-adherent sterile pad',
            ],
            [
                'chief_complaint' => 'Spontaneous anterior epistaxis (nosebleed)',
                'injury_type' => 'Epistaxis / Nosebleed',
                'body_part' => 'Anterior nasal cavity',
                'location' => 'Library Reading Area',
                'cause' => 'Dry nasal mucosa / ambient afternoon heat',
                'given' => json_encode(['Direct Nasal Compression', 'Cold Compress on Nasal Bridge', 'Vital Signs Check']),
                'notes' => 'Seated upright leaning slightly forward, direct alar compression for 10 minutes with cold compress over nasal bridge.',
                'supplies' => 'Ice pack, Sterile cotton balls, Disposable examination gloves',
            ],
        ];

        $now = now()->format('Y-m-d H:i:s');
        $visitRows = [];
        $visitCodes = [];
        $patientCount = $newPatients->count();
        $nurseCount = $nurses->count();

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $patient = $newPatients[($i + 9) % $patientCount];
            $nurse = $nurses[$seq % $nurseCount];
            $faCase = $firstAidCases[$seq % count($firstAidCases)];
            $visitDate = $this->resolveVisitDate($i + 6);
            $visitNumber = sprintf('VIS-FA-%s-%06d', $uniquePrefix, $i);
            $visitCodes[] = $visitNumber;

            $visitRows[] = array_merge($this->patientSnapshot($patient), [
                'visit_number' => $visitNumber,
                'patient_id' => $patient->id,
                'visit_type' => ClinicVisit::TYPE_FIRST_AID,
                'visit_category' => 'walk_in',
                'visit_date' => $visitDate->format('Y-m-d H:i:s'),
                'time_out' => $visitDate->copy()->addMinutes(20)->format('Y-m-d H:i:s'),
                'chief_complaint' => $faCase['chief_complaint'],
                'systolic' => 118,
                'diastolic' => 76,
                'pulse_rate' => 78,
                'respiratory_rate' => 18,
                'temperature' => 36.7,
                'oxygen_saturation' => 99,
                'pain_scale' => 3,
                'clinician_id' => $nurse->id,
                'recorded_by' => $adminUser->id,
                'disposition' => 'back_to_class',
                'status' => 'completed',
                'remarks' => 'First aid care rendered; patient stable.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($visitRows) >= self::CHUNK_SIZE) {
                DB::table('clinic_visits')->insert($visitRows);
                $visitRows = [];
            }
        }

        if ($visitRows !== []) {
            DB::table('clinic_visits')->insert($visitRows);
        }

        $visitIdMap = DB::table('clinic_visits')
            ->where('visit_number', 'like', "VIS-FA-{$uniquePrefix}-%")
            ->pluck('id', 'visit_number')
            ->all();

        $faRows = [];
        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $visitNumber = $visitCodes[$i - 1];
            $visitId = $visitIdMap[$visitNumber] ?? null;

            if (! $visitId) {
                continue;
            }

            $faCase = $firstAidCases[$seq % count($firstAidCases)];

            $faRows[] = [
                'clinic_visit_id' => $visitId,
                'injury_type' => $faCase['injury_type'],
                'body_part' => $faCase['body_part'],
                'incident_location' => $faCase['location'],
                'incident_cause' => $faCase['cause'],
                'first_aid_given' => $faCase['given'],
                'treatment_notes' => $faCase['notes'],
                'supplies_used' => $faCase['supplies'],
                'was_referred' => false,
                'referred_to' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($faRows) >= self::CHUNK_SIZE) {
                DB::table('first_aid_records')->insert($faRows);
                $faRows = [];
            }
        }

        if ($faRows !== []) {
            DB::table('first_aid_records')->insert($faRows);
        }
    }

    private function seedMedicineDispensingsAndTransactions(
        int $countToAdd,
        Collection $newPatients,
        Collection $doctors,
        Collection $medicinesWithBatches,
        User $adminUser,
        string $uniquePrefix
    ): void {
        if ($countToAdd <= 0 || $newPatients->isEmpty() || $medicinesWithBatches->isEmpty()) {
            return;
        }

        $existingDispensings = MedicineDispensing::count();
        $now = now()->format('Y-m-d H:i:s');
        $patientCount = $newPatients->count();
        $doctorCount = $doctors->count();
        $medCount = $medicinesWithBatches->count();

        $diagnoses = [
            'Upper Respiratory Tract Infection (URTI)',
            'Acute Viral Influenza Syndrome',
            'Acute Gastroenteritis',
            'Tension-Type Headache / Migraine',
            'Acid Peptic Disease / Acute Gastritis',
            'Primary Dysmenorrhea',
            'Bronchial Asthma Exacerbation',
            'Allergic Rhinitis',
        ];

        $dispRows = [];
        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existingDispensings + $i;
            $patient = $newPatients[($i - 1) % $patientCount];
            $doctor = $doctors[$seq % $doctorCount];
            $dispDate = $this->resolveVisitDate($i);
            $qty = ($seq % 5) + 2;

            $dispRows[] = [
                'dispensing_number' => sprintf('DSP-%s-%06d', $uniquePrefix, $i),
                'patient_id' => $patient->id,
                'clinician_id' => $doctor->id,
                'dispensed_by' => $adminUser->id,
                'dispensed_date' => $dispDate->format('Y-m-d H:i:s'),
                'diagnosis' => $diagnoses[$seq % count($diagnoses)],
                'instructions' => 'Take after meals with a full glass of water as prescribed.',
                'notes' => 'Dispensed at NVC Pharmacy window.',
                'total_items' => $qty,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($dispRows) >= self::CHUNK_SIZE) {
                DB::table('medicine_dispensings')->insert($dispRows);
                $dispRows = [];
            }
        }

        if ($dispRows !== []) {
            DB::table('medicine_dispensings')->insert($dispRows);
        }

        $newDispensingIds = DB::table('medicine_dispensings')
            ->where('dispensing_number', 'like', "DSP-{$uniquePrefix}-%")
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $dCount = count($newDispensingIds);
        if ($dCount > 0) {
            $existingItems = MedicineDispensingItem::count();
            $itemRows = [];

            for ($i = 1; $i <= $countToAdd; $i++) {
                $seq = $existingItems + $i;
                $med = $medicinesWithBatches[$seq % $medCount];
                $batch = $med->batches->first();
                $qty = ($seq % 5) + 2;

                $itemRows[] = [
                    'dispensing_id' => $newDispensingIds[($i - 1) % $dCount],
                    'medicine_id' => $med->id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'dosage_instruction' => 'Take 1 '.$med->unit.' every 6-8 hours after meals as directed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($itemRows) >= self::CHUNK_SIZE) {
                    DB::table('medicine_dispensing_items')->insert($itemRows);
                    $itemRows = [];
                }
            }

            if ($itemRows !== []) {
                DB::table('medicine_dispensing_items')->insert($itemRows);
            }
        }

        $existingTx = InventoryTransaction::count();
        $txRows = [];

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existingTx + $i;
            $med = $medicinesWithBatches[$seq % $medCount];
            $batch = $med->batches->first();
            $txDate = $this->resolveVisitDate($i);
            $qty = ($seq % 5) + 2;
            $isStockIn = ($seq % 10 === 0);

            $txRows[] = [
                'transaction_number' => sprintf('TRX-%s-%06d', $uniquePrefix, $i),
                'medicine_id' => $med->id,
                'batch_id' => $batch->id,
                'type' => $isStockIn ? 'stock_in' : 'dispensed',
                'quantity' => $isStockIn ? 50 : $qty,
                'quantity_before' => 500,
                'quantity_after' => $isStockIn ? 550 : (500 - $qty),
                'reference_number' => $isStockIn
                    ? sprintf('PO-%s-%05d', $uniquePrefix, $i)
                    : sprintf('DSP-%s-%06d', $uniquePrefix, $i),
                'reason' => $isStockIn
                    ? 'Periodic pharmacy stock replenishment'
                    : 'Dispensed to NVC clinic patient',
                'user_id' => $adminUser->id,
                'transaction_date' => $txDate->format('Y-m-d H:i:s'),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($txRows) >= self::CHUNK_SIZE) {
                DB::table('inventory_transactions')->insert($txRows);
                $txRows = [];
            }
        }

        if ($txRows !== []) {
            DB::table('inventory_transactions')->insert($txRows);
        }
    }

    private function seedAuditLogs(int $countToAdd, User $adminUser, Collection $newPatients): void
    {
        if ($countToAdd <= 0) {
            return;
        }

        $existing = AuditLog::count();

        $events = [
            ['event' => 'login', 'description' => 'Clinician / Super Admin authenticated into NVC Clinic Management System'],
            ['event' => 'created', 'description' => 'Registered new patient medical record and baseline health profile'],
            ['event' => 'created', 'description' => 'Logged clinic visit, medical consultation, and pharmacy dispensing'],
            ['event' => 'updated', 'description' => 'Updated oral health examination card and DMFT dental index'],
            ['event' => 'created', 'description' => 'Generated Data Aggregation Morbidity & Clinic Utilization Report'],
        ];

        $props = json_encode(['ip' => '127.0.0.1', 'module' => 'Clinic Simulation Seeder']);
        $patientIds = $newPatients->pluck('id')->all();
        $pCount = count($patientIds);
        $rows = [];

        for ($i = 1; $i <= $countToAdd; $i++) {
            $seq = $existing + $i;
            $evt = $events[$seq % count($events)];

            $rows[] = [
                'user_id' => $adminUser->id,
                'event' => $evt['event'],
                'description' => $evt['description'],
                'subject_type' => $pCount > 0 ? Patient::class : null,
                'subject_id' => $pCount > 0 ? $patientIds[($i - 1) % $pCount] : null,
                'properties' => $props,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) NVC-Clinic-System/1.0',
                'created_at' => Carbon::now()->subMinutes($i * 3)->format('Y-m-d H:i:s'),
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('audit_logs')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('audit_logs')->insert($rows);
        }
    }

    private function resolveVisitDate(int $seq): Carbon
    {
        if ($seq <= 15) {
            return Carbon::today()->subDays($seq % 5)->setTime(8 + ($seq % 8), ($seq * 7) % 55);
        }

        $year = (int) Carbon::now()->format('Y');
        $month = ($seq % 12) + 1;
        $day = (($seq * 3) % 27) + 1;

        return Carbon::create($year, $month, $day, 8 + ($seq % 8), ($seq * 11) % 55);
    }

    private function patientSnapshot(Patient $patient): array
    {
        if ($patient->type === 'student') {
            $label = trim(
                ($patient->college?->code ? $patient->college->code.' - ' : '')
                .($patient->program?->code ?? '')
                .($patient->year_level ? ' ('.$patient->year_level.')' : '')
            );

            return [
                'patient_classification' => 'student',
                'classification_label' => $label ?: 'Student',
                'college_id' => $patient->college_id,
                'program_id' => $patient->program_id,
                'academic_year_id' => $patient->academic_year_id,
                'year_level' => $patient->year_level,
                'office_department' => null,
            ];
        }

        $isTeaching = strtolower((string) $patient->category) === 'faculty';

        return [
            'patient_classification' => $isTeaching ? 'faculty' : 'staff',
            'classification_label' => trim(($patient->category ?? 'Employee').' - '.($patient->office_department ?? $patient->college?->code ?? 'General')),
            'college_id' => $patient->college_id,
            'program_id' => null,
            'academic_year_id' => $patient->academic_year_id,
            'year_level' => null,
            'office_department' => $patient->office_department,
        ];
    }
}
