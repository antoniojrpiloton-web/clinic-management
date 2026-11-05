<x-layouts.app title="Medical Consultation: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medical-consultations.index') }}">Medical Consultations</a>
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
                by <span class="fw-semibold text-dark">{{ $visit->clinician?->name ?? 'Attending Clinician' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('clinic-visits.print', $visit) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-pdf me-1"></i> Visit Slip (PDF)
            </a>
            @if($detail?->medical_certificate_issued)
                <a href="{{ route('clinic-visits.print-certificate', $visit) }}" target="_blank" class="btn btn-outline-info btn-sm px-3 py-2">
                    <i class="bi bi-file-earmark-check me-1"></i> Medical Certificate (PDF)
                </a>
            @endif
            @if($detail?->referred_to)
                <a href="{{ route('clinic-visits.print-referral', $visit) }}" target="_blank" class="btn btn-outline-warning btn-sm px-3 py-2">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Referral Form (PDF)
                </a>
            @endif
            @can('inventory.dispense')
                <a href="{{ route('inventory.dispensings.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary btn-sm px-3 py-2">
                    <i class="bi bi-capsule me-1"></i> Dispense Medicine
                </a>
            @endcan
            @can('medical-consultations.edit')
                <a href="{{ route('medical-consultations.edit', $visit) }}" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit Record
                </a>
            @endcan
            <a href="{{ route('medical-consultations.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
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
                            <span class="text-muted">Emergency Contact:</span>
                            <span class="fw-medium text-dark ms-1">{{ $patient->emergency_contact_name ?: 'N/A' }} ({{ $patient->emergency_contact_phone ?: 'N/A' }})</span>
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
                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Blood Pressure</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->blood_pressure ?: 'N/A' }}</span>
                                <small class="text-muted d-block">mmHg</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Temperature</small>
                                <span class="fw-bold fs-5 {{ $visit->temperature >= 37.8 ? 'text-danger' : 'text-dark' }}">{{ $visit->temperature ? $visit->temperature.' °C' : 'N/A' }}</span>
                                <small class="text-muted d-block">Body temp</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Pulse Rate</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pulse_rate ? $visit->pulse_rate.' bpm' : 'N/A' }}</span>
                                <small class="text-muted d-block">Heart rate</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Resp. Rate</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->respiratory_rate ? $visit->respiratory_rate.' cpm' : 'N/A' }}</span>
                                <small class="text-muted d-block">Breathing</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Oxygen (SpO2)</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->oxygen_saturation ? $visit->oxygen_saturation.' %' : 'N/A' }}</span>
                                <small class="text-muted d-block">Pulse oximeter</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Pain Scale</small>
                                <span class="fw-bold fs-5 {{ $visit->pain_scale >= 7 ? 'text-danger' : ($visit->pain_scale >= 4 ? 'text-warning' : 'text-dark') }}">{{ $visit->pain_scale !== null ? $visit->pain_scale.' / 10' : 'N/A' }}</span>
                                <small class="text-muted d-block">Self-reported</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Weight</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->weight_kg ? $visit->weight_kg.' kg' : 'N/A' }}</span>
                                <small class="text-muted d-block">Body mass</small>
                            </div>
                        </div>

                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Height</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->height_cm ? $visit->height_cm.' cm' : 'N/A' }}</span>
                                <small class="text-muted d-block">Stature</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-medical text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Clinical Notes &amp; Medical Findings</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">Chief Complaint</span>
                <div class="fw-bold text-dark fs-5 mt-1">{{ $visit->chief_complaint ?: 'N/A' }}</div>
            </div>

            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">History of Present Illness (HPI)</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->history_of_present_illness ?: 'None recorded.' }}</div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Physical Examination Findings</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->physical_findings ?: 'None recorded.' }}</div>
                </div>
            </div>

            <div class="p-3 border rounded border-primary bg-primary-subtle mb-4">
                <span class="text-primary small fw-bold text-uppercase d-block">Primary Clinical Diagnosis</span>
                <div class="fw-bold text-dark fs-4 mt-1">
                    {{ $detail->diagnosis }}
                    @if($detail->icd_code)
                        <span class="badge badge-soft-blue ms-2 font-monospace">{{ $detail->icd_code }}</span>
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Treatment Plan &amp; Management</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->treatment_plan ?: 'Standard supportive care.' }}</div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Prescriptions (Rx)</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded font-monospace">{{ $detail->medications_prescribed ?: 'None prescribed.' }}</div>
                </div>
            </div>

            @if($detail->advice)
                <div class="mb-4">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Patient Advice / Education</span>
                    <div class="text-dark mt-1">{{ $detail->advice }}</div>
                </div>
            @endif

            <hr class="my-4">

            <div class="row g-3 small">
                <div class="col-md-3">
                    <span class="text-muted d-block">Medical Certificate:</span>
                    <span class="fw-bold text-dark">{{ $detail->medical_certificate_issued ? 'Issued' : 'Not Requested' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Recommended Rest:</span>
                    <span class="fw-bold text-dark">{{ $detail->rest_days ? $detail->rest_days.' days' : 'None' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Follow-up Date:</span>
                    <span class="fw-bold text-dark">{{ $detail->follow_up_date ? $detail->follow_up_date->format('M d, Y') : 'As needed (PRN)' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Disposition:</span>
                    <span class="fw-bold text-dark">{{ $visit->disposition_label }}</span>
                </div>
                @if($detail->referred_to)
                    <div class="col-12 mt-2">
                        <span class="text-muted d-block">External Referral:</span>
                        <span class="fw-bold text-dark">{{ $detail->referred_to }} — {{ $detail->referral_reason }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>

