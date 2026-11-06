<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\College;
use App\Models\Patient;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

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

    public function index(Request $request): View|JsonResponse
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $collegeId = $request->query('college_id');
        $search = $request->query('search');

        if ($request->ajax()) {
            $canEdit = $request->user()?->can('patients.edit') ?? false;
            $canDelete = $request->user()?->can('patients.delete') ?? false;

            $query = Patient::query()
                ->select([
                    'id',
                    'patient_number',
                    'type',
                    'school_id_number',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                    'gender',
                    'date_of_birth',
                    'email',
                    'phone',
                    'college_id',
                    'program_id',
                    'office_department',
                    'status',
                    'created_at',
                ])
                ->with(['college:id,code,name', 'program:id,code,name'])
                ->when($type, fn (Builder $q) => $q->where('type', $type))
                ->when($status, fn (Builder $q) => $q->where('status', $status))
                ->when($collegeId, fn (Builder $q) => $q->where('college_id', $collegeId));

            return DataTables::eloquent($query)
                ->filter(function (Builder $q) use ($request): void {
                    $term = $request->input('custom_search') ?: $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $q->search(trim($term));
                    }
                })
                ->addColumn('patient_number_col', fn (Patient $p): string => '<span class="font-monospace fw-bold text-dark">'.e($p->patient_number).'</span>')
                ->addColumn('patient_name_col', function (Patient $p): string {
                    $initials = e($p->initials());
                    $url = route('patients.show', $p);
                    $name = e($p->full_name);
                    $sub = e($p->email ?: ($p->phone ?: 'N/A'));

                    return "<div class=\"d-flex align-items-center gap-2\">
                        <span class=\"avatar-initials\" style=\"width: 28px; height: 28px; font-size: 0.7rem;\">{$initials}</span>
                        <div>
                            <a href=\"{$url}\" class=\"fw-semibold text-dark text-decoration-none\">{$name}</a>
                            <small class=\"text-muted d-block\" style=\"font-size: 0.72rem;\">{$sub}</small>
                        </div>
                    </div>";
                })
                ->addColumn('classification_col', function (Patient $p): string {
                    if ($p->type === 'student') {
                        return '<span class="badge badge-pill badge-soft-blue"><i class="bi bi-mortarboard me-1"></i> Student</span>';
                    }
                    if ($p->type === 'employee') {
                        return '<span class="badge badge-pill badge-soft-green"><i class="bi bi-person-badge me-1"></i> Employee</span>';
                    }

                    return '<span class="badge badge-pill badge-soft-gray">Other</span>';
                })
                ->addColumn('identifier_col', fn (Patient $p): string => '<span class="font-monospace fw-semibold">'.e($p->primary_identifier ?: 'N/A').'</span>')
                ->addColumn('college_unit_col', function (Patient $p): string {
                    if ($p->type === 'student') {
                        if ($p->college || $p->program) {
                            $colCode = e($p->college?->code ?? 'N/A');
                            $progCode = e($p->program?->code ?? 'N/A');

                            return "<span class=\"badge badge-pill badge-soft-gray\">{$colCode}</span><small class=\"text-muted ms-1\">{$progCode}</small>";
                        }

                        return '<span class="badge badge-soft-gray">N/A</span>';
                    }
                    if ($p->type === 'employee') {
                        $unit = $p->office_department ?: $p->college?->name;

                        return $unit
                            ? '<small class="text-dark fw-medium">'.e($unit).'</small>'
                            : '<span class="badge badge-soft-gray">N/A</span>';
                    }

                    return '<span class="badge badge-soft-gray">N/A</span>';
                })
                ->addColumn('gender_age_col', function (Patient $p): string {
                    $gender = e($p->gender);
                    $age = $p->age ?? 'N/A';

                    return "<span class=\"text-dark\">{$gender}</span>, <small class=\"text-muted\">{$age} y/o</small>";
                })
                ->addColumn('status_col', function (Patient $p): string {
                    $cls = $p->status === 'active' ? 'completed' : 'pending';

                    return '<span class="status-badge '.$cls.'">'.e($p->status).'</span>';
                })
                ->addColumn('actions', function (Patient $p) use ($canEdit, $canDelete): string {
                    $showUrl = route('patients.show', $p);
                    $medProfileUrl = route('medical-profiles.show', $p);
                    $newVisitUrl = route('clinic-visits.create', ['patient_id' => $p->getRouteKey()]);
                    $newPeUrl = route('physical-examinations.create', ['patient_id' => $p->getRouteKey()]);

                    $items = "<li><a class=\"dropdown-item\" href=\"{$showUrl}\"><i class=\"bi bi-person-vcard text-primary me-2\"></i> View Profile</a></li>";
                    $items .= "<li><a class=\"dropdown-item\" href=\"{$medProfileUrl}\"><i class=\"bi bi-journal-medical text-info me-2\"></i> Medical Profile</a></li>";

                    if ($canEdit) {
                        $editUrl = route('patients.edit', $p);
                        $items .= "<li><a class=\"dropdown-item\" href=\"{$editUrl}\"><i class=\"bi bi-pencil text-warning me-2\"></i> Edit Record</a></li>";
                    }

                    $items .= '<li><hr class="dropdown-divider"></li>';
                    $items .= "<li><a class=\"dropdown-item\" href=\"{$newVisitUrl}\"><i class=\"bi bi-clipboard2-pulse text-success me-2\"></i> Log Clinic Visit</a></li>";
                    $items .= "<li><a class=\"dropdown-item\" href=\"{$newPeUrl}\"><i class=\"bi bi-heart-pulse text-danger me-2\"></i> New Physical Exam</a></li>";

                    if ($canDelete) {
                        $deleteUrl = route('patients.destroy', $p);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $name = e($p->full_name);
                        $items .= "<li><hr class=\"dropdown-divider\"></li>
                        <li>
                            <form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"Are you sure you want to delete patient record for {$name}?\">
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
                ->orderColumn('patient_number_col', 'patient_number $1')
                ->orderColumn('patient_name_col', 'last_name $1')
                ->orderColumn('identifier_col', 'school_id_number $1')
                ->orderColumn('status_col', 'status $1')
                ->rawColumns(['patient_number_col', 'patient_name_col', 'classification_col', 'identifier_col', 'college_unit_col', 'gender_age_col', 'status_col', 'actions'])
                ->make(true);
        }

        $agg = Patient::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'student' THEN 1 ELSE 0 END) as students")
            ->selectRaw("SUM(CASE WHEN type = 'employee' THEN 1 ELSE 0 END) as employees")
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();

        $stats = [
            'total' => (int) ($agg?->total ?? 0),
            'students' => (int) ($agg?->students ?? 0),
            'employees' => (int) ($agg?->employees ?? 0),
            'active' => (int) ($agg?->active ?? 0),
        ];

        $colleges = College::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('patients.index', compact('stats', 'colleges', 'type', 'status', 'collegeId', 'search'));
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
