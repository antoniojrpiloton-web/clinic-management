<?php

namespace Tests\Feature;

use App\Models\Clinician;
use App\Models\ClinicLogbook;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\DentalConsultation;
use App\Models\DiseaseCategory;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
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

    public function test_medical_and_dental_records_and_login_records_reports_render_and_export_pdf(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $year = (int) now()->year;

        $college = College::first();
        Program::firstOrCreate(
            ['code' => 'BSMT'],
            [
                'college_id' => $college->id,
                'name' => 'Bachelor of Science in Marine Transportation',
                'is_active' => true,
            ]
        );

        $indexResponse = $this->actingAs($admin)->get(route('reports.clinic.index', ['year' => $year]));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Reports & Analytics');
        $indexResponse->assertSee('NVC Clinic Morbidity & Utilization Reports');
        $indexResponse->assertSee('1. Medical and Dental Service Reports');
        $indexResponse->assertSee('Scheduled Physical Examination');
        $indexResponse->assertSee('Total Clinic Visits');
        $indexResponse->assertSee('2. Patient Classification Reports');
        $indexResponse->assertSee('Faculty (Teaching Personnel)');
        $indexResponse->assertSee('Non-Teaching Personnel / Staff');
        $indexResponse->assertSee('New Registrations');

        $medDentalPage = $this->actingAs($admin)->get(route('reports.clinic.medical-dental', ['year' => $year]));
        $medDentalPage->assertOk();
        $medDentalPage->assertSee('MEDICAL AND DENTAL RECORDS');
        $medDentalPage->assertSee('CRIM Students');
        $medDentalPage->assertSee('BSHM/ TM Students');
        $medDentalPage->assertSee('Forensic Students');
        $medDentalPage->assertSee('TESDA Students');
        $medDentalPage->assertSee('BSMT Students');
        $medDentalPage->assertSee('Annual Service Totals & Student / Employee Distribution');
        $medDentalPage->assertSee('Comparison of Monthly Service Utilization');

        $loginPage = $this->actingAs($admin)->get(route('reports.clinic.login-records', ['year' => $year]));
        $loginPage->assertOk();
        $loginPage->assertSee('MEDICAL AND DENTAL LOG-IN RECORDS');
        $loginPage->assertSee('BSHM Students');
        $loginPage->assertSee('BSTM Students');
        $loginPage->assertSee('BSMT Students');

        $commPage = $this->actingAs($admin)->get(route('reports.clinic.communicable', ['year' => $year]));
        $commPage->assertOk();
        $commPage->assertSee("COMMUNICABLE DISEASES {$year} DATA");

        $nonCommPage = $this->actingAs($admin)->get(route('reports.clinic.non-communicable', ['year' => $year]));
        $nonCommPage->assertOk();
        $nonCommPage->assertSee("NON- COMMUNICABLE DISEASES {$year} DATA");

        $medDentalPdf = $this->actingAs($admin)->get(route('reports.clinic.print-medical-dental-records', ['year' => $year]));
        $medDentalPdf->assertOk();
        $medDentalPdf->assertHeader('Content-Type', 'application/pdf');
        $medDentalContent = $medDentalPdf->getContent();
        $this->assertStringStartsWith('%PDF-', $medDentalContent);
        $this->assertStringContainsString('MEDICAL AND DENTAL RECORDS', $medDentalContent);
        $this->assertStringContainsString('SCHEDULED', $medDentalContent);
        $this->assertStringContainsString('EXAMINATION', $medDentalContent);
        $this->assertStringContainsString('BSHM/ TM Students', $medDentalContent);
        $this->assertStringContainsString('BSMT Students', $medDentalContent);
        $this->assertStringContainsString('Athletes', $medDentalContent);
        $this->assertStringContainsString('TOTAL FOR THE MONTH', $medDentalContent);
        $this->assertStringContainsString('KIEN I. IGUP, RN', $medDentalContent);
        $this->assertStringContainsString('/Count 1', $medDentalContent);

        $loginPdf = $this->actingAs($admin)->get(route('reports.clinic.print-login-records', ['year' => $year]));
        $loginPdf->assertOk();
        $loginPdf->assertHeader('Content-Type', 'application/pdf');
        $loginContent = $loginPdf->getContent();
        $this->assertStringStartsWith('%PDF-', $loginContent);
        $this->assertStringContainsString('MEDICAL AND DENTAL LOG-IN RECORDS', $loginContent);
        $this->assertStringContainsString('BSHM Students', $loginContent);
        $this->assertStringContainsString('BSTM Students', $loginContent);
        $this->assertStringContainsString('BSMT Students', $loginContent);
        $this->assertStringContainsString('ELEMENTARY', $loginContent);
        $this->assertStringContainsString('TOTAL FOR THE MONTH', $loginContent);
        $this->assertStringContainsString('/Count 1', $loginContent);

        DiseaseCategory::firstOrCreate(
            ['code' => 'COMM-MONKEYPOX'],
            [
                'name' => 'Mpox / Viral Exanthem Surveillance',
                'classification' => DiseaseCategory::TYPE_COMMUNICABLE,
                'keywords' => 'mpox,monkeypox',
                'sort_order' => 40,
                'is_active' => true,
            ]
        );

        $commPdf = $this->actingAs($admin)->get(route('reports.clinic.print-communicable-diseases', ['year' => $year]));
        $commPdf->assertOk();
        $commPdf->assertHeader('Content-Type', 'application/pdf');
        $commContent = $commPdf->getContent();
        $this->assertStringStartsWith('%PDF-', $commContent);
        $this->assertStringContainsString("COMMUNICABLE DISEASES {$year} DATA", $commContent);
        $this->assertStringContainsString('Tuberculosis', $commContent);
        $this->assertStringContainsString('URTI \\(cough, colds, fever, runny nose, sore throat and tonsilitis\\)', $commContent);
        $this->assertStringContainsString('SVI \\(Chickenpox, Conjunctivitis, Dengue, Herpes Zoster\\)', $commContent);
        $this->assertStringContainsString('Mpox / Viral Exanthem Surveillance', $commContent);
        $this->assertStringContainsString('/Count 1', $commContent);

        $nonCommPdf = $this->actingAs($admin)->get(route('reports.clinic.print-non-communicable-diseases', ['year' => $year]));
        $nonCommPdf->assertOk();
        $nonCommPdf->assertHeader('Content-Type', 'application/pdf');
        $nonCommContent = $nonCommPdf->getContent();
        $this->assertStringStartsWith('%PDF-', $nonCommContent);
        $this->assertStringContainsString("NON- COMMUNICABLE DISEASES {$year} DATA", $nonCommContent);
        $this->assertStringContainsString('Illness of Student of Disabiltiy', $nonCommContent);
        $this->assertStringContainsString('Reproductive \\(Pregnancy, Cyst and Dysmenorrhea\\)', $nonCommContent);
        $this->assertStringContainsString('Cardiovascular', $nonCommContent);
        $this->assertStringContainsString('/Count 1', $nonCommContent);
    }

    public function test_physical_examination_pdf_renders_two_page_back_to_back_hsd_form_1(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = $this->getOrCreatePhysicalExam();

        $response = $this->actingAs($admin)->get(route('physical-examinations.print-pe', $exam));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Count 2', $content);
        $this->assertStringContainsString('HSD FORM 1  Full Physical Examination', $content);

        $this->assertStringContainsString('PHYSICAL EXAMINATION', $content);
        $this->assertStringContainsString('Work/Course:', $content);
        $this->assertStringContainsString('VITAL SIGNS:', $content);
        $this->assertStringContainsString('MEDICAL HISTORY', $content);
        $this->assertStringContainsString('Allergies \\(food, medication, etc.\\), Asthma', $content);
        $this->assertStringContainsString('HOSPITALIZATIONS', $content);
        $this->assertStringContainsString('SURGERIES', $content);
        $this->assertStringContainsString('Restricted by Doctor to participate in Physical', $content);
        $this->assertStringContainsString('FAMILY HISTORY', $content);
        $this->assertStringContainsString('HYPERTENSION', $content);
        $this->assertStringContainsString('LUNG DISEASE', $content);
        $this->assertStringContainsString('OTHERS, SPECIFY:', $content);

        $this->assertStringContainsString('1.    EYES', $content);
        $this->assertStringContainsString('2.    EARS, NOSE, THROAT', $content);
        $this->assertStringContainsString('9.    GENITALIA- HERNIA \\(MALE\\)', $content);
        $this->assertStringContainsString('10.  MUSKULOSKELETAL: ROM', $content);
        $this->assertStringContainsString('11.  NEUROMUSCULAR \\(REFLEXES\\)', $content);
        $this->assertStringContainsString('This is to certify that I have examined the above named on', $content);
        $this->assertStringContainsString('to Work/ Return to School', $content);
        $this->assertStringContainsString('Refusal of Medical Treatment', $content);
        $this->assertStringContainsString('Waiver: I have been advised of the procedures', $content);
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
        $staffResponse->assertSee($entry->full_name);
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

        $posResponse = $this->actingAs($admin)->get(route('inventory.dispensings.create'));
        $posResponse->assertOk();
        $posResponse->assertSee('Pharmacy POS Dispensing Terminal');
        $posResponse->assertSee('Dispensing Ticket');
    }
}
