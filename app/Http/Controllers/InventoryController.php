<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view', only: ['transactions', 'expirations', 'reports']),
            new Middleware('permission:inventory.stock-in', only: ['stockInForm', 'stockInProcess']),
            new Middleware('permission:inventory.stock-out', only: ['stockOutForm', 'stockOutProcess', 'disposeBatch']),
            new Middleware('permission:inventory.reports', only: ['reports']),
        ];
    }

    public function stockInForm(Request $request): View
    {
        $medicineId = $request->query('medicine_id');
        $preselectedMedicine = $medicineId ? Medicine::find($medicineId) : null;
        $medicines = Medicine::active()->orderBy('generic_name')->get();

        return view('inventory.stock-in', compact('medicines', 'preselectedMedicine'));
    }

    public function stockInProcess(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'batch_number' => ['required', 'string', 'max:80'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'expiration_date' => ['required', 'date', 'after:today'],
            'quantity_received' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'received_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:80'],
            'remarks' => ['nullable', 'string'],
        ]);

        $medicine = Medicine::findOrFail($validated['medicine_id']);
        $quantity = (int) $validated['quantity_received'];

        DB::transaction(function () use ($medicine, $validated, $quantity) {
            $batch = MedicineBatch::where('medicine_id', $medicine->id)
                ->where('batch_number', $validated['batch_number'])
                ->first();

            $beforeStock = $medicine->stock_on_hand;

            if ($batch) {
                $batch->quantity_received += $quantity;
                $batch->quantity_remaining += $quantity;
                $batch->expiration_date = $validated['expiration_date'];
                if (! empty($validated['unit_cost'])) {
                    $batch->unit_cost = $validated['unit_cost'];
                }
                $batch->save();
            } else {
                $batch = MedicineBatch::create([
                    'medicine_id' => $medicine->id,
                    'batch_number' => $validated['batch_number'],
                    'supplier' => $validated['supplier'] ?? null,
                    'expiration_date' => $validated['expiration_date'],
                    'quantity_received' => $quantity,
                    'quantity_remaining' => $quantity,
                    'unit_cost' => $validated['unit_cost'] ?? null,
                    'received_date' => $validated['received_date'],
                    'remarks' => $validated['remarks'] ?? null,
                ]);
            }

            $afterStock = $beforeStock + $quantity;

            InventoryTransaction::create([
                'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                'medicine_id' => $medicine->id,
                'batch_id' => $batch->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'quantity_before' => $beforeStock,
                'quantity_after' => $afterStock,
                'reference_number' => $validated['reference_number'] ?? null,
                'reason' => $validated['remarks'] ?: "Stock receipt delivery from {$validated['supplier']}",
                'user_id' => auth()->id(),
                'transaction_date' => now(),
            ]);

            $this->auditLogger->log('stock_in', "Received {$quantity} {$medicine->unit}s for {$medicine->display_name} (Batch: {$batch->batch_number})", $batch);
        });

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock-In recorded: {$quantity} {$medicine->unit}s received for {$medicine->display_name}.");
    }

    public function stockOutForm(Request $request): View
    {
        $medicineId = $request->query('medicine_id');
        $preselectedMedicine = $medicineId ? Medicine::with('batches')->find($medicineId) : null;
        $medicines = Medicine::active()->with('activeBatches')->orderBy('generic_name')->get();

        return view('inventory.stock-out', compact('medicines', 'preselectedMedicine'));
    }

    public function stockOutProcess(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'batch_id' => ['required', 'exists:medicine_batches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason_type' => ['required', 'in:damage,disposal,adjustment,return,other'],
            'reason' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:80'],
        ]);

        $batch = MedicineBatch::findOrFail($validated['batch_id']);
        $medicine = $batch->medicine;
        $quantity = (int) $validated['quantity'];

        if ($batch->quantity_remaining < $quantity) {
            return back()->withInput()->withErrors([
                'quantity' => "Cannot deduct {$quantity} {$medicine->unit}s. Only {$batch->quantity_remaining} {$medicine->unit}s remaining in batch {$batch->batch_number}.",
            ]);
        }

        DB::transaction(function () use ($medicine, $batch, $quantity, $validated) {
            $beforeStock = $medicine->stock_on_hand;

            $batch->quantity_remaining -= $quantity;
            $batch->save();

            $afterStock = $beforeStock - $quantity;

            InventoryTransaction::create([
                'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                'medicine_id' => $medicine->id,
                'batch_id' => $batch->id,
                'type' => $validated['reason_type'] === 'disposal' ? 'disposal' : 'stock_out',
                'quantity' => -$quantity,
                'quantity_before' => $beforeStock,
                'quantity_after' => $afterStock,
                'reference_number' => $validated['reference_number'] ?? null,
                'reason' => $validated['reason'],
                'user_id' => auth()->id(),
                'transaction_date' => now(),
            ]);

            $this->auditLogger->log('stock_out', "Deducted {$quantity} {$medicine->unit}s from {$medicine->display_name} ({$validated['reason']})", $batch);
        });

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock-Out recorded: {$quantity} {$medicine->unit}s deducted from batch {$batch->batch_number}.");
    }

    public function expirations(Request $request): View
    {
        $filter = $request->query('status', 'all_risk');

        $query = MedicineBatch::query()
            ->with('medicine.category')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiration_date');

        if ($filter === 'expired') {
            $query->where('expiration_date', '<', now()->startOfDay());
        } elseif ($filter === 'critical') {
            $query->where('expiration_date', '>=', now()->startOfDay())
                ->where('expiration_date', '<=', now()->addDays(30)->endOfDay());
        } elseif ($filter === 'warning') {
            $query->where('expiration_date', '>', now()->addDays(30)->endOfDay())
                ->where('expiration_date', '<=', now()->addDays(90)->endOfDay());
        } elseif ($filter === 'all_risk') {
            $query->where('expiration_date', '<=', now()->addDays(90)->endOfDay());
        }

        $batches = $query->paginate(20)->withQueryString();

        $expiredCount = MedicineBatch::where('quantity_remaining', '>', 0)->where('expiration_date', '<', now()->startOfDay())->count();
        $criticalCount = MedicineBatch::where('quantity_remaining', '>', 0)->where('expiration_date', '>=', now()->startOfDay())->where('expiration_date', '<=', now()->addDays(30)->endOfDay())->count();
        $warningCount = MedicineBatch::where('quantity_remaining', '>', 0)->where('expiration_date', '>', now()->addDays(30)->endOfDay())->where('expiration_date', '<=', now()->addDays(90)->endOfDay())->count();
        $goodCount = MedicineBatch::where('quantity_remaining', '>', 0)->where('expiration_date', '>', now()->addDays(90)->endOfDay())->count();

        return view('inventory.expirations', compact('batches', 'filter', 'expiredCount', 'criticalCount', 'warningCount', 'goodCount'));
    }

    public function disposeBatch(Request $request, MedicineBatch $batch): RedirectResponse
    {
        if ($batch->quantity_remaining <= 0) {
            return back()->with('info', "Batch {$batch->batch_number} has no remaining stock to dispose.");
        }

        $qtyToDispose = $batch->quantity_remaining;
        $medicine = $batch->medicine;

        DB::transaction(function () use ($medicine, $batch, $qtyToDispose) {
            $beforeStock = $medicine->stock_on_hand;

            $batch->quantity_remaining = 0;
            $batch->save();

            $afterStock = $beforeStock - $qtyToDispose;

            InventoryTransaction::create([
                'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                'medicine_id' => $medicine->id,
                'batch_id' => $batch->id,
                'type' => 'disposal',
                'quantity' => -$qtyToDispose,
                'quantity_before' => $beforeStock,
                'quantity_after' => $afterStock,
                'reason' => "Expired medicine write-off and safe medical disposal",
                'user_id' => auth()->id(),
                'transaction_date' => now(),
            ]);

            $this->auditLogger->log('disposal', "Disposed {$qtyToDispose} {$medicine->unit}s from expired batch {$batch->batch_number}", $batch);
        });

        return redirect()->route('inventory.expirations')
            ->with('success', "Disposed {$qtyToDispose} {$medicine->unit}s from batch {$batch->batch_number} for {$medicine->display_name}.");
    }

    public function transactions(Request $request): View
    {
        $type = $request->query('type');
        $medicineId = $request->query('medicine_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = InventoryTransaction::query()
            ->with(['medicine', 'batch', 'patient', 'user', 'clinician'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($medicineId, fn ($q) => $q->where('medicine_id', $medicineId))
            ->when($dateFrom, fn ($q) => $q->whereDate('transaction_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('transaction_date', '<=', $dateTo))
            ->latest('transaction_date');

        $transactions = $query->paginate(20)->withQueryString();
        $medicines = Medicine::orderBy('generic_name')->get();

        return view('inventory.transactions', compact('transactions', 'medicines', 'type', 'medicineId', 'dateFrom', 'dateTo'));
    }

    public function reports(Request $request): View
    {
        $categories = MedicineCategory::withCount('medicines')->get();

        $allMedicines = Medicine::with(['category', 'batches'])->get();
        $totalItems = $allMedicines->count();
        $totalStockUnits = 0;
        $totalInventoryValuation = 0.00;

        foreach ($allMedicines as $med) {
            foreach ($med->batches as $b) {
                if ($b->quantity_remaining > 0 && ! $b->is_expired) {
                    $totalStockUnits += $b->quantity_remaining;
                    $unitCost = (float) ($b->unit_cost ?? 0);
                    $totalInventoryValuation += ($b->quantity_remaining * $unitCost);
                }
            }
        }

        $lowStockList = $allMedicines->filter(fn (Medicine $m) => $m->stock_on_hand <= $m->reorder_level);

        $expiredBatches = MedicineBatch::expired()->where('quantity_remaining', '>', 0)->with('medicine')->get();
        $expiringSoonBatches = MedicineBatch::expiringSoon(60)->where('quantity_remaining', '>', 0)->with('medicine')->get();

        $recentTransactions = InventoryTransaction::with(['medicine', 'user', 'patient'])
            ->latest('transaction_date')
            ->take(10)
            ->get();

        return view('inventory.reports', compact(
            'totalItems',
            'totalStockUnits',
            'totalInventoryValuation',
            'lowStockList',
            'expiredBatches',
            'expiringSoonBatches',
            'recentTransactions',
            'categories'
        ));
    }
}

