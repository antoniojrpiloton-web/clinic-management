<?php

namespace App\Http\Controllers;

use App\Models\DiseaseCategory;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DiseaseCategoryController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:medical-consultations.view', only: ['index']),
            new Middleware('permission:medical-consultations.create', only: ['store']),
            new Middleware('permission:medical-consultations.edit', only: ['update']),
            new Middleware('permission:medical-consultations.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('medical-consultations.edit') ?? false;
            $canDelete = $request->user()?->can('medical-consultations.delete') ?? false;

            $query = DiseaseCategory::query()
                ->withCount('medicalConsultations')
                ->orderBy('classification')
                ->orderBy('sort_order')
                ->orderBy('name');

            return DataTables::eloquent($query)
                ->addColumn('code_col', fn (DiseaseCategory $cat): string => '<span class="badge badge-soft-gray font-monospace">'.e($cat->code).'</span>')
                ->addColumn('classification_col', function (DiseaseCategory $cat): string {
                    return $cat->classification === DiseaseCategory::TYPE_COMMUNICABLE
                        ? '<span class="badge badge-soft-green"><i class="bi bi-virus me-1"></i>Communicable</span>'
                        : '<span class="badge badge-soft-blue"><i class="bi bi-heart-pulse me-1"></i>Non-Communicable</span>';
                })
                ->addColumn('name_col', fn (DiseaseCategory $cat): string => '<span class="fw-bold text-dark">'.e($cat->name).'</span>')
                ->addColumn('keywords_col', fn (DiseaseCategory $cat): string => '<span class="text-muted small">'.e($cat->keywords ?: '—').'</span>')
                ->addColumn('count_col', fn (DiseaseCategory $cat): string => '<span class="badge badge-soft-gray">'.number_format($cat->medical_consultations_count).' cases</span>')
                ->addColumn('status_col', function (DiseaseCategory $cat): string {
                    return $cat->is_active
                        ? '<span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>'
                        : '<span class="badge badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>';
                })
                ->addColumn('actions_col', function (DiseaseCategory $cat) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-flex align-items-center justify-content-end gap-1">';

                    if ($canEdit) {
                        $updateUrl = route('disease-categories.update', $cat);
                        $name = e($cat->name);
                        $code = e($cat->code);
                        $classif = e($cat->classification);
                        $keywords = e($cat->keywords);
                        $sortOrder = (int) $cat->sort_order;
                        $active = $cat->is_active ? '1' : '0';

                        $html .= "<button type=\"button\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"Edit Disease Category\" data-bs-toggle=\"modal\" data-bs-target=\"#modal-edit-disease-category\" data-category-id=\"{$cat->id}\" data-category-name=\"{$name}\" data-category-code=\"{$code}\" data-category-classification=\"{$classif}\" data-category-keywords=\"{$keywords}\" data-category-sort=\"{$sortOrder}\" data-category-active=\"{$active}\" data-update-url=\"{$updateUrl}\"><i class=\"bi bi-pencil\"></i></button>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('disease-categories.destroy', $cat);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $nameJs = e(addslashes($cat->name));

                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" class=\"d-inline\" onsubmit=\"return confirm('Remove disease category {$nameJs}?');\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1 px-2\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('code_col', 'code $1')
                ->orderColumn('classification_col', 'classification $1')
                ->orderColumn('name_col', 'name $1')
                ->orderColumn('status_col', 'is_active $1')
                ->rawColumns(['code_col', 'classification_col', 'name_col', 'keywords_col', 'count_col', 'status_col', 'actions_col'])
                ->make(true);
        }

        $totalCategories = DiseaseCategory::count();
        $communicableCount = DiseaseCategory::communicable()->count();
        $nonCommunicableCount = DiseaseCategory::nonCommunicable()->count();

        return view('disease-categories.index', compact('totalCategories', 'communicableCount', 'nonCommunicableCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:disease_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', 'in:communicable,non_communicable'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 50);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = DiseaseCategory::create($validated);

        $this->auditLogger->log('created', "Created disease category {$category->name} ({$category->code})", $category);

        return redirect()->route('disease-categories.index')
            ->with('success', "Disease category '{$category->name}' added to database.");
    }

    public function update(Request $request, DiseaseCategory $diseaseCategory): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('disease_categories', 'code')->ignore($diseaseCategory->id)],
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', 'in:communicable,non_communicable'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $diseaseCategory->sort_order);
        $validated['is_active'] = $request->boolean('is_active');

        $diseaseCategory->update($validated);

        $this->auditLogger->log('updated', "Updated disease category {$diseaseCategory->name}", $diseaseCategory);

        return redirect()->route('disease-categories.index')
            ->with('success', "Disease category '{$diseaseCategory->name}' updated.");
    }

    public function destroy(DiseaseCategory $diseaseCategory): RedirectResponse
    {
        if ($diseaseCategory->medicalConsultations()->exists()) {
            return back()->with('error', "Cannot delete '{$diseaseCategory->name}' because it is linked to existing medical consultations.");
        }

        $name = $diseaseCategory->name;
        $diseaseCategory->delete();

        $this->auditLogger->log('deleted', "Deleted disease category {$name}", $diseaseCategory);

        return redirect()->route('disease-categories.index')
            ->with('success', "Disease category '{$name}' removed.");
    }
}
