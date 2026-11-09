<x-layouts.app title="Edit Physical Examination: {{ $exam->pe_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('physical-examinations.index') }}">Physical Examinations</a>
        <span>&gt;</span>
        <a href="{{ route('physical-examinations.show', $exam) }}">{{ $exam->pe_number }}</a>
        <span>&gt;</span>
        <span>Edit</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Physical Examination</h1>
            <p class="dashboard-subheading mb-0">Modifying record for {{ $patient->full_name }} ({{ $exam->pe_number }}).</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('physical-examinations.show', $exam) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Examination Details
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('physical-examinations.update', $exam) }}" novalidate id="pe-form">
        @csrf
        @method('PUT')

        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i>
                <span class="fw-bold text-dark">1. Patient Identification &amp; Examination Context</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <div class="p-2 border rounded bg-light">
                            <span class="fw-bold text-dark">{{ $patient->full_name }}</span>
                            <span class="badge {{ $patient->type === 'student' ? 'badge-blue' : 'badge-green' }} ms-1">{{ ucfirst($patient->type) }}</span>
                            <div class="small text-muted mt-1">ID: {{ $patient->primary_identifier }} &bull; {{ $patient->gender }} &bull; {{ $patient->age }} y/o</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">PE Reference Number</label>
                        <input type="text" name="pe_number" value="{{ old('pe_number', $exam->pe_number) }}" class="form-control fw-bold text-primary" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Examination Date</label>
                        <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date->format('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Examination Purpose</label>
                        <select name="purpose" class="form-select" required>
                            @foreach (['Annual Physical Exam', 'Entrance Examination', 'OJT Medical Clearance', 'Sports Clearance', 'Pre-Employment', 'General Medical'] as $p)
                                <option value="{{ $p }}" @selected(old('purpose', $exam->purpose) === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year / Term</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">None / Not Applicable</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" @selected(old('academic_year_id', $exam->academic_year_id) == $ay->id)>{{ $ay->code }} &mdash; {{ $ay->semester }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Examining Healthcare Provider</label>
                        <select name="clinician_id" id="clinician_id" class="form-select" data-ajax-url="{{ route('select2.clinicians') }}" data-placeholder="Search physician or nurse...">
                            @if (old('clinician_id'))
                                @php $selClinician = \App\Models\Clinician::find(old('clinician_id')); @endphp
                                @if ($selClinician)
                                    <option value="{{ $selClinician->id }}" selected>{{ $selClinician->name }} (PRC #{{ $selClinician->license_number }})</option>
                                @endif
                            @elseif ($exam->clinician)
                                <option value="{{ $exam->clinician->id }}" selected>{{ $exam->clinician->name }} (PRC #{{ $exam->clinician->license_number }})</option>
                            @endif
                        </select>
                        <div class="row g-2 mt-1">
                            <div class="col-7">
                                <input type="text" name="examiner_name" id="examiner_name" value="{{ old('examiner_name', $exam->examiner_name) }}" class="form-control form-control-sm" placeholder="Physician / Nurse Name">
                            </div>
                            <div class="col-5">
                                <input type="text" name="examiner_license_number" id="examiner_license_number" value="{{ old('examiner_license_number', $exam->examiner_license_number) }}" class="form-control form-control-sm font-monospace" placeholder="PRC Lic. #">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                <span class="fw-bold text-dark">2. Medical, Surgical &amp; Family History</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Past Medical History (Check all that apply)</label>
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
                                $selectedPast = old('past_medical_history', $patient->medicalProfile?->past_medical_history ?? []);
                            @endphp
                            @foreach ($pastConditions as $condition)
                                <div class="col-sm-6 col-md-3">
                                    <label class="pe-check-tile" for="pmh_{{ Str::slug($condition) }}">
                                        <input class="form-check-input" type="checkbox" name="past_medical_history[]" value="{{ $condition }}" id="pmh_{{ Str::slug($condition) }}" @checked(in_array($condition, (array) $selectedPast, true))>
                                        <span class="pe-tile-text small text-dark">{{ $condition }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="past_medical_history_notes" value="{{ old('past_medical_history_notes', $patient->medicalProfile?->past_medical_history_notes) }}" class="form-control form-control-sm mt-2" placeholder="Details or other illness not listed above...">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Family Medical History (Hereditary / Familial)</label>
                        <div class="row g-2">
                            @php
                                $familyConditions = ['Hypertension', 'Diabetes Mellitus', 'Asthma', 'Heart Disease', 'Cancer / Malignancy', 'Stroke / CVA', 'Mental Illness', 'Kidney Disease'];
                                $selectedFamily = old('family_medical_history', $patient->medicalProfile?->family_medical_history ?? []);
                            @endphp
                            @foreach ($familyConditions as $fCond)
                                <div class="col-sm-6 col-md-3">
                                    <label class="pe-check-tile" for="fmh_{{ Str::slug($fCond) }}">
                                        <input class="form-check-input" type="checkbox" name="family_medical_history[]" value="{{ $fCond }}" id="fmh_{{ Str::slug($fCond) }}" @checked(in_array($fCond, (array) $selectedFamily, true))>
                                        <span class="pe-tile-text small text-dark">{{ $fCond }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Known Drug Allergies</label>
                        <input type="text" name="allergies_drugs" value="{{ old('allergies_drugs', $patient->medicalProfile?->allergies_drugs ?? $patient->allergies) }}" class="form-control" placeholder="e.g. Penicillin, Aspirin, Sulfa drugs, None">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Known Food &amp; Other Allergies</label>
                        <input type="text" name="allergies_food" value="{{ old('allergies_food', $patient->medicalProfile?->allergies_food) }}" class="form-control" placeholder="e.g. Seafood, Eggs, Dust, Pollen">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Existing Diagnosed Conditions / Maintenance</label>
                        <input type="text" name="existing_conditions" value="{{ old('existing_conditions', $patient->medicalProfile?->existing_conditions) }}" class="form-control" placeholder="e.g. Bronchial asthma on salbutamol inhaler">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Maintenance Medications</label>
                        <input type="text" name="current_medications" value="{{ old('current_medications', $patient->medicalProfile?->current_medications) }}" class="form-control" placeholder="e.g. Amlodipine 5mg OD, Multivitamins">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse text-danger"></i>
                    <span class="fw-bold text-dark">3. Vital Signs &amp; Anthropometrics</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Computed BMI:</span>
                    <span id="live-bmi-badge" class="badge bg-secondary px-2 py-1 fs-6">0.0 (—)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Blood Pressure (Systolic)</label>
                        <div class="input-group">
                            <input type="number" name="systolic" id="systolic" value="{{ old('systolic', $exam->systolic) }}" class="form-control" placeholder="120">
                            <span class="input-group-text small">mmHg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Diastolic</label>
                        <div class="input-group">
                            <input type="number" name="diastolic" id="diastolic" value="{{ old('diastolic', $exam->diastolic) }}" class="form-control" placeholder="80">
                            <span class="input-group-text small">mmHg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Pulse / Heart Rate</label>
                        <div class="input-group">
                            <input type="number" name="pulse_rate" value="{{ old('pulse_rate', $exam->pulse_rate) }}" class="form-control">
                            <span class="input-group-text small">bpm</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Respiratory Rate</label>
                        <div class="input-group">
                            <input type="number" name="respiratory_rate" value="{{ old('respiratory_rate', $exam->respiratory_rate) }}" class="form-control">
                            <span class="input-group-text small">cpm</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Body Temperature</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="temperature" value="{{ old('temperature', $exam->temperature) }}" class="form-control">
                            <span class="input-group-text small">°C</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Oxygen Saturation (SpO2)</label>
                        <div class="input-group">
                            <input type="number" name="oxygen_saturation" value="{{ old('oxygen_saturation', $exam->oxygen_saturation) }}" class="form-control">
                            <span class="input-group-text small">%</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Height</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="height_cm" id="height_cm" value="{{ old('height_cm', $exam->height_cm) }}" class="form-control" required>
                            <span class="input-group-text small">cm</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Weight</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="weight_kg" id="weight_kg" value="{{ old('weight_kg', $exam->weight_kg) }}" class="form-control" required>
                            <span class="input-group-text small">kg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Visual Acuity (OD / Right)</label>
                        <input type="text" name="visual_acuity_right" value="{{ old('visual_acuity_right', $exam->visual_acuity_right) }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Visual Acuity (OS / Left)</label>
                        <input type="text" name="visual_acuity_left" value="{{ old('visual_acuity_left', $exam->visual_acuity_left) }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <label class="pe-check-tile" for="visual_acuity_is_corrected">
                            <input class="form-check-input" type="checkbox" name="visual_acuity_is_corrected" value="1" id="visual_acuity_is_corrected" @checked(old('visual_acuity_is_corrected', $exam->visual_acuity_is_corrected))>
                            <span class="pe-tile-text small text-dark">With corrective lenses</span>
                        </label>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Color Vision</label>
                        <select name="color_vision" class="form-select">
                            <option value="Normal" @selected(old('color_vision', $exam->color_vision) === 'Normal')>Normal / Intact</option>
                            <option value="Defective" @selected(old('color_vision', $exam->color_vision) === 'Defective')>Color Deficiency Detected</option>
                            <option value="Not Tested" @selected(old('color_vision', $exam->color_vision) === 'Not Tested')>Not Tested</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hearing Acuity</label>
                        <select name="hearing" class="form-select">
                            <option value="Normal" @selected(old('hearing', $exam->hearing) === 'Normal')>Normal / Intact</option>
                            <option value="Impaired" @selected(old('hearing', $exam->hearing) === 'Impaired')>Hearing Impaired</option>
                            <option value="Not Tested" @selected(old('hearing', $exam->hearing) === 'Not Tested')>Not Tested</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">General Appearance</label>
                        <input type="text" name="general_appearance" value="{{ old('general_appearance', $exam->general_appearance) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-clipboard2-pulse text-primary"></i>
                <span class="fw-bold text-dark">3. Systematic Physical Examination Findings</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">ORGAN SYSTEM / REGION</th>
                                <th style="width: 25%;">ASSESSMENT</th>
                                <th style="width: 50%;">REMARKS / CLINICAL FINDINGS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $systems = [
                                    'skin' => 'Skin & Integument',
                                    'heent' => 'Head, Eyes, Ears, Nose & Throat (HEENT)',
                                    'neck' => 'Neck & Cervical Lymph Nodes',
                                    'chest_lungs' => 'Chest & Lungs',
                                    'heart' => 'Cardiovascular & Heart',
                                    'abdomen' => 'Abdomen & Viscera',
                                    'musculoskeletal' => 'Musculoskeletal & Spine',
                                    'neurological' => 'Neurological / Cranial Nerves',
                                    'dental' => 'Dental / Oral Cavity'
                                ];
                            @endphp
                            @foreach ($systems as $key => $label)
                                @php
                                    $sysData = $exam->{$key} ?? [];
                                    $currentStatus = is_array($sysData) ? ($sysData['status'] ?? 'normal') : 'normal';
                                    $currentRemarks = is_array($sysData) ? ($sysData['remarks'] ?? null) : null;
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $label }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label class="pe-status-pill pill-normal" for="{{ $key }}_norm">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="normal" id="{{ $key }}_norm" @checked(old("systems.{$key}.status", $currentStatus) === 'normal')>
                                                <span>Normal</span>
                                            </label>
                                            <label class="pe-status-pill pill-abnormal" for="{{ $key }}_abn">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="abnormal" id="{{ $key }}_abn" @checked(old("systems.{$key}.status", $currentStatus) === 'abnormal')>
                                                <span>Abnormal</span>
                                            </label>
                                            <label class="pe-status-pill pill-deferred" for="{{ $key }}_def">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="deferred" id="{{ $key }}_def" @checked(old("systems.{$key}.status", $currentStatus) === 'deferred')>
                                                <span>Deferred</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="systems[{{ $key }}][remarks]" value="{{ old("systems.{$key}.remarks", $currentRemarks) }}" class="form-control form-control-sm" placeholder="Normal findings or enter specific remarks...">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-journal-medical text-primary"></i>
                <span class="fw-bold text-dark">4. Diagnostics, Clinical Impression &amp; Recommendations</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Chest X-Ray Findings</label>
                        <input type="text" name="chest_xray_findings" value="{{ old('chest_xray_findings', $exam->chest_xray_findings) }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chest X-Ray Date</label>
                        <input type="date" name="chest_xray_date" value="{{ old('chest_xray_date', $exam->chest_xray_date?->format('Y-m-d')) }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Laboratory Diagnostic Summary</label>
                        <textarea name="laboratory_notes" rows="2" class="form-control">{{ old('laboratory_notes', $exam->laboratory_notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Clinical Impression / Diagnostic Findings</label>
                        <textarea name="clinical_findings" rows="2" class="form-control">{{ old('clinical_findings', $exam->clinical_findings) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Medical Recommendations &amp; Advice</label>
                        <textarea name="medical_recommendations" rows="2" class="form-control">{{ old('medical_recommendations', $exam->medical_recommendations) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-award text-success"></i>
                <span class="fw-bold text-dark">5. Medical Fitness Assessment &amp; Activity Clearance</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label required fw-bold text-dark">Overall Clinical Fitness Classification</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="pe-fitness-card fitness-success" for="fit_class_a">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class A" id="fit_class_a" @checked(old('fitness_assessment', $exam->fitness_assessment) === 'Class A') required>
                                    <div>
                                        <span class="fw-bold text-success d-block">Class A &mdash; Physically Fit</span>
                                        <div class="small text-muted mt-1">Fit for all school activities, sports, heavy physical training, work, and immersion.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label class="pe-fitness-card" for="fit_class_b">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class B" id="fit_class_b" @checked(old('fitness_assessment', $exam->fitness_assessment) === 'Class B')>
                                    <div>
                                        <span class="fw-bold text-primary d-block">Class B &mdash; Fit with Minor Defect</span>
                                        <div class="small text-muted mt-1">Fit to work / study with minor correctable defect.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label class="pe-fitness-card fitness-warning" for="fit_class_c">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class C" id="fit_class_c" @checked(old('fitness_assessment', $exam->fitness_assessment) === 'Class C')>
                                    <div>
                                        <span class="fw-bold text-warning d-block">Class C &mdash; Fit with Limitations</span>
                                        <div class="small text-muted mt-1">Requires medical supervision or limitation from strenuous activities.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="pe-fitness-card fitness-warning" for="fit_class_d">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class D" id="fit_class_d" @checked(old('fitness_assessment', $exam->fitness_assessment) === 'Class D')>
                                    <div>
                                        <span class="fw-bold text-secondary d-block">Class D &mdash; Temporarily Unfit</span>
                                        <div class="small text-muted mt-1">Pending diagnostic completion, treatment, or recovery.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="pe-fitness-card fitness-danger" for="fit_class_e">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class E" id="fit_class_e" @checked(old('fitness_assessment', $exam->fitness_assessment) === 'Class E')>
                                    <div>
                                        <span class="fw-bold text-danger d-block">Class E &mdash; Permanently Unfit</span>
                                        <div class="small text-muted mt-1">Medical condition precludes physical activity or enrollment duties.</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">Specific Activity Clearances (HSD Form 1 &amp; 4)</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">CLEARANCE ACTIVITY / PURPOSE</th>
                                        <th style="width: 25%;" class="text-center">FIT</th>
                                        <th style="width: 25%;" class="text-center">UNFIT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $activities = [
                                            'physical_activities' => 'Undergo Physical Activities / Trainings / Sports',
                                            'on_the_job_training' => 'On-the-Job Training (OJT)',
                                            'work_immersion' => 'Work Immersion',
                                            'to_work' => 'Fit to Work / Return to School',
                                            'to_travel' => 'Fit to Travel',
                                            'other_requirements' => 'Other Academic / Institutional Requirements'
                                        ];
                                        $savedActs = (array) ($exam->fitness_activities ?? []);
                                    @endphp
                                    @foreach ($activities as $actKey => $actLabel)
                                        @php
                                            $actVal = $savedActs[$actKey] ?? 'fit';
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $actLabel }}</td>
                                            <td class="text-center">
                                                <label class="pe-status-pill pill-normal" for="{{ $actKey }}_fit">
                                                    <input class="form-check-input" type="radio" name="fitness_activities[{{ $actKey }}]" value="fit" id="{{ $actKey }}_fit" @checked(old("fitness_activities.{$actKey}", $actVal) === 'fit')>
                                                    <span>Fit</span>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <label class="pe-status-pill pill-abnormal" for="{{ $actKey }}_unfit">
                                                    <input class="form-check-input" type="radio" name="fitness_activities[{{ $actKey }}]" value="unfit" id="{{ $actKey }}_unfit" @checked(old("fitness_activities.{$actKey}", $actVal) === 'unfit')>
                                                    <span>Unfit</span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('physical-examinations.show', $exam) }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Updating…">
                <i class="bi bi-check-lg me-1"></i> Update Physical Examination
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')

    @pushOnce('scripts', 'pe-calculator-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const heightInput = document.getElementById('height_cm');
                const weightInput = document.getElementById('weight_kg');
                const badge = document.getElementById('live-bmi-badge');

                function calculateBmi() {
                    const h = parseFloat(heightInput?.value);
                    const w = parseFloat(weightInput?.value);

                    if (!h || !w || h <= 0 || w <= 0) {
                        badge.className = 'badge bg-secondary px-2 py-1 fs-6';
                        badge.innerText = '0.0 (—)';
                        return;
                    }

                    const heightM = h / 100;
                    const bmi = (w / (heightM * heightM)).toFixed(2);
                    let category = 'Normal';
                    let badgeClass = 'badge bg-success px-2 py-1 fs-6';

                    if (bmi < 18.5) {
                        category = 'Underweight';
                        badgeClass = 'badge bg-warning text-dark px-2 py-1 fs-6';
                    } else if (bmi < 25.0) {
                        category = 'Normal';
                        badgeClass = 'badge bg-success px-2 py-1 fs-6';
                    } else if (bmi < 30.0) {
                        category = 'Overweight';
                        badgeClass = 'badge bg-warning text-dark px-2 py-1 fs-6';
                    } else if (bmi < 35.0) {
                        category = 'Obese I';
                        badgeClass = 'badge bg-danger px-2 py-1 fs-6';
                    } else {
                        category = 'Obese II';
                        badgeClass = 'badge bg-danger px-2 py-1 fs-6';
                    }

                    badge.className = badgeClass;
                    badge.innerText = `${bmi} (${category})`;
                }

                heightInput?.addEventListener('input', calculateBmi);
                weightInput?.addEventListener('input', calculateBmi);
                calculateBmi();

                if (window.jQuery) {
                    window.jQuery('#clinician_id').on('select2:select', (e) => {
                        const data = e.params.data;
                        if (data) {
                            if (data.name) {
                                document.getElementById('examiner_name').value = data.name;
                            }
                            if (data.license_number) {
                                document.getElementById('examiner_license_number').value = data.license_number;
                            }
                        }
                    });
                }
            });
        </script>
    @endPushOnce
</x-layouts.app>

