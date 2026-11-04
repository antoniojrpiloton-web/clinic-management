<?php

namespace App\Http\Controllers;

use App\Models\MedicalProfile;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalProfileController extends Controller
{
    public function show(Patient $patient): View
    {
        $profile = $patient->medicalProfile ?? new MedicalProfile(['patient_id' => $patient->id]);

        return view('medical-profiles.show', [
            'patient' => $patient,
            'profile' => $profile,
        ]);
    }

    public function edit(Patient $patient): View
    {
        $profile = $patient->medicalProfile ?? new MedicalProfile(['patient_id' => $patient->id]);

        return view('medical-profiles.edit', [
            'patient' => $patient,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'past_medical_history' => 'nullable|array',
            'past_medical_history.*' => 'string',
            'past_medical_history_notes' => 'nullable|string',
            'family_medical_history' => 'nullable|array',
            'family_medical_history.*' => 'string',
            'family_medical_history_notes' => 'nullable|string',
            'allergies_food' => 'nullable|string|max:500',
            'allergies_drugs' => 'nullable|string|max:500',
            'allergies_others' => 'nullable|string|max:500',
            'existing_conditions' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'hospitalization_history' => 'nullable|array',
            'hospitalization_history.*.year' => 'nullable|string',
            'hospitalization_history.*.reason' => 'nullable|string',
            'hospitalization_history.*.hospital' => 'nullable|string',
            'surgical_history' => 'nullable|array',
            'surgical_history.*.year' => 'nullable|string',
            'surgical_history.*.procedure' => 'nullable|string',
            'surgical_history.*.hospital' => 'nullable|string',
            'smoking_status' => 'nullable|string|in:non-smoker,occasional,regular,former',
            'alcohol_status' => 'nullable|string|in:non-drinker,occasional,regular',
            'immunizations' => 'nullable|array',
            'immunizations.*' => 'string',
            'notes' => 'nullable|string',
        ]);

        $hospitalizations = array_values(array_filter($validated['hospitalization_history'] ?? [], fn ($item) => ! empty($item['reason']) || ! empty($item['hospital'])));
        $surgeries = array_values(array_filter($validated['surgical_history'] ?? [], fn ($item) => ! empty($item['procedure']) || ! empty($item['hospital'])));

        $data = [
            'past_medical_history' => $validated['past_medical_history'] ?? [],
            'past_medical_history_notes' => $validated['past_medical_history_notes'] ?? null,
            'family_medical_history' => $validated['family_medical_history'] ?? [],
            'family_medical_history_notes' => $validated['family_medical_history_notes'] ?? null,
            'allergies_food' => $validated['allergies_food'] ?? null,
            'allergies_drugs' => $validated['allergies_drugs'] ?? null,
            'allergies_others' => $validated['allergies_others'] ?? null,
            'existing_conditions' => $validated['existing_conditions'] ?? null,
            'current_medications' => $validated['current_medications'] ?? null,
            'hospitalization_history' => $hospitalizations,
            'surgical_history' => $surgeries,
            'smoking_status' => $validated['smoking_status'] ?? null,
            'alcohol_status' => $validated['alcohol_status'] ?? null,
            'immunizations' => $validated['immunizations'] ?? [],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => auth()->id(),
        ];

        $patient->medicalProfile()->updateOrCreate(['patient_id' => $patient->id], $data);

        $patient->update([
            'allergies' => $data['allergies_drugs'] ?: ($data['allergies_food'] ?: $data['allergies_others']),
            'medical_history' => $data['existing_conditions'] ?: $data['past_medical_history_notes'],
        ]);

        return redirect()->route('patients.show', $patient)->with('success', 'Medical profile updated successfully.');
    }
}
