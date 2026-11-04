<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_login_screen_can_be_rendered_without_cdn_references(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('assets/plugins/bootstrap/css/bootstrap.min.css', false);
        $response->assertDontSee('https://cdn.', false);
        $response->assertDontSee('fonts.googleapis.com', false);
    }

    public function test_active_users_can_authenticate_and_reach_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Doctor');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'event' => 'login']);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_users_are_logged_out_on_next_request(): void
    {
        $user = User::factory()->inactive()->create();
        $user->assignRole('Doctor');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
