<?php

namespace Tests\Feature;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\Patient;
use App\Models\PhysicalExamSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_view_clinic_visits_index(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('clinic-visits.index'));
        $response->assertOk();
        $response->assertSee('Patient Visit Logs &amp; Central Registry', false);
    }

    public function test_clinic_visits_datatables_endpoint_returns_json(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->getJson(route('clinic-visits.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_super_admin_can_create_general_clinic_visit(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::where('type', 'student')->first();
        $clinician = Clinician::first();

        $response = $this->actingAs($admin)->post(route('clinic-visits.store'), [
            'patient_id' => $patient->id,
            'visit_type' => 'general',
            'visit_category' => 'walk_in',
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Blood pressure monitoring prior to university intramurals',
            'systolic' => 120,
            'diastolic' => 80,
            'pulse_rate' => 75,
            'temperature' => 36.6,
            'clinician_id' => $clinician->id,
            'disposition' => 'back_to_class',
            'status' => 'completed',
            'remarks' => 'Cleared for sports activity',
        ]);

        $visit = ClinicVisit::latest('id')->first();
        $this->assertNotNull($visit);
        $this->assertEquals('student', $visit->patient_classification);
        $this->assertEquals($patient->college_id, $visit->college_id);
        $response->assertRedirect(route('clinic-visits.show', $visit));
    }

    public function test_super_admin_can_create_medical_consultation(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $doctor = Clinician::where('type', 'Doctor')->first() ?? Clinician::first();

        $response = $this->actingAs($admin)->post(route('medical-consultations.store'), [
            'patient_id' => $patient->id,
            'visit_category' => 'walk_in',
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Moderate headache and nasal congestion for 2 days',
            'systolic' => 110,
            'diastolic' => 70,
            'pulse_rate' => 82,
            'temperature' => 37.5,
            'clinician_id' => $doctor->id,
            'disposition' => 'sent_home',
            'status' => 'completed',
            'history_of_present_illness' => 'Gradual onset after exposure to rain',
            'physical_findings' => 'Clear breath sounds, congested nasal mucosa',
            'diagnosis' => 'Acute Viral Rhinitis',
            'icd_code' => 'J00',
            'treatment_plan' => 'Hydration and symptomatic relief',
            'medications_prescribed' => 'Paracetamol 500mg tab q4h PRN #6',
            'medical_certificate_issued' => 1,
            'rest_days' => 1,
        ]);

        $visit = ClinicVisit::where('visit_type', 'medical')->latest('id')->first();
        $this->assertNotNull($visit);
        $this->assertNotNull($visit->medicalConsultation);
        $this->assertEquals('Acute Viral Rhinitis', $visit->medicalConsultation->diagnosis);
        $this->assertTrue($visit->medicalConsultation->medical_certificate_issued);
        $response->assertRedirect(route('medical-consultations.show', $visit));
    }

    public function test_super_admin_can_create_dental_consultation(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $clinician = Clinician::first();

        $response = $this->actingAs($admin)->post(route('dental-consultations.store'), [
            'patient_id' => $patient->id,
            'visit_category' => 'scheduled',
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Tooth cavity on upper left premolar',
            'systolic' => 115,
            'diastolic' => 75,
            'temperature' => 36.5,
            'clinician_id' => $clinician->id,
            'disposition' => 'back_to_class',
            'status' => 'completed',
            'tooth_chart' => [
                '24' => 'F',
            ],
            'procedures' => ['Oral Prophylaxis (Cleaning)', 'Permanent Filling (Restoration)'],
            'teeth_involved' => 'Tooth #24',
            'diagnosis' => 'Dental Caries (Class II, Tooth 24)',
            'treatment_rendered' => 'Light-cured composite restoration done',
            'oral_hygiene_status' => 'Good',
        ]);

        $visit = ClinicVisit::where('visit_type', 'dental')->latest('id')->first();
        $this->assertNotNull($visit);
        $this->assertNotNull($visit->dentalConsultation);
        $this->assertEquals('Dental Caries (Class II, Tooth 24)', $visit->dentalConsultation->diagnosis);
        $response->assertRedirect(route('dental-consultations.show', $visit));
    }

    public function test_super_admin_can_create_emergency_record(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $doctor = Clinician::first();

        $response = $this->actingAs($admin)->post(route('emergency-records.store'), [
            'patient_id' => $patient->id,
            'visit_category' => 'emergency_call',
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Sudden shortness of breath and wheezing in classroom',
            'systolic' => 130,
            'diastolic' => 85,
            'pulse_rate' => 110,
            'respiratory_rate' => 28,
            'temperature' => 36.8,
            'oxygen_saturation' => 91,
            'clinician_id' => $doctor->id,
            'disposition' => 'observation',
            'status' => 'completed',
            'emergency_nature' => 'Severe Asthma / Difficulty of Breathing',
            'triage_level' => 'red',
            'level_of_consciousness' => 'alert',
            'gcs_score' => 15,
            'incident_location' => 'Main Library 2nd Floor',
            'interventions' => ['Oxygen Therapy', 'Nebulization'],
            'intervention_notes' => 'Salbutamol neb 1 dose given with high-flow oxygen. SpO2 improved to 97%.',
            'ambulance_called' => 0,
            'transferred_to_hospital' => 0,
            'guardian_notified' => 1,
            'notified_person' => 'Parent (09181234567)',
            'outcome' => 'Stabilized and Discharged',
        ]);

        $visit = ClinicVisit::where('visit_type', 'emergency')->latest('id')->first();
        $this->assertNotNull($visit);
        $this->assertNotNull($visit->emergencyRecord);
        $this->assertEquals('red', $visit->emergencyRecord->triage_level);
        $this->assertTrue($visit->emergencyRecord->guardian_notified);
        $response->assertRedirect(route('emergency-records.show', $visit));
    }

    public function test_super_admin_can_create_first_aid_record(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $nurse = Clinician::first();

        $response = $this->actingAs($admin)->post(route('first-aid-records.store'), [
            'patient_id' => $patient->id,
            'visit_category' => 'walk_in',
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Pain and bleeding on left elbow following a fall',
            'systolic' => 118,
            'diastolic' => 78,
            'temperature' => 36.6,
            'clinician_id' => $nurse->id,
            'disposition' => 'back_to_class',
            'status' => 'completed',
            'injury_type' => 'Laceration / Cut',
            'body_part' => 'Left elbow',
            'incident_location' => 'Engineering Quadrangle',
            'incident_cause' => 'Tripped over walkway edge',
            'first_aid_given' => ['Wound Cleaning', 'Antiseptic Applied', 'Dressing / Bandage Applied'],
            'treatment_notes' => 'Minor laceration cleansed and dressed with sterile bandage',
            'supplies_used' => 'Gauze 4x4, Betadine, Micropore',
            'was_referred' => 0,
        ]);

        $visit = ClinicVisit::where('visit_type', 'first_aid')->latest('id')->first();
        $this->assertNotNull($visit);
        $this->assertNotNull($visit->firstAidRecord);
        $this->assertEquals('Laceration / Cut', $visit->firstAidRecord->injury_type);
        $response->assertRedirect(route('first-aid-records.show', $visit));
    }

    public function test_super_admin_can_create_and_manage_physical_exam_schedule(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();

        $response = $this->actingAs($admin)->post(route('physical-exam-schedules.store'), [
            'title' => 'College of Arts & Sciences Entrance PE Screening 2026',
            'purpose' => 'Entrance Examination',
            'target_classification' => 'student',
            'scheduled_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'venue' => 'Clinic Room 2',
            'capacity' => 100,
            'status' => 'scheduled',
            'auto_populate_attendees' => 0,
        ]);

        $schedule = PhysicalExamSchedule::latest('id')->first();
        $this->assertNotNull($schedule);
        $response->assertRedirect(route('physical-exam-schedules.show', $schedule));

        $addResponse = $this->actingAs($admin)->post(route('physical-exam-schedules.add-attendee', $schedule), [
            'patient_id' => $patient->id,
        ]);
        $addResponse->assertRedirect();
        $this->assertDatabaseHas('physical_exam_schedule_attendees', [
            'physical_exam_schedule_id' => $schedule->id,
            'patient_id' => $patient->id,
            'status' => 'pending',
        ]);

        $attendee = $schedule->attendees()->where('patient_id', $patient->id)->first();
        $statusResponse = $this->actingAs($admin)->post(route('physical-exam-schedules.update-attendee-status', [$schedule, $attendee]), [
            'status' => 'attended',
        ]);
        $statusResponse->assertRedirect();
        $attendee->refresh();
        $this->assertEquals('attended', $attendee->status);
    }

    public function test_printable_slips_render_successfully(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $visit = ClinicVisit::first();
        $schedule = PhysicalExamSchedule::first();

        $slipResponse = $this->actingAs($admin)->get(route('clinic-visits.print', $visit));
        $slipResponse->assertOk();
        $slipResponse->assertSee($visit->visit_number);

        $rosterResponse = $this->actingAs($admin)->get(route('physical-exam-schedules.print-roster', $schedule));
        $rosterResponse->assertOk();
        $rosterResponse->assertSee($schedule->schedule_number);
    }
}
