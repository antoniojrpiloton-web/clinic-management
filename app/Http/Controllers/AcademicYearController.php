<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class AcademicYearController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:academic-years.view', only: ['index']),
            new Middleware('permission:academic-years.create', only: ['store']),
            new Middleware('permission:academic-years.edit', only: ['update', 'setCurrent']),
            new Middleware('permission:academic-years.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $academicYears = AcademicYear::query()
            ->latest('start_date')
            ->paginate(15);

        return view('academic-years.index', compact('academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:academic_years,code'],
            'name' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,closed'],
        ]);

        $validated['is_current'] = $request->boolean('is_current');

        if ($validated['is_current']) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create($validated);

        $this->auditLogger->log('created', "Created academic year {$academicYear->code}", $academicYear);

        return redirect()->route('academic-years.index')->with('success', 'Academic year created successfully.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:academic_years,code,'.$academicYear->id],
            'name' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,closed'],
        ]);

        if ($request->boolean('is_current')) {
            $academicYear->makeCurrent();
        }

        $academicYear->update($validated);

        $this->auditLogger->log('updated', "Updated academic year {$academicYear->code}", $academicYear);

        return redirect()->route('academic-years.index')->with('success', 'Academic year updated successfully.');
    }

    public function setCurrent(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->makeCurrent();

        $this->auditLogger->log('updated', "Set academic year {$academicYear->code} as current", $academicYear);

        return redirect()->route('academic-years.index')->with('success', "Academic year {$academicYear->code} is now the active term.");
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_current) {
            return back()->with('error', 'Cannot delete the currently active academic year.');
        }

        $code = $academicYear->code;
        $academicYear->delete();

        $this->auditLogger->log('deleted', "Deleted academic year {$code}", $academicYear);

        return redirect()->route('academic-years.index')->with('success', 'Academic year deleted successfully.');
    }
}
