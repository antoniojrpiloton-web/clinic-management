<?php

namespace App\Http\Controllers;

use App\Models\Clinician;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClinicianController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:clinicians.view', only: ['index', 'show']),
            new Middleware('permission:clinicians.create', only: ['create', 'store']),
            new Middleware('permission:clinicians.edit', only: ['edit', 'update']),
            new Middleware('permission:clinicians.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Clinician::query()
                ->with('user')
                ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->when($request->filled('search.value'), function ($q) use ($request) {
                    $term = $request->input('search.value');
                    $q->search($term);
                })
                ->latest();

            return DataTables::eloquent($query)
                ->addColumn('provider_card', function (Clinician $c) {
                    $badgeClass = match ($c->type) {
                        'Doctor' => 'badge-blue',
                        'Nurse' => 'badge-green',
                        'Dentist' => 'badge-purple',
                        default => 'badge-gray',
                    };
                    $name = e($c->name);
                    $title = e($c->title);
                    $type = e($c->type);
                    $spec = $c->specialization ? ' &bull; '.e($c->specialization) : '';

                    return "<div>
                        <div class=\"fw-bold text-dark\">{$name}</div>
                        <div class=\"small text-muted d-flex align-items-center gap-1 mt-1\">
                            <span class=\"badge {$badgeClass} py-0 px-2\" style=\"font-size: 0.7rem;\">{$type}</span>
                            <span>{$title}{$spec}</span>
                        </div>
                    </div>";
                })
                ->addColumn('license_badge', function (Clinician $c) {
                    $lic = $c->license_number
                        ? '<span class="font-monospace fw-bold text-primary">'.e($c->license_number).'</span>'
                        : '<span class="badge badge-soft-gray">N/A</span>';
                    $ptr = $c->ptr_number
                        ? '<div class="small text-muted">PTR: '.e($c->ptr_number).'</div>'
                        : '<div class="small text-muted">PTR: N/A</div>';

                    return "<div>{$lic}{$ptr}</div>";
                })
                ->addColumn('contact_info', function (Clinician $c) {
                    if (! $c->phone && ! $c->email) {
                        return '<span class="badge badge-soft-gray">N/A</span>';
                    }

                    $phone = $c->phone
                        ? '<div><i class="bi bi-telephone text-muted me-1"></i>'.e($c->phone).'</div>'
                        : '<div class="text-muted"><i class="bi bi-telephone text-muted me-1"></i>N/A</div>';
                    $email = $c->email
                        ? '<div><i class="bi bi-envelope text-muted me-1"></i>'.e($c->email).'</div>'
                        : '<div class="text-muted"><i class="bi bi-envelope text-muted me-1"></i>N/A</div>';

                    return "<div class=\"small\">{$phone}{$email}</div>";
                })
                ->addColumn('status_badge', function (Clinician $c) {
                    return $c->is_active
                        ? '<span class="badge badge-pill badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>'
                        : '<span class="badge badge-pill badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>';
                })
                ->addColumn('actions', function (Clinician $c) {
                    $editUrl = route('clinicians.edit', $c);
                    $deleteUrl = route('clinicians.destroy', $c);
                    $csrf = csrf_token();

                    $buttons = "<div class=\"d-flex align-items-center justify-content-end gap-1\">
                        <a href=\"{$editUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"Edit Provider\">
                            <i class=\"bi bi-pencil\"></i>
                        </a>";

                    $buttons .= "<form action=\"{$deleteUrl}\" method=\"POST\" class=\"d-inline\" onsubmit=\"return confirm('Are you sure you want to remove or deactivate this healthcare provider?');\">
                        <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                        <input type=\"hidden\" name=\"_method\" value=\"DELETE\">
                        <button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1 px-2\" title=\"Remove Provider\">
                            <i class=\"bi bi-trash\"></i>
                        </button>
                    </form>";

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['provider_card', 'license_badge', 'contact_info', 'status_badge', 'actions'])
                ->make(true);
        }

        $agg = Clinician::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'Doctor' THEN 1 ELSE 0 END) as doctors")
            ->selectRaw("SUM(CASE WHEN type = 'Nurse' THEN 1 ELSE 0 END) as nurses")
            ->selectRaw("SUM(CASE WHEN type = 'Dentist' THEN 1 ELSE 0 END) as dentists")
            ->first();

        $totalCount = (int) ($agg?->total ?? 0);
        $doctorsCount = (int) ($agg?->doctors ?? 0);
        $nursesCount = (int) ($agg?->nurses ?? 0);
        $dentistsCount = (int) ($agg?->dentists ?? 0);

        return view('clinicians.index', compact('totalCount', 'doctorsCount', 'nursesCount', 'dentistsCount'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('clinicians.create', [
            'clinician' => new Clinician,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:Doctor,Nurse,Dentist,Other'],
            'title' => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string', 'max:60', 'unique:clinicians,license_number'],
            'ptr_number' => ['nullable', 'string', 'max:60'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:35'],
            'email' => ['nullable', 'email', 'max:120'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $clinician = Clinician::create($validated);

        $this->auditLogger->log('created', "Added healthcare provider {$clinician->name} ({$clinician->type})", $clinician);

        return redirect()->route('clinicians.index')
            ->with('success', "Healthcare Provider {$clinician->name} added successfully.");
    }

    public function edit(Clinician $clinician): View
    {
        $users = User::orderBy('name')->get();

        return view('clinicians.edit', [
            'clinician' => $clinician,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Clinician $clinician): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:Doctor,Nurse,Dentist,Other'],
            'title' => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string', 'max:60', Rule::unique('clinicians')->ignore($clinician->id)],
            'ptr_number' => ['nullable', 'string', 'max:60'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:35'],
            'email' => ['nullable', 'email', 'max:120'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $clinician->update($validated);

        $this->auditLogger->log('updated', "Updated healthcare provider {$clinician->name}", $clinician);

        return redirect()->route('clinicians.index')
            ->with('success', "Healthcare Provider {$clinician->name} updated successfully.");
    }

    public function destroy(Clinician $clinician): RedirectResponse
    {
        $name = $clinician->name;

        if ($clinician->physicalExaminations()->exists()) {
            $clinician->update(['is_active' => false]);
            $this->auditLogger->log('deactivated', "Deactivated healthcare provider {$name} (has linked examination records)", $clinician);

            return redirect()->route('clinicians.index')
                ->with('info', "Healthcare Provider {$name} has linked examination records and was set to Inactive instead of deletion.");
        }

        $clinician->delete();
        $this->auditLogger->log('deleted', "Deleted healthcare provider {$name}", $clinician);

        return redirect()->route('clinicians.index')
            ->with('success', "Healthcare Provider {$name} removed successfully.");
    }
}
