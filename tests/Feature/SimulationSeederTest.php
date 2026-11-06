<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_simulate_command_appends_exact_count_per_table_without_deleting_existing_data(): void
    {
        $this->artisan('clinic:simulate', ['--area' => 'all', '--count' => 15])
            ->assertSuccessful();

        $patientsAfterFirstRun = Patient::count();

        $this->assertGreaterThanOrEqual(15, $patientsAfterFirstRun);
        $this->assertSame(15, MedicalConsultation::count());
        $this->assertSame(15, DentalConsultation::count());
        $this->assertSame(15, EmergencyRecord::count());
        $this->assertSame(15, FirstAidRecord::count());
        $this->assertSame(15, MedicineDispensing::count());

        $this->artisan('clinic:simulate', ['--area' => 'all', '--count' => 10])
            ->assertSuccessful();

        $this->assertSame($patientsAfterFirstRun + 10, Patient::count());
        $this->assertSame($patientsAfterFirstRun + 10, MedicalProfile::count());
        $this->assertSame(25, PhysicalExamination::count());
        $this->assertSame(25, PhysicalExamSchedule::count());
        $this->assertSame(25, PhysicalExamScheduleAttendee::count());
        $this->assertSame(25, MedicalConsultation::count());
        $this->assertSame(25, DentalConsultation::count());
        $this->assertSame(25, EmergencyRecord::count());
        $this->assertSame(25, FirstAidRecord::count());
        $this->assertSame(25, MedicineDispensing::count());
        $this->assertSame(25, MedicineDispensingItem::count());
        $this->assertSame(25, AuditLog::count());
        $this->assertSame(100, ClinicVisit::count());
        $this->assertGreaterThanOrEqual(25, InventoryTransaction::count());
    }

    public function test_clinic_simulate_command_can_seed_specific_areas_only(): void
    {
        $this->artisan('clinic:simulate', ['--area' => 'patients', '--count' => 10])
            ->assertSuccessful();

        $patientCount = Patient::count();
        $this->assertSame(0, MedicalConsultation::count());
        $this->assertSame(0, DentalConsultation::count());

        $this->artisan('clinic:simulate', ['--area' => 'medical_consultations', '--count' => 12])
            ->assertSuccessful();

        $this->assertSame($patientCount, Patient::count());
        $this->assertSame(12, MedicalConsultation::count());
        $this->assertSame(12, ClinicVisit::count());
        $this->assertSame(0, DentalConsultation::count());

        $this->artisan('clinic:simulate', ['--area' => 'clinic_visits', '--count' => 8])
            ->assertSuccessful();

        $this->assertSame(12, MedicalConsultation::count());
        $this->assertSame(20, ClinicVisit::count());
    }
}
