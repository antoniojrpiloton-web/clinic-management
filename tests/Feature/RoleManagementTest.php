<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_can_create_and_update_roles_and_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'Pharmacist',
                'permissions' => ['dashboard.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::findByName('Pharmacist');
        $this->assertTrue($role->hasPermissionTo('dashboard.view'));

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'clinic_name' => 'City Care Clinic',
                'clinic_email' => 'info@citycare.test',
                'clinic_phone' => '555-0100',
                'clinic_address' => '123 Main St',
                'date_format' => 'Y-m-d',
                'table_page_length' => 25,
                'backup_enabled' => true,
                'backup_retention_days' => 14,
                'audit_log_retention_days' => 180,
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertDatabaseHas('settings', ['key' => 'clinic_name', 'value' => 'City Care Clinic']);
    }

    public function test_super_admin_role_cannot_be_modified_or_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::SUPER_ADMIN_ROLE);
        $superRole = Role::findByName(User::SUPER_ADMIN_ROLE);

        $this->actingAs($admin)
            ->put(route('roles.update', $superRole), ['name' => 'Renamed'])
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $superRole))
            ->assertSessionHas('error');

        $this->assertModelExists($superRole);
    }

    public function test_all_authenticated_blade_pages_render_without_cdn_dependencies(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::SUPER_ADMIN_ROLE);

        $routes = [
            route('dashboard'),
            route('users.index'),
            route('users.create'),
            route('roles.index'),
            route('roles.create'),
            route('permissions.index'),
            route('permissions.create'),
            route('audit-logs.index'),
            route('backups.index'),
            route('settings.edit'),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertDontSee('https://cdn.', false)
                ->assertDontSee('fonts.googleapis.com', false);
        }
    }
}
