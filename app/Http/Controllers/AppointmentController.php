<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Appointment;
use App\Models\Clinician;
use App\Models\Patient;
use App\Models\Program;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AppointmentController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    public function index(Request $request): View|JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $clinicianId = $request->query('clinician_id');
        $serviceType = $request->query('service_type');
        $status = $request->query('status');
        $priority = $request->query('priority');

        if ($request->ajax()) {
            $query = Appointment::query()
                ->with(['patient.college', 'patient.program', 'clinician', 'bookedBy', 'clinicVisit'])
                ->when($date !== 'all' && filled($date), fn ($q) => $q->whereDate('appointment_date', $date))
                ->when($clinicianId, fn ($q) => $q->where('clinician_id', $clinicianId))
                ->when($serviceType, fn ($q) => $q->where('service_type', $serviceType))
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($priority, fn ($q) => $q->where('priority_level', $priority))
                ->orderByRaw("CASE priority_level WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
                ->orderBy('appointment_time');

            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    $term = $request->input('custom_search') ?: $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $term = trim($term);
                        $q->where(function ($sub) use ($term) {
                            $sub->where('appointment_number', 'like', "%{$term}%")
                                ->orWhere('reason', 'like', "%{$term}%")
                                ->orWhereHas('patient', function ($p) use ($term) {
                                    $p->search($term);
                                });
                        });
                    }
                })
                ->addColumn('time_ref_col', function (Appointment $app) {
                    $dateFormat = $this->settingService->dateFormat();
                    $time = e($app->appointment_time);
                    $num = e($app->appointment_number);
                    $dateStr = $app->appointment_date ? $app->appointment_date->format($dateFormat) : '—';

                    return "<div><div class=\"fw-bold text-dark\"><i class=\"bi bi-clock me-1 text-primary\"></i>{$time}</div><div class=\"fw-mono small text-muted\">{$num} &bull; {$dateStr}</div></div>";
                })
                ->addColumn('priority_col', function (Appointment $app) {
                    $cls = $app->priority_badge_class;
                    $icon = $app->priority_level === 'urgent' ? '<i class="bi bi-exclamation-triangle-fill me-1"></i>' : '';
                    $lbl = ucfirst($app->priority_level);

                    return "<span class=\"badge {$cls}\">{$icon}{$lbl}</span>";
                })
                ->addColumn('patient_col', function (Appointment $app) {
                    $p = $app->patient;
                    if (! $p) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    $name = e($p->full_name);
                    $id = e($p->primary_identifier);
                    $typeBadge = $p->type === 'employee' ? ($p->category === 'Faculty' ? 'Faculty' : 'Staff') : ($p->type === 'visitor' ? 'Visitor' : 'Student');
                    $progCode = $p->program ? ' &bull; '.e($p->program->code) : '';
                    $url = route('patients.show', $p);

                    return "<div><a href=\"{$url}\" class=\"fw-semibold text-dark text-decoration-none\">{$name}</a><div class=\"small text-muted\">{$id} &bull; <span class=\"badge badge-soft-blue\">{$typeBadge}</span>{$progCode}</div></div>";
                })
                ->addColumn('doctor_col', function (Appointment $app) {
                    if ($app->clinician) {
                        $name = e($app->clinician->name);
                        $title = e($app->clinician->title);
                        $type = e($app->clinician->type);

                        return "<div><div class=\"fw-medium text-dark\">{$name}</div><div class=\"small text-muted\">{$title} ({$type})</div></div>";
                    }

                    return '<span class="text-muted fst-italic">Any Available Doctor</span>';
                })
                ->addColumn('reason_col', function (Appointment $app) {
                    $reason = e($app->reason);
                    $types = Appointment::SERVICE_TYPES;
                    $stLabel = e($types[$app->service_type] ?? ucfirst($app->service_type));
                    $notes = $app->notes ? '<div class="small text-muted mt-1">'.e(Str::limit($app->notes, 60)).'</div>' : '';

                    return "<div><div class=\"fw-semibold text-dark\">{$reason}</div><span class=\"badge badge-soft-blue\" style=\"font-size: 0.7rem;\">{$stLabel}</span>{$notes}</div>";
                })
                ->addColumn('status_col', function (Appointment $app) {
                    $cls = $app->status_badge_class;
                    $statuses = Appointment::STATUSES;
                    $lbl = e($statuses[$app->status] ?? ucfirst($app->status));
                    $html = "<span class=\"badge {$cls}\">{$lbl}</span>";
                    if ($app->clinicVisit) {
                        $url = route('clinic-visits.show', $app->clinicVisit);
                        $vnum = e($app->clinicVisit->visit_number);
                        $html .= "<div class=\"mt-1\"><a href=\"{$url}\" class=\"badge badge-soft-purple text-decoration-none\"><i class=\"bi bi-link-45deg\"></i> {$vnum}</a></div>";
                    }

                    return $html;
                })
                ->addColumn('actions', function (Appointment $app) {
                    $token = csrf_token();
                    $items = '';

                    if ($app->status === 'scheduled') {
                        $checkInUrl = route('appointments.check-in', $app);
                        $items .= "<li>
                            <form method=\"POST\" action=\"{$checkInUrl}\">
                                <input type=\"hidden\" name=\"_token\" value=\"{$token}\">
                                <button type=\"submit\" class=\"dropdown-item text-primary\"><i class=\"bi bi-check2-circle text-primary me-2\"></i> Check In</button>
                            </form>
                        </li>";
                    }

                    if (in_array($app->status, ['scheduled', 'checked_in'])) {
                        $startUrl = route('appointments.start-consultation', $app);
                        $items .= "<li>
                            <form method=\"POST\" action=\"{$startUrl}\">
                                <input type=\"hidden\" name=\"_token\" value=\"{$token}\">
                                <button type=\"submit\" class=\"dropdown-item text-success\"><i class=\"bi bi-clipboard2-pulse text-success me-2\"></i> Start Consultation</button>
                            </form>
                        </li>";

                        $cancelUrl = route('appointments.cancel', $app);
                        $ref = e($app->appointment_number);
                        $items .= "<li><hr class=\"dropdown-divider\"></li>
                        <li>
                            <button type=\"button\" class=\"dropdown-item text-danger btn-open-cancel-modal\" data-action=\"{$cancelUrl}\" data-ref=\"{$ref}\">
                                <i class=\"bi bi-x-circle text-danger me-2\"></i> Cancel Appointment
                            </button>
                        </li>";
                    }

                    if ($app->status === 'completed' && $app->clinicVisit) {
                        $vUrl = route('clinic-visits.show', $app->clinicVisit);
                        $items .= "<li><a class=\"dropdown-item\" href=\"{$vUrl}\"><i class=\"bi bi-eye text-secondary me-2\"></i> View Visit Record</a></li>";
                    }

                    if ($app->patient) {
                        $pUrl = route('patients.show', $app->patient);
                        if ($items !== '') {
                            $items .= '<li><hr class="dropdown-divider"></li>';
                        }
                        $items .= "<li><a class=\"dropdown-item\" href=\"{$pUrl}\"><i class=\"bi bi-person-vcard text-info me-2\"></i> Patient Profile</a></li>";
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
                ->rawColumns(['time_ref_col', 'priority_col', 'patient_col', 'doctor_col', 'reason_col', 'status_col', 'actions'])
                ->toJson();
        }

        $today = Carbon::today()->toDateString();
        $todayCounts = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->selectRaw('COUNT(*) as total_today')
            ->selectRaw("SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as waiting_today")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_today")
            ->selectRaw("SUM(CASE WHEN priority_level = 'urgent' AND status IN ('scheduled', 'checked_in') THEN 1 ELSE 0 END) as urgent_today")
            ->first();

        $stats = [
            'total_today' => (int) ($todayCounts?->total_today ?? 0),
            'waiting_today' => (int) ($todayCounts?->waiting_today ?? 0),
            'completed_today' => (int) ($todayCounts?->completed_today ?? 0),
            'urgent_today' => (int) ($todayCounts?->urgent_today ?? 0),
        ];

        return view('appointments.index', [
            'stats' => $stats,
            'currentDate' => $date,
            'clinicians' => Clinician::active()->orderBy('name')->get(),
            'programs' => Program::where('is_active', true)->orderBy('code')->get(),
            'serviceTypes' => Appointment::SERVICE_TYPES,
            'priorities' => Appointment::PRIORITIES,
            'statuses' => Appointment::STATUSES,
            'timeSlots' => Appointment::TIME_SLOTS,
        ]);
    }

    public function calendar(Request $request): View
    {
        $clinicianId = $request->query('clinician_id');
        $serviceType = $request->query('service_type');
        $status = $request->query('status');
        $priority = $request->query('priority');

        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $currentMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $startOfMonth = (clone $currentMonth)->startOfMonth();
        $endOfMonth = (clone $currentMonth)->endOfMonth();

        $appointments = Appointment::with(['patient.college', 'patient.program', 'clinician', 'clinicVisit'])
            ->when($clinicianId, fn ($q) => $q->where('clinician_id', $clinicianId))
            ->when($serviceType, fn ($q) => $q->where('service_type', $serviceType))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($priority, fn ($q) => $q->where('priority_level', $priority))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $calendarEvents = $appointments->map(function (Appointment $app): array {
            $dateStr = $app->appointment_date ? $app->appointment_date->toDateString() : now()->toDateString();
            $timeRaw = $app->appointment_time ?? '09:00 AM';
            $time24 = date('H:i:s', strtotime($timeRaw));
            $startIso = "{$dateStr}T{$time24}";
            $endIso = date('Y-m-d\TH:i:s', strtotime("{$startIso} +30 minutes"));

            $color = match ($app->priority_level) {
                'urgent' => '#b42318',
                'high' => '#b86b14',
                default => '#1b5e3a',
            };
            if ($app->status === 'completed') {
                $color = '#62736b';
            } elseif ($app->status === 'checked_in') {
                $color = '#28666e';
            } elseif ($app->status === 'cancelled') {
                $color = '#8a9992';
            }

            $resolvedCategory = $app->patient?->type === 'employee'
                ? ($app->patient?->category === 'Faculty' ? 'Faculty' : 'Staff')
                : ($app->patient?->type === 'visitor' ? 'Visitor' : 'Student');

            return [
                'id' => (string) $app->id,
                'title' => "{$timeRaw} {$app->patient?->full_name}",
                'start' => $startIso,
                'end' => $endIso,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'id' => $app->id,
                    'appointment_number' => $app->appointment_number,
                    'patient_name' => $app->patient?->full_name ?? 'Unknown',
                    'patient_identifier' => $app->patient?->primary_identifier ?? '—',
                    'category' => $resolvedCategory,
                    'program' => $app->patient?->program?->code,
                    'phone' => $app->patient?->phone,
                    'doctor' => $app->clinician?->name ?? 'Any Available Doctor on Duty',
                    'service_type' => Appointment::SERVICE_TYPES[$app->service_type] ?? ucfirst($app->service_type),
                    'time' => $timeRaw,
                    'date' => $app->appointment_date ? $app->appointment_date->format($this->settingService->dateFormat()) : '—',
                    'date_raw' => $dateStr,
                    'reason' => $app->reason,
                    'notes' => $app->notes,
                    'status' => $app->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $app->status)),
                    'status_badge' => $app->statusBadgeClass,
                    'priority' => $app->priority_level,
                    'priority_badge' => $app->priorityBadgeClass,
                    'can_check_in' => $app->status === 'scheduled',
                    'can_start_consult' => in_array($app->status, ['scheduled', 'checked_in'], true),
                    'check_in_url' => route('appointments.check-in', $app),
                    'start_consult_url' => route('appointments.start-consultation', $app),
                ],
            ];
        })->values()->all();

        $monthAppointments = $appointments;
        $appointmentsByDate = $monthAppointments->groupBy(fn ($app) => $app->appointment_date?->toDateString() ?? '');
        $prevMonth = (clone $currentMonth)->subMonth();
        $nextMonth = (clone $currentMonth)->addMonth();

        $today = Carbon::today()->toDateString();
        $todayCounts = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->selectRaw('COUNT(*) as total_today')
            ->selectRaw("SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as waiting_today")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_today")
            ->selectRaw("SUM(CASE WHEN priority_level = 'urgent' AND status IN ('scheduled', 'checked_in') THEN 1 ELSE 0 END) as urgent_today")
            ->first();

        $stats = [
            'total_today' => (int) ($todayCounts?->total_today ?? 0),
            'waiting_today' => (int) ($todayCounts?->waiting_today ?? 0),
            'completed_today' => (int) ($todayCounts?->completed_today ?? 0),
            'urgent_today' => (int) ($todayCounts?->urgent_today ?? 0),
        ];

        $todayList = Appointment::with(['patient.college', 'patient.program', 'clinician', 'clinicVisit'])
            ->whereDate('appointment_date', $today)
            ->orderByRaw("CASE priority_level WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderBy('appointment_time')
            ->get();

        return view('appointments.calendar', [
            'stats' => $stats,
            'todayList' => $todayList,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'appointmentsByDate' => $appointmentsByDate,
            'calendarEvents' => $calendarEvents,
            'monthAppointmentsCount' => $monthAppointments->count(),
            'clinicians' => Clinician::active()->orderBy('name')->get(),
            'programs' => Program::where('is_active', true)->orderBy('code')->get(),
            'serviceTypes' => Appointment::SERVICE_TYPES,
            'priorities' => Appointment::PRIORITIES,
            'statuses' => Appointment::STATUSES,
            'timeSlots' => Appointment::TIME_SLOTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isNewPatient = $request->input('patient_mode') === 'new' || $request->boolean('is_new_student');

        if ($isNewPatient) {
            $patientData = $request->validate([
                'new_patient_type' => ['nullable', 'string', 'in:student,faculty,staff,employee,visitor'],
                'new_employee_category' => ['nullable', 'string', 'in:Faculty,Non-Teaching / Staff'],
                'new_id_number' => ['nullable', 'string', 'max:50'],
                'new_first_name' => ['required', 'string', 'max:60'],
                'new_last_name' => ['required', 'string', 'max:60'],
                'new_gender' => ['required', 'in:Male,Female,Other'],
                'new_program_id' => ['nullable', 'exists:programs,id'],
                'new_year_level' => ['nullable', 'string', 'max:30'],
                'new_phone' => ['nullable', 'string', 'max:35'],
            ]);

            $program = ! empty($patientData['new_program_id']) ? Program::find($patientData['new_program_id']) : null;
            $currentAyId = AcademicYear::current()->value('id');
            $requestedType = $patientData['new_patient_type'] ?? 'student';

            $type = 'student';
            $category = null;
            $employmentClassification = null;

            if ($requestedType === 'faculty') {
                $type = 'employee';
                $category = 'Faculty';
                $employmentClassification = 'Faculty';
            } elseif ($requestedType === 'staff') {
                $type = 'employee';
                $category = 'Non-Teaching / Staff';
                $employmentClassification = 'Non-Teaching';
            } elseif ($requestedType === 'employee') {
                $type = 'employee';
                $chosenCat = $patientData['new_employee_category'] ?? ($request->input('new_employee_category') ?: 'Faculty');
                $isFaculty = stripos($chosenCat, 'faculty') !== false || stripos($chosenCat, 'teaching') !== false;
                $category = $isFaculty ? 'Faculty' : 'Non-Teaching / Staff';
                $employmentClassification = $isFaculty ? 'Faculty' : 'Non-Teaching';
            } elseif ($requestedType === 'visitor') {
                $type = 'visitor';
                $category = 'Visitor / Guest';
            }

            $patient = Patient::create([
                'patient_number' => Patient::generatePatientNumber(),
                'school_id_number' => ! empty($patientData['new_id_number']) ? trim($patientData['new_id_number']) : null,
                'type' => $type,
                'category' => $category,
                'employment_classification' => $employmentClassification,
                'first_name' => trim($patientData['new_first_name']),
                'last_name' => trim($patientData['new_last_name']),
                'gender' => $patientData['new_gender'],
                'date_of_birth' => now()->subYears(18)->toDateString(),
                'civil_status' => 'Single',
                'phone' => $patientData['new_phone'] ?? null,
                'college_id' => $program?->college_id,
                'program_id' => $program?->id,
                'academic_year_id' => $currentAyId,
                'year_level' => ($patientData['new_year_level'] ?? null) ?: ($type === 'student' ? '1st Year' : null),
                'enrollment_status' => $type === 'student' ? 'Prospect / Newly Enrolled' : null,
                'status' => 'active',
            ]);

            $patientId = $patient->id;
        } else {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
            ]);
            $patientId = (int) $request->input('patient_id');
        }

        $validated = $request->validate([
            'clinician_id' => ['nullable', 'exists:clinicians,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'string', 'max:20'],
            'service_type' => ['required', 'string', 'in:'.implode(',', array_keys(Appointment::SERVICE_TYPES))],
            'priority_level' => ['required', 'string', 'in:normal,high,urgent'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'clinician_id' => $validated['clinician_id'] ?? null,
            'booked_by_user_id' => auth()->id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'service_type' => $validated['service_type'],
            'priority_level' => $validated['priority_level'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        $patient = Patient::find($patientId);

        return redirect()->back()->with('success', "Appointment {$appointment->appointment_number} booked for {$patient?->full_name} on ".Carbon::parse($appointment->appointment_date)->format($this->settingService->dateFormat())." at {$appointment->appointment_time}.");
    }

    public function checkIn(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status === 'cancelled' || $appointment->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot check in a completed or cancelled appointment.');
        }

        $appointment->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return redirect()->back()->with('success', "Patient {$appointment->patient?->full_name} is now checked in.");
    }

    public function startConsultation(Appointment $appointment): RedirectResponse
    {
        $appointment->update([
            'status' => 'in_consultation',
        ]);

        $params = [
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'complaint' => $appointment->reason,
        ];

        return match ($appointment->service_type) {
            'medical' => redirect()->route('medical-consultations.create', $params),
            'dental' => redirect()->route('dental-consultations.create', $params),
            default => redirect()->route('clinic-visits.create', array_merge($params, ['type' => $appointment->service_type])),
        };
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'] ?? 'Cancelled by clinic staff',
        ]);

        return redirect()->back()->with('success', "Appointment {$appointment->appointment_number} has been cancelled.");
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $num = $appointment->appointment_number;
        $appointment->delete();

        return redirect()->back()->with('success', "Appointment {$num} deleted.");
    }
}
