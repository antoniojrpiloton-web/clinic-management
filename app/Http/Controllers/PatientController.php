<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\College;
use App\Models\Patient;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:patients.view', only: ['index', 'show', 'checkDuplicate']),
            new Middleware('permission:patients.create', only: ['create', 'store']),
            new Middleware('permission:patients.edit', only: ['edit', 'update']),
            new Middleware('permission:patients.import', only: ['importForm', 'importProcess']),
            new Middleware('permission:patients.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Patient::query()
            ->with(['college', 'program', 'academicYear'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->search($search))
            ->latest();

        $patients = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Patient::count(),
            'students' => Patient::students()->count(),
            'employees' => Patient::employees()->count(),
            'active' => Patient::active()->count(),
        ];

        return view('patients.index', compact('patients', 'stats', 'type', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        $defaultType = $request->query('type', 'student');
        $colleges = College::where('is_active', true)->orderBy('name')->get();
        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('patients.create', compact('defaultType', 'colleges', 'programs', 'academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'student');

        $rules = [
            'type' => ['required', 'in:student,employee'],
            'school_id_number' => ['required', 'string', 'max:50', 'unique:patients,school_id_number'],
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
            'status' => ['nullable', 'in:active,inactive,archived'],
        ];

        if ($type === 'student') {
            $rules += [
                'college_id' => ['required', 'exists:colleges,id'],
                'program_id' => ['required', 'exists:programs,id'],
                'academic_year_id' => ['nullable', 'exists:academic_years,id'],
                'year_level' => ['required', 'string', 'max:30'],
                'section' => ['nullable', 'string', 'max:50'],
                'enrollment_status' => ['required', 'string', 'max:30'],
            ];
        } else {
            $rules += [
                'college_id' => ['nullable', 'exists:colleges,id'],
                'office_department' => ['nullable', 'string', 'max:120'],
                'employment_classification' => ['required', 'string', 'max:50'],
                'category' => ['required', 'in:Faculty,Non-Teaching / Staff'],
                'position' => ['required', 'string', 'max:120'],
                'employment_status' => ['required', 'string', 'max:30'],
            ];
        }

        $validated = $request->validate($rules);

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

        $validated['patient_number'] = Patient::generatePatientNumber();
        $validated['status'] = $validated['status'] ?? 'active';

        $patient = Patient::create($validated);

        $this->auditLogger->log('created', "Added new patient {$patient->full_name} ({$patient->primary_identifier})", $patient);

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient record for {$patient->full_name} registered successfully.");
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'college',
            'program',
            'academicYear',
            'medicalProfile',
            'physicalExaminations',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $colleges = College::where('is_active', true)->orderBy('name')->get();
        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('patients.edit', compact('patient', 'colleges', 'programs', 'academicYears'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $type = $request->input('type', $patient->type);

        $rules = [
            'type' => ['required', 'in:student,employee'],
            'school_id_number' => ['required', 'string', 'max:50', Rule::unique('patients', 'school_id_number')->ignore($patient->id)],
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
            'status' => ['required', 'in:active,inactive,archived'],
        ];

        if ($type === 'student') {
            $rules += [
                'college_id' => ['required', 'exists:colleges,id'],
                'program_id' => ['required', 'exists:programs,id'],
                'academic_year_id' => ['nullable', 'exists:academic_years,id'],
                'year_level' => ['required', 'string', 'max:30'],
                'section' => ['nullable', 'string', 'max:50'],
                'enrollment_status' => ['required', 'string', 'max:30'],
            ];
        } else {
            $rules += [
                'college_id' => ['nullable', 'exists:colleges,id'],
                'office_department' => ['nullable', 'string', 'max:120'],
                'employment_classification' => ['required', 'string', 'max:50'],
                'category' => ['required', 'in:Faculty,Non-Teaching / Staff'],
                'position' => ['required', 'string', 'max:120'],
                'employment_status' => ['required', 'string', 'max:30'],
            ];
        }

        $validated = $request->validate($rules);

        $duplicate = Patient::findDuplicate(
            $validated['first_name'],
            $validated['last_name'],
            $validated['date_of_birth'],
            $patient->id
        );

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'first_name' => "A patient record already exists for {$duplicate->full_name} born on {$duplicate->date_of_birth->format('M d, Y')}.",
            ]);
        }

        $patient->update($validated);

        $this->auditLogger->log('updated', "Updated patient record {$patient->full_name}", $patient);

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient record for {$patient->full_name} updated successfully.");
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $firstName = (string) $request->query('first_name');
        $lastName = (string) $request->query('last_name');
        $dob = (string) $request->query('date_of_birth');
        $exceptId = $request->query('except_id') ? (int) $request->query('except_id') : null;

        if (! $firstName || ! $lastName || ! $dob) {
            return response()->json(['found' => false]);
        }

        $duplicate = Patient::findDuplicate($firstName, $lastName, $dob, $exceptId);

        if ($duplicate) {
            return response()->json([
                'found' => true,
                'patient' => [
                    'id' => $duplicate->id,
                    'patient_number' => $duplicate->patient_number,
                    'name' => $duplicate->full_name,
                    'type' => ucfirst($duplicate->type),
                    'identifier' => $duplicate->primary_identifier,
                    'url' => route('patients.show', $duplicate),
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function importForm(): View
    {
        return view('patients.import');
    }

    public function importProcess(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'type' => 'required|in:student,employee',
        ]);

        $file = $request->file('csv_file');
        $type = $request->input('type');

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to open the uploaded file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->with('error', 'The file appears to be empty.');
        }

        $header = array_map(fn ($col) => strtolower(trim(str_replace([' ', '_', '-'], '', $col))), $header);

        $imported = 0;
        $duplicates = 0;
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($header, array_slice($row, 0, count($header)));

            $firstName = $data['firstname'] ?? $data['first'] ?? '';
            $lastName = $data['lastname'] ?? $data['last'] ?? '';
            $dob = $data['dateofbirth'] ?? $data['dob'] ?? $data['birthdate'] ?? '';

            if (! $firstName || ! $lastName || ! $dob) {
                continue;
            }

            $formattedDob = rescue(fn () => date('Y-m-d', strtotime($dob)), null);
            if (! $formattedDob) {
                continue;
            }

            if (Patient::findDuplicate($firstName, $lastName, $formattedDob)) {
                $duplicates++;

                continue;
            }

            $record = [
                'patient_number' => Patient::generatePatientNumber(),
                'type' => $type,
                'first_name' => $firstName,
                'middle_name' => $data['middlename'] ?? $data['middle'] ?? null,
                'last_name' => $lastName,
                'gender' => ucfirst($data['gender'] ?? 'Other'),
                'date_of_birth' => $formattedDob,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? $data['contact'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact_name' => $data['emergencyname'] ?? null,
                'emergency_contact_phone' => $data['emergencyphone'] ?? null,
                'blood_type' => $data['bloodtype'] ?? null,
                'status' => 'active',
            ];

            if ($type === 'student') {
                $college = College::where('code', $data['college'] ?? '')->first() ?? College::first();
                $program = Program::where('code', $data['program'] ?? '')->first() ?? Program::first();

                $record['school_id_number'] = $data['studentid'] ?? $data['idnumber'] ?? sprintf('STU-%d-%04d', now()->year, rand(1000, 9999));
                $record['college_id'] = $college?->id;
                $record['program_id'] = $program?->id;
                $record['year_level'] = $data['yearlevel'] ?? '1st Year';
                $record['section'] = $data['section'] ?? null;
                $record['enrollment_status'] = 'enrolled';
            } else {
                $record['school_id_number'] = $data['employeeid'] ?? $data['idnumber'] ?? sprintf('EMP-%d-%04d', now()->year, rand(1000, 9999));
                $record['office_department'] = $data['department'] ?? $data['office'] ?? 'General Services';
                $record['employment_classification'] = $data['classification'] ?? 'Permanent';
                $record['category'] = $data['category'] ?? 'Faculty';
                $record['position'] = $data['position'] ?? 'Staff';
                $record['employment_status'] = 'active';
            }

            Patient::create($record);
            $imported++;
        }

        fclose($handle);

        $this->auditLogger->log('imported', "Imported {$imported} {$type} records via batch file", properties: [
            'imported' => $imported,
            'duplicates_skipped' => $duplicates,
        ]);

        return redirect()->route('patients.index')->with('success', "Batch import completed: {$imported} records created, {$duplicates} duplicates skipped.");
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->full_name;
        $patient->delete();

        $this->auditLogger->log('deleted', "Deleted patient record {$name}", $patient);

        return redirect()->route('patients.index')->with('success', 'Patient record deleted successfully.');
    }
}
