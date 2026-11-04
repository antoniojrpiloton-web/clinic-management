<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\College;
use App\Models\Patient;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:students.view', only: ['index', 'show']),
            new Middleware('permission:students.create', only: ['create', 'store']),
            new Middleware('permission:students.edit', only: ['edit', 'update']),
            new Middleware('permission:students.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $collegeId = $request->query('college_id');
        $programId = $request->query('program_id');
        $yearLevel = $request->query('year_level');
        $search = $request->query('search');

        $query = Patient::query()
            ->students()
            ->with(['college', 'program', 'academicYear'])
            ->when($collegeId, fn ($q) => $q->where('college_id', $collegeId))
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->when($yearLevel, fn ($q) => $q->where('year_level', $yearLevel))
            ->when($search, fn ($q) => $q->search($search))
            ->latest();

        $students = $query->paginate(15)->withQueryString();

        $colleges = College::orderBy('name')->get(['id', 'name', 'code']);
        $programs = Program::orderBy('name')->get(['id', 'name', 'code', 'college_id']);

        return view('students.index', compact('students', 'colleges', 'programs', 'collegeId', 'programId', 'yearLevel', 'search'));
    }

    public function create(): View
    {
        $colleges = College::where('is_active', true)->orderBy('name')->get();
        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('students.create', compact('colleges', 'programs', 'academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:50', 'unique:patients,school_id_number'],
            'college_id' => ['required', 'exists:colleges,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'year_level' => ['required', 'string', 'max:30'],
            'section' => ['nullable', 'string', 'max:50'],
            'enrollment_status' => ['required', 'string', 'max:30'],

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
            'school_id_number' => $validated['student_id'],
            'type' => 'student',
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
            'college_id' => $validated['college_id'],
            'program_id' => $validated['program_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'year_level' => $validated['year_level'],
            'section' => $validated['section'] ?? null,
            'enrollment_status' => $validated['enrollment_status'],
        ]);

        $this->auditLogger->log('created', "Registered student {$patient->full_name} ({$validated['student_id']})", $patient);

        return redirect()->route('students.index')->with('success', 'Student record registered successfully.');
    }

    public function edit(Patient $student): View
    {
        $colleges = College::where('is_active', true)->orderBy('name')->get();
        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('students.edit', compact('student', 'colleges', 'programs', 'academicYears'));
    }

    public function update(Request $request, Patient $student): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:50', Rule::unique('patients', 'school_id_number')->ignore($student->id)],
            'college_id' => ['required', 'exists:colleges,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'year_level' => ['required', 'string', 'max:30'],
            'section' => ['nullable', 'string', 'max:50'],
            'enrollment_status' => ['required', 'string', 'max:30'],

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

        $student->update([
            'school_id_number' => $validated['student_id'],
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
            'college_id' => $validated['college_id'],
            'program_id' => $validated['program_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'year_level' => $validated['year_level'],
            'section' => $validated['section'] ?? null,
            'enrollment_status' => $validated['enrollment_status'],
        ]);

        $this->auditLogger->log('updated', "Updated student profile {$student->full_name} ({$student->school_id_number})", $student);

        return redirect()->route('students.index')->with('success', 'Student record updated successfully.');
    }

    public function destroy(Patient $student): RedirectResponse
    {
        $name = $student->full_name;
        $student->delete();

        $this->auditLogger->log('deleted', "Deleted student record {$name}", $student);

        return redirect()->route('students.index')->with('success', 'Student record deleted successfully.');
    }
}
