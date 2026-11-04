<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicineAndInventorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $categories = [
            ['name' => 'Analgesics & Antipyretics', 'code' => 'ANAL', 'description' => 'Pain relievers and fever reducers'],
            ['name' => 'Antibiotics & Antimicrobials', 'code' => 'ANTI', 'description' => 'Prescription antibacterial agents'],
            ['name' => 'Antihistamines & Anti-Allergy', 'code' => 'HIST', 'description' => 'Allergy, rhinitis, and urticaria relief'],
            ['name' => 'Respiratory & Cough / Cold', 'code' => 'RESP', 'description' => 'Bronchodilators, mucolytics, and decongestants'],
            ['name' => 'Gastrointestinal Preparations', 'code' => 'GI', 'description' => 'Antacids, anti-diarrheal, and digestive aids'],
            ['name' => 'Vitamins & Nutritional Supplements', 'code' => 'VIT', 'description' => 'Immune boosters and daily multivitamins'],
            ['name' => 'First Aid & Antiseptics', 'code' => 'AID', 'description' => 'Wound care solutions and emergency clinic supplies'],
            ['name' => 'Cardiovascular & Emergency', 'code' => 'CARD', 'description' => 'Blood pressure and vital emergency medications'],
        ];

        $catMap = [];
        foreach ($categories as $cat) {
            $catMap[$cat['code']] = MedicineCategory::firstOrCreate(
                ['code' => $cat['code']],
                $cat
            );
        }

        $medicinesData = [
            [
                'code' => 'MED-0001',
                'category_id' => $catMap['ANAL']->id,
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Biogesic',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'tablet',
                'reorder_level' => 100,
                'description' => 'First-line relief for mild to moderate pain and headache/fever.',
                'side_effects' => 'Rare in normal dosage; potential hepatotoxicity in extreme overdose.',
                'storage_conditions' => 'Store at temperatures not exceeding 30°C.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-PCM-2026-A1',
                    'supplier' => 'Unilab Pharmaceuticals',
                    'expiration_date' => now()->addMonths(20)->format('Y-m-d'),
                    'quantity_received' => 500,
                    'quantity_remaining' => 450,
                    'unit_cost' => 3.50,
                    'received_date' => now()->subDays(20)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0002',
                'category_id' => $catMap['ANAL']->id,
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Medicol Advance',
                'dosage_form' => 'Capsule',
                'strength' => '200mg',
                'unit' => 'capsule',
                'reorder_level' => 60,
                'description' => 'NSAID for inflammatory headache, toothache, and dysmenorrhea.',
                'side_effects' => 'Gastric irritation, take with meals.',
                'storage_conditions' => 'Store below 25°C in a dry place.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-IBU-2026-03',
                    'supplier' => 'Zuellig Pharma',
                    'expiration_date' => now()->addMonths(16)->format('Y-m-d'),
                    'quantity_received' => 200,
                    'quantity_remaining' => 175,
                    'unit_cost' => 6.25,
                    'received_date' => now()->subDays(15)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0003',
                'category_id' => $catMap['ANAL']->id,
                'generic_name' => 'Mefenamic Acid',
                'brand_name' => 'Dolfenal',
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'unit' => 'capsule',
                'reorder_level' => 50,
                'description' => 'For acute post-traumatic pain and severe dysmenorrhea.',
                'side_effects' => 'GI discomfort, nausea.',
                'storage_conditions' => 'Store at room temperature.',
                'is_prescription_required' => true,
                'batch' => [
                    'batch_number' => 'LOT-MEF-2025-08',
                    'supplier' => 'Metro Drug Distribution',
                    'expiration_date' => now()->addDays(40)->format('Y-m-d'),
                    'quantity_received' => 100,
                    'quantity_remaining' => 25,
                    'unit_cost' => 12.00,
                    'received_date' => now()->subMonths(6)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0004',
                'category_id' => $catMap['HIST']->id,
                'generic_name' => 'Cetirizine Dihydrochloride',
                'brand_name' => 'Alnix',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'unit' => 'tablet',
                'reorder_level' => 50,
                'description' => 'Second generation antihistamine for allergic rhinitis and skin rashes.',
                'side_effects' => 'Mild drowsiness in sensitive patients.',
                'storage_conditions' => 'Protect from moisture and heat.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-CET-2026-X1',
                    'supplier' => 'Zuellig Pharma',
                    'expiration_date' => now()->addMonths(24)->format('Y-m-d'),
                    'quantity_received' => 300,
                    'quantity_remaining' => 280,
                    'unit_cost' => 8.50,
                    'received_date' => now()->subDays(10)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0005',
                'category_id' => $catMap['RESP']->id,
                'generic_name' => 'Salbutamol Sulfate',
                'brand_name' => 'Ventolin Nebules',
                'dosage_form' => 'Nebule',
                'strength' => '2.5mg/2.5ml',
                'unit' => 'nebule',
                'reorder_level' => 30,
                'description' => 'Short-acting beta2-agonist for acute bronchospasm and asthma relief.',
                'side_effects' => 'Fine tremor, palpitations, headache.',
                'storage_conditions' => 'Store below 30°C. Protect from light.',
                'is_prescription_required' => true,
                'batch' => [
                    'batch_number' => 'LOT-SAL-2026-09',
                    'supplier' => 'GlaxoSmithKline Philippines',
                    'expiration_date' => now()->addMonths(18)->format('Y-m-d'),
                    'quantity_received' => 80,
                    'quantity_remaining' => 65,
                    'unit_cost' => 28.00,
                    'received_date' => now()->subDays(25)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0006',
                'category_id' => $catMap['GI']->id,
                'generic_name' => 'Antacid (AlOH + MgOH + Simethicone)',
                'brand_name' => 'Kremil-S',
                'dosage_form' => 'Chewable Tablet',
                'strength' => 'Standard',
                'unit' => 'tablet',
                'reorder_level' => 60,
                'description' => 'Fast acting antacid for hyperacidity, gas, and heartburn.',
                'side_effects' => 'Chalky taste.',
                'storage_conditions' => 'Keep container tightly closed.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-KRM-2026-11',
                    'supplier' => 'Unilab Pharmaceuticals',
                    'expiration_date' => now()->addMonths(14)->format('Y-m-d'),
                    'quantity_received' => 250,
                    'quantity_remaining' => 210,
                    'unit_cost' => 7.00,
                    'received_date' => now()->subDays(18)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0007',
                'category_id' => $catMap['GI']->id,
                'generic_name' => 'Oral Rehydration Salts',
                'brand_name' => 'Hydrite',
                'dosage_form' => 'Sachet',
                'strength' => 'Formula 75',
                'unit' => 'sachet',
                'reorder_level' => 40,
                'description' => 'Restores fluid and electrolytes lost during diarrhea or dehydration.',
                'side_effects' => 'None when properly reconstituted with drinking water.',
                'storage_conditions' => 'Store in a cool dry area.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-ORS-2026-01',
                    'supplier' => 'DOH Provincial Supply Depot',
                    'expiration_date' => now()->addMonths(22)->format('Y-m-d'),
                    'quantity_received' => 120,
                    'quantity_remaining' => 95,
                    'unit_cost' => 5.00,
                    'received_date' => now()->subDays(30)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0008',
                'category_id' => $catMap['VIT']->id,
                'generic_name' => 'Ascorbic Acid + Zinc',
                'brand_name' => 'ImmunPro',
                'dosage_form' => 'Tablet',
                'strength' => '500mg/10mg',
                'unit' => 'tablet',
                'reorder_level' => 100,
                'description' => 'Immune defense supplement for staff and student athletes.',
                'side_effects' => 'Minimal.',
                'storage_conditions' => 'Store in cool dry conditions.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-VIT-2026-55',
                    'supplier' => 'Unilab Pharmaceuticals',
                    'expiration_date' => now()->addMonths(19)->format('Y-m-d'),
                    'quantity_received' => 400,
                    'quantity_remaining' => 360,
                    'unit_cost' => 9.20,
                    'received_date' => now()->subDays(14)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0009',
                'category_id' => $catMap['AID']->id,
                'generic_name' => 'Povidone Iodine Solution',
                'brand_name' => 'Betadine 10%',
                'dosage_form' => 'Bottle',
                'strength' => '10% w/v 60ml',
                'unit' => 'bottle',
                'reorder_level' => 15,
                'description' => 'Broad spectrum antiseptic for wounds, cuts, and scrapes.',
                'side_effects' => 'Local skin irritation in iodine-sensitive patients.',
                'storage_conditions' => 'Store below 30°C.',
                'is_prescription_required' => false,
                'batch' => [
                    'batch_number' => 'LOT-BET-2026-02',
                    'supplier' => 'Mundipharma Philippines',
                    'expiration_date' => now()->addMonths(26)->format('Y-m-d'),
                    'quantity_received' => 30,
                    'quantity_remaining' => 24,
                    'unit_cost' => 125.00,
                    'received_date' => now()->subDays(22)->format('Y-m-d'),
                ],
            ],
            [
                'code' => 'MED-0010',
                'category_id' => $catMap['CARD']->id,
                'generic_name' => 'Amlodipine Besilate',
                'brand_name' => 'Norvasc',
                'dosage_form' => 'Tablet',
                'strength' => '5mg',
                'unit' => 'tablet',
                'reorder_level' => 40,
                'description' => 'Calcium channel blocker for faculty and staff hypertension maintenance.',
                'side_effects' => 'Peripheral edema, flushing, dizziness.',
                'storage_conditions' => 'Store at 15-30°C.',
                'is_prescription_required' => true,
                'batch' => [
                    'batch_number' => 'LOT-AML-2024-99',
                    'supplier' => 'Pfizer Philippines',
                    'expiration_date' => now()->subDays(10)->format('Y-m-d'),
                    'quantity_received' => 50,
                    'quantity_remaining' => 10,
                    'unit_cost' => 18.00,
                    'received_date' => now()->subMonths(18)->format('Y-m-d'),
                ],
            ],
        ];

        foreach ($medicinesData as $item) {
            $batchData = $item['batch'];
            unset($item['batch']);

            $medicine = Medicine::firstOrCreate(
                ['code' => $item['code']],
                $item
            );

            $batch = MedicineBatch::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => $batchData['batch_number'],
                ],
                $batchData
            );

            InventoryTransaction::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'type' => 'stock_in',
                    'reference_number' => 'PO-INIT-'.$medicine->code,
                ],
                [
                    'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                    'quantity' => $batch->quantity_received,
                    'quantity_before' => 0,
                    'quantity_after' => $batch->quantity_received,
                    'reason' => 'Initial inventory delivery / stock receipt',
                    'user_id' => $admin?->id,
                    'transaction_date' => now()->subDays(15),
                ]
            );
        }
    }
}

