<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinician;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_appointments_index(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('appointments.index'));
        $response->assertOk();
        $response->assertSee('Doctor Appointments', false);
    }

    public function test_authenticated_user_can_view_appointments_calendar(): void
    {
        $admin = User::first();

        $response = $this->actingAs($admin)->get(route('appointments.calendar'));
        $response->assertOk();
        $response->assertSee('Appointment Calendar', false);
    }

    public function test_user_can_book_doctor_appointment(): void
    {
        $admin = User::first();
        $patient = Patient::first();
        $clinician = Clinician::first();

        $response = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00 AM',
            'service_type' => 'medical',
            'priority_level' => 'urgent',
            'reason' => 'Severe fever and chills for 3 days',
            'notes' => 'Please fast-track upon physician arrival',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'service_type' => 'medical',
            'priority_level' => 'urgent',
            'reason' => 'Severe fever and chills for 3 days',
            'status' => 'scheduled',
        ]);
    }

    public function test_user_can_book_appointment_for_prospect_or_new_patient(): void
    {
        $admin = User::first();
        $clinician = Clinician::first();

        $response = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_mode' => 'new',
            'new_patient_type' => 'student',
            'new_id_number' => '2026-APP-9988',
            'new_first_name' => 'Eduardo',
            'new_last_name' => 'Salvador',
            'new_gender' => 'Male',
            'new_year_level' => '1st Year',
            'new_phone' => '09171234567',
            'clinician_id' => $clinician->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:30 AM',
            'service_type' => 'physical_exam',
            'priority_level' => 'high',
            'reason' => 'Freshmen entrance physical examination and clearance',
        ]);

        $response->assertSessionHas('success');

        $newPatient = Patient::where('first_name', 'Eduardo')->where('last_name', 'Salvador')->first();
        $this->assertNotNull($newPatient);
        $this->assertSame('student', $newPatient->type);
        $this->assertSame('2026-APP-9988', $newPatient->school_id_number);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $newPatient->id,
            'service_type' => 'physical_exam',
            'priority_level' => 'high',
            'reason' => 'Freshmen entrance physical examination and clearance',
            'status' => 'scheduled',
        ]);
    }

    public function test_patient_can_check_in_for_appointment(): void
    {
        $admin = User::first();
        $patient = Patient::first();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00 AM',
            'service_type' => 'medical',
            'priority_level' => 'high',
            'reason' => 'Follow up consultation',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($admin)->post(route('appointments.check-in', $appointment));
        $response->assertSessionHas('success');

        $appointment->refresh();
        $this->assertSame('checked_in', $appointment->status);
        $this->assertNotNull($appointment->checked_in_at);
    }

    public function test_start_consultation_redirects_and_fulfills_appointment(): void
    {
        $admin = User::first();
        $patient = Patient::first();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00 AM',
            'service_type' => 'medical',
            'priority_level' => 'normal',
            'reason' => 'Routine checkup',
            'status' => 'checked_in',
        ]);

        $startResponse = $this->actingAs($admin)->post(route('appointments.start-consultation', $appointment));
        $startResponse->assertRedirect(route('medical-consultations.create', [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'complaint' => 'Routine checkup',
        ]));

        $consultResponse = $this->actingAs($admin)->post(route('medical-consultations.store'), [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'visit_category' => 'walk_in',
            'chief_complaint' => 'Routine checkup',
            'diagnosis' => 'Acute Upper Respiratory Tract Infection',
            'status' => 'completed',
        ]);

        $consultResponse->assertSessionHas('success');

        $appointment->refresh();
        $this->assertSame('completed', $appointment->status);
        $this->assertNotNull($appointment->clinic_visit_id);
    }

    public function test_user_can_cancel_appointment(): void
    {
        $admin = User::first();
        $patient = Patient::first();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '02:00 PM',
            'service_type' => 'dental',
            'priority_level' => 'normal',
            'reason' => 'Dental cleaning',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($admin)->post(route('appointments.cancel', $appointment), [
            'cancellation_reason' => 'Patient called to reschedule',
        ]);

        $response->assertSessionHas('success');
        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame('Patient called to reschedule', $appointment->cancellation_reason);
    }

    public function test_user_can_book_appointment_for_faculty_and_staff_separately(): void
    {
        $admin = User::first();

        $facultyResponse = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_mode' => 'new',
            'new_patient_type' => 'faculty',
            'new_id_number' => 'FAC-2026-001',
            'new_first_name' => 'Maria',
            'new_last_name' => 'Santos',
            'new_gender' => 'Female',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '01:30 PM',
            'service_type' => 'medical',
            'priority_level' => 'normal',
            'reason' => 'Annual faculty medical examination',
        ]);
        $facultyResponse->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'school_id_number' => 'FAC-2026-001',
            'type' => 'employee',
            'category' => 'Faculty',
        ]);

        $staffResponse = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_mode' => 'new',
            'new_patient_type' => 'staff',
            'new_id_number' => 'STF-2026-001',
            'new_first_name' => 'Jose',
            'new_last_name' => 'Reyes',
            'new_gender' => 'Male',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '02:30 PM',
            'service_type' => 'medical',
            'priority_level' => 'normal',
            'reason' => 'Staff clearance',
        ]);
        $staffResponse->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'school_id_number' => 'STF-2026-001',
            'type' => 'employee',
            'category' => 'Non-Teaching / Staff',
        ]);

        $employeeResponse = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_mode' => 'new',
            'new_patient_type' => 'employee',
            'new_employee_category' => 'Faculty',
            'new_id_number' => 'EMP-FAC-2026-09',
            'new_first_name' => 'Teresa',
            'new_last_name' => 'Dela Cruz',
            'new_gender' => 'Female',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '03:30 PM',
            'service_type' => 'consultation',
            'priority_level' => 'normal',
            'reason' => 'Routine faculty consult',
        ]);
        $employeeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'school_id_number' => 'EMP-FAC-2026-09',
            'type' => 'employee',
            'category' => 'Faculty',
        ]);
    }
}
