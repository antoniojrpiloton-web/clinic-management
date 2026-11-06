<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Services\AuditLogger;
use App\Support\IdObfuscator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

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
        $resolvedMedicineId = $medicineId ? IdObfuscator::decodeOrRaw($medicineId, Medicine::class) : null;
        $preselectedMedicine = $resolvedMedicineId ? Medicine::find($resolvedMedicineId) : null;
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
        $resolvedMedicineId = $medicineId ? IdObfuscator::decodeOrRaw($medicineId, Medicine::class) : null;
        $preselectedMedicine = $resolvedMedicineId ? Medicine::with('batches')->find($resolvedMedicineId) : null;
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

    public function expirations(Request $request): View|JsonResponse
    {
        $filter = $request->query('status', 'all_risk');
        $today = now()->startOfDay()->toDateString();
        $day30 = now()->addDays(30)->endOfDay()->toDateString();
        $day90 = now()->addDays(90)->endOfDay()->toDateString();

        if ($request->ajax()) {
            $canStockOut = $request->user()?->can('inventory.stock-out') ?? false;

            $query = MedicineBatch::query()
                ->with(['medicine.category'])
                ->where('quantity_remaining', '>', 0);

            if ($filter === 'expired') {
                $query->where('expiration_date', '<', $today);
            } elseif ($filter === 'critical') {
                $query->where('expiration_date', '>=', $today)
                    ->where('expiration_date', '<=', $day30);
            } elseif ($filter === 'warning') {
                $query->where('expiration_date', '>', $day30)
                    ->where('expiration_date', '<=', $day90);
            } elseif ($filter === 'all_risk') {
                $query->where('expiration_date', '<=', $day90);
            }

            return DataTables::eloquent($query)
                ->filter(function (Builder $q) use ($request): void {
                    $term = $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $term = trim($term);
                        $q->where(function (Builder $inner) use ($term): void {
                            $inner->where('batch_number', 'like', "%{$term}%")
                                ->orWhere('supplier', 'like', "%{$term}%")
                                ->orWhereHas('medicine', fn (Builder $m) => $m->search($term));
                        });
                    }
                })
                ->addColumn('medicine_col', function (MedicineBatch $batch): string {
                    $m = $batch->medicine;
                    if (! $m) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    $url = route('medicines.show', $m);
                    $name = e($m->generic_name);
                    $brand = $m->brand_name ? ' <span class="text-primary fw-medium">('.e($m->brand_name).')</span>' : '';
                    $sub = e($m->code.' • '.$m->dosage_form);

                    return "<div><a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$name}{$brand}</a></div><small class=\"text-muted\">{$sub}</small>";
                })
                ->addColumn('category_col', fn (MedicineBatch $batch): string => '<span class="badge badge-soft-blue">'.e($batch->medicine?->category?->name ?? 'N/A').'</span>')
                ->addColumn('batch_col', fn (MedicineBatch $batch): string => '<span class="font-monospace fw-semibold">'.e($batch->batch_number).'</span>')
                ->addColumn('expiration_col', fn (MedicineBatch $batch): string => '<span class="fw-medium">'.e($batch->expiration_date->format('M d, Y')).'</span>')
                ->addColumn('risk_col', function (MedicineBatch $batch): string {
                    if ($batch->is_expired) {
                        return '<span class="badge badge-soft-red"><i class="bi bi-exclamation-octagon me-1"></i>EXPIRED ('.abs($batch->days_until_expiration).'d ago)</span>';
                    }
                    if ($batch->days_until_expiration <= 30) {
                        return '<span class="badge badge-soft-red"><i class="bi bi-exclamation-triangle me-1"></i>'.$batch->days_until_expiration.' days left (Critical)</span>';
                    }
                    if ($batch->days_until_expiration <= 90) {
                        return '<span class="badge badge-soft-amber"><i class="bi bi-hourglass-split me-1"></i>'.$batch->days_until_expiration.' days left</span>';
                    }

                    return '<span class="badge badge-soft-green"><i class="bi bi-shield-check me-1"></i>Good ('.$batch->days_until_expiration.' days)</span>';
                })
                ->addColumn('remaining_col', function (MedicineBatch $batch): string {
                    $cls = $batch->is_expired ? 'text-danger' : 'text-dark';
                    $qty = number_format($batch->quantity_remaining);
                    $unit = e($batch->medicine?->unit ?? 'unit');

                    return "<span class=\"fw-bold {$cls}\">{$qty}</span> <small class=\"text-muted\">{$unit}s</small>";
                })
                ->addColumn('supplier_col', fn (MedicineBatch $batch): string => '<span class="text-muted small">'.e($batch->supplier ?? 'N/A').'</span>')
                ->addColumn('actions', function (MedicineBatch $batch) use ($canStockOut): string {
                    if (! $canStockOut) {
                        return '';
                    }
                    if ($batch->is_expired) {
                        $disposeUrl = route('inventory.batches.dispose', $batch);
                        $csrf = csrf_field();
                        $qty = (int) $batch->quantity_remaining;

                        return "<form method=\"POST\" action=\"{$disposeUrl}\" class=\"d-inline\" data-confirm=\"Confirm safe medical disposal and write-off of remaining {$qty} units?\">
                            {$csrf}
                            <button type=\"submit\" class=\"btn btn-sm btn-danger py-1 px-2\">
                                <i class=\"bi bi-trash3 me-1\"></i> Dispose Batch
                            </button>
                        </form>";
                    }

                    $stockOutUrl = route('inventory.stock-out', ['medicine_id' => $batch->medicine_id, 'batch_id' => $batch->id]);

                    return "<a href=\"{$stockOutUrl}\" class=\"btn btn-sm btn-light border py-1 px-2 text-danger\">
                        <i class=\"bi bi-box-arrow-up me-1\"></i> Deduct
                    </a>";
                })
                ->orderColumn('batch_col', 'batch_number $1')
                ->orderColumn('expiration_col', 'expiration_date $1')
                ->orderColumn('remaining_col', 'quantity_remaining $1')
                ->rawColumns(['medicine_col', 'category_col', 'batch_col', 'expiration_col', 'risk_col', 'remaining_col', 'supplier_col', 'actions'])
                ->make(true);
        }

        $agg = MedicineBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->selectRaw('SUM(CASE WHEN expiration_date < ? THEN 1 ELSE 0 END) as expired_count', [$today])
            ->selectRaw('SUM(CASE WHEN expiration_date >= ? AND expiration_date <= ? THEN 1 ELSE 0 END) as critical_count', [$today, $day30])
            ->selectRaw('SUM(CASE WHEN expiration_date > ? AND expiration_date <= ? THEN 1 ELSE 0 END) as warning_count', [$day30, $day90])
            ->selectRaw('SUM(CASE WHEN expiration_date > ? THEN 1 ELSE 0 END) as good_count', [$day90])
            ->first();

        $expiredCount = (int) ($agg?->expired_count ?? 0);
        $criticalCount = (int) ($agg?->critical_count ?? 0);
        $warningCount = (int) ($agg?->warning_count ?? 0);
        $goodCount = (int) ($agg?->good_count ?? 0);

        return view('inventory.expirations', compact('filter', 'expiredCount', 'criticalCount', 'warningCount', 'goodCount'));
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
                'reason' => 'Expired medicine write-off and safe medical disposal',
                'user_id' => auth()->id(),
                'transaction_date' => now(),
            ]);

            $this->auditLogger->log('disposal', "Disposed {$qtyToDispose} {$medicine->unit}s from expired batch {$batch->batch_number}", $batch);
        });

        return redirect()->route('inventory.expirations')
            ->with('success', "Disposed {$qtyToDispose} {$medicine->unit}s from batch {$batch->batch_number} for {$medicine->display_name}.");
    }

    public function transactions(Request $request): View|JsonResponse
    {
        $type = $request->query('type');
        $medicineId = $request->query('medicine_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if ($request->ajax()) {
            $query = InventoryTransaction::query()
                ->with([
                    'medicine:id,generic_name,brand_name,dosage_form',
                    'batch:id,batch_number',
                    'patient:id,first_name,middle_name,last_name,suffix,type,school_id_number',
                    'user:id,name',
                ])
                ->when($type, fn (Builder $q) => $q->where('type', $type))
                ->when($medicineId, fn (Builder $q) => $q->where('medicine_id', $medicineId))
                ->when($dateFrom, fn (Builder $q) => $q->whereDate('transaction_date', '>=', $dateFrom))
                ->when($dateTo, fn (Builder $q) => $q->whereDate('transaction_date', '<=', $dateTo))
                ->latest('transaction_date');

            return DataTables::eloquent($query)
                ->filter(function (Builder $q) use ($request): void {
                    $term = $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $term = trim($term);
                        $q->where(function (Builder $inner) use ($term): void {
                            $inner->where('transaction_number', 'like', "%{$term}%")
                                ->orWhere('reason', 'like', "%{$term}%")
                                ->orWhere('reference_number', 'like', "%{$term}%")
                                ->orWhereHas('medicine', fn (Builder $m) => $m->search($term));
                        });
                    }
                })
                ->addColumn('date_col', fn (InventoryTransaction $txn): string => '<span class="small text-muted text-nowrap">'.($txn->transaction_date ? e($txn->transaction_date->format('M d, Y h:i A')) : 'N/A').'</span>')
                ->addColumn('txn_col', fn (InventoryTransaction $txn): string => '<span class="font-monospace small fw-medium">'.e($txn->transaction_number).'</span>')
                ->addColumn('type_col', function (InventoryTransaction $txn): string {
                    if ($txn->type === 'stock_in') {
                        return '<span class="badge badge-soft-green"><i class="bi bi-arrow-down-left me-1"></i>Stock In</span>';
                    }
                    if ($txn->type === 'dispensed') {
                        return '<span class="badge badge-soft-blue"><i class="bi bi-person-fill-check me-1"></i>Dispensed</span>';
                    }
                    if ($txn->type === 'disposal') {
                        return '<span class="badge badge-soft-red"><i class="bi bi-trash3 me-1"></i>Disposal</span>';
                    }

                    return '<span class="badge badge-soft-amber"><i class="bi bi-arrow-up-right me-1"></i>Stock Out</span>';
                })
                ->addColumn('medicine_col', function (InventoryTransaction $txn): string {
                    if (! $txn->medicine) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    $url = route('medicines.show', $txn->medicine);
                    $name = e($txn->medicine->generic_name);
                    $form = e($txn->medicine->dosage_form);

                    return "<a href=\"{$url}\" class=\"fw-semibold text-dark text-decoration-none\">{$name}</a><small class=\"text-muted d-block\">{$form}</small>";
                })
                ->addColumn('batch_col', fn (InventoryTransaction $txn): string => '<span class="font-monospace small">'.e($txn->batch?->batch_number ?? 'N/A').'</span>')
                ->addColumn('qty_col', function (InventoryTransaction $txn): string {
                    $cls = $txn->quantity > 0 ? 'text-success' : 'text-danger';
                    $val = $txn->quantity > 0 ? '+'.$txn->quantity : (string) $txn->quantity;

                    return "<span class=\"fw-bold {$cls}\">{$val}</span>";
                })
                ->addColumn('balance_col', fn (InventoryTransaction $txn): string => '<span class="fw-semibold text-dark">'.number_format($txn->quantity_after).'</span>')
                ->addColumn('reason_col', function (InventoryTransaction $txn): string {
                    if ($txn->patient) {
                        $url = route('patients.show', $txn->patient);
                        $name = e($txn->patient->full_name);
                        $id = e($txn->patient->primary_identifier);

                        return "<div><i class=\"bi bi-person me-1 text-primary\"></i><a href=\"{$url}\" class=\"text-decoration-none fw-medium text-dark\">{$name}</a></div><span class=\"text-muted small\">{$id}</span>";
                    }
                    $reason = e($txn->reason ?: 'N/A');

                    return "<span class=\"text-muted text-truncate d-block\" style=\"max-width: 260px;\" title=\"{$reason}\">{$reason}</span>";
                })
                ->addColumn('user_col', fn (InventoryTransaction $txn): string => '<span class="small text-muted">'.e($txn->user?->name ?? 'System').'</span>')
                ->orderColumn('date_col', 'transaction_date $1')
                ->orderColumn('txn_col', 'transaction_number $1')
                ->orderColumn('qty_col', 'quantity $1')
                ->orderColumn('balance_col', 'quantity_after $1')
                ->rawColumns(['date_col', 'txn_col', 'type_col', 'medicine_col', 'batch_col', 'qty_col', 'balance_col', 'reason_col', 'user_col'])
                ->make(true);
        }

        $medicines = Medicine::orderBy('generic_name')->get(['id', 'generic_name', 'brand_name']);

        return view('inventory.transactions', compact('medicines', 'type', 'medicineId', 'dateFrom', 'dateTo'));
    }

    public function reports(Request $request): View
    {
        $today = now()->toDateString();
        $categories = MedicineCategory::withCount('medicines')->orderBy('name')->get();

        $allMedicines = Medicine::query()
            ->with('category:id,name,code')
            ->withSum(['batches as computed_stock_on_hand' => fn (Builder $b) => $b->where('quantity_remaining', '>', 0)->where('expiration_date', '>=', $today)], 'quantity_remaining')
            ->orderBy('generic_name')
            ->get();

        $totalItems = $allMedicines->count();

        $batchStats = MedicineBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->where('expiration_date', '>=', $today)
            ->selectRaw('COALESCE(SUM(quantity_remaining), 0) as total_units')
            ->selectRaw('COALESCE(SUM(quantity_remaining * COALESCE(unit_cost, 0)), 0) as total_valuation')
            ->first();

        $totalStockUnits = (int) ($batchStats?->total_units ?? 0);
        $totalInventoryValuation = (float) ($batchStats?->total_valuation ?? 0.0);

        $lowStockList = $allMedicines->filter(function (Medicine $med): bool {
            $stock = (int) ($med->computed_stock_on_hand ?? 0);

            return $stock <= $med->reorder_level;
        })->values();

        $expiredBatches = MedicineBatch::expired()->where('quantity_remaining', '>', 0)->with('medicine:id,generic_name,brand_name,unit')->get();
        $expiringSoonBatches = MedicineBatch::expiringSoon(60)->where('quantity_remaining', '>', 0)->with('medicine:id,generic_name,brand_name,unit')->get();

        $recentTransactions = InventoryTransaction::with(['medicine:id,generic_name,brand_name', 'user:id,name', 'patient:id,first_name,middle_name,last_name,suffix,type,school_id_number'])
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
