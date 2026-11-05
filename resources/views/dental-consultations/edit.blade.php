<x-layouts.app title="Edit Dental Consultation #{{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('dental-consultations.index') }}">Dental Consultations</a>
        <span>&gt;</span>
        <a href="{{ route('dental-consultations.show', $visit) }}">{{ $visit->visit_number }}</a>
        <span>&gt;</span>
        <span>Edit</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Dental Consultation: {{ $visit->visit_number }}</h1>
            <p class="dashboard-subheading mb-0">Update oral examination, dental chart, and restorative procedures.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dental-consultations.show', $visit) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Record
            </a>
            <a href="{{ route('dental-consultations.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('dental-consultations.update', $visit) }}" novalidate>
        @csrf
        @method('PUT')

        @include('clinic-visits.partials.patient-select', [
            'preselectedPatient' => $preselectedPatient,
            'defaultClinician' => $defaultClinician,
            'visit' => $visit,
        ])

        @include('clinic-visits.partials.vitals-form', ['visit' => $visit])

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-emoji-smile text-cyan fs-5"></i>
                    <span class="fw-bold text-dark">Oral Health Examination</span>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-3" id="dentalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-history" data-bs-toggle="tab" data-bs-target="#panel-history" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Dental History
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-exam" data-bs-toggle="tab" data-bs-target="#panel-exam" type="button" role="tab">
                            <i class="bi bi-search me-1"></i> Oral Examination
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-chart" data-bs-toggle="tab" data-bs-target="#panel-chart" type="button" role="tab">
                            <i class="bi bi-grid me-1"></i> Tooth Chart
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-clinical" data-bs-toggle="tab" data-bs-target="#panel-clinical" type="button" role="tab">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Clinical Management
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-dmft" data-bs-toggle="tab" data-bs-target="#panel-dmft" type="button" role="tab">
                            <i class="bi bi-123 me-1"></i> DMFT / DMFS
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="dentalTabsContent">
                    <div class="tab-pane fade show active" id="panel-history" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Chief Complaint / Dental Reason</label>
                            <textarea name="chief_complaint" rows="2" class="form-control @error('chief_complaint') is-invalid @enderror" required>{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                            @error('chief_complaint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Previous Dental Treatment Frequency</label>
                                <select name="dental_treatment_frequency" class="form-select">
                                    <option value="">-- Select Frequency --</option>
                                    @foreach($treatmentFrequencies as $key => $label)
                                        <option value="{{ $key }}" @selected(old('dental_treatment_frequency', $detail->dental_treatment_frequency) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Last Dental Visit Date</label>
                                <input type="date" name="last_dental_visit" value="{{ old('last_dental_visit', $detail->last_dental_visit?->format('Y-m-d')) }}" class="form-control">
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dental_fears" value="1" id="dental_fears" @checked(old('dental_fears', $detail->dental_fears))>
                                    <label class="form-check-label" for="dental_fears">Patient has dental fears / anxiety</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Previous Dental Treatments (History)</label>
                                <textarea name="previous_dental_treatment" rows="3" class="form-control" placeholder="Previous extractions, fillings, orthodontic work, etc.">{{ old('previous_dental_treatment', $detail->previous_dental_treatment) }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Dental Concerns</label>
                                <textarea name="dental_concerns" rows="2" class="form-control" placeholder="Chief dental concerns expressed by the patient...">{{ old('dental_concerns', $detail->dental_concerns) }}</textarea>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Oral Health Conditions</label>
                                <div class="p-3 border rounded bg-light">
                                    <div class="row g-2">
                                        @php
                                            $oralConditions = [
                                                'has_toothache' => 'Toothache',
                                                'has_bleeding_gums' => 'Bleeding Gums',
                                                'has_bad_breath' => 'Bad Breath / Halitosis',
                                                'has_tooth_sensitivity' => 'Tooth Sensitivity',
                                                'has_jaw_pain' => 'Jaw Pain / TMJ',
                                                'has_loose_teeth' => 'Loose Teeth',
                                                'has_swelling' => 'Facial Swelling',
                                            ];
                                        @endphp
                                        @foreach($oralConditions as $field => $condLabel)
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" @checked(old($field, $detail->{$field}))>
                                                    <label class="form-check-label small" for="{{ $field }}">{{ $condLabel }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Systemic Conditions Affecting Oral Health</label>
                                <div class="p-3 border rounded bg-light">
                                    <div class="row g-2">
                                        @php
                                            $systemicConditions = [
                                                'systemic_diabetes' => 'Diabetes Mellitus',
                                                'systemic_hypertension' => 'Hypertension',
                                                'systemic_heart_disease' => 'Heart Disease',
                                                'systemic_bleeding_disorder' => 'Bleeding Disorder',
                                                'systemic_allergies' => 'Allergies',
                                            ];
                                        @endphp
                                        @foreach($systemicConditions as $field => $condLabel)
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" @checked(old($field, $detail->{$field}))>
                                                    <label class="form-check-label small" for="{{ $field }}">{{ $condLabel }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="col-12 mt-2">
                                            <label class="form-label small">Allergy Details</label>
                                            <input type="text" name="systemic_allergy_details" value="{{ old('systemic_allergy_details', $detail->systemic_allergy_details) }}" class="form-control form-control-sm" placeholder="Specify allergens if applicable">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel-exam" role="tabpanel">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Soft Tissue Examination</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Area</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $softTissues = [
                                                'soft_tissue_lips' => 'Lips',
                                                'soft_tissue_cheeks' => 'Cheeks',
                                                'soft_tissue_tongue' => 'Tongue',
                                                'soft_tissue_palate' => 'Palate',
                                                'soft_tissue_gingiva' => 'Gingiva / Gums',
                                                'soft_tissue_floor_of_mouth' => 'Floor of Mouth',
                                            ];
                                        @endphp
                                        @foreach($softTissues as $field => $areaLabel)
                                            <tr>
                                                <td class="fw-semibold">{{ $areaLabel }}</td>
                                                <td>
                                                    <select name="{{ $field }}" class="form-select form-select-sm">
                                                        <option value="">-- Select --</option>
                                                        @foreach($softTissueStatuses as $key => $statusLabel)
                                                            <option value="{{ $key }}" @selected(old($field, $detail->{$field}) === $key)>{{ $statusLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Periodontal Status</label>
                                <select name="periodontal_status" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($periodontalStatuses as $key => $statusLabel)
                                        <option value="{{ $key }}" @selected(old('periodontal_status', $detail->periodontal_status) === $key)>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Oral Hygiene Status</label>
                                <select name="oral_hygiene_status" class="form-select">
                                    <option value="">-- Select Hygiene Status --</option>
                                    @foreach($hygieneStatuses as $status)
                                        <option value="{{ $status }}" @selected(old('oral_hygiene_status', $detail->oral_hygiene_status) === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Calculus Index</label>
                                <select name="calculus_index" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($calculusIndices as $key => $indexLabel)
                                        <option value="{{ $key }}" @selected(old('calculus_index', $detail->calculus_index) === $key)>{{ $indexLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gingival Index</label>
                                <select name="gingival_index" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($gingivalIndices as $key => $indexLabel)
                                        <option value="{{ $key }}" @selected(old('gingival_index', $detail->gingival_index) === $key)>{{ $indexLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Plaque Index</label>
                                <select name="plaque_index" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($plaqueIndices as $key => $indexLabel)
                                        <option value="{{ $key }}" @selected(old('plaque_index', $detail->plaque_index) === $key)>{{ $indexLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Oral Examination Findings</label>
                                <textarea name="oral_examination_findings" rows="4" class="form-control" placeholder="Detailed oral examination findings...">{{ old('oral_examination_findings', $detail->oral_examination_findings) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel-chart" role="tabpanel">
                        <div class="p-3 bg-light rounded border mb-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <span class="fw-bold text-dark small text-uppercase">Permanent Teeth — Odontogram (FDI Notation)</span>
                                <div class="small text-muted d-flex flex-wrap gap-2">
                                    @foreach($toothConditions as $code => $lbl)
                                        <span><strong class="text-primary">{{ $code }}</strong>: {{ $lbl }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block fw-semibold mb-1">MAXILLARY / UPPER ARCH</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($teeth['upper'] as $t)
                                        @php $chartVal = old("tooth_chart.{$t}", $detail->tooth_chart[$t] ?? ''); @endphp
                                        <div class="text-center p-1 border rounded bg-white" style="width: 58px;">
                                            <div class="fw-bold small text-dark mb-1">#{{ $t }}</div>
                                            <select name="tooth_chart[{{ $t }}]" class="form-select form-select-sm p-1 text-center font-monospace" style="font-size: 0.75rem;">
                                                <option value="">-</option>
                                                @foreach($toothConditions as $code => $lbl)
                                                    <option value="{{ $code }}" @selected($chartVal === $code)>{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <small class="text-muted d-block fw-semibold mb-1">MANDIBULAR / LOWER ARCH</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($teeth['lower'] as $t)
                                        @php $chartVal = old("tooth_chart.{$t}", $detail->tooth_chart[$t] ?? ''); @endphp
                                        <div class="text-center p-1 border rounded bg-white" style="width: 58px;">
                                            <div class="fw-bold small text-dark mb-1">#{{ $t }}</div>
                                            <select name="tooth_chart[{{ $t }}]" class="form-select form-select-sm p-1 text-center font-monospace" style="font-size: 0.75rem;">
                                                <option value="">-</option>
                                                @foreach($toothConditions as $code => $lbl)
                                                    <option value="{{ $code }}" @selected($chartVal === $code)>{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded border">
                            <div class="fw-bold text-dark small text-uppercase mb-3">Primary / Deciduous Teeth</div>

                            <div class="mb-3">
                                <small class="text-muted d-block fw-semibold mb-1">MAXILLARY / UPPER ARCH</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($deciduousTeeth['upper'] as $t)
                                        @php $chartVal = old("primary_tooth_chart.{$t}", $detail->primary_tooth_chart[$t] ?? ''); @endphp
                                        <div class="text-center p-1 border rounded bg-white" style="width: 58px;">
                                            <div class="fw-bold small text-warning mb-1">#{{ $t }}</div>
                                            <select name="primary_tooth_chart[{{ $t }}]" class="form-select form-select-sm p-1 text-center font-monospace" style="font-size: 0.75rem;">
                                                <option value="">-</option>
                                                @foreach($toothConditions as $code => $lbl)
                                                    <option value="{{ $code }}" @selected($chartVal === $code)>{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <small class="text-muted d-block fw-semibold mb-1">MANDIBULAR / LOWER ARCH</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($deciduousTeeth['lower'] as $t)
                                        @php $chartVal = old("primary_tooth_chart.{$t}", $detail->primary_tooth_chart[$t] ?? ''); @endphp
                                        <div class="text-center p-1 border rounded bg-white" style="width: 58px;">
                                            <div class="fw-bold small text-warning mb-1">#{{ $t }}</div>
                                            <select name="primary_tooth_chart[{{ $t }}]" class="form-select form-select-sm p-1 text-center font-monospace" style="font-size: 0.75rem;">
                                                <option value="">-</option>
                                                @foreach($toothConditions as $code => $lbl)
                                                    <option value="{{ $code }}" @selected($chartVal === $code)>{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel-clinical" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label required">Dental Diagnosis</label>
                                <input type="text" name="diagnosis" value="{{ old('diagnosis', $detail->diagnosis) }}" class="form-control @error('diagnosis') is-invalid @enderror" required>
                                @error('diagnosis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Priority Level</label>
                                <select name="priority_level" class="form-select">
                                    <option value="">-- Select Priority --</option>
                                    @foreach($priorityLevels as $key => $levelLabel)
                                        <option value="{{ $key }}" @selected(old('priority_level', $detail->priority_level) === $key)>{{ $levelLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Dental Procedures Rendered</label>
                                <div class="row g-2">
                                    @php $selectedProcedures = old('procedures', $detail->procedures ?? []); @endphp
                                    @foreach($procedureOptions as $proc)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="procedures[]" value="{{ $proc }}" id="proc_{{ md5($proc) }}" @checked(in_array($proc, (array)$selectedProcedures))>
                                                <label class="form-check-label small" for="proc_{{ md5($proc) }}">{{ $proc }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teeth Involved</label>
                                <input type="text" name="teeth_involved" value="{{ old('teeth_involved', $detail->teeth_involved) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Local Anesthesia Used</label>
                                <input type="text" name="anesthesia_used" value="{{ old('anesthesia_used', $detail->anesthesia_used) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Treatment Rendered / Clinical Notes</label>
                                <textarea name="treatment_rendered" rows="4" class="form-control">{{ old('treatment_rendered', $detail->treatment_rendered) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Post-Operative Instructions & Recommendations</label>
                                <textarea name="recommendations" rows="4" class="form-control">{{ old('recommendations', $detail->recommendations) }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Treatment Plan</label>
                                <textarea name="treatment_plan" rows="3" class="form-control" placeholder="Long-term treatment plan including future appointments, referrals, preventive measures...">{{ old('treatment_plan', $detail->treatment_plan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel-dmft" role="tabpanel">
                        <div class="alert alert-info d-flex gap-2 mb-4">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>DMFT</strong> measures dental caries experience in permanent teeth: <strong>D</strong> = Decayed, <strong>M</strong> = Missing due to caries, <strong>F</strong> = Filled. <strong>DMFS</strong> measures at the surface level. Total = D + M + F.
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <span class="fw-bold text-primary">DMFT Index (Tooth Level)</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-4">
                                                <label class="form-label">D — Decayed</label>
                                                <input type="number" name="dmft_decayed" value="{{ old('dmft_decayed', $detail->dmft_decayed) }}" min="0" max="32" class="form-control text-center" id="dmft_d" oninput="calcDmft()">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">M — Missing</label>
                                                <input type="number" name="dmft_missing" value="{{ old('dmft_missing', $detail->dmft_missing) }}" min="0" max="32" class="form-control text-center" id="dmft_m" oninput="calcDmft()">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">F — Filled</label>
                                                <input type="number" name="dmft_filled" value="{{ old('dmft_filled', $detail->dmft_filled) }}" min="0" max="32" class="form-control text-center" id="dmft_f" oninput="calcDmft()">
                                            </div>
                                            <div class="col-12 text-center">
                                                <div class="border rounded p-2 bg-light">
                                                    <small class="text-muted d-block">DMFT Total (D+M+F)</small>
                                                    <span class="fw-bold fs-4 text-primary" id="dmft_total">{{ $detail->dmft_total }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <span class="fw-bold text-success">DMFS Index (Surface Level)</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-4">
                                                <label class="form-label">D — Decayed</label>
                                                <input type="number" name="dmfs_decayed" value="{{ old('dmfs_decayed', $detail->dmfs_decayed) }}" min="0" max="128" class="form-control text-center" id="dmfs_d" oninput="calcDmfs()">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">M — Missing</label>
                                                <input type="number" name="dmfs_missing" value="{{ old('dmfs_missing', $detail->dmfs_missing) }}" min="0" max="128" class="form-control text-center" id="dmfs_m" oninput="calcDmfs()">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">F — Filled</label>
                                                <input type="number" name="dmfs_filled" value="{{ old('dmfs_filled', $detail->dmfs_filled) }}" min="0" max="128" class="form-control text-center" id="dmfs_f" oninput="calcDmfs()">
                                            </div>
                                            <div class="col-12 text-center">
                                                <div class="border rounded p-2 bg-light">
                                                    <small class="text-muted d-block">DMFS Total (D+M+F)</small>
                                                    <span class="fw-bold fs-4 text-success" id="dmfs_total">{{ $detail->dmfs_total }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Follow-up Dental Visit Date</label>
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $detail->follow_up_date ? $detail->follow_up_date->format('Y-m-d') : '') }}" class="form-control">
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('dental-consultations.show', $visit) }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Update Dental Consultation Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')

    @push('scripts')
        <script>
            function calcDmft() {
                const d = parseInt(document.getElementById('dmft_d').value) || 0;
                const m = parseInt(document.getElementById('dmft_m').value) || 0;
                const f = parseInt(document.getElementById('dmft_f').value) || 0;
                document.getElementById('dmft_total').textContent = d + m + f;
            }

            function calcDmfs() {
                const d = parseInt(document.getElementById('dmfs_d').value) || 0;
                const m = parseInt(document.getElementById('dmfs_m').value) || 0;
                const f = parseInt(document.getElementById('dmfs_f').value) || 0;
                document.getElementById('dmfs_total').textContent = d + m + f;
            }
        </script>
    @endpush
</x-layouts.app>
