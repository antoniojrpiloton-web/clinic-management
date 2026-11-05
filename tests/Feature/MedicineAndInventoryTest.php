<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_view_medicines_index(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('medicines.index'));
        $response->assertOk();
        $response->assertSee('Medicine Masterlist &amp; Inventory', false);
    }

    public function test_medicines_datatables_endpoint_returns_json(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->getJson(route('medicines.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
        $this->assertStringContainsString('Paracetamol', $response->getContent());
    }

    public function test_super_admin_can_view_create_medicine_page(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('medicines.create'));
        $response->assertOk();
        $response->assertSee('Add New Medicine');
    }

    public function test_super_admin_can_create_new_medicine(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $category = MedicineCategory::first();

        $response = $this->actingAs($admin)->post(route('medicines.store'), [
            'code' => 'MED-TEST-999',
            'category_id' => $category->id,
            'generic_name' => 'Diphenhydramine HCl',
            'brand_name' => 'Benadryl',
            'dosage_form' => 'Capsule',
            'strength' => '50 mg',
            'unit' => 'Capsule',
            'reorder_level' => 30,
            'description' => 'Antihistamine for allergic reactions',
            'is_prescription_required' => false,
            'is_active' => true,
        ]);

        $medicine = Medicine::where('code', 'MED-TEST-999')->first();
        $this->assertNotNull($medicine);
        $response->assertRedirect(route('medicines.show', $medicine));
    }

    public function test_super_admin_can_view_medicine_categories(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('medicine-categories.index'));
        $response->assertOk();
        $response->assertSee('Medicine Categories');
    }

    public function test_super_admin_can_view_stock_in_form_and_receive_delivery(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $medicine = Medicine::first();

        $formResponse = $this->actingAs($admin)->get(route('inventory.stock-in', ['medicine_id' => $medicine->id]));
        $formResponse->assertOk();
        $formResponse->assertSee('Receive Medicine Stock');

        $initialStock = $medicine->stock_on_hand;

        $postResponse = $this->actingAs($admin)->post(route('inventory.stock-in.process'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'LOT-TEST-12345',
            'supplier' => 'Metro Drug Corp',
            'expiration_date' => now()->addMonths(18)->format('Y-m-d'),
            'quantity_received' => 200,
            'unit_cost' => 5.50,
            'received_date' => now()->format('Y-m-d'),
            'reference_number' => 'DR-998877',
            'remarks' => 'Direct clinic inventory delivery',
        ]);

        $postResponse->assertRedirect(route('medicines.show', $medicine));
        $medicine->refresh();
        $this->assertEquals($initialStock + 200, $medicine->stock_on_hand);
    }

    public function test_super_admin_can_view_stock_out_form_and_deduct_inventory(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $batch = MedicineBatch::where('quantity_remaining', '>=', 10)->first();
        $medicine = $batch->medicine;
        $initialBatchQty = $batch->quantity_remaining;

        $formResponse = $this->actingAs($admin)->get(route('inventory.stock-out', ['medicine_id' => $medicine->id, 'batch_id' => $batch->id]));
        $formResponse->assertOk();
        $formResponse->assertSee('Deduct Medicine Stock');

        $postResponse = $this->actingAs($admin)->post(route('inventory.stock-out.process'), [
            'medicine_id' => $medicine->id,
            'batch_id' => $batch->id,
            'quantity' => 5,
            'reason_type' => 'damage',
            'reason' => 'Broken package during handling',
            'reference_number' => 'IR-001',
        ]);

        $postResponse->assertRedirect(route('medicines.show', $medicine));
        $batch->refresh();
        $this->assertEquals($initialBatchQty - 5, $batch->quantity_remaining);
    }

    public function test_super_admin_can_view_dispensing_log_and_dispense_medicine(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $patient = Patient::first();
        $batch = MedicineBatch::where('quantity_remaining', '>=', 10)->first();
        $medicine = $batch->medicine;
        $initialQty = $batch->quantity_remaining;

        $indexResponse = $this->actingAs($admin)->get(route('inventory.dispensings.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Medicine Dispensing Log');

        $createResponse = $this->actingAs($admin)->get(route('inventory.dispensings.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Issue Medicine Dispensing');

        $storeResponse = $this->actingAs($admin)->post(route('inventory.dispensings.store'), [
            'patient_id' => $patient->id,
            'dispensing_number' => 'DISP-TEST-9999',
            'dispensed_date' => now()->format('Y-m-d'),
            'diagnosis' => 'Tension Headache',
            'instructions' => 'Drink plenty of water',
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'quantity' => 4,
                    'dosage_instruction' => '1 tablet every 6 hours PRN',
                ],
            ],
        ]);

        $storeResponse->assertRedirect();
        $batch->refresh();
        $this->assertEquals($initialQty - 4, $batch->quantity_remaining);
    }

    public function test_super_admin_can_view_expirations_and_dispose_expired_batch(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $medicine = Medicine::first();

        $expiredBatch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'EXP-BATCH-999',
            'expiration_date' => now()->subDays(5)->format('Y-m-d'),
            'quantity_received' => 20,
            'quantity_remaining' => 20,
            'received_date' => now()->subMonths(6)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)->get(route('inventory.expirations'));
        $response->assertOk();
        $response->assertSee('Expiration Monitoring &amp; Disposal', false);

        $disposeResponse = $this->actingAs($admin)->post(route('inventory.batches.dispose', $expiredBatch));
        $disposeResponse->assertRedirect(route('inventory.expirations'));

        $expiredBatch->refresh();
        $this->assertEquals(0, $expiredBatch->quantity_remaining);
    }

    public function test_super_admin_can_view_movement_ledger_and_reports(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $ledgerResponse = $this->actingAs($admin)->get(route('inventory.transactions'));
        $ledgerResponse->assertOk();
        $ledgerResponse->assertSee('Inventory Transaction Movement Ledger');

        $reportResponse = $this->actingAs($admin)->get(route('inventory.reports'));
        $reportResponse->assertOk();
        $reportResponse->assertSee('Pharmacy &amp; Inventory Reports', false);
    }
}
