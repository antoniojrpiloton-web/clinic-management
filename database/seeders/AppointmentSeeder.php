<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinician;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $clinicians = Clinician::active()->get();
        $adminUser = User::first();
        $bookedById = $adminUser?->id;

        $students = Patient::where('type', 'student')->inRandomOrder()->limit(60)->get();
        $faculty = Patient::where('type', 'employee')->where('category', 'Faculty')->inRandomOrder()->limit(30)->get();
        $staff = Patient::where('type', 'employee')->where('category', '!=', 'Faculty')->inRandomOrder()->limit(30)->get();

        $allPatients = $students->concat($faculty)->concat($staff)->shuffle();
        if ($allPatients->isEmpty()) {
            return;
        }

        $timeSlots = [
            '08:00 AM',
            '08:30 AM',
            '09:00 AM',
            '09:30 AM',
            '10:00 AM',
            '10:30 AM',
            '11:00 AM',
            '01:00 PM',
            '01:30 PM',
            '02:00 PM',
            '02:30 PM',
            '03:00 PM',
            '03:30 PM',
            '04:00 PM',
        ];

        $reasons = [
            'medical' => [
                'Persistent dry cough and mild fever for 3 days',
                'Follow-up evaluation for Stage 2 Hypertension',
                'Severe tension headache and blurred vision episodes',
                'Recurrent asthma flare-up with nocturnal wheezing',
                'Acute epigastric pain and suspected acid reflux',
                'Allergic contact dermatitis and skin erythema',
                'Annual executive faculty medical evaluation',
                'Dizziness, orthostatic hypotension assessment',
            ],
            'dental' => [
                'Severe lower left molar toothache with cold sensitivity',
                'Oral prophylaxis and calculus removal',
                'Evaluation for wisdom tooth impaction and extraction',
                'Gingival bleeding and dental caries assessment',
                'Follow-up after dental tooth extraction',
                'Temporary filling replacement and cavity check',
            ],
            'physical_exam' => [
                'Freshmen pre-enrollment physical examination and clearance',
                'Annual physical exam for non-teaching personnel',
                'Athletic clearance for collegiate basketball tournament',
                'Internship & OJT medical certificate issuance',
                'Laboratory diagnostic results review for employment',
            ],
            'consultation' => [
                'General health consultation and vital signs assessment',
                'Nutritional counseling and lifestyle intervention',
                'Stress management and mental health check-in',
                'Follow-up on laboratory chemistry panel results',
            ],
            'clearance' => [
                'Graduation medical clearance verification',
                'Return-to-work clearance after medical leave',
                'Inter-school sports competition fitness clearance',
                'Off-campus excursion health clearance endorsement',
            ],
            'follow_up' => [
                'BP monitoring follow-up (Day 7)',
                'Wound dressing change and suture inspection',
                'Post-consultation symptom resolution check',
            ],
        ];

        $nurseNotes = [
            'medical' => [
                'Patient reports taking Paracetamol with minimal relief. BP 130/85 mmHg.',
                'Maintenance medication Losartan 50mg OD recorded. Nurse advised rest.',
                'Accompanied by classmate; endorsed to doctor on duty as priority.',
                'Requested medical certificate for missed midterm examination.',
            ],
            'dental' => [
                'Visible caries on tooth #36; mild periapical tenderness on percussion.',
                'Routine annual prophylaxis requested by employee.',
                'Swelling on lower jaw observed; ice pack provided at reception.',
            ],
            'physical_exam' => [
                'All lab results (CBC, Urinalysis, Chest X-ray) attached and completed.',
                'Student brought official physical exam checklist from registrar.',
                'Employee submitted annual laboratory panel for physician sign-off.',
            ],
            'consultation' => [
                'Routine consultation booked during nurse duty triage.',
                'Patient reported mild fatigue and irregular sleep cycle.',
            ],
            'clearance' => [
                'Medical requirements verified complete by nursing desk.',
                'Signed endorsement form prepared for physician review.',
            ],
            'follow_up' => [
                'Previous visit records retrieved and placed on doctor desk.',
                'Symptoms improved; scheduled for routine re-assessment.',
            ],
        ];

        $baseDate = Carbon::create(2026, 11, 9);
        $patientIndex = 0;
        $appointmentSeq = 100;

        for ($dayOffset = -7; $dayOffset <= 14; $dayOffset++) {
            $currentDate = (clone $baseDate)->addDays($dayOffset);

            if ($currentDate->isWeekend()) {
                continue;
            }

            $countForDay = $dayOffset === 0 ? 10 : ($dayOffset < 0 ? rand(2, 4) : rand(3, 6));
            $usedSlots = [];

            for ($i = 0; $i < $countForDay; $i++) {
                $patient = $allPatients[$patientIndex % $allPatients->count()];
                $patientIndex++;

                $availableSlots = array_values(array_diff($timeSlots, $usedSlots));
                if (empty($availableSlots)) {
                    break;
                }
                $time = $availableSlots[array_rand($availableSlots)];
                $usedSlots[] = $time;

                $serviceKeys = array_keys($reasons);
                $service = $serviceKeys[array_rand($serviceKeys)];

                $clinician = null;
                if ($service === 'dental') {
                    $clinician = $clinicians->firstWhere('type', 'Dentist');
                } else {
                    $medicalClinicians = $clinicians->where('type', '!=', 'Dentist');
                    if ($medicalClinicians->isNotEmpty()) {
                        $clinician = $medicalClinicians->random();
                    }
                }

                $priority = 'normal';
                $randPriority = rand(1, 10);
                if ($randPriority === 1) {
                    $priority = 'urgent';
                } elseif ($randPriority <= 3) {
                    $priority = 'high';
                }

                $status = 'scheduled';
                $checkedInAt = null;
                $cancellationReason = null;

                if ($dayOffset < 0) {
                    $isCancelled = rand(1, 10) === 1;
                    if ($isCancelled) {
                        $status = 'cancelled';
                        $cancellationReason = 'Patient requested cancellation or rescheduled.';
                    } else {
                        $status = 'completed';
                        $checkedInAt = (clone $currentDate)->setTime(rand(8, 15), rand(0, 59));
                    }
                } elseif ($dayOffset === 0) {
                    if ($i < 3) {
                        $status = 'completed';
                        $checkedInAt = (clone $currentDate)->setTime(8, 30);
                    } elseif ($i < 6) {
                        $status = 'checked_in';
                        $checkedInAt = (clone $currentDate)->setTime(rand(8, 11), rand(10, 50));
                        if ($i === 3) {
                            $priority = 'urgent';
                        }
                    } else {
                        $status = 'scheduled';
                        if ($i === 6) {
                            $priority = 'high';
                        }
                    }
                } else {
                    $status = 'scheduled';
                }

                $reasonText = $reasons[$service][array_rand($reasons[$service])];
                $notesList = $nurseNotes[$service] ?? ['Standard appointment booked via clinic reception desk.'];
                $notesText = $notesList[array_rand($notesList)];

                $appointmentSeq++;
                $appointmentNumber = sprintf('APP-%s-%04d', $currentDate->format('Ymd'), $appointmentSeq);

                Appointment::create([
                    'appointment_number' => $appointmentNumber,
                    'patient_id' => $patient->id,
                    'clinician_id' => $clinician?->id,
                    'booked_by_user_id' => $bookedById,
                    'appointment_date' => $currentDate->toDateString(),
                    'appointment_time' => $time,
                    'service_type' => $service,
                    'priority_level' => $priority,
                    'status' => $status,
                    'reason' => $reasonText,
                    'notes' => $notesText,
                    'checked_in_at' => $checkedInAt,
                    'cancellation_reason' => $cancellationReason,
                ]);
            }
        }
    }
}
