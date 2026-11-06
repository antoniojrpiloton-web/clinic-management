<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\DiseaseCategory;
use Illuminate\Http\Request;

class MedicalConsultationController extends ClinicRecordController
{
    protected static function routePrefix(): string
    {
        return 'medical-consultations';
    }

    protected function visitType(): string
    {
        return ClinicVisit::TYPE_MEDICAL;
    }

    protected function detailRelation(): string
    {
        return 'medicalConsultation';
    }

    protected function formOptions(): array
    {
        return [
            'diseaseCategories' => DiseaseCategory::active()
                ->orderBy('classification')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ];
    }

    protected function detailRules(): array
    {
        return [
            'disease_category_id' => ['nullable', 'exists:disease_categories,id'],
            'history_of_present_illness' => ['nullable', 'string', 'max:5000'],
            'physical_findings' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'icd_code' => ['nullable', 'string', 'max:20'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'medications_prescribed' => ['nullable', 'string', 'max:3000'],
            'advice' => ['nullable', 'string', 'max:3000'],
            'medical_certificate_issued' => ['nullable', 'boolean'],
            'rest_days' => ['nullable', 'integer', 'between:0,60'],
            'referred_to' => ['nullable', 'string', 'max:150'],
            'referral_reason' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }

    protected function detailData(array $validated, Request $request): array
    {
        $diseaseCategoryId = $validated['disease_category_id'] ?? null;
        if (! $diseaseCategoryId && ! empty($validated['diagnosis'])) {
            $diseaseCategoryId = $this->resolveDiseaseCategoryFromDiagnosis((string) $validated['diagnosis']);
        }

        return [
            'disease_category_id' => $diseaseCategoryId,
            'history_of_present_illness' => $validated['history_of_present_illness'] ?? null,
            'physical_findings' => $validated['physical_findings'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'icd_code' => $validated['icd_code'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'medications_prescribed' => $validated['medications_prescribed'] ?? null,
            'advice' => $validated['advice'] ?? null,
            'medical_certificate_issued' => $request->boolean('medical_certificate_issued'),
            'rest_days' => $validated['rest_days'] ?? null,
            'referred_to' => $validated['referred_to'] ?? null,
            'referral_reason' => $validated['referral_reason'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ];
    }

    protected function resolveDiseaseCategoryFromDiagnosis(string $diagnosis): ?int
    {
        $lower = strtolower($diagnosis);
        $categories = DiseaseCategory::active()->orderBy('sort_order')->get();

        foreach ($categories as $cat) {
            if (strtolower($cat->name) === $lower) {
                return $cat->id;
            }
            foreach ($cat->keywordList() as $kw) {
                if ($kw !== '' && str_contains($lower, $kw)) {
                    return $cat->id;
                }
            }
        }

        return null;
    }
}
