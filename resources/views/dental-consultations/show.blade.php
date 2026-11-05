<x-layouts.app title="Dental Consultation: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('dental-consultations.index') }}">Dental Consultations</a>
        <span>&gt;</span>
        <span>{{ $visit->visit_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">{{ $visit->visit_number }}</h1>
                <span class="badge {{ $visit->type_badge }}"><i class="bi {{ $visit->type_icon }} me-1"></i>{{ $visit->type_label }}</span>
                <span class="badge {{ $visit->status_badge }}">{{ $visit->status_label }}</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Consulted on {{ $visit->visit_date->format('F d, Y \a\t h:i A') }}
                by <span class="fw-semibold text-dark">{{ $visit->clinician?->name ?? 'Attending Dentist' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('dental-consultations.print-card', $visit) }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-medical me-1"></i> Oral Health Card
            </a>
            <a href="{{ route('clinic-visits.print', $visit) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Visit Slip
            </a>
            @can('dental-consultations.edit')
                <a href="{{ route('dental-consultations.edit', $visit) }}" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit Record
                </a>
            @endcan
            <a href="{{ route('dental-consultations.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Patient Demographics</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 pb-3 border-bottom mb-3">
                        <div class="patient-avatar-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
                            {{ $patient->initials() }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">
                                <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none text-dark">
                                    {{ $patient->full_name }}
                                </a>
                            </div>
                            <span class="badge {{ $visit->classification_badge }} me-1">{{ ucfirst($visit->patient_classification) }}</span>
                            <span class="badge badge-soft-gray font-monospace">{{ $patient->primary_identifier }}</span>
                        </div>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-12">
                            <span class="text-muted">Classification Details:</span>
                            <span class="fw-medium text-dark ms-1">{{ $visit->classification_label ?: 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Sex / Age:</span>
                            <span class="fw-medium text-dark ms-1">{{ $patient->gender }} / {{ $patient->age }} yrs</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Blood Type:</span>
                            <span class="badge badge-soft-red ms-1">{{ $patient->blood_type ?: 'N/A' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted">Program / Year:</span>
                            <span class="fw-medium text-dark ms-1">{{ $patient->program?->code ?? 'N/A' }} {{ $patient->year_level }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-heart-pulse text-danger fs-5"></i>
                        <span class="fw-bold text-dark">Vital Signs on Presentation</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Blood Pressure</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->blood_pressure ?: 'N/A' }}</span>
                                <small class="text-muted d-block">mmHg</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Temperature</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->temperature ? $visit->temperature.' °C' : 'N/A' }}</span>
                                <small class="text-muted d-block">Body temp</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Pulse Rate</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pulse_rate ? $visit->pulse_rate.' bpm' : 'N/A' }}</span>
                                <small class="text-muted d-block">Heart rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-muted">Chief Complaint:</span>
                        <span class="fw-medium text-dark ms-1">{{ $visit->chief_complaint ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-secondary fs-5"></i>
                        <span class="fw-bold text-dark">Dental History</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Treatment Frequency:</span>
                            <span class="fw-medium text-dark">{{ $detail ? (\App\Models\DentalConsultation::TREATMENT_FREQUENCIES[$detail->dental_treatment_frequency] ?? 'N/A') : 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Last Dental Visit:</span>
                            <span class="fw-medium text-dark">{{ $detail?->last_dental_visit?->format('M d, Y') ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">Dental Fears / Anxiety:</span>
                            @if($detail?->dental_fears)
                                <span class="badge badge-soft-amber">Yes</span>
                            @else
                                <span class="badge badge-soft-gray">No</span>
                            @endif
                        </div>
                        @if($detail?->dental_concerns)
                            <div class="col-12">
                                <span class="text-muted d-block">Dental Concerns:</span>
                                <div class="text-dark p-2 bg-light rounded mt-1">{{ $detail->dental_concerns }}</div>
                            </div>
                        @endif
                        @if($detail?->previous_dental_treatment)
                            <div class="col-12">
                                <span class="text-muted d-block">Previous Treatments:</span>
                                <div class="text-dark p-2 bg-light rounded mt-1">{{ $detail->previous_dental_treatment }}</div>
                            </div>
                        @endif
                    </div>

                    @if($detail && count($detail->oral_condition_list) > 0)
                        <hr class="my-3">
                        <div class="small">
                            <span class="text-muted fw-semibold text-uppercase d-block mb-2">Oral Health Conditions</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($detail->oral_condition_list as $cond)
                                    <span class="badge badge-soft-red">{{ $cond }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($detail && count($detail->systemic_condition_list) > 0)
                        <hr class="my-3">
                        <div class="small">
                            <span class="text-muted fw-semibold text-uppercase d-block mb-2">Systemic Conditions</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($detail->systemic_condition_list as $cond)
                                    <span class="badge badge-soft-amber">{{ $cond }}</span>
                                @endforeach
                                @if($detail->systemic_allergy_details)
                                    <span class="d-block w-100 text-muted mt-1">Allergies: <strong>{{ $detail->systemic_allergy_details }}</strong></span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-search text-info fs-5"></i>
                        <span class="fw-bold text-dark">Oral Examination</span>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $softTissues = [
                            'soft_tissue_lips' => 'Lips',
                            'soft_tissue_cheeks' => 'Cheeks',
                            'soft_tissue_tongue' => 'Tongue',
                            'soft_tissue_palate' => 'Palate',
                            'soft_tissue_gingiva' => 'Gingiva / Gums',
                            'soft_tissue_floor_of_mouth' => 'Floor of Mouth',
                        ];
                        $hasSoftTissue = $detail && collect(array_keys($softTissues))->contains(fn($f) => $detail->{$f} !== null);
                    @endphp

                    @if($hasSoftTissue)
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-2">Soft Tissue Examination</span>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Area</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($softTissues as $field => $areaLabel)
                                        @if($detail->{$field})
                                            <tr>
                                                <td class="fw-semibold">{{ $areaLabel }}</td>
                                                <td>
                                                    @php $stStatus = $detail->{$field}; @endphp
                                                    <span class="badge {{ $stStatus === 'normal' ? 'badge-soft-green' : 'badge-soft-red' }}">
                                                        {{ \App\Models\DentalConsultation::SOFT_TISSUE_STATUSES[$stStatus] ?? $stStatus }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="row g-2 small">
                        @if($detail?->periodontal_status)
                            <div class="col-6">
                                <span class="text-muted d-block">Periodontal Status:</span>
                                <span class="fw-medium text-dark">{{ \App\Models\DentalConsultation::PERIODONTAL_STATUSES[$detail->periodontal_status] ?? $detail->periodontal_status }}</span>
                            </div>
                        @endif
                        @if($detail?->oral_hygiene_status)
                            <div class="col-6">
                                <span class="text-muted d-block">Oral Hygiene:</span>
                                <span class="badge badge-soft-blue">{{ $detail->oral_hygiene_status }}</span>
                            </div>
                        @endif
                        @if($detail?->calculus_index)
                            <div class="col-4">
                                <span class="text-muted d-block">Calculus Index:</span>
                                <span class="fw-medium text-dark">{{ \App\Models\DentalConsultation::CALCULUS_INDICES[$detail->calculus_index] ?? $detail->calculus_index }}</span>
                            </div>
                        @endif
                        @if($detail?->gingival_index)
                            <div class="col-4">
                                <span class="text-muted d-block">Gingival Index:</span>
                                <span class="fw-medium text-dark">{{ \App\Models\DentalConsultation::GINGIVAL_INDICES[$detail->gingival_index] ?? $detail->gingival_index }}</span>
                            </div>
                        @endif
                        @if($detail?->plaque_index)
                            <div class="col-4">
                                <span class="text-muted d-block">Plaque Index:</span>
                                <span class="fw-medium text-dark">{{ \App\Models\DentalConsultation::PLAQUE_INDICES[$detail->plaque_index] ?? $detail->plaque_index }}</span>
                            </div>
                        @endif
                    </div>

                    @if($detail?->oral_examination_findings)
                        <div class="mt-3 small">
                            <span class="text-muted fw-semibold text-uppercase d-block mb-1">Examination Findings</span>
                            <div class="p-2 bg-light rounded">{{ $detail->oral_examination_findings }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($detail && (!empty($detail->tooth_chart) || !empty($detail->primary_tooth_chart)))
        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid text-cyan fs-5"></i>
                    <h2 class="h6 mb-0 fw-bold text-dark">Odontogram — Tooth Chart</h2>
                </div>
            </div>
            <div class="card-body">
                @if(!empty($detail->tooth_chart))
                    <div class="mb-4">
                        <span class="text-muted small fw-bold text-uppercase d-block mb-2">Permanent Teeth (FDI Notation)</span>
                        @php
                            $permanentTeeth = \App\Models\DentalConsultation::permanentTeeth();
                            $conditionColors = ['C' => 'badge-soft-red', 'M' => 'badge-soft-gray', 'X' => 'badge-soft-amber', 'F' => 'badge-soft-green', 'RF' => 'badge-soft-purple', 'IMP' => 'badge-soft-blue', 'J' => 'badge-soft-blue'];
                        @endphp
                        <div class="mb-2">
                            <small class="text-muted fw-semibold">MAXILLARY / UPPER ARCH</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($permanentTeeth['upper'] as $t)
                                    @if(isset($detail->tooth_chart[(string)$t]))
                                        @php $code = $detail->tooth_chart[(string)$t]; @endphp
                                        <div class="text-center border rounded p-1 bg-white" style="min-width:50px;">
                                            <div class="fw-bold small text-primary">#{{ $t }}</div>
                                            <span class="badge {{ $conditionColors[$code] ?? 'badge-soft-gray' }} d-block">{{ $code }}</span>
                                            <div style="font-size:0.65rem;" class="text-muted">{{ \App\Models\DentalConsultation::TOOTH_CONDITIONS[$code] ?? $code }}</div>
                                        </div>
                                    @else
                                        <div class="text-center border rounded p-1 bg-light" style="min-width:50px; opacity:0.5;">
                                            <div class="small text-muted">#{{ $t }}</div>
                                            <span class="text-muted" style="font-size:0.7rem;">—</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <small class="text-muted fw-semibold">MANDIBULAR / LOWER ARCH</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($permanentTeeth['lower'] as $t)
                                    @if(isset($detail->tooth_chart[(string)$t]))
                                        @php $code = $detail->tooth_chart[(string)$t]; @endphp
                                        <div class="text-center border rounded p-1 bg-white" style="min-width:50px;">
                                            <div class="fw-bold small text-primary">#{{ $t }}</div>
                                            <span class="badge {{ $conditionColors[$code] ?? 'badge-soft-gray' }} d-block">{{ $code }}</span>
                                            <div style="font-size:0.65rem;" class="text-muted">{{ \App\Models\DentalConsultation::TOOTH_CONDITIONS[$code] ?? $code }}</div>
                                        </div>
                                    @else
                                        <div class="text-center border rounded p-1 bg-light" style="min-width:50px; opacity:0.5;">
                                            <div class="small text-muted">#{{ $t }}</div>
                                            <span class="text-muted" style="font-size:0.7rem;">—</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($detail->primary_tooth_chart))
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-2">Primary / Deciduous Teeth</span>
                        @php $deciduousTeeth = \App\Models\DentalConsultation::deciduousTeeth(); @endphp
                        <div class="mb-2">
                            <small class="text-muted fw-semibold">MAXILLARY / UPPER ARCH</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($deciduousTeeth['upper'] as $t)
                                    @if(isset($detail->primary_tooth_chart[(string)$t]))
                                        @php $code = $detail->primary_tooth_chart[(string)$t]; @endphp
                                        <div class="text-center border rounded p-1 bg-white" style="min-width:50px;">
                                            <div class="fw-bold small text-warning">#{{ $t }}</div>
                                            <span class="badge {{ $conditionColors[$code] ?? 'badge-soft-gray' }} d-block">{{ $code }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <small class="text-muted fw-semibold">MANDIBULAR / LOWER ARCH</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($deciduousTeeth['lower'] as $t)
                                    @if(isset($detail->primary_tooth_chart[(string)$t]))
                                        @php $code = $detail->primary_tooth_chart[(string)$t]; @endphp
                                        <div class="text-center border rounded p-1 bg-white" style="min-width:50px;">
                                            <div class="fw-bold small text-warning">#{{ $t }}</div>
                                            <span class="badge {{ $conditionColors[$code] ?? 'badge-soft-gray' }} d-block">{{ $code }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-3 d-flex flex-wrap gap-2 small">
                    @foreach(\App\Models\DentalConsultation::TOOTH_CONDITIONS as $code => $lbl)
                        <span><strong class="text-primary">{{ $code }}</strong>: {{ $lbl }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clipboard2-pulse text-cyan fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Clinical Management</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="p-3 border rounded border-info bg-info-subtle mb-4">
                <span class="text-info small fw-bold text-uppercase d-block">Dental Diagnosis</span>
                <div class="fw-bold text-dark fs-5 mt-1">{{ $detail?->diagnosis ?? 'N/A' }}</div>
                @if($detail?->teeth_involved)
                    <div class="small text-muted mt-1">Teeth Involved: <strong>{{ $detail->teeth_involved }}</strong></div>
                @endif
                @if($detail?->priority_level)
                    <div class="small mt-1">
                        Priority: <span class="badge {{ match($detail->priority_level) { 'urgent' => 'badge-red', 'soon' => 'badge-amber', 'routine' => 'badge-blue', default => 'badge-gray' } }}">{{ \App\Models\DentalConsultation::PRIORITY_LEVELS[$detail->priority_level] ?? $detail->priority_level }}</span>
                    </div>
                @endif
            </div>

            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Procedures Rendered</span>
                    <div class="mt-2">
                        @forelse($detail?->procedures ?? [] as $proc)
                            <span class="badge badge-soft-green p-2 me-1 mb-1 fs-6"><i class="bi bi-check2 me-1"></i>{{ $proc }}</span>
                        @empty
                            <span class="text-muted">None specified.</span>
                        @endforelse
                    </div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Local Anesthesia</span>
                    <div class="text-dark mt-2 fw-medium">{{ $detail?->anesthesia_used ?: 'None used / non-invasive procedure' }}</div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Treatment Rendered / Clinical Notes</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail?->treatment_rendered ?: 'Routine oral care rendered.' }}</div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Post-Operative Instructions &amp; Advice</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail?->recommendations ?: 'Maintain standard oral hygiene.' }}</div>
                </div>

                @if($detail?->treatment_plan)
                    <div class="col-12">
                        <span class="text-muted small fw-semibold text-uppercase d-block">Treatment Plan</span>
                        <div class="text-dark mt-1 p-3 bg-light rounded border-start border-primary border-3">{{ $detail->treatment_plan }}</div>
                    </div>
                @endif
            </div>

            <hr class="my-4">

            <div class="row g-3 small">
                <div class="col-md-3">
                    <span class="text-muted d-block">Oral Hygiene Assessment:</span>
                    <span class="badge badge-soft-gray mt-1">{{ $detail?->oral_hygiene_status ?: 'N/A' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Next Dental Checkup:</span>
                    <span class="fw-bold text-dark">{{ $detail?->follow_up_date ? $detail->follow_up_date->format('M d, Y') : 'In 6 months' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Disposition:</span>
                    <span class="fw-bold text-dark">{{ $visit->disposition_label }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Visit Duration:</span>
                    <span class="fw-bold text-dark">{{ $visit->duration_label }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($detail && ($detail->dmft_decayed !== null || $detail->dmft_missing !== null || $detail->dmft_filled !== null || $detail->dmfs_decayed !== null))
        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-123 text-primary fs-5"></i>
                    <h2 class="h6 mb-0 fw-bold text-dark">Dental Statistics — DMFT / DMFS Indices</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border border-primary-subtle">
                            <div class="card-header bg-primary bg-opacity-10">
                                <span class="fw-bold text-primary">DMFT Index (Tooth Level)</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">D — Decayed</small>
                                            <span class="fw-bold fs-4 text-danger">{{ $detail->dmft_decayed ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">M — Missing</small>
                                            <span class="fw-bold fs-4 text-secondary">{{ $detail->dmft_missing ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">F — Filled</small>
                                            <span class="fw-bold fs-4 text-success">{{ $detail->dmft_filled ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2 bg-primary bg-opacity-10">
                                            <small class="text-muted d-block">Total</small>
                                            <span class="fw-bold fs-4 text-primary">{{ $detail->dmft_total }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border border-success-subtle">
                            <div class="card-header bg-success bg-opacity-10">
                                <span class="fw-bold text-success">DMFS Index (Surface Level)</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">D — Decayed</small>
                                            <span class="fw-bold fs-4 text-danger">{{ $detail->dmfs_decayed ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">M — Missing</small>
                                            <span class="fw-bold fs-4 text-secondary">{{ $detail->dmfs_missing ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">F — Filled</small>
                                            <span class="fw-bold fs-4 text-success">{{ $detail->dmfs_filled ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2 bg-success bg-opacity-10">
                                            <small class="text-muted d-block">Total</small>
                                            <span class="fw-bold fs-4 text-success">{{ $detail->dmfs_total }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
