<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinician;
use App\Models\ClinicLogbook;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\Patient;
use App\Models\Program;
use App\Services\ClinicVisitService;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClinicVisitController extends Controller implements HasMiddleware
{
    public function __construct(
        protected ClinicVisitService $visitService,
        protected SettingService $settingService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:clinic-visits.view', only: ['index', 'show', 'printSlip', 'printCertificate', 'printReferral']),
            new Middleware('permission:clinic-visits.create', only: ['create', 'store']),
            new Middleware('permission:clinic-visits.edit', only: ['edit', 'update']),
            new Middleware('permission:clinic-visits.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->input('table') === 'logbook' || $request->routeIs('clinic-logbooks.index')) {
                $logQuery = ClinicLogbook::with(['patient', 'clinicVisit', 'program', 'college'])
                    ->filter([
                        'search' => $request->input('log_search') ?: $request->input('search.value'),
                        'visitor_type' => $request->input('visitor_type'),
                        'program_id' => $request->input('program_id'),
                        'purpose' => $request->input('purpose'),
                        'status' => $request->input('log_status') ?: $request->input('status'),
                        'date_from' => $request->input('log_date_from') ?: $request->input('date_from'),
                        'date_to' => $request->input('log_date_to') ?: $request->input('date_to'),
                    ])
                    ->orderByDesc('log_date')
                    ->orderByDesc('time_in')
                    ->orderByDesc('id');

                return DataTables::eloquent($logQuery)
                    ->addColumn('log_number_col', function (ClinicLogbook $log) {
                        $num = e($log->log_number);
                        $date = $log->log_date ? $log->log_date->format($this->settingService->dateFormat()) : '—';

                        return "<div><span class=\"fw-mono text-dark fw-bold\">{$num}</span><div class=\"small text-muted\">{$date}</div></div>";
                    })
                    ->addColumn('time_col', function (ClinicLogbook $log) {
                        $timeIn = $log->time_in ? Carbon::parse($log->time_in)->format('h:i A') : '—';
                        $timeOut = $log->time_out
                            ? '<i class="bi bi-box-arrow-right text-secondary me-1"></i>'.Carbon::parse($log->time_out)->format('h:i A')
                            : '<span class="badge badge-soft-amber" style="font-size: 0.7rem;"><i class="bi bi-dot"></i> Inside</span>';

                        return "<div><div class=\"fw-medium text-dark\"><i class=\"bi bi-box-arrow-in-right text-success me-1\"></i>{$timeIn}</div><div class=\"small text-muted\">{$timeOut}</div></div>";
                    })
                    ->addColumn('visitor_col', function (ClinicLogbook $log) {
                        $name = e($log->full_name);
                        $id = e($log->id_number ?: 'N/A');
                        $contact = $log->contact_number ? ' &bull; '.e($log->contact_number) : '';

                        return "<div><div class=\"fw-semibold text-dark\">{$name}</div><div class=\"small text-muted\">ID: {$id}{$contact}</div></div>";
                    })
                    ->addColumn('type_dept_col', function (ClinicLogbook $log) {
                        $badgeClass = match ($log->visitor_type) {
                            'student' => 'badge-soft-green',
                            'faculty' => 'badge-soft-purple',
                            'staff' => 'badge-soft-blue',
                            default => 'badge-soft-gray',
                        };
                        $typeLabel = match ($log->visitor_type) {
                            'faculty' => 'Faculty',
                            'staff' => 'Staff',
                            default => ucfirst($log->visitor_type),
                        };
                        $dept = e($log->course_or_department ?: '—');
                        $pos = $log->year_or_position ? ' &bull; '.e($log->year_or_position) : '';

                        return "<div><span class=\"badge {$badgeClass}\">{$typeLabel}</span><div class=\"small text-muted mt-1\">{$dept}{$pos}</div></div>";
                    })
                    ->addColumn('purpose_col', function (ClinicLogbook $log) {
                        $purp = e($log->purpose);
                        $notes = $log->notes ? '<div class="small text-muted">'.e($log->notes).'</div>' : '';
                        $linked = '';
                        if ($log->clinicVisit) {
                            $vUrl = route('clinic-visits.show', $log->clinicVisit);
                            $vNum = e($log->clinicVisit->visit_number);
                            $vLbl = e($log->clinicVisit->type_label);
                            $linked = "<div class=\"mt-1\"><a href=\"{$vUrl}\" class=\"badge badge-soft-purple text-decoration-none\"><i class=\"bi bi-link-45deg\"></i> Linked: {$vNum} ({$vLbl})</a></div>";
                        }

                        return "<div><div class=\"fw-semibold text-dark\">{$purp}</div>{$notes}{$linked}</div>";
                    })
                    ->addColumn('status_col', function (ClinicLogbook $log) {
                        if ($log->status === 'inside') {
                            return '<span class="badge bg-warning text-dark"><i class="bi bi-person-walking me-1"></i>Inside</span>';
                        }

                        return '<span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Completed</span>';
                    })
                    ->addColumn('actions', function (ClinicLogbook $log) {
                        $token = csrf_token();
                        $items = '';

                        if (! $log->time_out) {
                            $outUrl = route('clinic-logbooks.time-out', $log);
                            $items .= "<li>
                                <form method=\"POST\" action=\"{$outUrl}\">
                                    <input type=\"hidden\" name=\"_token\" value=\"{$token}\">
                                    <button type=\"submit\" class=\"dropdown-item text-warning\"><i class=\"bi bi-box-arrow-right text-warning me-2\"></i> Time Out</button>
                                </form>
                            </li>";
                        }

                        if (! $log->clinicVisit) {
                            $createUrl = route('clinic-visits.create', ['patient_id' => $log->patient_id, 'logbook_id' => $log->id, 'complaint' => $log->purpose]);
                            $items .= "<li><a class=\"dropdown-item text-primary\" href=\"{$createUrl}\"><i class=\"bi bi-plus-circle text-primary me-2\"></i> Log Clinic Visit</a></li>";
                        } else {
                            $showUrl = route('clinic-visits.show', $log->clinicVisit);
                            $items .= "<li><a class=\"dropdown-item\" href=\"{$showUrl}\"><i class=\"bi bi-eye text-secondary me-2\"></i> View Linked Visit</a></li>";
                        }

                        $delUrl = route('clinic-logbooks.destroy', $log);
                        $num = e($log->log_number);
                        $items .= "<li><hr class=\"dropdown-divider\"></li>
                        <li>
                            <form method=\"POST\" action=\"{$delUrl}\" onsubmit=\"return confirm('Delete logbook entry {$num}?');\">
                                <input type=\"hidden\" name=\"_token\" value=\"{$token}\">
                                <input type=\"hidden\" name=\"_method\" value=\"DELETE\">
                                <button type=\"submit\" class=\"dropdown-item text-danger\"><i class=\"bi bi-trash me-2\"></i> Delete</button>
                            </form>
                        </li>";

                        return "<div class=\"dropdown\">
                            <button class=\"btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                <i class=\"bi bi-gear-fill me-1\"></i> Action
                            </button>
                            <ul class=\"dropdown-menu shadow-sm\">
                                {$items}
                            </ul>
                        </div>";
                    })
                    ->rawColumns(['log_number_col', 'time_col', 'visitor_col', 'type_dept_col', 'purpose_col', 'status_col', 'actions'])
                    ->toJson();
            }

            $query = ClinicVisit::query()
                ->with(['patient', 'clinician', 'recorder', 'medicalConsultation', 'dentalConsultation', 'emergencyRecord', 'firstAidRecord', 'physicalExamination'])
                ->filter($request->only(['visit_type', 'patient_classification', 'status', 'disposition', 'college_id', 'date_from', 'date_to', 'patient_id']))
                ->latest('visit_date');

            return $this->visitService->dataTable($query);
        }

        $stats = $this->visitService->stats();
        $activeTab = $request->query('tab', 'visits');

        $logbookFilters = $request->only(['log_search', 'visitor_type', 'program_id', 'purpose', 'log_status', 'log_date_from', 'log_date_to']);
        if (! $request->has('log_date_from') && ! $request->has('log_search')) {
            $logbookFilters['log_date_from'] = Carbon::today()->startOfMonth()->toDateString();
            $logbookFilters['log_date_to'] = Carbon::today()->toDateString();
        }

        $today = Carbon::today()->toDateString();
        $agg = ClinicLogbook::query()
            ->whereDate('log_date', $today)
            ->selectRaw('COUNT(*) as total_today')
            ->selectRaw("SUM(CASE WHEN status = 'inside' THEN 1 ELSE 0 END) as currently_inside")
            ->selectRaw("SUM(CASE WHEN visitor_type = 'student' THEN 1 ELSE 0 END) as students_today")
            ->selectRaw("SUM(CASE WHEN visitor_type IN ('faculty', 'staff', 'employee') THEN 1 ELSE 0 END) as employees_today")
            ->first();

        $logbookStats = [
            'total_today' => (int) ($agg?->total_today ?? 0),
            'currently_inside' => (int) ($agg?->currently_inside ?? 0),
            'students_today' => (int) ($agg?->students_today ?? 0),
            'employees_today' => (int) ($agg?->employees_today ?? 0),
        ];

        $todayAppointments = Appointment::with(['patient.college', 'patient.program', 'clinician', 'bookedBy', 'clinicVisit'])
            ->whereDate('appointment_date', $today)
            ->orderByRaw("CASE priority_level WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderBy('appointment_time')
            ->get();

        $appAgg = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->selectRaw('COUNT(*) as total_today')
            ->selectRaw("SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as waiting_today")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_today")
            ->selectRaw("SUM(CASE WHEN priority_level = 'urgent' AND status IN ('scheduled', 'checked_in') THEN 1 ELSE 0 END) as urgent_today")
            ->first();

        $appointmentStats = [
            'total_today' => (int) ($appAgg?->total_today ?? 0),
            'waiting_today' => (int) ($appAgg?->waiting_today ?? 0),
            'completed_today' => (int) ($appAgg?->completed_today ?? 0),
            'urgent_today' => (int) ($appAgg?->urgent_today ?? 0),
        ];

        return view('clinic-visits.index', [
            'stats' => $stats,
            'logbookStats' => $logbookStats,
            'logbookFilters' => $logbookFilters,
            'activeTab' => $activeTab,
            'todayAppointments' => $todayAppointments,
            'appointmentStats' => $appointmentStats,
            'clinicians' => Clinician::active()->orderBy('name')->get(),
            'serviceTypes' => Appointment::SERVICE_TYPES,
            'priorities' => Appointment::PRIORITIES,
            'timeSlots' => Appointment::TIME_SLOTS,
            'programs' => Program::where('is_active', true)->orderBy('id')->get(),
            'purposes' => ClinicLogbook::PURPOSES,
            'colleges' => College::orderBy('name')->get(),
            'types' => ClinicVisit::TYPES,
            'categories' => ClinicVisit::CATEGORIES,
            'statuses' => ClinicVisit::STATUSES,
            'dispositions' => ClinicVisit::DISPOSITIONS,
        ]);
    }

    public function create(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $logbookId = $request->query('logbook_id');
        $appointmentId = $request->query('appointment_id');
        $complaint = $request->query('complaint');

        $patient = $patientId ? Patient::with(['college', 'program'])->find($patientId) : null;
        $defaultClinician = Clinician::active()->where('user_id', auth()->id())->first() ?? Clinician::active()->first();

        return view('clinic-visits.create', [
            'visit' => new ClinicVisit([
                'visit_date' => now(),
                'visit_type' => $request->query('type', ClinicVisit::TYPE_GENERAL),
                'visit_category' => 'walk_in',
                'status' => 'completed',
                'chief_complaint' => $complaint ?: null,
                'clinician_id' => $defaultClinician?->id,
            ]),
            'preselectedPatient' => $patient,
            'defaultClinician' => $defaultClinician,
            'purposes' => ClinicVisit::GENERAL_PURPOSES,
            'logbookId' => $logbookId,
            'appointmentId' => $appointmentId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->visitService->visitRules(true), [
            'visit_type' => ['required', Rule::in(array_keys(ClinicVisit::TYPES))],
            'logbook_id' => ['nullable', 'exists:clinic_logbooks,id'],
        ]));

        $visit = $this->visitService->create($validated['visit_type'], $validated);

        if (! empty($validated['logbook_id'])) {
            ClinicLogbook::where('id', $validated['logbook_id'])->update([
                'clinic_visit_id' => $visit->id,
                'status' => 'completed',
                'time_out' => now()->format('H:i:s'),
            ]);
        }

        if ($request->filled('appointment_id')) {
            Appointment::where('id', $request->input('appointment_id'))->update([
                'clinic_visit_id' => $visit->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('clinic-visits.show', $visit)
            ->with('success', "Patient Visit Log {$visit->visit_number} recorded successfully.");
    }

    public function show(ClinicVisit $clinicVisit): View
    {
        $clinicVisit->load([
            'patient.college',
            'patient.program',
            'patient.medicalProfile',
            'college',
            'program',
            'academicYear',
            'clinician',
            'recorder',
            'medicalConsultation',
            'dentalConsultation',
            'emergencyRecord',
            'firstAidRecord',
            'physicalExamination',
            'medicineDispensing.items.medicine',
        ]);

        $previousVisits = ClinicVisit::where('patient_id', $clinicVisit->patient_id)
            ->where('id', '!=', $clinicVisit->id)
            ->latest('visit_date')
            ->take(5)
            ->get();

        return view('clinic-visits.show', [
            'visit' => $clinicVisit,
            'patient' => $clinicVisit->patient,
            'previousVisits' => $previousVisits,
        ]);
    }

    public function edit(ClinicVisit $clinicVisit): View
    {
        $clinicVisit->load(['patient.college', 'patient.program', 'clinician']);

        return view('clinic-visits.edit', [
            'visit' => $clinicVisit,
            'preselectedPatient' => $clinicVisit->patient,
            'defaultClinician' => $clinicVisit->clinician,
            'purposes' => ClinicVisit::GENERAL_PURPOSES,
        ]);
    }

    public function update(Request $request, ClinicVisit $clinicVisit): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->visitService->visitRules(true), [
            'visit_type' => ['required', Rule::in(array_keys(ClinicVisit::TYPES))],
        ]));

        $this->visitService->update($clinicVisit, $validated);

        return redirect()->route('clinic-visits.show', $clinicVisit)
            ->with('success', "Visit Log {$clinicVisit->visit_number} updated successfully.");
    }

    public function destroy(ClinicVisit $clinicVisit): RedirectResponse
    {
        $number = $clinicVisit->visit_number;
        $this->visitService->delete($clinicVisit);

        return redirect()->route('clinic-visits.index')
            ->with('success', "Visit log {$number} removed.");
    }

    public function printSlip(Request $request, ClinicVisit $clinicVisit, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $clinicVisit->load([
            'patient.college',
            'patient.program',
            'clinician',
            'recorder',
            'medicalConsultation',
            'dentalConsultation',
            'emergencyRecord',
            'firstAidRecord',
            'physicalExamination',
            'medicineDispensing.items.medicine',
        ]);

        if ($request->query('format') === 'html') {
            return view('clinic-visits.print', [
                'visit' => $clinicVisit,
                'patient' => $clinicVisit->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->clinicVisitSlip($clinicVisit, $settingService->all(), $clinicVisit->clinician);
        $filename = "VisitSlip-{$clinicVisit->visit_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printCertificate(Request $request, ClinicVisit $clinicVisit, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $clinicVisit->load(['patient.college', 'patient.program', 'clinician', 'medicalConsultation']);

        $pdf = $pdfService->medicalCertificateFromVisit($clinicVisit, $settingService->all());
        $filename = "Medical-Certificate-{$clinicVisit->visit_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printReferral(Request $request, ClinicVisit $clinicVisit, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $clinicVisit->load(['patient.college', 'patient.program', 'clinician', 'medicalConsultation', 'emergencyRecord']);

        $pdf = $pdfService->medicalReferralFromVisit($clinicVisit, $settingService->all());
        $filename = "Medical-Referral-{$clinicVisit->visit_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
