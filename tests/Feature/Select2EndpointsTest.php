<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Select2EndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_query_select2_endpoints(): void
    {
        $user = User::role(User::SUPER_ADMIN_ROLE)->first();
        $college = College::first();

        $patientsResponse = $this->actingAs($user)->getJson(route('select2.patients', ['q' => '']));
        $patientsResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $firstPatient = Patient::first();
        if ($firstPatient) {
            $nameSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => $firstPatient->last_name]));
            $nameSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);

            $idSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => $firstPatient->school_id_number ?: $firstPatient->patient_number]));
            $idSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);

            $multiSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => "{$firstPatient->first_name} {$firstPatient->last_name}"]));
            $multiSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);
        }

        $firstPatient = Patient::first();
        if ($firstPatient) {
            $nameSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => $firstPatient->last_name]));
            $nameSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);

            $idSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => $firstPatient->school_id_number ?: $firstPatient->patient_number]));
            $idSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);

            $multiSearch = $this->actingAs($user)->getJson(route('select2.patients', ['q' => "{$firstPatient->first_name} {$firstPatient->last_name}"]));
            $multiSearch->assertOk()
                ->assertJsonStructure(['results', 'pagination' => ['more']]);
        }

        $collegesResponse = $this->actingAs($user)->getJson(route('select2.colleges', ['q' => '']));
        $collegesResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $programsResponse = $this->actingAs($user)->getJson(route('select2.programs', ['college_id' => $college->id]));
        $programsResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $yearsResponse = $this->actingAs($user)->getJson(route('select2.academic-years'));
        $yearsResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $usersResponse = $this->actingAs($user)->getJson(route('select2.users'));
        $usersResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $rolesResponse = $this->actingAs($user)->getJson(route('select2.roles'));
        $rolesResponse->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);
    }

    public function test_guest_cannot_access_select2_endpoints(): void
    {
        $this->getJson(route('select2.patients'))->assertUnauthorized();
        $this->getJson(route('select2.colleges'))->assertUnauthorized();
        $this->getJson(route('select2.users'))->assertUnauthorized();
    }
}
