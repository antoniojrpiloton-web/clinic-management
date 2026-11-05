<?php

namespace App\Http\Controllers;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\Patient;
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
        if ($request->ajax()) {
            $query = ClinicVisit::query()
                ->with(['patient.college', 'clinician', 'recorder', 'medicalConsultation', 'dentalConsultation', 'emergencyRecord', 'firstAidRecord', 'physicalExamination'])
                ->filter($request->only(['visit_type', 'patient_classification', 'status', 'disposition', 'college_id', 'date_from', 'date_to', 'patient_id']))
                ->latest('visit_date');

            return $this->visitService->dataTable($query);
        }

        $stats = [
            'total' => ClinicVisit::count(),
            'today' => ClinicVisit::whereDate('visit_date', Carbon::today())->count(),
            'students' => ClinicVisit::where('patient_classification', 'student')->count(),
            'employees' => ClinicVisit::where('patient_classification', 'employee')->count(),
        ];

        return view('clinic-visits.index', [
            'stats' => $stats,
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
        $patient = $patientId ? Patient::with(['college', 'program'])->find($patientId) : null;
        $defaultClinician = Clinician::active()->where('user_id', auth()->id())->first() ?? Clinician::active()->first();

        return view('clinic-visits.create', [
            'visit' => new ClinicVisit([
                'visit_date' => now(),
                'visit_type' => $request->query('type', ClinicVisit::TYPE_GENERAL),
                'visit_category' => 'walk_in',
                'status' => 'completed',
                'clinician_id' => $defaultClinician?->id,
            ]),
            'preselectedPatient' => $patient,
            'defaultClinician' => $defaultClinician,
            'purposes' => ClinicVisit::GENERAL_PURPOSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge($this->visitService->visitRules(true), [
            'visit_type' => ['required', Rule::in(array_keys(ClinicVisit::TYPES))],
        ]));

        $visit = $this->visitService->create($validated['visit_type'], $validated);

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
