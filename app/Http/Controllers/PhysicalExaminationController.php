<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Clinician;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use App\Support\IdObfuscator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PhysicalExaminationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = PhysicalExamination::query()
                ->with(['patient.college', 'academicYear', 'examiner'])
                ->filter($request->only(['search', 'purpose', 'fitness_assessment', 'academic_year_id', 'date_from', 'date_to']))
                ->latest('exam_date');

            return DataTables::eloquent($query)
                ->addColumn('patient_card', function (PhysicalExamination $pe) {
                    $patient = $pe->patient;
                    $badgeClass = $patient->type === 'student' ? 'badge-blue' : 'badge-green';
                    $typeLabel = ucfirst($patient->type);
                    $identifier = e($patient->primary_identifier);
                    $name = e($patient->full_name);
                    $url = route('patients.show', $patient);

                    return "<div>
                        <a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$name}</a>
                        <div class=\"small text-muted d-flex align-items-center gap-1 mt-1\">
                            <span class=\"badge {$badgeClass} py-0 px-2\" style=\"font-size: 0.7rem;\">{$typeLabel}</span>
                            <span>ID: {$identifier}</span>
                        </div>
                    </div>";
                })
                ->addColumn('vitals_summary', function (PhysicalExamination $pe) {
                    $bp = $pe->blood_pressure ?: 'BP: N/A';
                    $temp = $pe->temperature ? "{$pe->temperature}°C" : 'Temp: N/A';
                    $pr = $pe->pulse_rate ? "{$pe->pulse_rate} bpm" : 'PR: N/A';

                    return "<div class=\"small\">
                        <span class=\"fw-semibold text-dark\">{$bp}</span>
                        <div class=\"text-muted mt-1\">{$temp} • {$pr}</div>
                    </div>";
                })
                ->addColumn('bmi_badge', function (PhysicalExamination $pe) {
                    if (! $pe->bmi) {
                        return '<span class="badge badge-soft-gray">N/A</span>';
                    }

                    $badge = $pe->bmi_badge_class;

                    return "<span class=\"badge {$badge}\">{$pe->bmi} ({$pe->bmi_category})</span>";
                })
                ->addColumn('fitness_badge', function (PhysicalExamination $pe) {
                    $badge = $pe->fitness_badge_class;
                    $assessment = e($pe->fitness_assessment ?: 'Class A');

                    return "<span class=\"badge {$badge} px-2 py-1\">{$assessment}</span>";
                })
                ->addColumn('exam_date_formatted', fn (PhysicalExamination $pe) => $pe->exam_date->format('M d, Y'))
                ->addColumn('actions', function (PhysicalExamination $pe) {
                    $viewUrl = route('physical-examinations.show', $pe);
                    $editUrl = route('physical-examinations.edit', $pe);
                    $deleteUrl = route('physical-examinations.destroy', $pe);
                    $printPeUrl = route('physical-examinations.print-pe', $pe);
                    $printCertUrl = route('physical-examinations.print-certificate', $pe);
                    $printExemptUrl = route('physical-examinations.print-exemption', $pe);
                    $printReferralUrl = route('physical-examinations.print-referral', $pe);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    return "<div class=\"dropdown\">
                        <button class=\"btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                            <i class=\"bi bi-gear-fill me-1\"></i> Action
                        </button>
                        <ul class=\"dropdown-menu dropdown-menu-end shadow-sm\">
                            <li><a class=\"dropdown-item\" href=\"{$viewUrl}\"><i class=\"bi bi-eye text-primary me-2\"></i> View Examination</a></li>
                            <li><a class=\"dropdown-item\" href=\"{$editUrl}\"><i class=\"bi bi-pencil text-warning me-2\"></i> Edit Record</a></li>
                            <li><hr class=\"dropdown-divider\"></li>
                            <li><a class=\"dropdown-item\" href=\"{$printPeUrl}\" target=\"_blank\"><i class=\"bi bi-file-earmark-medical text-success me-2\"></i> Print PE Form</a></li>
                            <li><a class=\"dropdown-item\" href=\"{$printCertUrl}\" target=\"_blank\"><i class=\"bi bi-file-earmark-check text-info me-2\"></i> Print Medical Cert (HSD-4)</a></li>
                            <li><a class=\"dropdown-item\" href=\"{$printExemptUrl}\" target=\"_blank\"><i class=\"bi bi-file-earmark-x text-danger me-2\"></i> Print Exemption (HSD-6)</a></li>
                            <li><a class=\"dropdown-item\" href=\"{$printReferralUrl}\" target=\"_blank\"><i class=\"bi bi-box-arrow-up-right text-secondary me-2\"></i> Print Referral (HSD-3)</a></li>
                            <li><hr class=\"dropdown-divider\"></li>
                            <li>
                                <form method=\"POST\" action=\"{$deleteUrl}\" onsubmit=\"return confirm('Delete physical exam {$pe->pe_number}?');\">
                                    {$csrf}
                                    {$method}
                                    <button type=\"submit\" class=\"dropdown-item text-danger\"><i class=\"bi bi-trash me-2\"></i> Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>";
                })
                ->rawColumns(['patient_card', 'vitals_summary', 'bmi_badge', 'fitness_badge', 'actions'])
                ->make(true);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $todayDate = Carbon::today()->toDateString();

        $agg = PhysicalExamination::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN exam_date = ? THEN 1 ELSE 0 END) as today', [$todayDate])
            ->selectRaw("SUM(CASE WHEN fitness_assessment = 'Class A' THEN 1 ELSE 0 END) as class_a")
            ->selectRaw("SUM(CASE WHEN fitness_assessment IN ('Class B', 'Class C', 'Class D', 'Class E') THEN 1 ELSE 0 END) as needs_attention")
            ->first();

        $stats = [
            'total' => (int) ($agg?->total ?? 0),
            'today' => (int) ($agg?->today ?? 0),
            'class_a' => (int) ($agg?->class_a ?? 0),
            'needs_attention' => (int) ($agg?->needs_attention ?? 0),
        ];

        return view('physical-examinations.index', [
            'academicYears' => $academicYears,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $resolvedPatientId = $patientId ? IdObfuscator::decodeOrRaw($patientId, Patient::class) : null;
        $patient = $resolvedPatientId ? Patient::with(['college', 'program', 'academicYear', 'medicalProfile'])->find($resolvedPatientId) : null;
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $currentAy = AcademicYear::current()->first();
        $defaultClinician = Clinician::where('user_id', auth()->id())->first()
            ?? Clinician::where('license_number', '0052551')->first()
            ?? Clinician::first();

        return view('physical-examinations.create', [
            'preselectedPatient' => $patient,
            'academicYears' => $academicYears,
            'currentAy' => $currentAy,
            'nextPeNumber' => PhysicalExamination::generatePeNumber(),
            'defaultClinician' => $defaultClinician,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateExam($request);

        $patient = Patient::findOrFail($validated['patient_id']);

        $examData = $this->extractExamData($validated, $request);
        $exam = PhysicalExamination::create($examData);

        $this->syncMedicalProfileFromExam($patient, $validated);

        return redirect()->route('physical-examinations.show', $exam)
            ->with('success', "Physical Examination {$exam->pe_number} recorded successfully.");
    }

    public function show(PhysicalExamination $physicalExamination): View
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'patient.medicalProfile', 'academicYear', 'examiner', 'clinician']);

        return view('physical-examinations.show', [
            'exam' => $physicalExamination,
            'patient' => $physicalExamination->patient,
        ]);
    }

    public function edit(PhysicalExamination $physicalExamination): View
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'patient.medicalProfile', 'academicYear', 'clinician']);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('physical-examinations.edit', [
            'exam' => $physicalExamination,
            'patient' => $physicalExamination->patient,
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request, PhysicalExamination $physicalExamination): RedirectResponse
    {
        $validated = $this->validateExam($request, $physicalExamination);

        $examData = $this->extractExamData($validated, $request);
        $physicalExamination->update($examData);

        $this->syncMedicalProfileFromExam($physicalExamination->patient, $validated);

        return redirect()->route('physical-examinations.show', $physicalExamination)
            ->with('success', "Physical Examination {$physicalExamination->pe_number} updated successfully.");
    }

    public function destroy(PhysicalExamination $physicalExamination): RedirectResponse
    {
        $peNumber = $physicalExamination->pe_number;
        $physicalExamination->delete();

        return redirect()->route('physical-examinations.index')
            ->with('success', "Physical Examination {$peNumber} has been removed.");
    }

    public function printPe(Request $request, PhysicalExamination $physicalExamination, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'patient.medicalProfile', 'academicYear', 'examiner', 'clinician']);

        if ($request->query('format') === 'html') {
            return view('physical-examinations.print.pe-form', [
                'exam' => $physicalExamination,
                'patient' => $physicalExamination->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->physicalExamination($physicalExamination, $settingService->all(), $physicalExamination->clinician);
        $filename = "PE-{$physicalExamination->pe_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printCertificate(Request $request, PhysicalExamination $physicalExamination, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'academicYear', 'examiner', 'clinician']);

        if ($request->query('format') === 'html') {
            return view('physical-examinations.print.medical-certificate', [
                'exam' => $physicalExamination,
                'patient' => $physicalExamination->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->medicalCertificate($physicalExamination, $settingService->all(), $physicalExamination->clinician);
        $filename = "Medical-Certificate-{$physicalExamination->pe_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printExemption(Request $request, PhysicalExamination $physicalExamination, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'academicYear', 'examiner', 'clinician']);

        if ($request->query('format') === 'html') {
            return view('physical-examinations.print.medical-exemption', [
                'exam' => $physicalExamination,
                'patient' => $physicalExamination->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->medicalExemption($physicalExamination, $settingService->all(), $physicalExamination->clinician);
        $filename = "Medical-Exemption-{$physicalExamination->pe_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printReferral(Request $request, PhysicalExamination $physicalExamination, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $physicalExamination->load(['patient.college', 'patient.program', 'patient.academicYear', 'academicYear', 'examiner', 'clinician']);

        if ($request->query('format') === 'html') {
            return view('physical-examinations.print.medical-referral', [
                'exam' => $physicalExamination,
                'patient' => $physicalExamination->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->medicalReferral($physicalExamination, $settingService->all(), $physicalExamination->clinician);
        $filename = "Medical-Referral-{$physicalExamination->pe_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    private function validateExam(Request $request, ?PhysicalExamination $exam = null): array
    {
        $uniqueRule = $exam ? "unique:physical_examinations,pe_number,{$exam->id}" : 'unique:physical_examinations,pe_number';

        return $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'clinician_id' => 'nullable|exists:clinicians,id',
            'pe_number' => "nullable|string|max:50|{$uniqueRule}",
            'exam_date' => 'required|date',
            'purpose' => 'required|string|max:80',
            'examiner_name' => 'nullable|string|max:120',
            'examiner_license_number' => 'nullable|string|max:50',
            'systolic' => 'nullable|integer|between:50,300',
            'diastolic' => 'nullable|integer|between:30,200',
            'pulse_rate' => 'nullable|integer|between:30,250',
            'respiratory_rate' => 'nullable|integer|between:5,80',
            'temperature' => 'nullable|numeric|between:30,45',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'height_cm' => 'nullable|numeric|between:50,250',
            'weight_kg' => 'nullable|numeric|between:10,350',
            'visual_acuity_right' => 'nullable|string|max:20',
            'visual_acuity_left' => 'nullable|string|max:20',
            'visual_acuity_is_corrected' => 'nullable|boolean',
            'color_vision' => 'nullable|string|max:30',
            'hearing' => 'nullable|string|max:30',
            'general_appearance' => 'nullable|string|max:190',
            'systems' => 'nullable|array',
            'chest_xray_findings' => 'nullable|string|max:190',
            'chest_xray_date' => 'nullable|date',
            'laboratory_notes' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'medical_recommendations' => 'nullable|string',
            'fitness_assessment' => 'required|string|in:Class A,Class B,Class C,Class D,Class E',
            'fitness_activities' => 'nullable|array',
            'past_medical_history' => 'nullable|array',
            'past_medical_history_notes' => 'nullable|string',
            'family_medical_history' => 'nullable|array',
            'allergies_food' => 'nullable|string|max:500',
            'allergies_drugs' => 'nullable|string|max:500',
            'existing_conditions' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'hospitalization_history' => 'nullable|string|max:1000',
            'surgical_history' => 'nullable|string|max:1000',
        ]);
    }

    private function extractExamData(array $validated, Request $request): array
    {
        $height = isset($validated['height_cm']) ? (float) $validated['height_cm'] : null;
        $weight = isset($validated['weight_kg']) ? (float) $validated['weight_kg'] : null;
        $bmi = ($height && $weight) ? PhysicalExamination::calculateBmi($weight, $height) : null;
        $bmiCategory = $bmi ? PhysicalExamination::determineBmiCategory($bmi) : null;

        $systems = $validated['systems'] ?? [];

        $clinician = ! empty($validated['clinician_id']) ? Clinician::find($validated['clinician_id']) : null;
        $examinerName = $validated['examiner_name'] ?: ($clinician?->name ?? auth()->user()->name);
        $examinerLicense = $validated['examiner_license_number'] ?: ($clinician?->license_number ?? null);

        return [
            'pe_number' => $validated['pe_number'] ?? PhysicalExamination::generatePeNumber(),
            'patient_id' => $validated['patient_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'examiner_id' => auth()->id(),
            'clinician_id' => $validated['clinician_id'] ?? null,
            'examiner_name' => $examinerName,
            'examiner_license_number' => $examinerLicense,
            'exam_date' => $validated['exam_date'],
            'purpose' => $validated['purpose'],
            'systolic' => $validated['systolic'] ?? null,
            'diastolic' => $validated['diastolic'] ?? null,
            'pulse_rate' => $validated['pulse_rate'] ?? null,
            'respiratory_rate' => $validated['respiratory_rate'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
            'height_cm' => $height,
            'weight_kg' => $weight,
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'visual_acuity_right' => $validated['visual_acuity_right'] ?? '20/20',
            'visual_acuity_left' => $validated['visual_acuity_left'] ?? '20/20',
            'visual_acuity_is_corrected' => (bool) ($validated['visual_acuity_is_corrected'] ?? false),
            'color_vision' => $validated['color_vision'] ?? 'Normal',
            'hearing' => $validated['hearing'] ?? 'Normal',
            'general_appearance' => $validated['general_appearance'] ?? 'Conscious, coherent, ambulatory, well-nourished',
            'skin' => $systems['skin'] ?? null,
            'heent' => $systems['heent'] ?? null,
            'neck' => $systems['neck'] ?? null,
            'chest_lungs' => $systems['chest_lungs'] ?? null,
            'heart' => $systems['heart'] ?? null,
            'abdomen' => $systems['abdomen'] ?? null,
            'musculoskeletal' => $systems['musculoskeletal'] ?? null,
            'neurological' => $systems['neurological'] ?? null,
            'dental' => $systems['dental'] ?? null,
            'chest_xray_findings' => $validated['chest_xray_findings'] ?? null,
            'chest_xray_date' => $validated['chest_xray_date'] ?? null,
            'laboratory_notes' => $validated['laboratory_notes'] ?? null,
            'clinical_findings' => $validated['clinical_findings'] ?? null,
            'medical_recommendations' => $validated['medical_recommendations'] ?? null,
            'fitness_assessment' => $validated['fitness_assessment'],
            'fitness_activities' => $validated['fitness_activities'] ?? null,
            'status' => 'completed',
        ];
    }

    private function syncMedicalProfileFromExam(Patient $patient, array $validated): void
    {
        $profile = $patient->medicalProfile ?: $patient->medicalProfile()->create([]);

        $updates = [];
        if (! empty($validated['past_medical_history'])) {
            $updates['past_medical_history'] = $validated['past_medical_history'];
        }
        if (! empty($validated['past_medical_history_notes'])) {
            $updates['past_medical_history_notes'] = $validated['past_medical_history_notes'];
        }
        if (! empty($validated['family_medical_history'])) {
            $updates['family_medical_history'] = $validated['family_medical_history'];
        }
        if (! empty($validated['allergies_food'])) {
            $updates['allergies_food'] = $validated['allergies_food'];
        }
        if (! empty($validated['allergies_drugs'])) {
            $updates['allergies_drugs'] = $validated['allergies_drugs'];
        }
        if (! empty($validated['existing_conditions'])) {
            $updates['existing_conditions'] = $validated['existing_conditions'];
        }
        if (! empty($validated['current_medications'])) {
            $updates['current_medications'] = $validated['current_medications'];
        }

        if ($updates) {
            $updates['updated_by'] = auth()->id();
            $profile->update($updates);
        }
    }
}
