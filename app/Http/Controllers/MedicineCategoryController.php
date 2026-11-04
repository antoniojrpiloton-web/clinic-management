<?php

namespace App\Http\Controllers;

use App\Models\MedicineCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    public function index(): View
    {
        $categories = MedicineCategory::withCount('medicines')->orderBy('name')->get();

        return view('medicine-categories.index', compact('categories'));
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

