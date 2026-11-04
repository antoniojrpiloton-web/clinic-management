<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $category = $request->query('category');
        $collegeId = $request->query('college_id');
        $classification = $request->query('classification');
        $search = $request->query('search');

        $query = Patient::query()
            ->employees()
            ->with(['college'])
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($collegeId, fn ($q) => $q->where('college_id', $collegeId))
            ->when($classification, fn ($q) => $q->where('employment_classification', $classification))
            ->when($search, fn ($q) => $q->search($search))
            ->latest();

        $employees = $query->paginate(15)->withQueryString();

        $colleges = College::orderBy('name')->get(['id', 'name', 'code']);

        return view('employees.index', compact('employees', 'colleges', 'category', 'collegeId', 'classification', 'search'));
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
