<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\EmergencyRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmergencyRecordController extends ClinicRecordController
{
    protected static function routePrefix(): string
    {
        return 'emergency-records';
    }

    protected function visitType(): string
    {
        return ClinicVisit::TYPE_EMERGENCY;
    }

    protected function detailRelation(): string
    {
        return 'emergencyRecord';
    }

    protected function formOptions(): array
    {
        return [
            'triageLevels' => EmergencyRecord::TRIAGE_LEVELS,
            'natures' => EmergencyRecord::NATURES,
            'consciousnessLevels' => EmergencyRecord::CONSCIOUSNESS_LEVELS,
            'interventionOptions' => EmergencyRecord::INTERVENTIONS,
            'outcomes' => EmergencyRecord::OUTCOMES,
        ];
    }

    protected function detailRules(): array
    {
        return [
            'incident_datetime' => ['nullable', 'date'],
            'incident_location' => ['nullable', 'string', 'max:150'],
            'emergency_nature' => ['required', 'string', 'max:60'],
            'triage_level' => ['required', Rule::in(array_keys(EmergencyRecord::TRIAGE_LEVELS))],
            'level_of_consciousness' => ['nullable', Rule::in(array_keys(EmergencyRecord::CONSCIOUSNESS_LEVELS))],
            'gcs_score' => ['nullable', 'integer', 'between:3,15'],
            'incident_description' => ['nullable', 'string', 'max:5000'],
            'interventions' => ['nullable', 'array'],
            'interventions.*' => ['string', Rule::in(EmergencyRecord::INTERVENTIONS)],
            'intervention_notes' => ['nullable', 'string', 'max:5000'],
            'ambulance_called' => ['nullable', 'boolean'],
            'transferred_to_hospital' => ['nullable', 'boolean'],
            'hospital_name' => ['nullable', 'required_if:transferred_to_hospital,1', 'string', 'max:150'],
            'transport_mode' => ['nullable', 'string', 'max:60'],
            'transferred_at' => ['nullable', 'date'],
            'guardian_notified' => ['nullable', 'boolean'],
            'notified_person' => ['nullable', 'string', 'max:120'],
            'notified_at' => ['nullable', 'date'],
            'incident_report_number' => ['nullable', 'string', 'max:60'],
            'outcome' => ['nullable', Rule::in(EmergencyRecord::OUTCOMES)],
        ];
    }

    protected function detailData(array $validated, Request $request): array
    {
        $transferred = $request->boolean('transferred_to_hospital');
        $notified = $request->boolean('guardian_notified');

        return [
            'incident_datetime' => $validated['incident_datetime'] ?? null,
            'incident_location' => $validated['incident_location'] ?? null,
            'emergency_nature' => $validated['emergency_nature'],
            'triage_level' => $validated['triage_level'],
            'level_of_consciousness' => $validated['level_of_consciousness'] ?? null,
            'gcs_score' => $validated['gcs_score'] ?? null,
            'incident_description' => $validated['incident_description'] ?? null,
            'interventions' => $validated['interventions'] ?? [],
            'intervention_notes' => $validated['intervention_notes'] ?? null,
            'ambulance_called' => $request->boolean('ambulance_called'),
            'transferred_to_hospital' => $transferred,
            'hospital_name' => $transferred ? ($validated['hospital_name'] ?? null) : null,
            'transport_mode' => $transferred ? ($validated['transport_mode'] ?? null) : null,
            'transferred_at' => $transferred ? ($validated['transferred_at'] ?? null) : null,
            'guardian_notified' => $notified,
            'notified_person' => $notified ? ($validated['notified_person'] ?? null) : null,
            'notified_at' => $notified ? ($validated['notified_at'] ?? null) : null,
            'incident_report_number' => $validated['incident_report_number'] ?? null,
            'outcome' => $validated['outcome'] ?? null,
        ];
    }
}
