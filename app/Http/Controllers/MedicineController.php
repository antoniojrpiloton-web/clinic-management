<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MedicineController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:medicines.view', only: ['index', 'show']),
            new Middleware('permission:medicines.create', only: ['create', 'store']),
            new Middleware('permission:medicines.edit', only: ['edit', 'update']),
            new Middleware('permission:medicines.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        $todayStart = now()->startOfDay();

        if ($request->ajax()) {
            $query = Medicine::query()
                ->with(['category:id,name'])
                ->withSum(['batches as active_stock' => fn ($q) => $q->where('expiration_date', '>=', $todayStart)], 'quantity_remaining')
                ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
                ->when($request->filled('dosage_form'), fn ($q) => $q->where('dosage_form', $request->dosage_form))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->when($request->filled('search.value'), function ($q) use ($request) {
                    $term = $request->input('search.value');
                    $q->search($term);
                })
                ->orderBy('generic_name');

            return DataTables::eloquent($query)
                ->addColumn('medicine_card', function (Medicine $m) {
                    $code = e($m->code);
                    $name = e($m->generic_name);
                    $brand = $m->brand_name ? '<span class="text-primary fw-medium">('.e($m->brand_name).')</span>' : '';
                    $strength = $m->strength ? e($m->strength).' ' : '';
                    $form = e($m->dosage_form);
                    $url = route('medicines.show', $m);

                    return "<div>
                        <div>
                            <a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$name} {$brand}</a>
                        </div>
                        <div class=\"small text-muted d-flex align-items-center gap-1 mt-1\">
                            <span class=\"badge badge-soft-gray py-0 px-2\" style=\"font-size: 0.7rem;\">{$code}</span>
                            <span>{$strength}{$form}</span>
                        </div>
                    </div>";
                })
                ->addColumn('category_badge', function (Medicine $m) {
                    $cat = e($m->category?->name ?? 'N/A');

                    return "<span class=\"badge badge-soft-blue\">{$cat}</span>";
                })
                ->addColumn('stock_summary', function (Medicine $m) {
                    $stock = (int) ($m->active_stock ?? 0);
                    $unit = e($m->unit);

                    if ($stock <= 0) {
                        return "<div>
                            <span class=\"fw-bold text-danger fs-6\">0</span> <small class=\"text-muted\">{$unit}s</small>
                            <div><span class=\"badge badge-soft-red\">Out of Stock</span></div>
                        </div>";
                    }

                    if ($stock <= $m->reorder_level) {
                        return "<div>
                            <span class=\"fw-bold text-warning fs-6\">{$stock}</span> <small class=\"text-muted\">{$unit}s</small>
                            <div><span class=\"badge badge-soft-amber\"><i class=\"bi bi-exclamation-triangle me-1\"></i>Low Stock</span></div>
                        </div>";
                    }

                    return "<div>
                        <span class=\"fw-bold text-success fs-6\">{$stock}</span> <small class=\"text-muted\">{$unit}s</small>
                        <div><span class=\"badge badge-soft-green\">In Stock</span></div>
                    </div>";
                })
                ->addColumn('reorder_level_formatted', function (Medicine $m) {
                    return "<span class=\"small text-muted\">{$m->reorder_level} {$m->unit}s</span>";
                })
                ->addColumn('prescription_badge', function (Medicine $m) {
                    return $m->is_prescription_required
                        ? '<span class="badge badge-soft-amber"><i class="bi bi-prescription me-1"></i>Rx Required</span>'
                        : '<span class="badge badge-soft-green">OTC</span>';
                })
                ->addColumn('status_badge', function (Medicine $m) {
                    return $m->is_active
                        ? '<span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>'
                        : '<span class="badge badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>';
                })
                ->addColumn('actions', function (Medicine $m) {
                    $viewUrl = route('medicines.show', $m);
                    $editUrl = route('medicines.edit', $m);
                    $stockInUrl = route('inventory.stock-in', ['medicine_id' => $m->getRouteKey()]);
                    $deleteUrl = route('medicines.destroy', $m);
                    $csrf = csrf_token();

                    return "<div class=\"d-flex align-items-center justify-content-end gap-1\">
                        <a href=\"{$stockInUrl}\" class=\"btn btn-sm btn-light border p-1 px-2 text-success\" title=\"Receive Stock (Stock-In)\">
                            <i class=\"bi bi-box-arrow-in-down\"></i>
                        </a>
                        <a href=\"{$viewUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"View Batches & History\">
                            <i class=\"bi bi-eye\"></i>
                        </a>
                        <a href=\"{$editUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"Edit Medicine\">
                            <i class=\"bi bi-pencil\"></i>
                        </a>
                        <form action=\"{$deleteUrl}\" method=\"POST\" class=\"d-inline\" onsubmit=\"return confirm('Are you sure you want to remove or deactivate this medicine?');\">
                            <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                            <input type=\"hidden\" name=\"_method\" value=\"DELETE\">
                            <button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1 px-2\" title=\"Delete\">
                                <i class=\"bi bi-trash\"></i>
                            </button>
                        </form>
                    </div>";
                })
                ->rawColumns(['medicine_card', 'category_badge', 'stock_summary', 'reorder_level_formatted', 'prescription_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        $categories = MedicineCategory::active()->orderBy('name')->get();
        $medicinesWithStock = Medicine::query()
            ->select(['id', 'reorder_level'])
            ->withSum(['batches as active_stock' => fn ($q) => $q->where('expiration_date', '>=', $todayStart)], 'quantity_remaining')
            ->get();

        $totalMedicines = $medicinesWithStock->count();
        $totalStockUnits = (int) $medicinesWithStock->sum('active_stock');
        $lowStockCount = $medicinesWithStock->filter(fn (Medicine $m) => (int) $m->active_stock <= $m->reorder_level && (int) $m->active_stock > 0)->count();
        $outOfStockCount = $medicinesWithStock->filter(fn (Medicine $m) => (int) $m->active_stock <= 0)->count();
        $expiringSoonCount = MedicineBatch::expiringSoon(60)->where('quantity_remaining', '>', 0)->count();
        $expiredCount = MedicineBatch::expired()->where('quantity_remaining', '>', 0)->count();

        return view('medicines.index', compact(
            'categories',
            'totalMedicines',
            'totalStockUnits',
            'lowStockCount',
            'outOfStockCount',
            'expiringSoonCount',
            'expiredCount'
        ));
    }

    public function create(): View
    {
        $categories = MedicineCategory::active()->orderBy('name')->get();
        $nextCode = Medicine::generateCode();

        return view('medicines.create', [
            'medicine' => new Medicine,
            'categories' => $categories,
            'nextCode' => $nextCode,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:medicines,code'],
            'category_id' => ['required', 'exists:medicine_categories,id'],
            'generic_name' => ['required', 'string', 'max:150'],
            'brand_name' => ['nullable', 'string', 'max:150'],
            'dosage_form' => ['required', 'string', 'max:60'],
            'strength' => ['nullable', 'string', 'max:60'],
            'unit' => ['required', 'string', 'max:40'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'side_effects' => ['nullable', 'string'],
            'storage_conditions' => ['nullable', 'string', 'max:150'],
            'is_prescription_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_prescription_required'] = $request->boolean('is_prescription_required');
        $validated['is_active'] = $request->boolean('is_active', true);

        $medicine = Medicine::create($validated);

        $this->auditLogger->log('created', "Added medicine {$medicine->generic_name} ({$medicine->code}) to masterlist", $medicine);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Medicine {$medicine->display_name} recorded successfully. You may now receive initial stock batches.");
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load([
            'category',
            'batches' => fn ($q) => $q->orderBy('expiration_date'),
            'transactions' => fn ($q) => $q->with(['user', 'patient', 'batch'])->latest('transaction_date')->take(20),
        ]);

        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        $categories = MedicineCategory::active()->orderBy('name')->get();

        return view('medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('medicines')->ignore($medicine->id)],
            'category_id' => ['required', 'exists:medicine_categories,id'],
            'generic_name' => ['required', 'string', 'max:150'],
            'brand_name' => ['nullable', 'string', 'max:150'],
            'dosage_form' => ['required', 'string', 'max:60'],
            'strength' => ['nullable', 'string', 'max:60'],
            'unit' => ['required', 'string', 'max:40'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'side_effects' => ['nullable', 'string'],
            'storage_conditions' => ['nullable', 'string', 'max:150'],
            'is_prescription_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_prescription_required'] = $request->boolean('is_prescription_required');
        $validated['is_active'] = $request->boolean('is_active');

        $medicine->update($validated);

        $this->auditLogger->log('updated', "Updated medicine {$medicine->generic_name}", $medicine);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Medicine {$medicine->display_name} updated successfully.");
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $name = $medicine->display_name;

        if ($medicine->batches()->where('quantity_remaining', '>', 0)->exists()) {
            $medicine->update(['is_active' => false]);
            $this->auditLogger->log('deactivated', "Deactivated medicine {$name} (has active stock on hand)", $medicine);

            return redirect()->route('medicines.index')
                ->with('info', "Medicine {$name} has active stock on hand and was set to Inactive instead of deletion.");
        }

        $medicine->delete();
        $this->auditLogger->log('deleted', "Deleted medicine {$name}", $medicine);

        return redirect()->route('medicines.index')
            ->with('success', "Medicine {$name} removed from masterlist.");
    }
}
