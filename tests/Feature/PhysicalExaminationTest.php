<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhysicalExaminationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authorized_user_can_view_physical_examination_registry(): void
    {
        $user = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($user)->get(route('physical-examinations.index'));
        $response->assertOk();

        $ajaxResponse = $this->actingAs($user)->getJson(route('physical-examinations.index', [
            'search' => ['value' => '', 'regex' => 'false'],
        ]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $ajaxResponse->assertOk()
            ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);

        $searchResponse = $this->actingAs($user)->getJson(route('physical-examinations.index', [
            'search' => ['value' => 'PE-2026', 'regex' => 'false'],
        ]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $searchResponse->assertOk();
    }

    public function test_can_create_physical_examination_and_auto_compute_bmi(): void
    {
        $user = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $ay = AcademicYear::first();

        $data = [
            'patient_id' => $patient->id,
            'academic_year_id' => $ay->id,
            'pe_number' => 'PE-2026-99999',
            'exam_date' => '2026-11-05',
            'purpose' => 'Annual Physical Exam',
            'examiner_name' => 'Dr. Vicente S. Quimpo',
            'examiner_license_number' => '0052551',
            'systolic' => 118,
            'diastolic' => 78,
            'pulse_rate' => 72,
            'respiratory_rate' => 16,
            'temperature' => 36.5,
            'oxygen_saturation' => 99,
            'height_cm' => 170.0,
            'weight_kg' => 65.0,
            'visual_acuity_right' => '20/20',
            'visual_acuity_left' => '20/20',
            'visual_acuity_is_corrected' => 0,
            'color_vision' => 'Normal',
            'hearing' => 'Normal',
            'general_appearance' => 'Well-nourished and oriented',
            'systems' => [
                'skin' => ['status' => 'normal', 'remarks' => 'Clear'],
                'chest_lungs' => ['status' => 'normal', 'remarks' => 'Clear breath sounds'],
                'heart' => ['status' => 'normal', 'remarks' => 'Regular rate and rhythm'],
            ],
            'chest_xray_findings' => 'Clear lung fields',
            'clinical_findings' => 'Essentially normal physical exam',
            'medical_recommendations' => 'Routine annual follow-up',
            'fitness_assessment' => 'Class A',
            'fitness_activities' => [
                'physical_activities' => 'fit',
                'on_the_job_training' => 'fit',
                'work_immersion' => 'fit',
                'to_work' => 'fit',
                'to_travel' => 'fit',
                'other_requirements' => 'fit',
            ],
            'past_medical_history' => ['Asthma / Wheezing'],
            'allergies_drugs' => 'Penicillin',
        ];

        $response = $this->actingAs($user)->post(route('physical-examinations.store'), $data);

        $exam = PhysicalExamination::where('pe_number', 'PE-2026-99999')->first();
        $this->assertNotNull($exam);
        $this->assertEquals(22.49, $exam->bmi);
        $this->assertEquals('Normal', $exam->bmi_category);
        $this->assertEquals('118/78 mmHg', $exam->blood_pressure);

        $response->assertRedirect(route('physical-examinations.show', $exam));

        $patient->refresh();
        $this->assertEquals('Penicillin', $patient->medicalProfile->allergies_drugs);
    }

    public function test_can_render_all_printable_forms_and_certificates(): void
    {
        $user = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = PhysicalExamination::first();
        $exam->update(['examiner_id' => $user->id]);
        $exam->refresh();

        $peForm = $this->actingAs($user)->get(route('physical-examinations.print-pe', $exam));
        $peForm->assertOk()->assertSee('PHYSICAL EXAMINATION RECORD');

        $cert = $this->actingAs($user)->get(route('physical-examinations.print-certificate', $exam));
        $cert->assertOk()->assertSee('MEDICAL CERTIFICATE')->assertSee('HSD FORM 4');

        $exempt = $this->actingAs($user)->get(route('physical-examinations.print-exemption', $exam));
        $exempt->assertOk()->assertSee('MEDICAL EXEMPTION CERTIFICATE')->assertSee('HSD FORM 6');

        $referral = $this->actingAs($user)->get(route('physical-examinations.print-referral', $exam));
        $referral->assertOk()->assertSee('MEDICAL REFERRAL FORM')->assertSee('HSD FORM 3');
    }

    public function test_can_view_and_update_medical_profile(): void
    {
        $user = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();

        $viewResponse = $this->actingAs($user)->get(route('medical-profiles.show', $patient));
        $viewResponse->assertOk()->assertSee($patient->full_name);

        $editResponse = $this->actingAs($user)->get(route('medical-profiles.edit', $patient));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($user)->put(route('medical-profiles.update', $patient), [
            'past_medical_history' => ['Hypertension', 'Diabetes Mellitus'],
            'allergies_drugs' => 'Aspirin',
            'allergies_food' => 'Peanuts',
            'existing_conditions' => 'Controlled Stage 1 Hypertension',
            'smoking_status' => 'non-smoker',
            'alcohol_status' => 'occasional',
            'hospitalization_history' => [
                ['year' => '2023', 'hospital' => 'Surigao Medical Center', 'reason' => 'Acute Gastritis'],
            ],
        ]);

        $updateResponse->assertRedirect(route('patients.show', $patient));

        $patient->refresh();
        $this->assertEquals('Aspirin', $patient->medicalProfile->allergies_drugs);
        $this->assertEquals('Peanuts', $patient->medicalProfile->allergies_food);
        $this->assertCount(1, $patient->medicalProfile->hospitalization_history);
    }
}
