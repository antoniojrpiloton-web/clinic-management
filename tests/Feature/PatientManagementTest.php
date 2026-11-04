<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Patient;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_view_patients_and_register_student(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $college = College::first();
        $program = Program::first();

        $response = $this->actingAs($admin)->get(route('patients.index'));
        $response->assertOk();

        $createResponse = $this->actingAs($admin)->post(route('students.store'), [
            'student_id' => '2025-99887',
            'college_id' => $college->id,
            'program_id' => $program->id,
            'year_level' => '1st Year',
            'section' => 'BSIT 1-A',
            'enrollment_status' => 'enrolled',
            'first_name' => 'Alexander',
            'middle_name' => 'Grant',
            'last_name' => 'Hamilton',
            'suffix' => null,
            'gender' => 'Male',
            'date_of_birth' => '2005-02-18',
            'civil_status' => 'Single',
            'blood_type' => 'O+',
            'email' => 'alex.hamilton@snsu.edu.ph',
            'phone' => '+63 911 222 3344',
            'address' => 'Surigao City',
            'emergency_contact_name' => 'Elizabeth Hamilton',
            'emergency_contact_relationship' => 'Mother',
            'emergency_contact_phone' => '+63 911 444 5566',
            'allergies' => 'None',
            'medical_history' => 'Healthy',
        ]);

        $createResponse->assertRedirect(route('students.index'));

        $patient = Patient::where('first_name', 'Alexander')->where('last_name', 'Hamilton')->first();
        $this->assertNotNull($patient);
        $this->assertEquals('student', $patient->type);
        $this->assertNotNull($patient->studentProfile);
        $this->assertEquals('2025-99887', $patient->studentProfile->student_id);

        $showResponse = $this->actingAs($admin)->get(route('patients.show', $patient));
        $showResponse->assertOk()->assertSee($patient->full_name);
    }

    public function test_super_admin_can_register_employee(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $college = College::first();

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'employee_id' => 'EMP-2024-9981',
            'college_id' => $college->id,
            'office_department' => 'Department of Nursing',
            'employment_classification' => 'Permanent',
            'category' => 'Faculty',
            'position' => 'Instructor I',
            'employment_status' => 'active',
            'first_name' => 'Grace',
            'middle_name' => 'B.',
            'last_name' => 'Hopper',
            'suffix' => null,
            'gender' => 'Female',
            'date_of_birth' => '1988-12-09',
            'civil_status' => 'Single',
            'blood_type' => 'A+',
            'email' => 'grace.hopper@snsu.edu.ph',
            'phone' => '+63 922 888 7766',
            'address' => 'Main Campus Staff Housing',
            'emergency_contact_name' => 'William Hopper',
            'emergency_contact_relationship' => 'Brother',
            'emergency_contact_phone' => '+63 922 999 0011',
            'allergies' => 'Penicillin',
            'medical_history' => 'None',
        ]);

        $response->assertRedirect(route('employees.index'));

        $patient = Patient::where('first_name', 'Grace')->where('last_name', 'Hopper')->first();
        $this->assertNotNull($patient);
        $this->assertEquals('employee', $patient->type);
        $this->assertNotNull($patient->employeeProfile);
        $this->assertEquals('EMP-2024-9981', $patient->employeeProfile->employee_id);
    }

    public function test_duplicate_check_api_endpoint(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('patients.check-duplicate', [
            'first_name' => 'Krystal',
            'last_name' => 'Bernardino',
            'date_of_birth' => '2004-05-14',
        ]));

        $response->assertOk();
        $response->assertJson([
            'found' => true,
        ]);

        $notFoundResponse = $this->actingAs($admin)->get(route('patients.check-duplicate', [
            'first_name' => 'NonExistent',
            'last_name' => 'Person',
            'date_of_birth' => '2000-01-01',
        ]));

        $notFoundResponse->assertOk();
        $notFoundResponse->assertJson(['found' => false]);
    }

    public function test_batch_csv_import_for_students(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $csvContent = "student_id,first_name,last_name,gender,date_of_birth,college,program,email,phone\n".
                      "2026-90001,Lucas,Dela Cruz,Male,2005-07-22,CCIS,BSIT,lucas.dc@snsu.edu.ph,09121112222\n".
                      "2026-90002,Sophia,Mercado,Female,2004-09-15,CCIS,BSIT,sophia.m@snsu.edu.ph,09123334444\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('patients.import.process'), [
            'type' => 'student',
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('patients.index'));

        $this->assertDatabaseHas('patients', ['first_name' => 'Lucas', 'last_name' => 'Dela Cruz']);
        $this->assertDatabaseHas('patients', ['first_name' => 'Sophia', 'last_name' => 'Mercado']);
        $this->assertDatabaseHas('patients', ['school_id_number' => '2026-90001']);
        $this->assertDatabaseHas('patients', ['school_id_number' => '2026-90002']);
    }

    public function test_super_admin_can_create_patient_via_unified_endpoint(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $college = College::first();
        $program = Program::first();

        $formResponse = $this->actingAs($admin)->get(route('patients.create'));
        $formResponse->assertOk()->assertSee('Register New Patient');

        $storeResponse = $this->actingAs($admin)->post(route('patients.store'), [
            'type' => 'student',
            'school_id_number' => '2026-77889',
            'college_id' => $college->id,
            'program_id' => $program->id,
            'year_level' => '1st Year',
            'section' => 'BSIT 1-C',
            'enrollment_status' => 'enrolled',
            'first_name' => 'Danilo',
            'middle_name' => 'Castro',
            'last_name' => 'Mendoza',
            'gender' => 'Male',
            'date_of_birth' => '2005-04-10',
            'civil_status' => 'Single',
            'status' => 'active',
        ]);

        $patient = Patient::where('school_id_number', '2026-77889')->first();
        $this->assertNotNull($patient);
        $storeResponse->assertRedirect(route('patients.show', $patient));
        $this->assertEquals('student', $patient->type);
        $this->assertEquals('Danilo', $patient->first_name);

        $editResponse = $this->actingAs($admin)->get(route('patients.edit', $patient));
        $editResponse->assertOk()->assertSee('Edit Patient Record');

        $updateResponse = $this->actingAs($admin)->put(route('patients.update', $patient), [
            'type' => 'student',
            'school_id_number' => '2026-77889',
            'college_id' => $college->id,
            'program_id' => $program->id,
            'year_level' => '2nd Year',
            'section' => 'BSIT 2-C',
            'enrollment_status' => 'enrolled',
            'first_name' => 'Danilo',
            'middle_name' => 'Castro',
            'last_name' => 'Mendoza',
            'gender' => 'Male',
            'date_of_birth' => '2005-04-10',
            'civil_status' => 'Single',
            'status' => 'active',
        ]);

        $updateResponse->assertRedirect(route('patients.show', $patient));
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'year_level' => '2nd Year',
            'section' => 'BSIT 2-C',
        ]);
    }
}
