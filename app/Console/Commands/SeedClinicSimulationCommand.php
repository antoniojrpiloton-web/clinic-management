<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Models\EmergencyRecord;
use App\Models\FirstAidRecord;
use App\Models\InventoryTransaction;
use App\Models\MedicalConsultation;
use App\Models\MedicalProfile;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\PhysicalExamScheduleAttendee;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SimulationSeeder;
use Illuminate\Console\Command;

class SeedClinicSimulationCommand extends Command
{
    protected $signature = 'clinic:simulate
                            {--area= : Specific area to seed (all, clinic_visits, medical_consultations, dental_consultations, emergency_records, first_aid_records, patients, physical_exams, schedules, pharmacy, audit_logs)}
                            {--count= : Number of records to ADD (e.g. 100, 500, 1000)}
                            {--fresh : Run migrate:fresh before seeding}';

    protected $description = 'Interactive NVC Clinic simulation seeder that adds records to all tables or a specific clinical area/module';

    public function handle(): int
    {
        $areaOption = $this->option('area');

        if ($areaOption !== null && $areaOption !== '') {
            $area = SimulationSeeder::normalizeArea((string) $areaOption);
        } elseif ($this->input->isInteractive() && ! app()->runningUnitTests()) {
            $areas = SimulationSeeder::availableAreas();
            $selected = $this->choice(
                'Which clinic area / module would you like to add records to?',
                $areas,
                'all'
            );
            $key = array_search($selected, $areas, true);
            $area = is_string($key) ? $key : SimulationSeeder::normalizeArea((string) $selected);
        } else {
            $area = 'all';
        }

        $countOption = $this->option('count');

        if ($countOption !== null && $countOption !== '') {
            $count = max(1, min(50000, (int) $countOption));
        } else {
            $count = (int) $this->ask(
                'How many records would you like to ADD for ['.(SimulationSeeder::availableAreas()[$area] ?? $area).']?',
                '100'
            );
            $count = max(1, min(50000, $count));
        }

        $before = $this->option('fresh') ? [] : $this->snapshotCounts();

        SimulationSeeder::$customArea = $area;
        SimulationSeeder::$customCount = $count;

        try {
            if ($this->option('fresh')) {
                $this->info('Refreshing database schema...');
                $this->call('migrate:fresh');
                $this->call('db:seed', ['--class' => DatabaseSeeder::class]);
            } else {
                $this->call('db:seed', ['--class' => SimulationSeeder::class]);
            }
        } finally {
            SimulationSeeder::$customArea = null;
            SimulationSeeder::$customCount = null;
        }

        $after = $this->snapshotCounts();

        $rows = [];
        foreach ($this->tableLabels() as $key => $label) {
            $prev = $before[$key] ?? 0;
            $curr = $after[$key] ?? 0;
            $diff = max(0, $curr - $prev);
            $rows[] = [
                $label,
                $diff > 0 ? '+'.number_format($diff) : '0',
                number_format($curr),
            ];
        }

        $this->newLine();
        $this->table(
            ['Database Table / Clinical Document', 'Added Just Now', 'Total Records in DB'],
            $rows
        );

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function snapshotCounts(): array
    {
        return [
            'patients' => Patient::count(),
            'medical_profiles' => MedicalProfile::count(),
            'physical_examinations' => PhysicalExamination::count(),
            'physical_exam_schedules' => PhysicalExamSchedule::count(),
            'physical_exam_schedule_attendees' => PhysicalExamScheduleAttendee::count(),
            'medical_consultations' => MedicalConsultation::count(),
            'dental_consultations' => DentalConsultation::count(),
            'emergency_records' => EmergencyRecord::count(),
            'first_aid_records' => FirstAidRecord::count(),
            'medicine_dispensings' => MedicineDispensing::count(),
            'medicine_dispensing_items' => MedicineDispensingItem::count(),
            'inventory_transactions' => InventoryTransaction::count(),
            'audit_logs' => AuditLog::count(),
            'clinic_visits' => ClinicVisit::count(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function tableLabels(): array
    {
        return [
            'patients' => 'Patients (Students, Faculty & Staff)',
            'medical_profiles' => 'Medical Profiles (Baseline Health & History)',
            'physical_examinations' => 'Annual Physical Examinations (Classes A–E)',
            'physical_exam_schedules' => 'Physical Exam Schedules (Batches)',
            'physical_exam_schedule_attendees' => 'Physical Exam Schedule Attendees',
            'medical_consultations' => '1. Medical Consultations (Communicable & Non-Communicable)',
            'dental_consultations' => '2. Dental Consultations (Oral Health Card & DMFT)',
            'emergency_records' => '3. Emergency Triage & Stabilization Records',
            'first_aid_records' => '4. First Aid Treatment Records',
            'medicine_dispensings' => 'Medicine Dispensings (Patient Prescriptions)',
            'medicine_dispensing_items' => 'Medicine Dispensing Items',
            'inventory_transactions' => 'Inventory Ledger Transactions (Stock In / Dispensed)',
            'audit_logs' => 'System Audit Logs',
            'clinic_visits' => 'Master Clinic Visit Logs (clinic_visits)',
        ];
    }
}
