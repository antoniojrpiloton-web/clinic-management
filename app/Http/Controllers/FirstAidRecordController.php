<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\FirstAidRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FirstAidRecordController extends ClinicRecordController
{
    protected static function routePrefix(): string
    {
        return 'first-aid-records';
    }

    protected function visitType(): string
    {
        return ClinicVisit::TYPE_FIRST_AID;
    }

    protected function detailRelation(): string
    {
        return 'firstAidRecord';
    }

    protected function complaintRequired(): bool
    {
        return false;
    }

    protected function formOptions(): array
    {
        return [
            'injuryTypes' => FirstAidRecord::INJURY_TYPES,
            'actionOptions' => FirstAidRecord::FIRST_AID_ACTIONS,
        ];
    }

    protected function detailRules(): array
    {
        return [
            'injury_type' => ['required', 'string', 'max:60'],
            'body_part' => ['nullable', 'string', 'max:100'],
            'incident_location' => ['nullable', 'string', 'max:150'],
            'incident_cause' => ['nullable', 'string', 'max:190'],
            'first_aid_given' => ['nullable', 'array'],
            'first_aid_given.*' => ['string', Rule::in(FirstAidRecord::FIRST_AID_ACTIONS)],
            'treatment_notes' => ['nullable', 'string', 'max:5000'],
            'supplies_used' => ['nullable', 'string', 'max:2000'],
            'was_referred' => ['nullable', 'boolean'],
            'referred_to' => ['nullable', 'required_if:was_referred,1', 'string', 'max:150'],
        ];
    }

    protected function detailData(array $validated, Request $request): array
    {
        $referred = $request->boolean('was_referred');

        return [
            'injury_type' => $validated['injury_type'],
            'body_part' => $validated['body_part'] ?? null,
            'incident_location' => $validated['incident_location'] ?? null,
            'incident_cause' => $validated['incident_cause'] ?? null,
            'first_aid_given' => $validated['first_aid_given'] ?? [],
            'treatment_notes' => $validated['treatment_notes'] ?? null,
            'supplies_used' => $validated['supplies_used'] ?? null,
            'was_referred' => $referred,
            'referred_to' => $referred ? ($validated['referred_to'] ?? null) : null,
        ];
    }
}
