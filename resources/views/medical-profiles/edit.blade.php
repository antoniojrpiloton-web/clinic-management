<x-layouts.app title="Edit Medical Profile: {{ $patient->full_name }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('patients.index') }}">Patients</a>
        <span>&gt;</span>
        <a href="{{ route('patients.show', $patient) }}">{{ $patient->full_name }}</a>
        <span>&gt;</span>
        <span>Edit Medical Profile</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Medical Profile</h1>
            <p class="dashboard-subheading mb-0">Update health history, allergies, chronic illnesses, and surgical history for {{ $patient->full_name }}.</p>
        </div>

        <a href="{{ route('medical-profiles.show', $patient) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <form method="POST" action="{{ route('medical-profiles.update', $patient) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-shield-slash text-danger"></i>
                <span class="fw-bold text-dark">1. Allergies &amp; Drug Sensitivities</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Known Drug Allergies</label>
                        <input type="text" name="allergies_drugs" value="{{ old('allergies_drugs', $profile->allergies_drugs) }}" class="form-control" placeholder="e.g. Penicillin, Aspirin, Ibuprofen">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Food Allergies</label>
                        <input type="text" name="allergies_food" value="{{ old('allergies_food', $profile->allergies_food) }}" class="form-control" placeholder="e.g. Seafood, Shrimps, Peanuts, Eggs">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Other / Environmental Allergies</label>
                        <input type="text" name="allergies_others" value="{{ old('allergies_others', $profile->allergies_others) }}" class="form-control" placeholder="e.g. Dust, Latex, Pollen">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                <span class="fw-bold text-dark">2. Past Medical History</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Personal Illnesses (Check all that apply)</label>
                        <div class="row g-2">
                            @php
                                $pastConditions = [
                                    'Asthma / Wheezing',
                                    'Hypertension',
                                    'Diabetes Mellitus',
                                    'Tuberculosis / Primary Complex',
                                    'Epilepsy / Convulsion',
                                    'Heart Disease',
                                    'Kidney / Urinary Problem',
                                    'Hepatitis / Jaundice',
                                    'Allergy / Hay Fever',
                                    'Ear / Vision Problem',
                                    'Bleeding Disorder',
                                    'None'
                                ];
                                $selectedPast = old('past_medical_history', (array) ($profile->past_medical_history ?? []));
                            @endphp
                            @foreach ($pastConditions as $condition)
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="past_medical_history[]" value="{{ $condition }}" id="pmh_{{ Str::slug($condition) }}" @checked(in_array($condition, $selectedPast, true))>
                                        <label class="form-check-label small" for="pmh_{{ Str::slug($condition) }}">{{ $condition }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Past Medical History Details / Notes</label>
                        <textarea name="past_medical_history_notes" rows="2" class="form-control" placeholder="Provide additional details on any past hospitalizations or diagnoses...">{{ old('past_medical_history_notes', $profile->past_medical_history_notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i>
                <span class="fw-bold text-dark">3. Family Medical History (Hereditary / Familial)</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Familial Conditions</label>
                        <div class="row g-2">
                            @php
                                $familyConditions = ['Hypertension', 'Diabetes Mellitus', 'Asthma', 'Heart Disease', 'Cancer / Malignancy', 'Stroke / CVA', 'Mental Illness', 'Kidney Disease'];
                                $selectedFamily = old('family_medical_history', (array) ($profile->family_medical_history ?? []));
                            @endphp
                            @foreach ($familyConditions as $fCond)
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="family_medical_history[]" value="{{ $fCond }}" id="fmh_{{ Str::slug($fCond) }}" @checked(in_array($fCond, $selectedFamily, true))>
                                        <label class="form-check-label small" for="fmh_{{ Str::slug($fCond) }}">{{ $fCond }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Family Medical History Notes</label>
                        <textarea name="family_medical_history_notes" rows="2" class="form-control" placeholder="Specific family relatives affected (e.g. maternal grandmother with diabetes)...">{{ old('family_medical_history_notes', $profile->family_medical_history_notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-heart-pulse text-danger"></i>
                <span class="fw-bold text-dark">4. Active Medical Conditions &amp; Medications</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Existing Diagnosed Conditions</label>
                        <textarea name="existing_conditions" rows="2" class="form-control" placeholder="e.g. Hypertension, Allergic Rhinitis, Scoliosis">{{ old('existing_conditions', $profile->existing_conditions) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Maintenance Medications</label>
                        <textarea name="current_medications" rows="2" class="form-control" placeholder="e.g. Losartan 50mg daily, Cetirizine 10mg PRN">{{ old('current_medications', $profile->current_medications) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Smoking Status</label>
                        <select name="smoking_status" class="form-select">
                            <option value="non-smoker" @selected(old('smoking_status', $profile->smoking_status) === 'non-smoker')>Non-smoker</option>
                            <option value="occasional" @selected(old('smoking_status', $profile->smoking_status) === 'occasional')>Occasional Smoker / Vape</option>
                            <option value="regular" @selected(old('smoking_status', $profile->smoking_status) === 'regular')>Regular Smoker</option>
                            <option value="former" @selected(old('smoking_status', $profile->smoking_status) === 'former')>Former Smoker</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alcohol Status</label>
                        <select name="alcohol_status" class="form-select">
                            <option value="non-drinker" @selected(old('alcohol_status', $profile->alcohol_status) === 'non-drinker')>Non-drinker</option>
                            <option value="occasional" @selected(old('alcohol_status', $profile->alcohol_status) === 'occasional')>Occasional Drinker</option>
                            <option value="regular" @selected(old('alcohol_status', $profile->alcohol_status) === 'regular')>Regular Drinker</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-hospital text-primary"></i>
                <span class="fw-bold text-dark">5. Hospitalization &amp; Surgical Records</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prior Hospitalization History</label>
                        @php
                            $hosps = old('hospitalization_history', $profile->hospitalization_history ?? []);
                        @endphp
                        @for ($i = 0; $i < 2; $i++)
                            <div class="p-2 border rounded mb-2 bg-light">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="text" name="hospitalization_history[{{ $i }}][year]" value="{{ $hosps[$i]['year'] ?? '' }}" class="form-control form-control-sm" placeholder="Year (e.g. 2021)">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="hospitalization_history[{{ $i }}][hospital]" value="{{ $hosps[$i]['hospital'] ?? '' }}" class="form-control form-control-sm" placeholder="Hospital / City">
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="hospitalization_history[{{ $i }}][reason]" value="{{ $hosps[$i]['reason'] ?? '' }}" class="form-control form-control-sm" placeholder="Reason / Diagnosis">
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Surgical History / Operations</label>
                        @php
                            $surgs = old('surgical_history', $profile->surgical_history ?? []);
                        @endphp
                        @for ($i = 0; $i < 2; $i++)
                            <div class="p-2 border rounded mb-2 bg-light">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="text" name="surgical_history[{{ $i }}][year]" value="{{ $surgs[$i]['year'] ?? '' }}" class="form-control form-control-sm" placeholder="Year (e.g. 2022)">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="surgical_history[{{ $i }}][hospital]" value="{{ $surgs[$i]['hospital'] ?? '' }}" class="form-control form-control-sm" placeholder="Hospital / City">
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="surgical_history[{{ $i }}][procedure]" value="{{ $surgs[$i]['procedure'] ?? '' }}" class="form-control form-control-sm" placeholder="Procedure / Operation">
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('medical-profiles.show', $patient) }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Saving profile…">
                <i class="bi bi-check-lg me-1"></i> Save Medical Profile
            </button>
        </div>
    </form>
</x-layouts.app>

