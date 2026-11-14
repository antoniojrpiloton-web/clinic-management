<?php

namespace Tests\Feature;

use App\Models\Clinician;
use App\Models\ClinicLogbook;
use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\Program;
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
        $visit = ClinicVisit::first();

        $response = $this->actingAs($admin)->get(route('clinic-visits.print-certificate', $visit));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('MEDICAL CERTIFICATE', $content);
        $this->assertStringContainsString('To Whom It May Concern:', $content);
        $this->assertStringContainsString('has been seen and examined by the undersigned', $content);
        $this->assertStringContainsString('Attending Physician', $content);
        $this->assertStringContainsString('HSD FORM 4- Personal Copy', $content);
    }

    public function test_medical_referral_form_pdf_matches_hsd_form_3_layout(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $visit = ClinicVisit::first();

        $response = $this->actingAs($admin)->get(route('clinic-visits.print-referral', $visit));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('MEDICAL REFERRAL FORM', $content);
        $this->assertStringContainsString('Refer to:', $content);
        $this->assertStringContainsString('Patient Information:', $content);
        $this->assertStringContainsString('In case of Emergency:', $content);
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
        $visit = ClinicVisit::where('visit_type', 'medical')->first() ?? ClinicVisit::first();

        $visitCertResponse = $this->actingAs($admin)->get(route('clinic-visits.print-certificate', $visit));
        $visitCertResponse->assertOk();
        $visitCertResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('MEDICAL CERTIFICATE', $visitCertResponse->getContent());

        $visitRefResponse = $this->actingAs($admin)->get(route('clinic-visits.print-referral', $visit));
        $visitRefResponse->assertOk();
        $visitRefResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('MEDICAL REFERRAL FORM', $visitRefResponse->getContent());
    }

    public function test_documents_reflect_custom_settings_table_data(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $visit = ClinicVisit::first();

        app(SettingService::class)->set([
            'university_name' => 'Custom State University of Science',
            'clinic_name' => 'Custom University Medical Clinic',
            'clinic_head' => 'Dr. Alexandra Cruz, MD',
            'clinic_license_no' => 'DOH-LIC-998877',
        ]);

        $response = $this->actingAs($admin)->get(route('clinic-visits.print-certificate', $visit));
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('Custom State University of Science', $content);
        $this->assertStringContainsString('CUSTOM UNIVERSITY MEDICAL CLINIC', $content);
        $this->assertStringContainsString('DR. ALEXANDRA CRUZ, MD', $content);
        $this->assertStringContainsString('License #: DOH-LIC-998877', $content);
    }

    public function test_oral_health_card_pdf_uses_dynamic_rows_and_maximizes_page_space(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $consultation = DentalConsultation::first();
        $this->assertNotNull($consultation);

        $consultation->update([
            'anesthesia_used' => 'Lidocaine 2% with 1:100,000 Epinephrine',
            'treatment_rendered' => 'Tooth extraction under local infiltration anesthesia; post-op gauze pack applied and hemostasis achieved.',
            'recommendations' => 'Bite firmly on sterile gauze for 30 minutes; avoid hot food and vigorous rinsing for 24 hours.',
        ]);

        $response = $this->actingAs($admin)->get(route('dental-consultations.print-card', $consultation->clinic_visit_id));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('SCHOOL ORAL HEALTH EXAMINATION CARD', $content);
        $this->assertStringContainsString('College Students & University Personnel', $content);
        $this->assertStringContainsString('MEDICAL HISTORY', $content);
        $this->assertStringContainsString('Guided Questions', $content);
        $this->assertStringContainsString('CONDITION AND TREATMENT NEEDS', $content);
        $this->assertStringContainsString('ORAL HEALTH CONDITION', $content);
        $this->assertStringContainsString('DENTAL PROCEDURES', $content);
        $this->assertStringContainsString('dft Index', $content);
        $this->assertStringContainsString('DMFMT Index', $content);
        $this->assertStringContainsString('SYMBOL FOR MOUTH EXAMINATION', $content);
        $this->assertStringContainsString('SYMBOLS FOR ACCOMPLISHMENT', $content);
        $this->assertStringContainsString('ARTIFICIAL RESTORATION', $content);
        $this->assertStringContainsString('Note: Use letter to record ailments and place X if not examined.', $content);
        $this->assertStringContainsString('Lidocaine 2% with 1:100,000 Epinephrine', $content);
        $this->assertStringContainsString('/Count 2', $content);
    }

    public function test_public_clinic_logbook_kiosk_and_staff_management_workflow(): void
    {
        $patient = Patient::first();
        $program = Program::where('code', 'BSCS')->first() ?? Program::first();

        $kioskResponse = $this->get(route('logbook.kiosk'));
        $kioskResponse->assertOk();
        $kioskResponse->assertSee('Clinic Visitor Logbook');

        $lookupResponse = $this->getJson(route('logbook.lookup', ['id_number' => $patient->patient_number]));
        $lookupResponse->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('patient_id', $patient->id);

        $storeResponse = $this->post(route('logbook.kiosk.store'), [
            'visitor_type' => 'student',
            'id_number' => $patient->patient_number,
            'full_name' => $patient->full_name,
            'program_id' => $program?->id,
            'year_or_position' => '3rd Year',
            'contact_number' => '09171234567',
            'purpose' => 'Blood Pressure (BP) Monitoring',
            'notes' => 'Walk-in BP check before class',
        ]);
        $storeResponse->assertRedirect(route('logbook.kiosk'));

        $entry = ClinicLogbook::latest('id')->first();
        $this->assertNotNull($entry);
        $this->assertSame($patient->id, $entry->patient_id);
        $this->assertSame('Blood Pressure (BP) Monitoring', $entry->purpose);
        $this->assertNull($entry->time_out);

        $timeOutResponse = $this->post(route('logbook.kiosk.time-out', $entry));
        $timeOutResponse->assertRedirect(route('logbook.kiosk'));
        $entry->refresh();
        $this->assertNotNull($entry->time_out);

        $admin = User::first();
        $staffResponse = $this->actingAs($admin)->get(route('clinic-logbooks.index'));
        $staffResponse->assertOk();
        $staffResponse->assertSee('Daily Clinic Logbook');

        $ajaxResponse = $this->actingAs($admin)->getJson(route('clinic-logbooks.index'));
        $ajaxResponse->assertOk();
        $this->assertStringContainsString($entry->full_name, json_encode($ajaxResponse->json('data')));
    }

    public function test_global_navbar_search_returns_categorized_results_across_system(): void
    {
        $admin = User::first();
        $patient = Patient::first();

        $quickResponse = $this->actingAs($admin)->getJson(route('global-search'));
        $quickResponse->assertOk()
            ->assertJsonStructure(['query', 'category', 'total', 'groups']);
        $this->assertNotEmpty($quickResponse->json('groups'));

        $patientResponse = $this->actingAs($admin)->getJson(route('global-search', [
            'q' => $patient->last_name,
            'category' => 'patients',
        ]));
        $patientResponse->assertOk();
        $this->assertGreaterThanOrEqual(1, $patientResponse->json('total'));
        $this->assertSame('patients', $patientResponse->json('groups.0.key'));

        $pageResponse = $this->actingAs($admin)->getJson(route('global-search', [
            'q' => 'Communicable',
            'category' => 'pages',
        ]));
        $pageResponse->assertOk();
        $this->assertGreaterThanOrEqual(1, $pageResponse->json('total'));

        $medicine = Medicine::first();
        $txn = InventoryTransaction::first() ?? InventoryTransaction::create([
            'transaction_number' => 'TXN-2026-00001',
            'medicine_id' => $medicine?->id,
            'type' => 'stock_in',
            'quantity' => 50,
            'quantity_before' => 0,
            'quantity_after' => 50,
            'reference_number' => 'PO-2026-9988',
            'reason' => 'Initial stock replenishment',
            'user_id' => $admin->id,
            'transaction_date' => now(),
        ]);

        $txnResponse = $this->actingAs($admin)->getJson(route('global-search', [
            'q' => $txn->transaction_number,
        ]));
        $txnResponse->assertOk();
        $this->assertGreaterThanOrEqual(1, $txnResponse->json('total'));
        $this->assertSame('smart', $txnResponse->json('groups.0.key'));

        $shorthandResponse = $this->actingAs($admin)->getJson(route('global-search', [
            'q' => 'pat 1',
        ]));
        $shorthandResponse->assertOk();
        $this->assertGreaterThanOrEqual(1, $shorthandResponse->json('total'));

        $fullNameResponse = $this->actingAs($admin)->getJson(route('global-search', [
            'q' => "{$patient->first_name} {$patient->last_name}",
            'category' => 'patients',
        ]));
        $fullNameResponse->assertOk();
        $this->assertGreaterThanOrEqual(1, $fullNameResponse->json('total'));
    }
}
