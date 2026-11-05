<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Services\Pdf\DentalPdfService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class DentalConsultationController extends ClinicRecordController
{
    protected static function routePrefix(): string
    {
        return 'dental-consultations';
    }

    protected function visitType(): string
    {
        return ClinicVisit::TYPE_DENTAL;
    }

    protected function detailRelation(): string
    {
        return 'dentalConsultation';
    }

    public static function middleware(): array
    {
        $prefix = static::routePrefix();

        return [
            ...parent::middleware(),
            new Middleware("permission:{$prefix}.view", only: ['printCard']),
        ];
    }

    protected function formOptions(): array
    {
        return [
            'procedureOptions' => DentalConsultation::PROCEDURES,
            'toothConditions' => DentalConsultation::TOOTH_CONDITIONS,
            'hygieneStatuses' => DentalConsultation::HYGIENE_STATUSES,
            'teeth' => DentalConsultation::permanentTeeth(),
            'deciduousTeeth' => DentalConsultation::deciduousTeeth(),
            'treatmentFrequencies' => DentalConsultation::TREATMENT_FREQUENCIES,
            'periodontalStatuses' => DentalConsultation::PERIODONTAL_STATUSES,
            'calculusIndices' => DentalConsultation::CALCULUS_INDICES,
            'gingivalIndices' => DentalConsultation::GINGIVAL_INDICES,
            'plaqueIndices' => DentalConsultation::PLAQUE_INDICES,
            'priorityLevels' => DentalConsultation::PRIORITY_LEVELS,
            'softTissueStatuses' => DentalConsultation::SOFT_TISSUE_STATUSES,
        ];
    }

    protected function detailRules(): array
    {
        return [
            'oral_examination_findings' => ['nullable', 'string', 'max:5000'],
            'tooth_chart' => ['nullable', 'array'],
            'tooth_chart.*' => ['nullable', Rule::in(array_keys(DentalConsultation::TOOTH_CONDITIONS))],
            'primary_tooth_chart' => ['nullable', 'array'],
            'primary_tooth_chart.*' => ['nullable', Rule::in(array_keys(DentalConsultation::TOOTH_CONDITIONS))],
            'procedures' => ['nullable', 'array'],
            'procedures.*' => ['string', Rule::in(DentalConsultation::PROCEDURES)],
            'teeth_involved' => ['nullable', 'string', 'max:150'],
            'anesthesia_used' => ['nullable', 'string', 'max:120'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'treatment_rendered' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'string', 'max:3000'],
            'oral_hygiene_status' => ['nullable', Rule::in(DentalConsultation::HYGIENE_STATUSES)],
            'follow_up_date' => ['nullable', 'date'],
            'previous_dental_treatment' => ['nullable', 'string', 'max:5000'],
            'dental_treatment_frequency' => ['nullable', Rule::in(array_keys(DentalConsultation::TREATMENT_FREQUENCIES))],
            'last_dental_visit' => ['nullable', 'date'],
            'dental_fears' => ['nullable', 'boolean'],
            'dental_concerns' => ['nullable', 'string', 'max:3000'],
            'has_toothache' => ['nullable', 'boolean'],
            'has_bleeding_gums' => ['nullable', 'boolean'],
            'has_bad_breath' => ['nullable', 'boolean'],
            'has_tooth_sensitivity' => ['nullable', 'boolean'],
            'has_jaw_pain' => ['nullable', 'boolean'],
            'has_loose_teeth' => ['nullable', 'boolean'],
            'has_swelling' => ['nullable', 'boolean'],
            'systemic_diabetes' => ['nullable', 'boolean'],
            'systemic_hypertension' => ['nullable', 'boolean'],
            'systemic_heart_disease' => ['nullable', 'boolean'],
            'systemic_bleeding_disorder' => ['nullable', 'boolean'],
            'systemic_allergies' => ['nullable', 'boolean'],
            'systemic_allergy_details' => ['nullable', 'string', 'max:200'],
            'soft_tissue_lips' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'soft_tissue_cheeks' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'soft_tissue_tongue' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'soft_tissue_palate' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'soft_tissue_gingiva' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'soft_tissue_floor_of_mouth' => ['nullable', Rule::in(array_keys(DentalConsultation::SOFT_TISSUE_STATUSES))],
            'periodontal_status' => ['nullable', Rule::in(array_keys(DentalConsultation::PERIODONTAL_STATUSES))],
            'calculus_index' => ['nullable', Rule::in(array_keys(DentalConsultation::CALCULUS_INDICES))],
            'gingival_index' => ['nullable', Rule::in(array_keys(DentalConsultation::GINGIVAL_INDICES))],
            'plaque_index' => ['nullable', Rule::in(array_keys(DentalConsultation::PLAQUE_INDICES))],
            'dmft_decayed' => ['nullable', 'integer', 'min:0', 'max:32'],
            'dmft_missing' => ['nullable', 'integer', 'min:0', 'max:32'],
            'dmft_filled' => ['nullable', 'integer', 'min:0', 'max:32'],
            'dmfs_decayed' => ['nullable', 'integer', 'min:0', 'max:128'],
            'dmfs_missing' => ['nullable', 'integer', 'min:0', 'max:128'],
            'dmfs_filled' => ['nullable', 'integer', 'min:0', 'max:128'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'priority_level' => ['nullable', Rule::in(array_keys(DentalConsultation::PRIORITY_LEVELS))],
        ];
    }

    protected function detailData(array $validated, Request $request): array
    {
        $chart = collect($validated['tooth_chart'] ?? [])->filter()->all();
        $primaryChart = collect($validated['primary_tooth_chart'] ?? [])->filter()->all();

        return [
            'oral_examination_findings' => $validated['oral_examination_findings'] ?? null,
            'tooth_chart' => $chart ?: null,
            'primary_tooth_chart' => $primaryChart ?: null,
            'procedures' => $validated['procedures'] ?? [],
            'teeth_involved' => $validated['teeth_involved'] ?? null,
            'anesthesia_used' => $validated['anesthesia_used'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'treatment_rendered' => $validated['treatment_rendered'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'oral_hygiene_status' => $validated['oral_hygiene_status'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'previous_dental_treatment' => $validated['previous_dental_treatment'] ?? null,
            'dental_treatment_frequency' => $validated['dental_treatment_frequency'] ?? null,
            'last_dental_visit' => $validated['last_dental_visit'] ?? null,
            'dental_fears' => (bool) ($validated['dental_fears'] ?? false),
            'dental_concerns' => $validated['dental_concerns'] ?? null,
            'has_toothache' => (bool) ($validated['has_toothache'] ?? false),
            'has_bleeding_gums' => (bool) ($validated['has_bleeding_gums'] ?? false),
            'has_bad_breath' => (bool) ($validated['has_bad_breath'] ?? false),
            'has_tooth_sensitivity' => (bool) ($validated['has_tooth_sensitivity'] ?? false),
            'has_jaw_pain' => (bool) ($validated['has_jaw_pain'] ?? false),
            'has_loose_teeth' => (bool) ($validated['has_loose_teeth'] ?? false),
            'has_swelling' => (bool) ($validated['has_swelling'] ?? false),
            'systemic_diabetes' => (bool) ($validated['systemic_diabetes'] ?? false),
            'systemic_hypertension' => (bool) ($validated['systemic_hypertension'] ?? false),
            'systemic_heart_disease' => (bool) ($validated['systemic_heart_disease'] ?? false),
            'systemic_bleeding_disorder' => (bool) ($validated['systemic_bleeding_disorder'] ?? false),
            'systemic_allergies' => (bool) ($validated['systemic_allergies'] ?? false),
            'systemic_allergy_details' => $validated['systemic_allergy_details'] ?? null,
            'soft_tissue_lips' => $validated['soft_tissue_lips'] ?? null,
            'soft_tissue_cheeks' => $validated['soft_tissue_cheeks'] ?? null,
            'soft_tissue_tongue' => $validated['soft_tissue_tongue'] ?? null,
            'soft_tissue_palate' => $validated['soft_tissue_palate'] ?? null,
            'soft_tissue_gingiva' => $validated['soft_tissue_gingiva'] ?? null,
            'soft_tissue_floor_of_mouth' => $validated['soft_tissue_floor_of_mouth'] ?? null,
            'periodontal_status' => $validated['periodontal_status'] ?? null,
            'calculus_index' => $validated['calculus_index'] ?? null,
            'gingival_index' => $validated['gingival_index'] ?? null,
            'plaque_index' => $validated['plaque_index'] ?? null,
            'dmft_decayed' => isset($validated['dmft_decayed']) ? (int) $validated['dmft_decayed'] : null,
            'dmft_missing' => isset($validated['dmft_missing']) ? (int) $validated['dmft_missing'] : null,
            'dmft_filled' => isset($validated['dmft_filled']) ? (int) $validated['dmft_filled'] : null,
            'dmfs_decayed' => isset($validated['dmfs_decayed']) ? (int) $validated['dmfs_decayed'] : null,
            'dmfs_missing' => isset($validated['dmfs_missing']) ? (int) $validated['dmfs_missing'] : null,
            'dmfs_filled' => isset($validated['dmfs_filled']) ? (int) $validated['dmfs_filled'] : null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'priority_level' => $validated['priority_level'] ?? null,
        ];
    }

    public function printCard(ClinicVisit $visit, DentalPdfService $pdfService, SettingService $settingService): Response
    {
        abort_unless($visit->visit_type === $this->visitType(), 404);
        $visit->load(['patient.college', 'patient.program', 'clinician', 'dentalConsultation']);

        $settings = $settingService->all();
        $pdf = $pdfService->oralHealthCard($visit, $settings);

        $filename = 'oral-health-card-'.$visit->visit_number.'.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
