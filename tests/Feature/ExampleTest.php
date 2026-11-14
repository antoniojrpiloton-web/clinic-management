<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_all_milestone_stub_pages_render_ongoing_development_card(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $routes = [
            route('inventory.dispensings.index'),
            route('inventory.dispensings.create'),
            route('physical-examinations.index'),
            route('physical-examinations.create'),
            route('physical-exam-schedules.index'),
            route('medicines.index'),
            route('inventory.stock-in'),
            route('inventory.stock-out'),
            route('inventory.expirations'),
            route('inventory.transactions'),
            route('reports.clinic.index'),
            route('reports.clinic.medical-dental'),
            route('reports.clinic.login-records'),
            route('reports.clinic.communicable'),
            route('reports.clinic.non-communicable'),
            route('backups.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertOk();
            $response->assertSee('In Development');
            $response->assertSee('Back to Dashboard');
        }
    }
}
