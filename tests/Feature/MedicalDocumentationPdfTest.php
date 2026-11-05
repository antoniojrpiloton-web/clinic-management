<?php

namespace Tests\Feature;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalDocumentationPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function getOrCreatePhysicalExam(): PhysicalExamination
    {
        $exam = PhysicalExamination::first();
        if ($exam) {
            return $exam;
        }

        $patient = Patient::first();
        $clinician = Clinician::first();

        return PhysicalExamination::create([
            'pe_number' => 'PE-2026-0099',
            'patient_id' => $patient->id,
            'clinician_id' => $clinician?->id,
            'examiner_name' => 'DR. VICENTE S. QUIMPO, MD',
            'examiner_license_number' => '0052551',
            'exam_date' => now()->toDateString(),
            'purpose' => 'Entrance Examination',
            'systolic' => 120,
            'diastolic' => 80,
            'pulse_rate' => 74,
            'respiratory_rate' => 18,
            'temperature' => 36.6,
            'height_cm' => 168,
            'weight_kg' => 62,
            'clinical_findings' => 'Essentially normal physical examination',
            'medical_recommendations' => 'Cleared for academic activities',
            'fitness_assessment' => 'Class A',
        ]);
    }

    public function test_medical_certificate_pdf_matches_hsd_form_4_layout(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();

        $response = $this->actingAs($admin)->get(route('physical-examinations.print-certificate', $exam));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('MEDICAL CERTIFICATE', $content);
        $this->assertStringContainsString('To Whom It May Concern:', $content);
        $this->assertStringContainsString('has been seen and examined by the undersigned', $content);
        $this->assertStringContainsString('to undergo Physical Activities/ Trainings', $content);
        $this->assertStringContainsString('On-the-Job Training', $content);
        $this->assertStringContainsString('Work Immersion', $content);
        $this->assertStringContainsString('Attending Physician', $content);
        $this->assertStringContainsString('HSD FORM 4- Personal Copy', $content);
    }

    public function test_medical_exemption_certificate_pdf_matches_hsd_form_6_layout(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();

        $response = $this->actingAs($admin)->get(route('physical-examinations.print-exemption', $exam));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('MEDICAL EXEMPTION CERTIFICATE', $content);
        $this->assertStringContainsString('To whom it may concern:', $content);
        $this->assertStringContainsString('I hereby certify that I have examined', $content);
        $this->assertStringContainsString('Medical Condition:', $content);
        $this->assertStringContainsString('Exempted Activities:', $content);
        $this->assertStringContainsString('The exemption is effective from', $content);
        $this->assertStringContainsString('School Physician', $content);
        $this->assertStringContainsString('HSD FORM 6- Medical Exemption Certificate', $content);
    }

    public function test_medical_referral_form_pdf_matches_hsd_form_3_layout(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();

        $response = $this->actingAs($admin)->get(route('physical-examinations.print-referral', $exam));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('MEDICAL REFERRAL FORM', $content);
        $this->assertStringContainsString('Refer to:', $content);
        $this->assertStringContainsString('Patient Information:', $content);
        $this->assertStringContainsString('In case of Emergency:', $content);
        $this->assertStringContainsString('Complaints/ Findings:', $content);
        $this->assertStringContainsString('Treatment:', $content);
        $this->assertStringContainsString('Reasons for Referral:', $content);
        $this->assertStringContainsString('School Physician', $content);
        $this->assertStringContainsString('HSD FORM 3- Referral Form', $content);
    }

    public function test_consultation_form_pdf_matches_hsd_form_5_layout(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $visit = ClinicVisit::first();

        $response = $this->actingAs($admin)->get(route('clinic-visits.print', $visit));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('CONSULTATION FORM', $content);
        $this->assertStringContainsString('Patient Name:', $content);
        $this->assertStringContainsString('Course/Occupation:', $content);
        $this->assertStringContainsString('COMPLAINTS/ FINDINGS', $content);
        $this->assertStringContainsString('TREATMENT', $content);
        $this->assertStringContainsString('HSD FORM 5- Consultation Form', $content);
        $this->assertStringContainsString($visit->visit_number, $content);
    }

    public function test_other_print_records_generate_valid_fpdf_documents(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();
        $visit = ClinicVisit::where('visit_type', 'medical')->first() ?? ClinicVisit::first();
        $schedule = PhysicalExamSchedule::first();
        $dispensing = MedicineDispensing::first();

        $peResponse = $this->actingAs($admin)->get(route('physical-examinations.print-pe', $exam));
        $peResponse->assertOk();
        $peResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $peResponse->getContent());

        $visitCertResponse = $this->actingAs($admin)->get(route('clinic-visits.print-certificate', $visit));
        $visitCertResponse->assertOk();
        $visitCertResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('MEDICAL CERTIFICATE', $visitCertResponse->getContent());

        $visitRefResponse = $this->actingAs($admin)->get(route('clinic-visits.print-referral', $visit));
        $visitRefResponse->assertOk();
        $visitRefResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('MEDICAL REFERRAL FORM', $visitRefResponse->getContent());

        if ($schedule) {
            $rosterResponse = $this->actingAs($admin)->get(route('physical-exam-schedules.print-roster', $schedule));
            $rosterResponse->assertOk();
            $rosterResponse->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringContainsString($schedule->schedule_number, $rosterResponse->getContent());
        }

        if ($dispensing) {
            $dispResponse = $this->actingAs($admin)->get(route('inventory.dispensings.print', $dispensing));
            $dispResponse->assertOk();
            $dispResponse->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringContainsString($dispensing->dispensing_number, $dispResponse->getContent());
        }
    }

    public function test_documents_reflect_custom_settings_table_data(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();

        app(SettingService::class)->set([
            'university_name' => 'Custom State University of Science',
            'clinic_name' => 'Custom University Medical Clinic',
            'clinic_head' => 'Dr. Alexandra Cruz, MD',
            'clinic_license_no' => 'DOH-LIC-998877',
        ]);

        $response = $this->actingAs($admin)->get(route('physical-examinations.print-certificate', $exam));
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('Custom State University of Science', $content);
        $this->assertStringContainsString('CUSTOM UNIVERSITY MEDICAL CLINIC', $content);
        $this->assertStringContainsString('DR. ALEXANDRA CRUZ, MD', $content);
        $this->assertStringContainsString('License #: DOH-LIC-998877', $content);
    }
}
