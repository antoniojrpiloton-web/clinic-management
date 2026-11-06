<?php

namespace Tests\Feature;

use App\Models\ClinicVisit;
use App\Models\MedicalConsultation;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
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

    public function test_dashboard_is_accessible_by_authorized_user(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertViewHasAll(['stats', 'charts', 'recentVisits', 'recentDispensings']);
    }

    public function test_dashboard_ajax_metrics_endpoint_returns_json_payload(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('dashboard.metrics'));

        $response->assertOk();
        $response->assertJsonStructure([
            'stats' => [
                'total_visits',
                'total_patients',
                'total_pe',
                'medicines_dispensed',
                'medical_consultations_count',
                'dental_consultations_count',
                'emergency_records_count',
                'first_aid_records_count',
            ],
            'charts' => [
                'monthly_utilization',
                'classification_breakdown',
                'services_breakdown',
                'top_complaints',
                'disease_classification',
                'top_medicines',
                'pe_fitness_breakdown',
                'college_utilization',
            ],
            'sections' => [
                'alerts',
                'communicable',
                'non_communicable',
                'recent_visits',
                'recent_pe',
                'upcoming_schedules',
                'recent_dispensings',
                'recent_medical',
                'recent_dental',
                'recent_emergency',
                'recent_first_aid',
                'recent_audit',
            ],
            'generated_at',
        ]);
    }

    public function test_dashboard_analytics_service_calculates_correct_stats(): void
    {
        $patient = Patient::create([
            'patient_number' => 'PAT-2026-0001',
            'type' => 'student',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'gender' => 'Female',
            'date_of_birth' => '2004-05-15',
            'status' => 'active',
        ]);

        $faculty = Patient::create([
            'patient_number' => 'PAT-2026-0002',
            'type' => 'employee',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'date_of_birth' => '1985-03-20',
            'status' => 'active',
        ]);

        ClinicVisit::create([
            'visit_number' => 'VIS-20261105-0001',
            'patient_id' => $patient->id,
            'visit_type' => ClinicVisit::TYPE_MEDICAL,
            'visit_category' => 'walk_in',
            'patient_classification' => 'student',
            'visit_date' => Carbon::now(),
            'chief_complaint' => 'High fever and dry cough',
            'status' => 'completed',
        ]);

        ClinicVisit::create([
            'visit_number' => 'VIS-20261105-0002',
            'patient_id' => $faculty->id,
            'visit_type' => ClinicVisit::TYPE_DENTAL,
            'visit_category' => 'scheduled',
            'patient_classification' => 'faculty',
            'visit_date' => Carbon::now(),
            'chief_complaint' => 'Tooth cavity checkup',
            'status' => 'completed',
        ]);

        $service = app(DashboardAnalyticsService::class);
        $stats = $service->stats();

        $this->assertSame(2, $stats['total_visits']);
        $this->assertSame(2, $stats['today_visits']);
        $this->assertSame(1, $stats['student_visits']);
        $this->assertSame(1, $stats['employee_visits']);
        $this->assertSame(1, $stats['medical_visits']);
        $this->assertSame(1, $stats['dental_visits']);
    }

    public function test_disease_classification_differentiates_communicable_and_non_communicable(): void
    {
        $patient = Patient::create([
            'patient_number' => 'PAT-2026-0003',
            'type' => 'student',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'gender' => 'Female',
            'date_of_birth' => '2005-08-10',
            'status' => 'active',
        ]);

        $v1 = ClinicVisit::create([
            'visit_number' => 'VIS-20261105-0003',
            'patient_id' => $patient->id,
            'visit_type' => ClinicVisit::TYPE_MEDICAL,
            'visit_category' => 'walk_in',
            'patient_classification' => 'student',
            'visit_date' => Carbon::now(),
            'chief_complaint' => 'Sore throat and fever',
            'status' => 'completed',
        ]);

        MedicalConsultation::create([
            'clinic_visit_id' => $v1->id,
            'diagnosis' => 'Acute Upper Respiratory Tract Infection (URTI)',
        ]);

        $v2 = ClinicVisit::create([
            'visit_number' => 'VIS-20261105-0004',
            'patient_id' => $patient->id,
            'visit_type' => ClinicVisit::TYPE_MEDICAL,
            'visit_category' => 'walk_in',
            'patient_classification' => 'student',
            'visit_date' => Carbon::now(),
            'chief_complaint' => 'Occasional dizziness and headache',
            'status' => 'completed',
        ]);

        MedicalConsultation::create([
            'clinic_visit_id' => $v2->id,
            'diagnosis' => 'Essential Hypertension Stage 1',
        ]);

        $service = app(DashboardAnalyticsService::class);
        $diseases = $service->diseaseClassification();

        $this->assertSame(1, $diseases['communicable_count']);
        $this->assertSame(1, $diseases['non_communicable_count']);
    }

    public function test_medicine_utilization_chart_data(): void
    {
        $patient = Patient::create([
            'patient_number' => 'PAT-2026-0004',
            'type' => 'student',
            'first_name' => 'Mark',
            'last_name' => 'Cruz',
            'gender' => 'Male',
            'date_of_birth' => '2003-01-20',
            'status' => 'active',
        ]);

        $category = MedicineCategory::create([
            'name' => 'Analgesics',
            'code' => 'ANAL-TEST',
        ]);

        $med = Medicine::create([
            'code' => 'MED-TEST-001',
            'category_id' => $category->id,
            'generic_name' => 'Paracetamol',
            'brand_name' => 'Biogesic',
            'dosage_form' => 'Tablet',
            'strength' => '500mg',
            'unit' => 'tablet',
            'reorder_level' => 10,
        ]);

        $batch = MedicineBatch::create([
            'medicine_id' => $med->id,
            'batch_number' => 'BATCH-TEST-01',
            'expiration_date' => Carbon::now()->addYear(),
            'received_date' => Carbon::now(),
            'quantity_received' => 100,
            'quantity_remaining' => 80,
            'unit_cost' => 2.50,
        ]);

        $disp = MedicineDispensing::create([
            'dispensing_number' => 'DISP-20261105-0001',
            'patient_id' => $patient->id,
            'dispensed_by' => $this->admin->id,
            'dispensed_date' => Carbon::now(),
            'notes' => 'Fever relief',
        ]);

        $disp->items()->create([
            'medicine_id' => $med->id,
            'batch_id' => $batch->id,
            'quantity' => 20,
            'dosage_instruction' => '1 tab every 4 hours PRN',
        ]);

        $service = app(DashboardAnalyticsService::class);
        $topMeds = $service->topMedicineUtilization();

        $this->assertSame(20, $topMeds['total_dispensed']);
        $this->assertContains('Paracetamol (Biogesic)', $topMeds['labels']);
    }
}
