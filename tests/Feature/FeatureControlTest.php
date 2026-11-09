<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FeatureControlService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $developer;

    protected User $clientAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.enforce_feature_gate_in_tests' => true]);

        $this->seed([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
        ]);

        $this->developer = User::factory()->create([
            'name' => 'Master Developer',
            'email' => 'apiloton1@snsu.edu.ph',
            'is_active' => true,
        ]);
        $this->developer->assignRole(User::SUPER_ADMIN_ROLE);

        $this->clientAdmin = User::factory()->create([
            'name' => 'Client Super Admin',
            'email' => 'admin@clinic.test',
            'is_active' => true,
        ]);
        $this->clientAdmin->assignRole(User::SUPER_ADMIN_ROLE);
    }

    public function test_default_turn_over_progress_is_25_percent_and_blocks_locked_modules_for_client_users(): void
    {
        $service = app(FeatureControlService::class);
        $summary = $service->progressSummary();

        $this->assertSame(3, $summary['enabled_count']);
        $this->assertSame(12, $summary['total_count']);
        $this->assertSame(25, $summary['percentage']);

        $this->actingAs($this->clientAdmin)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->clientAdmin)->get(route('patients.index'))->assertOk();
        $this->actingAs($this->clientAdmin)->get(route('clinic-logbooks.index'))->assertOk();

        $this->actingAs($this->clientAdmin)
            ->get(route('dental-consultations.index'))
            ->assertForbidden()
            ->assertSee('Dental Consultations & Oral Health Card is Currently Locked');

        $this->actingAs($this->clientAdmin)
            ->get(route('inventory.dispensings.create'))
            ->assertForbidden()
            ->assertSee('Pharmacy POS Dispensing Terminal is Currently Locked');

        $this->actingAs($this->clientAdmin)
            ->get(route('reports.clinic.index'))
            ->assertForbidden()
            ->assertSee('Reports, Analytics & Official PDF Exports is Currently Locked');

        $this->actingAs($this->developer)->get(route('dental-consultations.index'))->assertOk();
        $this->actingAs($this->developer)->get(route('inventory.dispensings.create'))->assertOk();
        $this->actingAs($this->developer)->get(route('reports.clinic.index'))->assertOk();
    }

    public function test_only_apiloton1_can_access_and_toggle_feature_release_controls(): void
    {

        $this->actingAs($this->clientAdmin)
            ->get(route('feature-control.index'))
            ->assertForbidden();

        $this->actingAs($this->clientAdmin)
            ->post(route('feature-control.preset'), ['milestone' => 100])
            ->assertForbidden();

        $this->actingAs($this->developer)
            ->get(route('feature-control.index'))
            ->assertOk()
            ->assertSee('System Turn-Over &amp; Feature Release Control', false)
            ->assertSee('apiloton1@snsu.edu.ph');

        $this->actingAs($this->developer)
            ->put(route('feature-control.update'), [
                'feature_lock_display_mode' => 'hide',
                'features' => [
                    'feature_dashboard' => '1',
                    'feature_patients' => '1',
                    'feature_logbook' => '1',
                    'feature_dental_consultations' => '1',
                ],
            ])
            ->assertRedirect(route('feature-control.index'));

        $this->actingAs($this->clientAdmin)->get(route('dental-consultations.index'))->assertOk();
        $this->actingAs($this->clientAdmin)->get(route('inventory.dispensings.create'))->assertForbidden();

        $this->actingAs($this->developer)
            ->post(route('feature-control.preset'), ['milestone' => 100])
            ->assertRedirect(route('feature-control.index'));

        $this->assertSame(100, app(FeatureControlService::class)->progressSummary()['percentage']);
        $this->actingAs($this->clientAdmin)->get(route('inventory.dispensings.create'))->assertOk();
    }

    public function test_master_developer_account_cannot_be_modified_or_deleted_by_other_admins(): void
    {

        $this->actingAs($this->developer)
            ->post(route('feature-control.preset'), ['milestone' => 100]);

        $this->actingAs($this->clientAdmin)
            ->get(route('users.edit', $this->developer))
            ->assertForbidden();

        $this->actingAs($this->clientAdmin)
            ->delete(route('users.destroy', $this->developer))
            ->assertSessionHas('error', 'The master developer account cannot be deleted.');

        $this->assertDatabaseHas('users', [
            'id' => $this->developer->id,
            'email' => 'apiloton1@snsu.edu.ph',
            'is_active' => true,
        ]);
    }

    public function test_clinic_phase_artisan_command_unlocks_next_phases_and_individual_modules(): void
    {
        $service = app(FeatureControlService::class);
        $this->assertSame(25, $service->progressSummary()['percentage']);

        $this->artisan('clinic:phase next')
            ->assertExitCode(0);
        $this->assertSame(50, $service->progressSummary()['percentage']);

        $this->artisan('clinic:phase next')
            ->assertExitCode(0);
        $this->assertSame(75, $service->progressSummary()['percentage']);

        $this->artisan('clinic:phase next')
            ->assertExitCode(0);
        $this->assertSame(100, $service->progressSummary()['percentage']);

        $this->artisan('clinic:phase 25')
            ->assertExitCode(0);
        $this->assertSame(25, $service->progressSummary()['percentage']);

        $this->artisan('clinic:phase unlock dental_consultations --display=hide')
            ->assertExitCode(0);

        $this->assertTrue($service->isEnabled('feature_dental_consultations'));
    }
}
