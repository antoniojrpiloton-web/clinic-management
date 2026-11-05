<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Clinician;
use App\Models\College;
use App\Models\Patient;
use App\Models\PhysicalExamSchedule;
use App\Models\PhysicalExamScheduleAttendee;
use App\Models\Program;
use App\Services\AuditLogger;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PhysicalExamScheduleController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:physical-examinations.view', only: ['index', 'show', 'printRoster']),
            new Middleware('permission:physical-examinations.create', only: ['create', 'store', 'addAttendee', 'populateAttendees']),
            new Middleware('permission:physical-examinations.edit', only: ['edit', 'update', 'updateAttendeeStatus']),
            new Middleware('permission:physical-examinations.delete', only: ['destroy', 'removeAttendee']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = PhysicalExamSchedule::query()
                ->with(['academicYear', 'college', 'program', 'clinician'])
                ->withCount([
                    'attendees as total_attendees',
                    'attendees as attended_count' => fn ($q) => $q->where('status', 'attended'),
                    'attendees as pending_count' => fn ($q) => $q->where('status', 'pending'),
                ])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('target_classification'), fn ($q) => $q->where('target_classification', $request->target_classification))
                ->when($request->filled('college_id'), fn ($q) => $q->where('college_id', $request->college_id))
                ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
                ->when($request->filled('date_from'), fn ($q) => $q->whereDate('scheduled_date', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn ($q) => $q->whereDate('scheduled_date', '<=', $request->date_to))
                ->latest('scheduled_date');

            return DataTables::eloquent($query)
                ->addColumn('schedule_card', function (PhysicalExamSchedule $s) {
                    $url = route('physical-exam-schedules.show', $s);
                    $num = e($s->schedule_number);
                    $title = e($s->title);
                    $purpose = e($s->purpose);

                    return "<div>
                        <a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$title}</a>
                        <div class=\"small text-muted d-flex align-items-center gap-1 mt-1\">
                            <span class=\"badge badge-soft-gray font-monospace\" style=\"font-size: 0.72rem;\">{$num}</span>
                            <span>{$purpose}</span>
                        </div>
                    </div>";
                })
                ->addColumn('target_group', fn (PhysicalExamSchedule $s) => '<span class="small text-dark fw-medium">'.e($s->target_group_label).'</span>')
                ->addColumn('schedule_timing', function (PhysicalExamSchedule $s) {
                    $date = $s->scheduled_date->format('M d, Y');
                    $time = e($s->time_range);
                    $venue = e($s->venue ?: 'Clinic Examination Room');

                    return "<div class=\"small\">
                        <div class=\"fw-semibold text-dark\">{$date}</div>
                        <div class=\"text-muted\">{$time} • {$venue}</div>
                    </div>";
                })
                ->addColumn('progress_summary', function (PhysicalExamSchedule $s) {
                    $total = $s->total_attendees;
                    $attended = $s->attended_count;
                    $percent = $total > 0 ? round(($attended / $total) * 100) : 0;

                    return "<div class=\"small\" style=\"min-width: 140px;\">
                        <div class=\"d-flex justify-content-between mb-1\">
                            <span class=\"fw-bold text-dark\">{$attended} / {$total}</span>
                            <span class=\"text-muted\">{$percent}%</span>
                        </div>
                        <div class=\"progress\" style=\"height: 6px;\">
                            <div class=\"progress-bar bg-success\" role=\"progressbar\" style=\"width: {$percent}%\"></div>
                        </div>
                    </div>";
                })
                ->addColumn('status_badge', fn (PhysicalExamSchedule $s) => "<span class=\"badge {$s->status_badge}\">".e($s->status_label).'</span>')
                ->addColumn('actions', function (PhysicalExamSchedule $s) {
                    $showUrl = route('physical-exam-schedules.show', $s);
                    $editUrl = route('physical-exam-schedules.edit', $s);
                    $deleteUrl = route('physical-exam-schedules.destroy', $s);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    return "<div class=\"d-flex align-items-center justify-content-end gap-1\">
                        <a href=\"{$showUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"View Attendees & Check-in\">
                            <i class=\"bi bi-people\"></i>
                        </a>
                        <a href=\"{$editUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"Edit Schedule\">
                            <i class=\"bi bi-pencil\"></i>
                        </a>
                        <form method=\"POST\" action=\"{$deleteUrl}\" class=\"d-inline\" data-confirm=\"Delete schedule {$s->schedule_number}?\">
                            {$csrf}
                            {$method}
                            <button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1 px-2\" title=\"Delete\">
                                <i class=\"bi bi-trash\"></i>
                            </button>
                        </form>
                    </div>";
                })
                ->rawColumns(['schedule_card', 'target_group', 'schedule_timing', 'progress_summary', 'status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => PhysicalExamSchedule::count(),
            'upcoming' => PhysicalExamSchedule::upcoming()->count(),
            'ongoing' => PhysicalExamSchedule::where('status', 'ongoing')->count(),
            'completed' => PhysicalExamSchedule::where('status', 'completed')->count(),
        ];

        return view('physical-exam-schedules.index', [
            'stats' => $stats,
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'colleges' => College::orderBy('name')->get(),
            'statuses' => PhysicalExamSchedule::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('physical-exam-schedules.create', [
            'schedule' => new PhysicalExamSchedule([
                'scheduled_date' => now()->addDays(2),
                'start_time' => '08:00',
                'end_time' => '17:00',
                'status' => 'scheduled',
                'purpose' => 'Annual Physical Exam',
                'target_classification' => 'student',
                'capacity' => 50,
            ]),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'colleges' => College::with('programs')->orderBy('name')->get(),
            'programs' => Program::orderBy('name')->get(),
            'clinicians' => Clinician::active()->orderBy('name')->get(),
            'purposes' => [
                'Annual Physical Exam',
                'Entrance Examination',
                'OJT Medical Clearance',
                'Sports Clearance',
                'Pre-Employment',
                'Periodic Employee Health Screening',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:80'],
            'target_classification' => ['required', Rule::in(['student', 'employee'])],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'year_level' => ['nullable', 'string', 'max:30'],
            'office_department' => ['nullable', 'string', 'max:120'],
            'scheduled_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'venue' => ['nullable', 'string', 'max:150'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'clinician_id' => ['nullable', 'exists:clinicians,id'],
            'status' => ['required', Rule::in(array_keys(PhysicalExamSchedule::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'auto_populate_attendees' => ['nullable', 'boolean'],
        ]);

        $validated['schedule_number'] = PhysicalExamSchedule::generateScheduleNumber();
        $validated['created_by'] = auth()->id();

        $schedule = PhysicalExamSchedule::create($validated);

        if ($request->boolean('auto_populate_attendees')) {
            $this->populateAttendeesFromFilters($schedule);
        }

        $this->auditLogger->log('created', "Created physical exam schedule {$schedule->schedule_number}: {$schedule->title}", $schedule);

        return redirect()->route('physical-exam-schedules.show', $schedule)
            ->with('success', "Schedule {$schedule->schedule_number} created successfully.");
    }

    public function show(PhysicalExamSchedule $physicalExamSchedule): View
    {
        $physicalExamSchedule->load([
            'academicYear',
            'college',
            'program',
            'clinician',
            'creator',
            'attendees.patient.college',
            'attendees.patient.program',
            'attendees.physicalExamination',
        ]);

        $attendees = $physicalExamSchedule->attendees()->with(['patient.college', 'patient.program', 'physicalExamination'])->get();

        return view('physical-exam-schedules.show', [
            'schedule' => $physicalExamSchedule,
            'attendees' => $attendees,
        ]);
    }

    public function edit(PhysicalExamSchedule $physicalExamSchedule): View
    {
        return view('physical-exam-schedules.edit', [
            'schedule' => $physicalExamSchedule,
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'colleges' => College::with('programs')->orderBy('name')->get(),
            'programs' => Program::orderBy('name')->get(),
            'clinicians' => Clinician::active()->orderBy('name')->get(),
            'purposes' => [
                'Annual Physical Exam',
                'Entrance Examination',
                'OJT Medical Clearance',
                'Sports Clearance',
                'Pre-Employment',
                'Periodic Employee Health Screening',
            ],
        ]);
    }

    public function update(Request $request, PhysicalExamSchedule $physicalExamSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:80'],
            'target_classification' => ['required', Rule::in(['student', 'employee'])],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'year_level' => ['nullable', 'string', 'max:30'],
            'office_department' => ['nullable', 'string', 'max:120'],
            'scheduled_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'venue' => ['nullable', 'string', 'max:150'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'clinician_id' => ['nullable', 'exists:clinicians,id'],
            'status' => ['required', Rule::in(array_keys(PhysicalExamSchedule::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $physicalExamSchedule->update($validated);

        $this->auditLogger->log('updated', "Updated physical exam schedule {$physicalExamSchedule->schedule_number}", $physicalExamSchedule);

        return redirect()->route('physical-exam-schedules.show', $physicalExamSchedule)
            ->with('success', "Schedule {$physicalExamSchedule->schedule_number} updated successfully.");
    }

    public function destroy(PhysicalExamSchedule $physicalExamSchedule): RedirectResponse
    {
        $num = $physicalExamSchedule->schedule_number;
        $physicalExamSchedule->delete();

        $this->auditLogger->log('deleted', "Deleted physical exam schedule {$num}", $physicalExamSchedule);

        return redirect()->route('physical-exam-schedules.index')
            ->with('success', "Schedule {$num} removed.");
    }

    public function addAttendee(Request $request, PhysicalExamSchedule $physicalExamSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
        ]);

        $exists = $physicalExamSchedule->attendees()->where('patient_id', $validated['patient_id'])->exists();
        if ($exists) {
            return back()->with('info', 'Patient is already on this schedule attendee roster.');
        }

        $physicalExamSchedule->attendees()->create([
            'patient_id' => $validated['patient_id'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Patient added to physical exam schedule roster.');
    }

    public function populateAttendees(PhysicalExamSchedule $physicalExamSchedule): RedirectResponse
    {
        $count = $this->populateAttendeesFromFilters($physicalExamSchedule);

        return back()->with('success', "Added {$count} eligible patients to the attendee roster based on schedule target filters.");
    }

    public function updateAttendeeStatus(Request $request, PhysicalExamSchedule $physicalExamSchedule, PhysicalExamScheduleAttendee $attendee): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'attended', 'no_show'])],
        ]);

        $attendee->update([
            'status' => $validated['status'],
            'attended_at' => $validated['status'] === 'attended' ? now() : null,
        ]);

        return back()->with('success', "Attendee {$attendee->patient->full_name} status updated to {$attendee->status_label}.");
    }

    public function removeAttendee(PhysicalExamSchedule $physicalExamSchedule, PhysicalExamScheduleAttendee $attendee): RedirectResponse
    {
        $attendee->delete();

        return back()->with('success', 'Patient removed from schedule roster.');
    }

    public function printRoster(Request $request, PhysicalExamSchedule $physicalExamSchedule, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $physicalExamSchedule->load([
            'academicYear',
            'college',
            'program',
            'clinician',
            'attendees.patient.college',
            'attendees.patient.program',
            'attendees.physicalExamination',
        ]);

        if ($request->query('format') === 'html') {
            return view('physical-exam-schedules.print', [
                'schedule' => $physicalExamSchedule,
                'attendees' => $physicalExamSchedule->attendees,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->physicalExamScheduleRoster($physicalExamSchedule, $settingService->all());
        $filename = "Roster-{$physicalExamSchedule->schedule_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    protected function populateAttendeesFromFilters(PhysicalExamSchedule $schedule): int
    {
        $query = Patient::active()->where('type', $schedule->target_classification);

        if ($schedule->college_id) {
            $query->where('college_id', $schedule->college_id);
        }
        if ($schedule->program_id) {
            $query->where('program_id', $schedule->program_id);
        }
        if ($schedule->year_level) {
            $query->where('year_level', $schedule->year_level);
        }
        if ($schedule->office_department) {
            $query->where('office_department', $schedule->office_department);
        }

        $existingIds = $schedule->attendees()->pluck('patient_id')->all();
        $candidates = $query->whereNotIn('id', $existingIds);

        if ($schedule->capacity && $schedule->capacity > count($existingIds)) {
            $candidates->take($schedule->capacity - count($existingIds));
        }

        $patients = $candidates->get();
        $added = 0;

        foreach ($patients as $p) {
            $schedule->attendees()->create([
                'patient_id' => $p->id,
                'status' => 'pending',
            ]);
            $added++;
        }

        return $added;
    }
}
