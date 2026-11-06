<?php

namespace App\Http\Controllers;

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

class MedicineCategoryController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:medicine-categories.view', only: ['index']),
            new Middleware('permission:medicine-categories.create', only: ['store']),
            new Middleware('permission:medicine-categories.edit', only: ['update']),
            new Middleware('permission:medicine-categories.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('medicine-categories.edit') ?? false;
            $canDelete = $request->user()?->can('medicine-categories.delete') ?? false;

            $query = MedicineCategory::query()
                ->withCount('medicines')
                ->orderBy('name');

            return DataTables::eloquent($query)
                ->addColumn('code_col', fn (MedicineCategory $cat): string => '<span class="badge badge-soft-blue font-monospace">'.e($cat->code).'</span>')
                ->addColumn('name_col', fn (MedicineCategory $cat): string => '<span class="fw-bold text-dark">'.e($cat->name).'</span>')
                ->addColumn('desc_col', fn (MedicineCategory $cat): string => '<span class="text-muted small">'.e($cat->description ?: 'N/A').'</span>')
                ->addColumn('count_col', function (MedicineCategory $cat): string {
                    $url = route('medicines.index', ['category_id' => $cat->id]);

                    return "<a href=\"{$url}\" class=\"badge badge-soft-gray text-decoration-none\">{$cat->medicines_count} items</a>";
                })
                ->addColumn('status_col', function (MedicineCategory $cat): string {
                    return $cat->is_active
                        ? '<span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>'
                        : '<span class="badge badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>';
                })
                ->addColumn('actions_col', function (MedicineCategory $cat) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-flex align-items-center justify-content-end gap-1">';

                    if ($canEdit) {
                        $updateUrl = route('medicine-categories.update', $cat);
                        $name = e($cat->name);
                        $code = e($cat->code);
                        $desc = e($cat->description);
                        $active = $cat->is_active ? '1' : '0';

                        $html .= "<button type=\"button\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"Edit Category\" data-bs-toggle=\"modal\" data-bs-target=\"#modal-edit-category\" data-category-id=\"{$cat->id}\" data-category-name=\"{$name}\" data-category-code=\"{$code}\" data-category-description=\"{$desc}\" data-category-active=\"{$active}\" data-update-url=\"{$updateUrl}\"><i class=\"bi bi-pencil\"></i></button>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('medicine-categories.destroy', $cat);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $nameJs = e(addslashes($cat->name));

                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" class=\"d-inline\" onsubmit=\"return confirm('Are you sure you want to remove category {$nameJs}?');\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1 px-2\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('code_col', 'code $1')
                ->orderColumn('name_col', 'name $1')
                ->orderColumn('count_col', 'medicines_count $1')
                ->orderColumn('status_col', 'is_active $1')
                ->rawColumns(['code_col', 'name_col', 'desc_col', 'count_col', 'status_col', 'actions_col'])
                ->make(true);
        }

        $totalCategories = MedicineCategory::count();

        return view('medicine-categories.index', compact('totalCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:medicine_categories,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = MedicineCategory::create($validated);

        $this->auditLogger->log('created', "Created medicine category {$category->name} ({$category->code})", $category);

        return redirect()->route('medicine-categories.index')
            ->with('success', "Medicine category {$category->name} created successfully.");
    }

    public function update(Request $request, MedicineCategory $medicineCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', Rule::unique('medicine_categories')->ignore($medicineCategory->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $medicineCategory->update($validated);

        $this->auditLogger->log('updated', "Updated medicine category {$medicineCategory->name}", $medicineCategory);

        return redirect()->route('medicine-categories.index')
            ->with('success', "Medicine category {$medicineCategory->name} updated successfully.");
    }

    public function destroy(MedicineCategory $medicineCategory): RedirectResponse
    {
        if ($medicineCategory->medicines()->exists()) {
            return back()->with('error', "Cannot delete category {$medicineCategory->name} because it has associated medicines in the masterlist.");
        }

        $name = $medicineCategory->name;
        $medicineCategory->delete();

        $this->auditLogger->log('deleted', "Deleted medicine category {$name}", $medicineCategory);

        return redirect()->route('medicine-categories.index')
            ->with('success', "Medicine category {$name} removed successfully.");
    }
}
