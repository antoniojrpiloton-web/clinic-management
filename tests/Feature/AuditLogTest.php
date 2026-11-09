<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_unauthorized_users_cannot_access_audit_logs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_audit_logs_index_page(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($superAdmin)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit Logs')
            ->assertSee('audit-logs-table');
    }

    public function test_super_admin_can_query_audit_logs_datatables_endpoint_with_html_columns(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::SUPER_ADMIN_ROLE);

        $log = AuditLog::factory()->create([
            'user_id' => $superAdmin->id,
            'event' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($superAdmin)
            ->getJson(route('audit-logs.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data' => [
                    '*' => [
                        'timestamp_col',
                        'user_col',
                        'event_col',
                        'description_col',
                        'subject_col',
                        'ip_address_col',
                        'actions_col',
                    ],
                ],
            ]);

        $firstRow = $response->json('data.0');
        $this->assertIsString($firstRow['event_col']);
        $this->assertStringContainsString('badge', $firstRow['event_col']);
        $this->assertStringContainsString('Login', $firstRow['event_col']);
        $this->assertStringNotContainsString('[object Object]', $firstRow['event_col']);

        $this->assertIsString($firstRow['actions_col']);
        $this->assertStringContainsString('data-view-url', $firstRow['actions_col']);
        $this->assertStringNotContainsString('[object Object]', $firstRow['actions_col']);
    }

    public function test_super_admin_can_view_audit_log_details_modal_via_ajax(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::SUPER_ADMIN_ROLE);

        $log = AuditLog::factory()->create([
            'user_id' => $superAdmin->id,
            'event' => 'updated',
            'description' => 'Updated system settings',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('audit-logs.show', $log), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('Updated system settings');
    }
}
