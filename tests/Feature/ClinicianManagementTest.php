<?php

namespace Tests\Feature;

use App\Models\Clinician;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicianManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_view_clinicians_index(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('clinicians.index'));
        $response->assertOk();
        $response->assertSee('Healthcare Providers &amp; Clinicians', false);
    }

    public function test_clinicians_datatables_endpoint_returns_json(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->getJson(route('clinicians.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
        $this->assertStringContainsString('0052551', $response->getContent());
    }

    public function test_super_admin_can_create_clinician(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->post(route('clinicians.store'), [
            'name' => 'DR. GABRIEL CRUZ, MD',
            'type' => 'Doctor',
            'title' => 'Cardiologist / Visiting Physician',
            'license_number' => '0098765',
            'ptr_number' => 'PTR-2026-999',
            'specialization' => 'Cardiology',
            'phone' => '+63 917 555 1234',
            'email' => 'gabriel.cruz@clinic.test',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('clinicians.index'));
        $this->assertDatabaseHas('clinicians', [
            'name' => 'DR. GABRIEL CRUZ, MD',
            'license_number' => '0098765',
            'specialization' => 'Cardiology',
        ]);
    }

    public function test_clinician_can_be_updated(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $clinician = Clinician::where('license_number', '0052551')->first();

        $response = $this->actingAs($admin)->put(route('clinicians.update', $clinician), [
            'name' => 'DR. VICENTE S. QUIMPO, MD, FPSMS',
            'type' => 'Doctor',
            'title' => 'Chief University Physician',
            'license_number' => '0052551',
            'ptr_number' => 'PTR-AKL-2026-8910-UPDATED',
            'specialization' => 'Occupational & Preventive Medicine',
            'phone' => '+63 917 111 2233',
            'email' => 'vicente.quimpo@nvc.edu.ph',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('clinicians.index'));
        $this->assertDatabaseHas('clinicians', [
            'id' => $clinician->id,
            'name' => 'DR. VICENTE S. QUIMPO, MD, FPSMS',
            'title' => 'Chief University Physician',
        ]);
    }

    public function test_clinician_with_exams_is_deactivated_on_delete(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $clinician = Clinician::where('license_number', '0052551')->first();

        $this->assertTrue($clinician->physicalExaminations()->exists());

        $response = $this->actingAs($admin)->delete(route('clinicians.destroy', $clinician));
        $response->assertRedirect(route('clinicians.index'));

        $this->assertDatabaseHas('clinicians', [
            'id' => $clinician->id,
            'is_active' => false,
        ]);
    }

    public function test_select2_clinicians_endpoint(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->getJson(route('select2.clinicians', ['q' => 'QUIMPO']));
        $response->assertOk();
        $response->assertJsonStructure([
            'results' => [
                '*' => ['id', 'text', 'subtext', 'name', 'license_number', 'title', 'type'],
            ],
            'pagination',
        ]);

        $results = $response->json('results');
        $this->assertNotEmpty($results);
        $this->assertEquals('0052551', $results[0]['license_number']);
    }

    public function test_physical_exam_creation_with_clinician_id(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $clinician = Clinician::where('license_number', '0078124')->first();

        $response = $this->actingAs($admin)->post(route('physical-examinations.store'), [
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'pe_number' => 'PE-2026-TEST01',
            'exam_date' => '2026-11-05',
            'purpose' => 'Annual Physical Exam',
            'examiner_name' => $clinician->name,
            'examiner_license_number' => $clinician->license_number,
            'systolic' => 110,
            'diastolic' => 70,
            'pulse_rate' => 70,
            'respiratory_rate' => 16,
            'temperature' => 36.5,
            'oxygen_saturation' => 99,
            'height_cm' => 160,
            'weight_kg' => 52,
            'fitness_assessment' => 'Class A',
        ]);

        $exam = PhysicalExamination::where('pe_number', 'PE-2026-TEST01')->first();
        $this->assertNotNull($exam);
        $response->assertRedirect(route('physical-examinations.show', $exam));
        $this->assertEquals($clinician->id, $exam->clinician_id);
        $this->assertEquals($clinician->name, $exam->examiner_name);
        $this->assertEquals($clinician->license_number, $exam->examiner_license_number);
    }
}
