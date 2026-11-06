<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CollegeController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:colleges.view', only: ['index']),
            new Middleware('permission:colleges.create', only: ['store']),
            new Middleware('permission:colleges.edit', only: ['update']),
            new Middleware('permission:colleges.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('colleges.edit') ?? false;
            $canDelete = $request->user()?->can('colleges.delete') ?? false;

            $query = College::query()
                ->withCount('programs')
                ->latest();

            return DataTables::eloquent($query)
                ->addColumn('code_col', fn (College $c): string => '<span class="fw-bold text-dark">'.e($c->code).'</span>')
                ->addColumn('name_col', function (College $c): string {
                    $name = e($c->name);
                    $desc = $c->description ? '<small class="text-muted text-truncate d-block" style="max-width: 320px;">'.e($c->description).'</small>' : '';

                    return "<div class=\"fw-semibold text-dark\">{$name}</div>{$desc}";
                })
                ->addColumn('dean_col', fn (College $c): string => '<span class="text-muted">'.e($c->dean ?: 'Not Assigned').'</span>')
                ->addColumn('programs_col', function (College $c): string {
                    $url = route('programs.index', ['college_id' => $c->id]);

                    return "<a href=\"{$url}\" class=\"badge badge-pill badge-soft-blue text-decoration-none\">{$c->programs_count} Programs</a>";
                })
                ->addColumn('status_col', function (College $c): string {
                    $cls = $c->is_active ? 'completed' : 'pending';
                    $lbl = $c->is_active ? 'active' : 'inactive';

                    return "<span class=\"status-badge {$cls}\">{$lbl}</span>";
                })
                ->addColumn('actions_col', function (College $c) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEdit) {
                        $updateUrl = route('colleges.update', $c);
                        $code = e($c->code);
                        $name = e($c->name);
                        $dean = e($c->dean);
                        $desc = e($c->description);
                        $active = $c->is_active ? '1' : '0';

                        $html .= "<button type=\"button\" class=\"btn btn-sm btn-light border p-1\" data-bs-toggle=\"modal\" data-bs-target=\"#edit-college-modal\" data-update-url=\"{$updateUrl}\" data-code=\"{$code}\" data-name=\"{$name}\" data-dean=\"{$dean}\" data-description=\"{$desc}\" data-active=\"{$active}\" title=\"Edit\"><i class=\"bi bi-pencil\"></i></button>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('colleges.destroy', $c);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');

                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"Are you sure you want to delete this college?\" class=\"d-inline\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('code_col', 'code $1')
                ->orderColumn('name_col', 'name $1')
                ->orderColumn('dean_col', 'dean $1')
                ->orderColumn('programs_col', 'programs_count $1')
                ->orderColumn('status_col', 'is_active $1')
                ->rawColumns(['code_col', 'name_col', 'dean_col', 'programs_col', 'status_col', 'actions_col'])
                ->make(true);
        }

        $collegeAgg = College::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $programAgg = Program::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN degree_level = 'Undergraduate' THEN 1 ELSE 0 END) as undergrad")
            ->first();

        $stats = [
            'total_colleges' => (int) ($collegeAgg?->total ?? 0),
            'active_colleges' => (int) ($collegeAgg?->active ?? 0),
            'total_programs' => (int) ($programAgg?->total ?? 0),
            'undergrad_programs' => (int) ($programAgg?->undergrad ?? 0),
        ];

        return view('colleges.index', compact('stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:colleges,code'],
            'name' => ['required', 'string', 'max:150'],
            'dean' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $college = College::create($validated);

        $this->auditLogger->log('created', "Created college {$college->code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College created successfully.');
    }

    public function update(Request $request, College $college): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:colleges,code,'.$college->id],
            'name' => ['required', 'string', 'max:150'],
            'dean' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $college->update($validated);

        $this->auditLogger->log('updated', "Updated college {$college->code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College updated successfully.');
    }

    public function destroy(College $college): RedirectResponse
    {
        if ($college->programs()->exists()) {
            return back()->with('error', 'Cannot delete college with existing academic programs. Please reassign or delete programs first.');
        }

        $code = $college->code;
        $college->delete();

        $this->auditLogger->log('deleted', "Deleted college {$code}", $college);

        return redirect()->route('colleges.index')->with('success', 'College deleted successfully.');
    }
}
