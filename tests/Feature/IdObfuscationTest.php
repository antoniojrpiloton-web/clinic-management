<?php

namespace Tests\Feature;

use App\Models\ClinicVisit;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\User;
use App\Support\IdObfuscator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdObfuscationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_obfuscator_encodes_and_decodes_deterministic_tokens(): void
    {
        $id = 42;
        $token = IdObfuscator::encode($id, Patient::class);

        $this->assertIsString($token);
        $this->assertSame(8, strlen($token));
        $this->assertSame($id, IdObfuscator::decode($token, Patient::class));
    }

    public function test_tampered_token_decodes_to_null(): void
    {
        $token = IdObfuscator::encode(1, Patient::class);
        $tampered = ($token[0] === 'Z' ? 'A' : 'Z').substr($token, 1);

        $this->assertNull(IdObfuscator::decode($tampered, Patient::class));
    }

    public function test_context_isolation_prevents_token_swapping_between_models(): void
    {
        $patientToken = IdObfuscator::encode(1, Patient::class);

        $this->assertNull(IdObfuscator::decode($patientToken, ClinicVisit::class));
    }

    public function test_patient_route_uses_obfuscated_token_in_address_bar(): void
    {
        $patient = Patient::first();
        $this->assertNotNull($patient);

        $url = route('patients.show', $patient);
        $routeKey = $patient->getRouteKey();

        $this->assertSame(8, strlen($routeKey));
        $this->assertStringContainsString("/patients/{$routeKey}", $url);
        $this->assertStringNotContainsString("/patients/{$patient->id}", $url);
    }

    public function test_super_admin_can_view_patient_via_obfuscated_url(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();

        $response = $this->actingAs($admin)->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertSee(e($patient->full_name));
    }

    public function test_tampered_patient_url_returns_404_not_found(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $validToken = $patient->getRouteKey();
        $tamperedToken = ($validToken[0] === 'Z' ? 'A' : 'Z').substr($validToken, 1);

        $response = $this->actingAs($admin)->get("/patients/{$tamperedToken}");

        $response->assertNotFound();
    }

    public function test_clinic_visit_and_consultation_routes_use_obfuscated_tokens(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $visit = ClinicVisit::where('visit_type', 'medical')->first();
        $this->assertNotNull($visit);

        $url = route('medical-consultations.show', $visit);
        $routeKey = $visit->getRouteKey();

        $this->assertSame(8, strlen($routeKey));
        $this->assertStringContainsString("/medical-consultations/{$routeKey}", $url);

        $response = $this->actingAs($admin)->get($url);
        $response->assertOk();
        $response->assertSee($visit->visit_number);
    }

    public function test_physical_examination_route_uses_obfuscated_token(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $exam = PhysicalExamination::first();
        if (! $exam) {
            $patient = Patient::first();
            $exam = PhysicalExamination::create([
                'pe_number' => 'PE-TEST-0001',
                'patient_id' => $patient->id,
                'exam_date' => now()->toDateString(),
                'purpose' => 'Annual Health Examination',
            ]);
        }

        $url = route('physical-examinations.show', $exam);
        $routeKey = $exam->getRouteKey();

        $this->assertSame(8, strlen($routeKey));
        $this->assertStringContainsString("/physical-examinations/{$routeKey}", $url);

        $response = $this->actingAs($admin)->get($url);
        $response->assertOk();
    }

    public function test_query_parameters_with_obfuscated_patient_id_resolve_correctly(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();

        $url = route('physical-examinations.create', ['patient_id' => $patient->getRouteKey()]);
        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
        $response->assertSee(e($patient->full_name));
    }
}
