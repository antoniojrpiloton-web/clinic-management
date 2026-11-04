<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_users_without_permission_cannot_access_user_management(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('Doctor');

        $this->actingAs($doctor)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_administrators_can_create_edit_and_list_users_via_datatables(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Dr. Jane Doe',
                'email' => 'jane@clinic.test',
                'phone' => '09171234567',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'is_active' => true,
                'roles' => ['Doctor'],
            ])
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'jane@clinic.test')->firstOrFail();
        $this->assertTrue($created->hasRole('Doctor'));

        $this->actingAs($admin)
            ->getJson(route('users.index', ['draw' => 1, 'start' => 0, 'length' => 10]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['email' => 'jane@clinic.test']);
    }

    public function test_users_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertModelExists($admin);
    }
}
