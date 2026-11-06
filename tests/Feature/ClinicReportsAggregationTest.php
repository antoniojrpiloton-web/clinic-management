<?php

namespace Tests\Feature;

use App\Models\ClinicVisit;
use App\Models\MedicalConsultation;
use App\Models\Patient;
use App\Models\User;
use App\Services\DataAggregationAlgorithmService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicReportsAggregationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_clinic_report_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.clinic.index'));

        $response->assertOk();
        $response->assertViewIs('reports.clinic');
        $response->assertViewHas('reportData');
    }

    public function test_aggregation_algorithm_computes_service_and_morbidity_rollups(): void
    {
        $student = Patient::create([
            'patient_number' => 'PAT-2026-0099',
            'type' => 'student',
            'first_name' => 'Carla',
            'last_name' => 'Mendoza',
            'gender' => 'Female',
            'date_of_birth' => '2004-10-12',
            'status' => 'active',
        ]);

        $v1 = ClinicVisit::create([
            'visit_number' => 'VIS-20261105-0091',
            'patient_id' => $student->id,
            'visit_type' => ClinicVisit::TYPE_MEDICAL,
            'visit_category' => 'walk_in',
            'patient_classification' => 'student',
            'visit_date' => Carbon::now(),
            'chief_complaint' => 'Moderate fever and cough',
            'status' => 'completed',
        ]);

        MedicalConsultation::create([
            'clinic_visit_id' => $v1->id,
            'diagnosis' => 'Acute Bronchitis',
        ]);

        $service = app(DataAggregationAlgorithmService::class);
        $result = $service->aggregateClinicReport([
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->assertSame(1, $result['summary']['total_visits']);
        $this->assertSame(1, $result['summary']['unique_patients']);
        $this->assertSame(1, $result['service_distribution']['Medical Consultations']);
        $this->assertSame(1, $result['classification_rollup']['Students']);
        $this->assertSame(1, $result['morbidity']['total_communicable']);
    }

    public function test_clinic_report_pdf_export_returns_pdf_stream(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.clinic.print-pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_clinic_report_ajax_request_returns_json_partial_and_pdf_url(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('reports.clinic.index', [
                'date_from' => '2026-01-01',
                'date_to' => '2026-11-06',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                'html',
                'pdf_url',
                'summary' => [
                    'total_visits',
                    'unique_patients',
                    'total_medicines_dispensed',
                ],
            ]);
    }
}
