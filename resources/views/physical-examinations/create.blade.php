<x-layouts.app title="New Physical Examination">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('physical-examinations.index') }}">Physical Examinations</a>
        <span>&gt;</span>
        <span>New Physical Examination</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">New Physical Examination</h1>
            <p class="dashboard-subheading mb-0">Record clinical vitals, systematic physical findings, and medical fitness assessment.</p>
        </div>

        <a href="{{ route('physical-examinations.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to PE Registry
        </a>
    </div>

    <form method="POST" action="{{ route('physical-examinations.store') }}" novalidate id="pe-form">
        @csrf

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i>
                <span class="fw-bold text-dark">1. Patient Identification &amp; Examination Context</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Select Patient</label>
                        @if ($preselectedPatient)
                            <input type="hidden" name="patient_id" value="{{ $preselectedPatient->id }}">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-bold text-dark">{{ $preselectedPatient->full_name }}</span>
                                    <span class="badge {{ $preselectedPatient->type === 'student' ? 'badge-blue' : 'badge-green' }} ms-1">{{ ucfirst($preselectedPatient->type) }}</span>
                                    <div class="small text-muted mt-1">ID: {{ $preselectedPatient->primary_identifier }} • DOB: {{ $preselectedPatient->date_of_birth->format('M d, Y') }} ({{ $preselectedPatient->age }} y/o) • {{ $preselectedPatient->gender }}</div>
                                </div>
                                <a href="{{ route('physical-examinations.create') }}" class="btn btn-sm btn-outline-secondary">Change</a>
                            </div>
                        @else
                            <select name="patient_id" id="patient_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search patient by name, ID number, or phone..." required>
                                @if (old('patient_id') && ($oldPatient = \App\Models\Patient::find(old('patient_id'))))
                                    <option value="{{ $oldPatient->id }}" selected>{{ $oldPatient->full_name }} ({{ $oldPatient->primary_identifier }})</option>
                                @endif
                            </select>
                        @endif
                        @error('patient_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">PE Reference Number</label>
                        <input type="text" name="pe_number" value="{{ old('pe_number', $nextPeNumber) }}" class="form-control fw-bold text-primary" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Examination Date</label>
                        <input type="date" name="exam_date" value="{{ old('exam_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Examination Purpose</label>
                        <select name="purpose" class="form-select" required>
                            @foreach (['Annual Physical Exam', 'Entrance Examination', 'OJT Medical Clearance', 'Sports Clearance', 'Pre-Employment', 'General Medical'] as $p)
                                <option value="{{ $p }}" @selected(old('purpose') === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year / Term</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">None / Not Applicable</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" @selected(old('academic_year_id', $currentAy?->id) == $ay->id)>{{ $ay->code }} &mdash; {{ $ay->semester }}</option>
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
                            @elseif ($defaultClinician)
                                <option value="{{ $defaultClinician->id }}" selected>{{ $defaultClinician->name }} (PRC #{{ $defaultClinician->license_number }})</option>
                            @endif
                        </select>
                        <div class="row g-2 mt-1">
                            <div class="col-7">
                                <input type="text" name="examiner_name" id="examiner_name" value="{{ old('examiner_name', $defaultClinician?->name ?? auth()->user()->name) }}" class="form-control form-control-sm" placeholder="Physician / Examiner Name">
                            </div>
                            <div class="col-5">
                                <input type="text" name="examiner_license_number" id="examiner_license_number" value="{{ old('examiner_license_number', $defaultClinician?->license_number ?? '') }}" class="form-control form-control-sm font-monospace" placeholder="PRC Lic. #">
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
                                $selectedPast = old('past_medical_history', $preselectedPatient?->medicalProfile?->past_medical_history ?? []);
                            @endphp
                            @foreach ($pastConditions as $condition)
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="past_medical_history[]" value="{{ $condition }}" id="pmh_{{ Str::slug($condition) }}" @checked(in_array($condition, (array) $selectedPast, true))>
                                        <label class="form-check-label small" for="pmh_{{ Str::slug($condition) }}">{{ $condition }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="past_medical_history_notes" value="{{ old('past_medical_history_notes', $preselectedPatient?->medicalProfile?->past_medical_history_notes) }}" class="form-control form-control-sm mt-2" placeholder="Details or other illness not listed above...">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Family Medical History (Hereditary / Familial)</label>
                        <div class="row g-2">
                            @php
                                $familyConditions = ['Hypertension', 'Diabetes Mellitus', 'Asthma', 'Heart Disease', 'Cancer / Malignancy', 'Stroke / CVA', 'Mental Illness', 'Kidney Disease'];
                                $selectedFamily = old('family_medical_history', $preselectedPatient?->medicalProfile?->family_medical_history ?? []);
                            @endphp
                            @foreach ($familyConditions as $fCond)
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="family_medical_history[]" value="{{ $fCond }}" id="fmh_{{ Str::slug($fCond) }}" @checked(in_array($fCond, (array) $selectedFamily, true))>
                                        <label class="form-check-label small" for="fmh_{{ Str::slug($fCond) }}">{{ $fCond }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Known Drug Allergies</label>
                        <input type="text" name="allergies_drugs" value="{{ old('allergies_drugs', $preselectedPatient?->medicalProfile?->allergies_drugs ?? $preselectedPatient?->allergies) }}" class="form-control" placeholder="e.g. Penicillin, Aspirin, Sulfa drugs, None">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Known Food &amp; Other Allergies</label>
                        <input type="text" name="allergies_food" value="{{ old('allergies_food', $preselectedPatient?->medicalProfile?->allergies_food) }}" class="form-control" placeholder="e.g. Seafood, Eggs, Dust, Pollen">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Existing Diagnosed Conditions / Maintenance</label>
                        <input type="text" name="existing_conditions" value="{{ old('existing_conditions', $preselectedPatient?->medicalProfile?->existing_conditions) }}" class="form-control" placeholder="e.g. Bronchial asthma on salbutamol inhaler">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Maintenance Medications</label>
                        <input type="text" name="current_medications" value="{{ old('current_medications', $preselectedPatient?->medicalProfile?->current_medications) }}" class="form-control" placeholder="e.g. Amlodipine 5mg OD, Multivitamins">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Previous Hospitalizations (Reason &amp; Year)</label>
                        <input type="text" name="hospitalization_history" value="{{ old('hospitalization_history') }}" class="form-control" placeholder="e.g. Dengue Fever (2021) at Surigao Medical Center">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Surgical History / Operations</label>
                        <input type="text" name="surgical_history" value="{{ old('surgical_history') }}" class="form-control" placeholder="e.g. Appendectomy (2022)">
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
                            <input type="number" name="systolic" id="systolic" value="{{ old('systolic', 120) }}" class="form-control" placeholder="120">
                            <span class="input-group-text small">mmHg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Diastolic</label>
                        <div class="input-group">
                            <input type="number" name="diastolic" id="diastolic" value="{{ old('diastolic', 80) }}" class="form-control" placeholder="80">
                            <span class="input-group-text small">mmHg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Pulse / Heart Rate</label>
                        <div class="input-group">
                            <input type="number" name="pulse_rate" value="{{ old('pulse_rate', 76) }}" class="form-control" placeholder="76">
                            <span class="input-group-text small">bpm</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Respiratory Rate</label>
                        <div class="input-group">
                            <input type="number" name="respiratory_rate" value="{{ old('respiratory_rate', 18) }}" class="form-control" placeholder="18">
                            <span class="input-group-text small">cpm</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Body Temperature</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="temperature" value="{{ old('temperature', 36.5) }}" class="form-control" placeholder="36.5">
                            <span class="input-group-text small">°C</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Oxygen Saturation (SpO2)</label>
                        <div class="input-group">
                            <input type="number" name="oxygen_saturation" value="{{ old('oxygen_saturation', 99) }}" class="form-control" placeholder="99">
                            <span class="input-group-text small">%</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Height</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="height_cm" id="height_cm" value="{{ old('height_cm') }}" class="form-control" placeholder="165.0" required>
                            <span class="input-group-text small">cm</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Weight</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="weight_kg" id="weight_kg" value="{{ old('weight_kg') }}" class="form-control" placeholder="60.0" required>
                            <span class="input-group-text small">kg</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Visual Acuity (OD / Right)</label>
                        <input type="text" name="visual_acuity_right" value="{{ old('visual_acuity_right', '20/20') }}" class="form-control" placeholder="20/20">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Visual Acuity (OS / Left)</label>
                        <input type="text" name="visual_acuity_left" value="{{ old('visual_acuity_left', '20/20') }}" class="form-control" placeholder="20/20">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="visual_acuity_is_corrected" value="1" id="visual_acuity_is_corrected" @checked(old('visual_acuity_is_corrected'))>
                            <label class="form-check-label small" for="visual_acuity_is_corrected">With corrective lenses / glasses</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Color Vision (Ishihara)</label>
                        <select name="color_vision" class="form-select">
                            <option value="Normal" @selected(old('color_vision') === 'Normal')>Normal / Intact</option>
                            <option value="Defective" @selected(old('color_vision') === 'Defective')>Color Deficiency Detected</option>
                            <option value="Not Tested" @selected(old('color_vision') === 'Not Tested')>Not Tested</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hearing Acuity</label>
                        <select name="hearing" class="form-select">
                            <option value="Normal" @selected(old('hearing') === 'Normal')>Normal / Intact</option>
                            <option value="Impaired" @selected(old('hearing') === 'Impaired')>Hearing Impaired</option>
                            <option value="Not Tested" @selected(old('hearing') === 'Not Tested')>Not Tested</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">General Appearance / Nutritional State</label>
                        <input type="text" name="general_appearance" value="{{ old('general_appearance', 'Conscious, coherent, ambulatory, well-nourished') }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-clipboard2-pulse text-primary"></i>
                <span class="fw-bold text-dark">4. Systematic Physical Examination Findings</span>
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
                                    'skin' => 'Skin & Integument (pallor, cyanosis, jaundice, rashes, scars)',
                                    'heent' => 'Head, Eyes, Ears, Nose & Throat (HEENT, conjunctiva, tonsils)',
                                    'neck' => 'Neck, Thyroid Gland & Cervical Lymph Nodes',
                                    'chest_lungs' => 'Chest & Lungs (symmetrical expansion, breath sounds)',
                                    'heart' => 'Cardiovascular / Heart (rhythm, heart sounds, murmurs)',
                                    'abdomen' => 'Abdomen (palpation, tenderness, liver, spleen)',
                                    'musculoskeletal' => 'Musculoskeletal & Spine (mobility, gait, deformities, scoliosis)',
                                    'neurological' => 'Neurological / Cranial Nerves / Motor Reflexes',
                                    'dental' => 'Dental / Oral Hygiene (caries, gums, prostheses)'
                                ];
                            @endphp
                            @foreach ($systems as $key => $label)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $label }}</td>
                                    <td>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="normal" id="{{ $key }}_norm" @checked(old("systems.{$key}.status", 'normal') === 'normal')>
                                                <label class="form-check-label text-success fw-semibold small" for="{{ $key }}_norm">Normal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="abnormal" id="{{ $key }}_abn" @checked(old("systems.{$key}.status") === 'abnormal')>
                                                <label class="form-check-label text-danger fw-semibold small" for="{{ $key }}_abn">Abnormal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="systems[{{ $key }}][status]" value="deferred" id="{{ $key }}_def" @checked(old("systems.{$key}.status") === 'deferred')>
                                                <label class="form-check-label text-muted small" for="{{ $key }}_def">Deferred</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="systems[{{ $key }}][remarks]" value="{{ old("systems.{$key}.remarks") }}" class="form-control form-control-sm" placeholder="Normal findings or enter specific remarks...">
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
                <span class="fw-bold text-dark">5. Diagnostic Tests &amp; Laboratory Findings</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Chest X-Ray Findings</label>
                        <input type="text" name="chest_xray_findings" value="{{ old('chest_xray_findings', 'Normal chest radiograph') }}" class="form-control" placeholder="e.g. Normal chest radiograph, No active lung lesions">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chest X-Ray Date</label>
                        <input type="date" name="chest_xray_date" value="{{ old('chest_xray_date') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Laboratory Diagnostic Summary (CBC, Urinalysis, Fecalysis, Drug Test, Hepatitis)</label>
                        <textarea name="laboratory_notes" rows="2" class="form-control" placeholder="e.g. CBC within normal limits. Urinalysis: Yellow, hazy, RBC 0-2, WBC 1-3. Drug test: Negative.">{{ old('laboratory_notes') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Clinical Impression / Diagnostic Findings</label>
                        <textarea name="clinical_findings" rows="2" class="form-control" placeholder="e.g. Essentially normal physical examination. Fit for academic and university activities.">{{ old('clinical_findings', 'Essentially normal physical examination findings.') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Medical Recommendations &amp; Advice</label>
                        <textarea name="medical_recommendations" rows="2" class="form-control" placeholder="e.g. Maintain healthy diet, adequate hydration, regular exercise. Annual follow-up.">{{ old('medical_recommendations', 'Maintain adequate hydration and balanced nutrition. Follow-up as needed.') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-award text-success"></i>
                <span class="fw-bold text-dark">6. Medical Fitness Assessment &amp; Activity Clearance</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label required fw-bold text-dark">Overall Clinical Fitness Classification</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check p-3 border rounded h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class A" id="fit_class_a" @checked(old('fitness_assessment', 'Class A') === 'Class A') required>
                                    <label class="form-check-label fw-bold text-success d-block" for="fit_class_a">Class A &mdash; Physically Fit</label>
                                    <div class="small text-muted mt-1">Fit for all school activities, sports, heavy physical training, work, and immersion.</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check p-3 border rounded h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class B" id="fit_class_b" @checked(old('fitness_assessment') === 'Class B')>
                                    <label class="form-check-label fw-bold text-info d-block" for="fit_class_b">Class B &mdash; Fit with Minor Defect</label>
                                    <div class="small text-muted mt-1">Fit to work / study with minor correctable defect (e.g. refractive error with glasses).</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check p-3 border rounded h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class C" id="fit_class_c" @checked(old('fitness_assessment') === 'Class C')>
                                    <label class="form-check-label fw-bold text-warning d-block" for="fit_class_c">Class C &mdash; Fit with Limitations</label>
                                    <div class="small text-muted mt-1">Requires medical supervision or limitation from strenuous physical activities.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check p-3 border rounded h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class D" id="fit_class_d" @checked(old('fitness_assessment') === 'Class D')>
                                    <label class="form-check-label fw-bold text-secondary d-block" for="fit_class_d">Class D &mdash; Temporarily Unfit</label>
                                    <div class="small text-muted mt-1">Pending diagnostic completion, treatment, or recovery from active illness.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check p-3 border rounded h-100 bg-light">
                                    <input class="form-check-input" type="radio" name="fitness_assessment" value="Class E" id="fit_class_e" @checked(old('fitness_assessment') === 'Class E')>
                                    <label class="form-check-label fw-bold text-danger d-block" for="fit_class_e">Class E &mdash; Permanently Unfit</label>
                                    <div class="small text-muted mt-1">Medical condition precludes physical activity or standard enrollment duties.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">Specific Activity Clearances (HSD Form 4 Standard)</label>
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
                                            'to_work' => 'Fit to Work',
                                            'to_travel' => 'Fit to Travel',
                                            'other_requirements' => 'Other Academic / Institutional Requirements'
                                        ];
                                    @endphp
                                    @foreach ($activities as $actKey => $actLabel)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $actLabel }}</td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="fitness_activities[{{ $actKey }}]" value="fit" id="{{ $actKey }}_fit" @checked(old("fitness_activities.{$actKey}", 'fit') === 'fit')>
                                                <label class="form-check-label ms-1 text-success fw-bold small" for="{{ $actKey }}_fit">Fit</label>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="radio" name="fitness_activities[{{ $actKey }}]" value="unfit" id="{{ $actKey }}_unfit" @checked(old("fitness_activities.{$actKey}") === 'unfit')>
                                                <label class="form-check-label ms-1 text-danger fw-bold small" for="{{ $actKey }}_unfit">Unfit</label>
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
            <a href="{{ route('physical-examinations.index') }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Saving exam…">
                <i class="bi bi-check-lg me-1"></i> Save Physical Examination Record
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

