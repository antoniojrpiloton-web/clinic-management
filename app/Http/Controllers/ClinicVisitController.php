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

class ClinicVisitController extends Controller implements HasMiddleware
{
    public function __construct(protected ClinicVisitService $visitService) {}

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
        if ($request->ajax() && ! $request->has('tab')) {
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

        $logs = ClinicLogbook::with(['patient', 'clinicVisit', 'program', 'college'])
            ->filter([
                'search' => $logbookFilters['log_search'] ?? null,
                'visitor_type' => $logbookFilters['visitor_type'] ?? null,
                'program_id' => $logbookFilters['program_id'] ?? null,
                'purpose' => $logbookFilters['purpose'] ?? null,
                'status' => $logbookFilters['log_status'] ?? null,
                'date_from' => $logbookFilters['log_date_from'] ?? null,
                'date_to' => $logbookFilters['log_date_to'] ?? null,
            ])
            ->orderByDesc('log_date')
            ->orderByDesc('time_in')
            ->orderByDesc('id')
            ->paginate(25, ['*'], 'log_page')
            ->withQueryString();

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
            'logs' => $logs,
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
