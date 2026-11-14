<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:employees.view', only: ['index', 'show']),
            new Middleware('permission:employees.create', only: ['create', 'store']),
            new Middleware('permission:employees.edit', only: ['edit', 'update']),
            new Middleware('permission:employees.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Patient::query()
                ->employees()
                ->with(['college'])
                ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
                ->when($request->filled('college_id'), fn ($q) => $q->where('college_id', $request->college_id))
                ->when($request->filled('classification'), fn ($q) => $q->where('employment_classification', $request->classification))
                ->latest();

            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    $term = $request->input('custom_search') ?: $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $q->search(trim($term));
                    }
                })
                ->addColumn('employee_id_col', fn (Patient $e) => '<span class="font-monospace fw-bold text-primary">'.e($e->school_id_number ?: $e->patient_number).'</span>')
                ->addColumn('employee_name_col', function (Patient $e) {
                    $url = route('patients.show', $e);
                    $name = e($e->full_name);
                    $sub = e($e->gender).', '.($e->age ?? 'N/A').' y/o';

                    return "<div><a href=\"{$url}\" class=\"fw-semibold text-dark text-decoration-none\">{$name}</a><small class=\"text-muted d-block\" style=\"font-size: 0.72rem;\">{$sub}</small></div>";
                })
                ->addColumn('category_position_col', function (Patient $e) {
                    $badgeColor = $e->category === 'Faculty' ? 'blue' : 'green';
                    $cat = e($e->category ?? 'Employee');
                    $pos = e($e->position ?? '—');

                    return "<span class=\"badge badge-pill badge-soft-{$badgeColor}\">{$cat}</span><div class=\"fw-medium text-dark small mt-1\">{$pos}</div>";
                })
                ->addColumn('office_college_col', function (Patient $e) {
                    $unit = e($e->office_department ?: ($e->college?->name ?? '—'));

                    return "<span class=\"text-dark small\">{$unit}</span>";
                })
                ->addColumn('classification_col', fn (Patient $e) => '<span class="badge badge-pill badge-soft-gray">'.e($e->employment_classification ?? '—').'</span>')
                ->addColumn('contact_col', fn (Patient $e) => '<span class="small text-muted">'.e($e->phone ?: ($e->email ?: '—')).'</span>')
                ->addColumn('status_col', function (Patient $e) {
                    $cls = $e->employment_status === 'active' ? 'completed' : 'pending';

                    return '<span class="status-badge '.$cls.'">'.e($e->employment_status ?? 'active').'</span>';
                })
                ->addColumn('actions', function (Patient $e) {
                    $showUrl = route('patients.show', $e);
                    $editUrl = route('employees.edit', $e);
                    $delUrl = route('employees.destroy', $e);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $name = e($e->full_name);

                    $canEdit = auth()->user()?->can('employees.edit');
                    $canDelete = auth()->user()?->can('employees.delete');

                    $items = "<li><a class=\"dropdown-item\" href=\"{$showUrl}\"><i class=\"bi bi-eye text-primary me-2\"></i> View Profile</a></li>";
                    if ($canEdit) {
                        $items .= "<li><a class=\"dropdown-item\" href=\"{$editUrl}\"><i class=\"bi bi-pencil text-warning me-2\"></i> Edit</a></li>";
                    }
                    if ($canDelete) {
                        $items .= "<li><hr class=\"dropdown-divider\"></li>
                        <li>
                            <form method=\"POST\" action=\"{$delUrl}\" onsubmit=\"return confirm('Delete employee {$name}?');\">
                                {$csrf}
                                {$method}
                                <button type=\"submit\" class=\"dropdown-item text-danger\"><i class=\"bi bi-trash me-2\"></i> Delete</button>
                            </form>
                        </li>";
                    }

                    return "<div class=\"dropdown\">
                        <button class=\"btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                            <i class=\"bi bi-gear-fill me-1\"></i> Action
                        </button>
                        <ul class=\"dropdown-menu shadow-sm\">
                            {$items}
                        </ul>
                    </div>";
                })
                ->rawColumns(['employee_id_col', 'employee_name_col', 'category_position_col', 'office_college_col', 'classification_col', 'contact_col', 'status_col', 'actions'])
                ->toJson();
        }

        $totalEmployees = Patient::employees()->count();
        $facultyCount = Patient::employees()->where('category', 'Faculty')->count();
        $staffCount = Patient::employees()->where('category', 'Non-Teaching / Staff')->count();
        $permanentCount = Patient::employees()->where('employment_classification', 'Permanent')->count();

        $colleges = College::orderBy('name')->get(['id', 'name', 'code']);

        return view('employees.index', compact('colleges', 'totalEmployees', 'facultyCount', 'staffCount', 'permanentCount'));
    }

    public function create(): View
    {
        $colleges = College::where('is_active', true)->orderBy('name')->get();

        return view('employees.create', compact('colleges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:50', 'unique:patients,school_id_number'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'office_department' => ['nullable', 'string', 'max:120'],
            'employment_classification' => ['required', 'string', 'max:50'],
            'category' => ['required', 'in:Faculty,Non-Teaching / Staff'],
            'position' => ['required', 'string', 'max:120'],
            'employment_status' => ['required', 'string', 'max:30'],

            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'suffix' => ['nullable', 'string', 'max:15'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', 'max:30'],
            'blood_type' => ['nullable', 'string', 'max:10'],

            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:35'],
            'address' => ['nullable', 'string'],

            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:60'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:35'],

            'allergies' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
        ]);

        $duplicate = Patient::findDuplicate(
            $validated['first_name'],
            $validated['last_name'],
            $validated['date_of_birth']
        );

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'first_name' => "A patient record already exists for {$duplicate->full_name} born on {$duplicate->date_of_birth->format('M d, Y')}.",
            ]);
        }

        $patient = Patient::create([
            'patient_number' => Patient::generatePatientNumber(),
            'school_id_number' => $validated['employee_id'],
            'type' => 'employee',
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'civil_status' => $validated['civil_status'],
            'blood_type' => $validated['blood_type'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'status' => 'active',
            'college_id' => $validated['college_id'] ?? null,
            'office_department' => $validated['office_department'] ?? null,
            'employment_classification' => $validated['employment_classification'],
            'category' => $validated['category'],
            'position' => $validated['position'],
            'employment_status' => $validated['employment_status'],
        ]);

        $this->auditLogger->log('created', "Registered employee {$patient->full_name} ({$validated['employee_id']})", $patient);

        return redirect()->route('employees.index')->with('success', 'Employee record registered successfully.');
    }

    public function edit(Patient $employee): View
    {
        $colleges = College::where('is_active', true)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'colleges'));
    }

    public function update(Request $request, Patient $employee): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('patients', 'school_id_number')->ignore($employee->id)],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'office_department' => ['nullable', 'string', 'max:120'],
            'employment_classification' => ['required', 'string', 'max:50'],
            'category' => ['required', 'in:Faculty,Non-Teaching / Staff'],
            'position' => ['required', 'string', 'max:120'],
            'employment_status' => ['required', 'string', 'max:30'],

            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'suffix' => ['nullable', 'string', 'max:15'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', 'max:30'],
            'blood_type' => ['nullable', 'string', 'max:10'],

            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:35'],
            'address' => ['nullable', 'string'],

            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:60'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:35'],

            'allergies' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
        ]);

        $employee->update([
            'school_id_number' => $validated['employee_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'civil_status' => $validated['civil_status'],
            'blood_type' => $validated['blood_type'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'college_id' => $validated['college_id'] ?? null,
            'office_department' => $validated['office_department'] ?? null,
            'employment_classification' => $validated['employment_classification'],
            'category' => $validated['category'],
            'position' => $validated['position'],
            'employment_status' => $validated['employment_status'],
        ]);

        $this->auditLogger->log('updated', "Updated employee profile {$employee->full_name} ({$employee->school_id_number})", $employee);

        return redirect()->route('employees.index')->with('success', 'Employee record updated successfully.');
    }

    public function destroy(Patient $employee): RedirectResponse
    {
        $name = $employee->full_name;
        $employee->delete();

        $this->auditLogger->log('deleted', "Deleted employee record {$name}", $employee);

        return redirect()->route('employees.index')->with('success', 'Employee record deleted successfully.');
    }
}
