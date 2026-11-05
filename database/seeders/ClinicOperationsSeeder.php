<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Models\EmergencyRecord;
use App\Models\FirstAidRecord;
use App\Models\MedicalConsultation;
use App\Models\Patient;
use App\Models\PhysicalExamSchedule;
use App\Models\User;
use App\Services\ClinicVisitService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClinicOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $doctor = Clinician::where('type', 'Doctor')->first() ?? Clinician::first();
        $nurse = Clinician::where('type', 'Nurse')->first() ?? $doctor;
        $dentist = Clinician::where('specialization', 'like', '%Dent%')->first() ?? $doctor;
        $ay = AcademicYear::current()->first() ?? AcademicYear::first();
        $visitService = app(ClinicVisitService::class);

        $patients = Patient::with(['college', 'program'])->take(10)->get();
        if ($patients->isEmpty()) {
            return;
        }

        $p1 = $patients->get(0);
        if ($p1) {
            $v1 = ClinicVisit::create(array_merge(
                $visitService->classificationSnapshot($p1),
                [
                    'visit_number' => 'VIS-'.now()->format('Ymd').'-0001',
                    'patient_id' => $p1->id,
                    'visit_type' => ClinicVisit::TYPE_MEDICAL,
                    'visit_category' => 'walk_in',
                    'visit_date' => Carbon::now()->subHours(4),
                    'time_out' => Carbon::now()->subHours(3)->addMinutes(30),
                    'chief_complaint' => 'Moderate fever (38.4°C), non-productive cough, and body malaise for 2 days.',
                    'systolic' => 110,
                    'diastolic' => 70,
                    'pulse_rate' => 88,
                    'respiratory_rate' => 19,
                    'temperature' => 38.4,
                    'oxygen_saturation' => 98,
                    'pain_scale' => 3,
                    'clinician_id' => $doctor?->id,
                    'recorded_by' => $admin?->id,
                    'disposition' => 'sent_home',
                    'status' => 'completed',
                    'remarks' => 'Advised oral hydration and bed rest for 2 days.',
                ],
            ));

            MedicalConsultation::create([
                'clinic_visit_id' => $v1->id,
                'history_of_present_illness' => 'Patient reports gradual onset of body malaise and dry cough 48 hours prior to consult. No shortness of breath.',
                'physical_findings' => 'Pharyngeal erythema (+), clear breath sounds, supple neck, soft non-tender abdomen.',
                'diagnosis' => 'Acute Upper Respiratory Tract Infection (URTI)',
                'icd_code' => 'J06.9',
                'treatment_plan' => 'Paracetamol 500mg tab q4h PRN for fever. Cetirizine 10mg tab OD at bedtime. Increase fluid intake.',
                'medications_prescribed' => 'Paracetamol 500mg #10, Cetirizine 10mg #5',
                'advice' => 'Adequate sleep, wear mask, return to clinic if fever persists beyond 3 days.',
                'medical_certificate_issued' => true,
                'rest_days' => 2,
                'follow_up_date' => Carbon::now()->addDays(3)->toDateString(),
            ]);
        }

        $p2 = $patients->get(1);
        if ($p2) {
            $v2 = ClinicVisit::create(array_merge(
                $visitService->classificationSnapshot($p2),
                [
                    'visit_number' => 'VIS-'.now()->format('Ymd').'-0002',
                    'patient_id' => $p2->id,
                    'visit_type' => ClinicVisit::TYPE_DENTAL,
                    'visit_category' => 'scheduled',
                    'visit_date' => Carbon::now()->subHours(3),
                    'time_out' => Carbon::now()->subHours(2),
                    'chief_complaint' => 'Occasional sensitivity to cold drinks on lower right molar.',
                    'systolic' => 120,
                    'diastolic' => 80,
                    'pulse_rate' => 76,
                    'respiratory_rate' => 16,
                    'temperature' => 36.6,
                    'oxygen_saturation' => 99,
                    'pain_scale' => 2,
                    'clinician_id' => $dentist?->id,
                    'recorded_by' => $admin?->id,
                    'disposition' => 'back_to_class',
                    'status' => 'completed',
                    'remarks' => 'Composite restoration completed uneventfully.',
                ],
            ));

            DentalConsultation::create([
                'clinic_visit_id' => $v2->id,
                'oral_examination_findings' => 'Deep occlusal caries noted on tooth #46 without pulp involvement. Gingiva pink and firm.',
                'tooth_chart' => ['46' => 'F'],
                'procedures' => ['Oral Examination / Consultation', 'Permanent Filling (Restoration)'],
                'teeth_involved' => '#46 (Lower Right 1st Molar)',
                'anesthesia_used' => 'Lidocaine 2% with 1:100,000 Epinephrine 1.8mL',
                'diagnosis' => 'Dental Caries, Class I (Tooth 46)',
                'treatment_rendered' => 'Caries excavation under local anesthesia, cavity conditioning, and light-cured composite resin restoration. Occlusion checked and adjusted.',
                'recommendations' => 'Avoid chewing hard foods for 2 hours. Maintain twice daily brushing and dental flossing.',
                'oral_hygiene_status' => 'Good',
                'follow_up_date' => Carbon::now()->addMonths(6)->toDateString(),
            ]);
        }

        $p3 = $patients->get(2);
        if ($p3) {
            $v3 = ClinicVisit::create(array_merge(
                $visitService->classificationSnapshot($p3),
                [
                    'visit_number' => 'VIS-'.now()->format('Ymd').'-0003',
                    'patient_id' => $p3->id,
                    'visit_type' => ClinicVisit::TYPE_EMERGENCY,
                    'visit_category' => 'emergency_call',
                    'visit_date' => Carbon::now()->subHours(2),
                    'time_out' => Carbon::now()->subHours(1),
                    'chief_complaint' => 'Sudden episode of syncope and loss of consciousness during university assembly.',
                    'systolic' => 90,
                    'diastolic' => 60,
                    'pulse_rate' => 96,
                    'respiratory_rate' => 20,
                    'temperature' => 36.4,
                    'oxygen_saturation' => 96,
                    'pain_scale' => 0,
                    'clinician_id' => $doctor?->id,
                    'recorded_by' => $admin?->id,
                    'disposition' => 'observation',
                    'status' => 'completed',
                    'remarks' => 'Regained full consciousness within 2 minutes. Vital signs stabilized after oral rehydration.',
                ],
            ));

            EmergencyRecord::create([
                'clinic_visit_id' => $v3->id,
                'incident_datetime' => Carbon::now()->subHours(2)->subMinutes(15),
                'incident_location' => 'University Gymnasium Main Stage',
                'emergency_nature' => 'Syncope / Loss of Consciousness',
                'triage_level' => 'yellow',
                'level_of_consciousness' => 'alert',
                'gcs_score' => 15,
                'incident_description' => 'Student collapsed while standing in line during the morning assembly under warm ambient temperature. Denies head trauma or convulsions.',
                'interventions' => ['Oxygen Therapy', 'Recovery Position', 'Monitoring of Vital Signs'],
                'intervention_notes' => 'Placed in Trendelenburg position. Humidified O2 at 3 LPM via nasal cannula given. Blood glucose checked (92 mg/dL - normal). Patient regained alertness.',
                'ambulance_called' => false,
                'transferred_to_hospital' => false,
                'guardian_notified' => true,
                'notified_person' => $p3->emergency_contact_name ?? 'Parent / Guardian',
                'notified_at' => Carbon::now()->subHours(2),
                'incident_report_number' => 'IR-'.now()->format('Y').'-001',
                'outcome' => 'Stabilized and Discharged',
            ]);
        }

        $p4 = $patients->get(3);
        if ($p4) {
            $v4 = ClinicVisit::create(array_merge(
                $visitService->classificationSnapshot($p4),
                [
                    'visit_number' => 'VIS-'.now()->format('Ymd').'-0004',
                    'patient_id' => $p4->id,
                    'visit_type' => ClinicVisit::TYPE_FIRST_AID,
                    'visit_category' => 'walk_in',
                    'visit_date' => Carbon::now()->subHours(1),
                    'time_out' => Carbon::now()->subMinutes(30),
                    'chief_complaint' => 'Abrasion wound on right knee and forearm following a slip and fall along the corridor walkway.',
                    'systolic' => 115,
                    'diastolic' => 75,
                    'pulse_rate' => 80,
                    'respiratory_rate' => 18,
                    'temperature' => 36.7,
                    'oxygen_saturation' => 99,
                    'pain_scale' => 4,
                    'clinician_id' => $nurse?->id,
                    'recorded_by' => $admin?->id,
                    'disposition' => 'back_to_class',
                    'status' => 'completed',
                    'remarks' => 'Dressing clean and dry. Advised to keep wound dry for 24 hours.',
                ],
            ));

            FirstAidRecord::create([
                'clinic_visit_id' => $v4->id,
                'injury_type' => 'Abrasion / Scratch',
                'body_part' => 'Right knee and right forearm',
                'incident_location' => 'Academic Building 2, 2nd Floor Corridor',
                'incident_cause' => 'Slipped on wet floor tile after rain shower',
                'first_aid_given' => ['Wound Cleaning', 'Antiseptic Applied', 'Dressing / Bandage Applied'],
                'treatment_notes' => 'Wound irrigated with sterile saline solution. Povidone-iodine antiseptic applied. Covered with sterile non-adherent gauze pad and hypoallergenic tape.',
                'supplies_used' => 'Normal Saline Solution 50mL, Betadine 10% solution, Gauze pads 4x4 (3 pcs), Micropore tape',
                'was_referred' => false,
            ]);
        }

        $p5 = $patients->get(4);
        if ($p5) {
            ClinicVisit::create(array_merge(
                $visitService->classificationSnapshot($p5),
                [
                    'visit_number' => 'VIS-'.now()->format('Ymd').'-0005',
                    'patient_id' => $p5->id,
                    'visit_type' => ClinicVisit::TYPE_GENERAL,
                    'visit_category' => 'walk_in',
                    'visit_date' => Carbon::now()->subMinutes(20),
                    'time_out' => Carbon::now()->subMinutes(5),
                    'chief_complaint' => 'Routine blood pressure monitoring and medical clearance request for university sports training.',
                    'systolic' => 118,
                    'diastolic' => 78,
                    'pulse_rate' => 72,
                    'respiratory_rate' => 16,
                    'temperature' => 36.5,
                    'oxygen_saturation' => 99,
                    'pain_scale' => 0,
                    'clinician_id' => $nurse?->id,
                    'recorded_by' => $admin?->id,
                    'disposition' => 'back_to_class',
                    'status' => 'completed',
                    'remarks' => 'Blood pressure within normal range. Issued clearance recommendation.',
                ],
            ));
        }

        $sched = PhysicalExamSchedule::create([
            'schedule_number' => 'PES-'.now()->format('Y').'-0001',
            'title' => 'College of Engineering & Information Technology Annual PE Screening',
            'purpose' => 'Annual Physical Exam',
            'target_classification' => 'student',
            'academic_year_id' => $ay?->id,
            'college_id' => $patients->first()->college_id ?? null,
            'program_id' => null,
            'year_level' => '1st Year',
            'scheduled_date' => Carbon::now()->addDays(3)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'venue' => 'University Health Services Department - Examination Room 1',
            'capacity' => 60,
            'clinician_id' => $doctor?->id,
            'status' => 'scheduled',
            'notes' => 'Students are advised to fast for 8 hours prior if blood chemistry is included. Bring student ID.',
            'created_by' => $admin?->id,
        ]);

        foreach ($patients->take(6) as $patient) {
            $sched->attendees()->create([
                'patient_id' => $patient->id,
                'status' => 'pending',
            ]);
        }
    }
}
