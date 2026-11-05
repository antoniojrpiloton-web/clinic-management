<?php

namespace App\Http\Controllers;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\Patient;
use App\Services\ClinicVisitService;
use App\Support\IdObfuscator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

abstract class ClinicRecordController extends Controller implements HasMiddleware
{
    public function __construct(protected ClinicVisitService $visitService) {}

    abstract protected static function routePrefix(): string;

    abstract protected function visitType(): string;

    abstract protected function detailRelation(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function detailRules(): array;

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    abstract protected function detailData(array $validated, Request $request): array;

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [];
    }

    protected function complaintRequired(): bool
    {
        return true;
    }

    public static function middleware(): array
    {
        $prefix = static::routePrefix();

        return [
            new Middleware("permission:{$prefix}.view", only: ['index', 'show']),
            new Middleware("permission:{$prefix}.create", only: ['create', 'store']),
            new Middleware("permission:{$prefix}.edit", only: ['edit', 'update']),
            new Middleware("permission:{$prefix}.delete", only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = ClinicVisit::query()
                ->with(['patient', 'clinician', 'recorder', $this->detailRelation()])
                ->ofType($this->visitType())
                ->filter($request->only(['patient_classification', 'status', 'disposition', 'college_id', 'date_from', 'date_to']))
                ->latest('visit_date');

            return $this->visitService->dataTable($query, static::routePrefix());
        }

        return view(static::routePrefix().'.index', [
            'stats' => $this->visitService->stats($this->visitType()),
            'colleges' => College::orderBy('name')->get(),
            'meta' => ClinicVisit::TYPES[$this->visitType()],
            'routePrefix' => static::routePrefix(),
        ]);
    }

    public function create(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $resolvedPatientId = $patientId ? IdObfuscator::decodeOrRaw($patientId, Patient::class) : null;
        $patient = $resolvedPatientId ? Patient::with(['college', 'program'])->find($resolvedPatientId) : null;

        return view(static::routePrefix().'.create', array_merge([
            'visit' => new ClinicVisit([
                'visit_date' => now(),
                'visit_category' => $this->visitType() === ClinicVisit::TYPE_EMERGENCY ? 'emergency_call' : 'walk_in',
                'status' => 'completed',
                'clinician_id' => $this->defaultClinician()?->id,
            ]),
            'detail' => $this->newDetail(),
            'preselectedPatient' => $patient,
            'defaultClinician' => $this->defaultClinician(),
            'meta' => ClinicVisit::TYPES[$this->visitType()],
            'routePrefix' => static::routePrefix(),
        ], $this->formOptions()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->visitService->visitRules($this->complaintRequired()), $this->detailRules()));

        $visit = DB::transaction(function () use ($validated, $request) {
            $visit = $this->visitService->create($this->visitType(), $validated);
            $visit->{$this->detailRelation()}()->create($this->detailData($validated, $request));

            return $visit;
        });

        return redirect()->route(static::routePrefix().'.show', $visit)
            ->with('success', "{$visit->type_label} {$visit->visit_number} recorded successfully.");
    }

    public function show(ClinicVisit $visit): View
    {
        $this->ensureType($visit);
        $visit->load(['patient.college', 'patient.program', 'patient.medicalProfile', 'college', 'program', 'clinician', 'recorder', 'medicineDispensing.items.medicine', $this->detailRelation()]);

        $previousVisits = ClinicVisit::where('patient_id', $visit->patient_id)
            ->where('id', '!=', $visit->id)
            ->latest('visit_date')
            ->take(5)
            ->get();

        return view(static::routePrefix().'.show', [
            'visit' => $visit,
            'detail' => $visit->{$this->detailRelation()},
            'patient' => $visit->patient,
            'previousVisits' => $previousVisits,
            'meta' => ClinicVisit::TYPES[$this->visitType()],
            'routePrefix' => static::routePrefix(),
        ]);
    }

    public function edit(ClinicVisit $visit): View
    {
        $this->ensureType($visit);
        $visit->load(['patient.college', 'patient.program', 'clinician', $this->detailRelation()]);

        return view(static::routePrefix().'.edit', array_merge([
            'visit' => $visit,
            'detail' => $visit->{$this->detailRelation()} ?? $this->newDetail(),
            'preselectedPatient' => $visit->patient,
            'defaultClinician' => $visit->clinician,
            'meta' => ClinicVisit::TYPES[$this->visitType()],
            'routePrefix' => static::routePrefix(),
        ], $this->formOptions()));
    }

    public function update(Request $request, ClinicVisit $visit): RedirectResponse
    {
        $this->ensureType($visit);

        $validated = $request->validate(array_merge($this->visitService->visitRules($this->complaintRequired()), $this->detailRules()));

        DB::transaction(function () use ($visit, $validated, $request) {
            $this->visitService->update($visit, $validated);
            $visit->{$this->detailRelation()}()->updateOrCreate(
                ['clinic_visit_id' => $visit->id],
                $this->detailData($validated, $request),
            );
        });

        return redirect()->route(static::routePrefix().'.show', $visit)
            ->with('success', "{$visit->type_label} {$visit->visit_number} updated successfully.");
    }

    public function destroy(ClinicVisit $visit): RedirectResponse
    {
        $this->ensureType($visit);
        $number = $visit->visit_number;

        $this->visitService->delete($visit);

        return redirect()->route(static::routePrefix().'.index')
            ->with('success', "Visit record {$number} has been removed.");
    }

    protected function ensureType(ClinicVisit $visit): void
    {
        abort_unless($visit->visit_type === $this->visitType(), 404);
    }

    protected function newDetail(): Model
    {
        $relation = (new ClinicVisit)->{$this->detailRelation()}();

        return $relation->getRelated()->newInstance();
    }

    protected function defaultClinician(): ?Clinician
    {
        return Clinician::active()->where('user_id', auth()->id())->first();
    }
}
