<?php

namespace App\Services;

use App\Models\ClinicVisit;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ClinicVisitService
{
    /**
     * @var list<string>
     */
    public const VISIT_FIELDS = [
        'visit_category',
        'visit_date',
        'time_out',
        'chief_complaint',
        'systolic',
        'diastolic',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'weight_kg',
        'height_cm',
        'pain_scale',
        'clinician_id',
        'disposition',
        'status',
        'remarks',
    ];

    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * @return array<string, mixed>
     */
    public function visitRules(bool $complaintRequired = true): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'visit_category' => ['required', Rule::in(array_keys(ClinicVisit::CATEGORIES))],
            'visit_date' => ['required', 'date'],
            'time_out' => ['nullable', 'date', 'after_or_equal:visit_date'],
            'chief_complaint' => [$complaintRequired ? 'required' : 'nullable', 'string', 'max:2000'],
            'systolic' => ['nullable', 'integer', 'between:50,300'],
            'diastolic' => ['nullable', 'integer', 'between:30,200'],
            'pulse_rate' => ['nullable', 'integer', 'between:20,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,80'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],
            'oxygen_saturation' => ['nullable', 'integer', 'between:40,100'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,400'],
            'height_cm' => ['nullable', 'numeric', 'between:30,250'],
            'pain_scale' => ['nullable', 'integer', 'between:0,10'],
            'clinician_id' => ['nullable', 'exists:clinicians,id'],
            'disposition' => ['nullable', Rule::in(array_keys(ClinicVisit::DISPOSITIONS))],
            'status' => ['required', Rule::in(array_keys(ClinicVisit::STATUSES))],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{patient_classification: string, classification_label: string, college_id: int|null, program_id: int|null, academic_year_id: int|null, year_level: string|null, office_department: string|null}
     */
    public function classificationSnapshot(Patient $patient): array
    {
        $patient->loadMissing(['college', 'program']);

        $parts = [ucfirst($patient->type)];

        if ($patient->type === 'student') {
            $parts[] = $patient->program?->code;
            $parts[] = $patient->year_level;
            $parts[] = $patient->college?->code;
        } else {
            $parts[] = $patient->category;
            $parts[] = $patient->position;
            $parts[] = $patient->office_department;
        }

        return [
            'patient_classification' => $patient->type,
            'classification_label' => implode(' • ', array_filter($parts)),
            'college_id' => $patient->college_id,
            'program_id' => $patient->program_id,
            'academic_year_id' => $patient->academic_year_id,
            'year_level' => $patient->year_level,
            'office_department' => $patient->office_department,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(string $type, array $validated): ClinicVisit
    {
        $patient = Patient::findOrFail($validated['patient_id']);

        $visit = ClinicVisit::create(array_merge(
            $this->extractVisitData($validated),
            $this->classificationSnapshot($patient),
            [
                'visit_number' => ClinicVisit::generateVisitNumber(),
                'patient_id' => $patient->id,
                'visit_type' => $type,
                'recorded_by' => auth()->id(),
            ],
        ));

        $this->auditLogger->log('created', "Recorded {$visit->type_label} {$visit->visit_number} for patient {$patient->full_name}", $visit);

        return $visit;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(ClinicVisit $visit, array $validated): ClinicVisit
    {
        $data = $this->extractVisitData($validated);

        if (isset($validated['patient_id']) && (int) $validated['patient_id'] !== $visit->patient_id) {
            $patient = Patient::findOrFail($validated['patient_id']);
            $data = array_merge($data, $this->classificationSnapshot($patient), ['patient_id' => $patient->id]);
        }

        $visit->update($data);

        $this->auditLogger->log('updated', "Updated {$visit->type_label} {$visit->visit_number}", $visit);

        return $visit;
    }

    public function delete(ClinicVisit $visit): void
    {
        $number = $visit->visit_number;
        $label = $visit->type_label;
        $visit->delete();

        $this->auditLogger->log('deleted', "Deleted {$label} {$number}", $visit);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function extractVisitData(array $validated): array
    {
        return collect(self::VISIT_FIELDS)
            ->filter(fn (string $field) => array_key_exists($field, $validated))
            ->mapWithKeys(fn (string $field) => [$field => $validated[$field] === '' ? null : $validated[$field]])
            ->all();
    }

    /**
     * @return array{total: int, today: int, this_month: int, students: int, employees: int}
     */
    public function stats(?string $type = null): array
    {
        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayEnd = now()->endOfDay()->toDateTimeString();
        $monthStart = now()->startOfMonth()->toDateTimeString();
        $monthEnd = now()->endOfMonth()->toDateTimeString();

        $agg = ClinicVisit::query()
            ->when($type, fn (Builder $q) => $q->where('visit_type', $type))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN visit_date >= ? AND visit_date <= ? THEN 1 ELSE 0 END) as today', [$todayStart, $todayEnd])
            ->selectRaw('SUM(CASE WHEN visit_date >= ? AND visit_date <= ? THEN 1 ELSE 0 END) as this_month', [$monthStart, $monthEnd])
            ->selectRaw("SUM(CASE WHEN patient_classification = 'student' THEN 1 ELSE 0 END) as students")
            ->selectRaw("SUM(CASE WHEN patient_classification = 'employee' THEN 1 ELSE 0 END) as employees")
            ->first();

        return [
            'total' => (int) ($agg?->total ?? 0),
            'today' => (int) ($agg?->today ?? 0),
            'this_month' => (int) ($agg?->this_month ?? 0),
            'students' => (int) ($agg?->students ?? 0),
            'employees' => (int) ($agg?->employees ?? 0),
        ];
    }

    public function dataTable(Builder $query, ?string $routePrefix = null): JsonResponse
    {
        return DataTables::eloquent($query)
            ->filter(function (Builder $q) {
                $term = request('search.value') ?: (is_string(request('search')) ? request('search') : null);

                if ($term) {
                    $q->where(function (Builder $inner) use ($term) {
                        $inner->where('visit_number', 'like', "%{$term}%")
                            ->orWhere('chief_complaint', 'like', "%{$term}%")
                            ->orWhereHas('patient', fn (Builder $p) => $p->search($term));
                    });
                }
            })
            ->addColumn('visit_card', function (ClinicVisit $visit) {
                $number = e($visit->visit_number);
                $url = $visit->detail_url;
                $category = e($visit->category_label);

                return "<div>
                    <a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none font-monospace\">{$number}</a>
                    <div class=\"small text-muted mt-1\">{$category}</div>
                </div>";
            })
            ->addColumn('patient_card', function (ClinicVisit $visit) {
                $patient = $visit->patient;
                $name = e($patient?->full_name ?? 'N/A');
                $identifier = e($patient?->primary_identifier ?? 'N/A');
                $badge = $visit->classification_badge;
                $classification = e(ucfirst($visit->patient_classification));
                $label = e($visit->classification_label ?: 'N/A');
                $url = $patient ? route('patients.show', $patient) : '#';

                return "<div>
                    <a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$name}</a>
                    <div class=\"small text-muted d-flex flex-wrap align-items-center gap-1 mt-1\">
                        <span class=\"badge {$badge} py-0 px-2\" style=\"font-size: 0.7rem;\">{$classification}</span>
                        <span>ID: {$identifier}</span>
                    </div>
                    <div class=\"small text-muted text-truncate\" style=\"max-width: 260px;\" title=\"{$label}\">{$label}</div>
                </div>";
            })
            ->addColumn('type_badge', function (ClinicVisit $visit) {
                $badge = $visit->type_badge;
                $icon = $visit->type_icon;
                $label = e($visit->type_label);

                return "<span class=\"badge {$badge}\"><i class=\"bi {$icon} me-1\"></i>{$label}</span>";
            })
            ->addColumn('summary_text', function (ClinicVisit $visit) {
                $summary = e($visit->summary ?: 'N/A');
                $complaint = e($visit->chief_complaint ?: 'N/A');

                return "<div class=\"small\" style=\"max-width: 280px;\">
                    <div class=\"fw-semibold text-dark text-truncate\" title=\"{$summary}\">{$summary}</div>
                    <div class=\"text-muted text-truncate\" title=\"{$complaint}\">CC: {$complaint}</div>
                </div>";
            })
            ->addColumn('vitals_summary', function (ClinicVisit $visit) {
                $bp = $visit->blood_pressure ?: 'N/A';
                $temp = $visit->temperature ? "{$visit->temperature} °C" : 'N/A';
                $pulse = $visit->pulse_rate ? "{$visit->pulse_rate} bpm" : 'N/A';

                return "<div class=\"small\">
                    <span class=\"fw-semibold text-dark\">BP {$bp}</span>
                    <div class=\"text-muted\">T {$temp} • PR {$pulse}</div>
                </div>";
            })
            ->addColumn('attending', function (ClinicVisit $visit) {
                $clinician = e($visit->clinician?->name ?? 'N/A');
                $recorder = e($visit->recorder?->name ?? 'System');

                return "<div class=\"small\">
                    <div class=\"fw-medium text-dark\">{$clinician}</div>
                    <div class=\"text-muted\">Recorded by {$recorder}</div>
                </div>";
            })
            ->addColumn('disposition_badge', function (ClinicVisit $visit) {
                $statusBadge = $visit->status_badge;
                $status = e($visit->status_label);
                $disposition = e($visit->disposition_label);

                return "<div>
                    <span class=\"badge {$statusBadge}\">{$status}</span>
                    <div class=\"small text-muted mt-1\">{$disposition}</div>
                </div>";
            })
            ->addColumn('visit_date_formatted', function (ClinicVisit $visit) {
                $date = $visit->visit_date->format('M d, Y');
                $time = $visit->visit_date->format('h:i A');
                $out = $visit->time_out ? 'Out '.$visit->time_out->format('h:i A') : 'No time-out';

                return "<div class=\"small\">
                    <div class=\"fw-semibold text-dark\">{$date}</div>
                    <div class=\"text-muted\">In {$time} • {$out}</div>
                </div>";
            })
            ->addColumn('actions', function (ClinicVisit $visit) use ($routePrefix) {
                $viewUrl = $visit->detail_url;
                $printUrl = route('clinic-visits.print', $visit);
                $prefix = $routePrefix ?? (ClinicVisit::TYPES[$visit->visit_type]['route'] ?? null);
                $editItem = '';
                $deleteItem = '';

                if ($prefix && auth()->user()->can("{$prefix}.edit")) {
                    $editUrl = route("{$prefix}.edit", $visit);
                    $editItem = "<li><a class=\"dropdown-item\" href=\"{$editUrl}\"><i class=\"bi bi-pencil text-warning me-2\"></i> Edit Record</a></li>";
                }

                if ($prefix && auth()->user()->can("{$prefix}.delete")) {
                    $deleteUrl = route("{$prefix}.destroy", $visit);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $number = e($visit->visit_number);
                    $deleteItem = "<li><hr class=\"dropdown-divider\"></li>
                        <li>
                            <form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"Delete visit record {$number}?\">
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
                    <ul class=\"dropdown-menu dropdown-menu-end shadow-sm\">
                        <li><a class=\"dropdown-item\" href=\"{$viewUrl}\"><i class=\"bi bi-eye text-primary me-2\"></i> View Record</a></li>
                        {$editItem}
                        <li><a class=\"dropdown-item\" href=\"{$printUrl}\" target=\"_blank\"><i class=\"bi bi-printer text-success me-2\"></i> Print Visit Slip</a></li>
                        {$deleteItem}
                    </ul>
                </div>";
            })
            ->rawColumns(['visit_card', 'patient_card', 'type_badge', 'summary_text', 'vitals_summary', 'attending', 'disposition_badge', 'visit_date_formatted', 'actions'])
            ->make(true);
    }
}
