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

class ProgramController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:programs.view', only: ['index']),
            new Middleware('permission:programs.create', only: ['store']),
            new Middleware('permission:programs.edit', only: ['update']),
            new Middleware('permission:programs.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        $collegeFilter = $request->query('college_id');

        if ($request->ajax()) {
            $canEdit = $request->user()?->can('programs.edit') ?? false;
            $canDelete = $request->user()?->can('programs.delete') ?? false;

            $query = Program::query()
                ->with('college:id,name,code')
                ->when($collegeFilter, fn ($q) => $q->where('college_id', $collegeFilter))
                ->latest();

            return DataTables::eloquent($query)
                ->addColumn('code_col', fn (Program $p): string => '<span class="fw-bold text-dark">'.e($p->code).'</span>')
                ->addColumn('name_col', fn (Program $p): string => '<div class="fw-semibold text-dark">'.e($p->name).'</div>')
                ->addColumn('college_col', function (Program $p): string {
                    $code = e($p->college?->code ?? 'N/A');
                    $name = e($p->college?->name ?? '');

                    return "<span class=\"badge badge-pill badge-soft-blue\">{$code}</span> <small class=\"text-muted ms-1\">{$name}</small>";
                })
                ->addColumn('level_col', fn (Program $p): string => '<span class="badge badge-pill badge-soft-gray">'.e($p->degree_level).'</span>')
                ->addColumn('years_col', fn (Program $p): string => "{$p->years} Years")
                ->addColumn('status_col', function (Program $p): string {
                    $cls = $p->is_active ? 'completed' : 'pending';
                    $lbl = $p->is_active ? 'active' : 'inactive';

                    return "<span class=\"status-badge {$cls}\">{$lbl}</span>";
                })
                ->addColumn('actions_col', function (Program $p) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEdit) {
                        $updateUrl = route('programs.update', $p);
                        $code = e($p->code);
                        $name = e($p->name);
                        $level = e($p->degree_level);
                        $active = $p->is_active ? '1' : '0';

                        $html .= "<button type=\"button\" class=\"btn btn-sm btn-light border p-1\" data-bs-toggle=\"modal\" data-bs-target=\"#edit-program-modal\" data-update-url=\"{$updateUrl}\" data-college-id=\"{$p->college_id}\" data-code=\"{$code}\" data-name=\"{$name}\" data-level=\"{$level}\" data-years=\"{$p->years}\" data-active=\"{$active}\" title=\"Edit\"><i class=\"bi bi-pencil\"></i></button>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('programs.destroy', $p);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');

                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"Are you sure you want to delete this program?\" class=\"d-inline\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('code_col', 'code $1')
                ->orderColumn('name_col', 'name $1')
                ->orderColumn('level_col', 'degree_level $1')
                ->orderColumn('years_col', 'years $1')
                ->orderColumn('status_col', 'is_active $1')
                ->rawColumns(['code_col', 'name_col', 'college_col', 'level_col', 'status_col', 'actions_col'])
                ->make(true);
        }

        $colleges = College::orderBy('name')->get(['id', 'name', 'code']);
        $agg = Program::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $stats = [
            'total' => (int) ($agg?->total ?? 0),
            'active' => (int) ($agg?->active ?? 0),
            'colleges' => $colleges->count(),
        ];

        return view('programs.index', compact('colleges', 'collegeFilter', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code'],
            'name' => ['required', 'string', 'max:150'],
            'degree_level' => ['required', 'string', 'max:50'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $program = Program::create($validated);

        $this->auditLogger->log('created', "Created program {$program->code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program created successfully.');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code,'.$program->id],
            'name' => ['required', 'string', 'max:150'],
            'degree_level' => ['required', 'string', 'max:50'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $program->update($validated);

        $this->auditLogger->log('updated', "Updated program {$program->code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $code = $program->code;
        $program->delete();

        $this->auditLogger->log('deleted', "Deleted program {$code}", $program);

        return redirect()->route('programs.index')->with('success', 'Academic program deleted successfully.');
    }
}
