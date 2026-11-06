<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

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

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('academic-years.edit') ?? false;
            $canDelete = $request->user()?->can('academic-years.delete') ?? false;

            $query = AcademicYear::query()->latest('start_date');

            return DataTables::eloquent($query)
                ->addColumn('code_col', fn (AcademicYear $ay): string => '<span class="fw-bold text-dark">'.e($ay->code).'</span>')
                ->addColumn('name_col', fn (AcademicYear $ay): string => e($ay->name))
                ->addColumn('semester_col', fn (AcademicYear $ay): string => '<span class="badge badge-pill badge-soft-blue">'.e($ay->semester).'</span>')
                ->addColumn('duration_col', fn (AcademicYear $ay): string => '<span class="text-muted small">'.$ay->start_date->format('M d, Y').' &mdash; '.$ay->end_date->format('M d, Y').'</span>')
                ->addColumn('current_col', function (AcademicYear $ay) use ($canEdit): string {
                    if ($ay->is_current) {
                        return '<span class="badge badge-pill badge-soft-green"><i class="bi bi-check-circle me-1"></i> Current Active</span>';
                    }

                    if ($canEdit) {
                        $url = route('academic-years.set-current', $ay);
                        $csrf = csrf_field();

                        return "<form method=\"POST\" action=\"{$url}\" class=\"d-inline\">{$csrf}<button type=\"submit\" class=\"btn btn-sm btn-outline-secondary py-0 px-2\" style=\"font-size: 0.75rem;\">Set as Current</button></form>";
                    }

                    return '<span class="text-muted small">—</span>';
                })
                ->addColumn('status_col', function (AcademicYear $ay): string {
                    $cls = $ay->status === 'active' ? 'completed' : 'pending';

                    return "<span class=\"status-badge {$cls}\">".e($ay->status).'</span>';
                })
                ->addColumn('actions_col', function (AcademicYear $ay) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEdit) {
                        $updateUrl = route('academic-years.update', $ay);
                        $code = e($ay->code);
                        $name = e($ay->name);
                        $sem = e($ay->semester);
                        $start = $ay->start_date->format('Y-m-d');
                        $end = $ay->end_date->format('Y-m-d');
                        $status = e($ay->status);
                        $current = $ay->is_current ? '1' : '0';

                        $html .= "<button type=\"button\" class=\"btn btn-sm btn-light border p-1\" data-bs-toggle=\"modal\" data-bs-target=\"#edit-ay-modal\" data-update-url=\"{$updateUrl}\" data-code=\"{$code}\" data-name=\"{$name}\" data-semester=\"{$sem}\" data-start=\"{$start}\" data-end=\"{$end}\" data-status=\"{$status}\" data-current=\"{$current}\" title=\"Edit\"><i class=\"bi bi-pencil\"></i></button>";
                    }

                    if ($canDelete && ! $ay->is_current) {
                        $deleteUrl = route('academic-years.destroy', $ay);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');

                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"Are you sure you want to delete this academic year?\" class=\"d-inline\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('code_col', 'code $1')
                ->orderColumn('name_col', 'name $1')
                ->orderColumn('semester_col', 'semester $1')
                ->orderColumn('duration_col', 'start_date $1')
                ->orderColumn('current_col', 'is_current $1')
                ->orderColumn('status_col', 'status $1')
                ->rawColumns(['code_col', 'semester_col', 'duration_col', 'current_col', 'status_col', 'actions_col'])
                ->make(true);
        }

        $agg = AcademicYear::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN semester = '1st Semester' THEN 1 ELSE 0 END) as first_sem")
            ->selectRaw("SUM(CASE WHEN semester = '2nd Semester' THEN 1 ELSE 0 END) as second_sem")
            ->first();

        $currentAy = AcademicYear::where('is_current', true)->first();

        $stats = [
            'total' => (int) ($agg?->total ?? 0),
            'first_sem' => (int) ($agg?->first_sem ?? 0),
            'second_sem' => (int) ($agg?->second_sem ?? 0),
            'current_code' => $currentAy?->code ?? 'Not Set',
            'current_semester' => $currentAy?->semester ?? 'None',
        ];

        return view('academic-years.index', compact('stats'));
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
