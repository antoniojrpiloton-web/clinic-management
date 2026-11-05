<x-layouts.app title="Emergency Record: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('emergency-records.index') }}">Emergency Records</a>
        <span>&gt;</span>
        <span>{{ $visit->visit_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0 text-danger">{{ $visit->visit_number }}</h1>
                <span class="badge {{ $detail->triage_badge }} fs-6 px-3 py-1">{{ $detail->triage_label }}</span>
                <span class="badge {{ $visit->status_badge }}">{{ $visit->status_label }}</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Emergency response on {{ $visit->visit_date->format('F d, Y \a\t h:i A') }}
                by <span class="fw-semibold text-dark">{{ $visit->clinician?->name ?? 'Attending Clinician / Responders' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('clinic-visits.print', $visit) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Incident Slip
            </a>
            @can('emergency-records.edit')
                <a href="{{ route('emergency-records.edit', $visit) }}" class="btn btn-danger btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit Record
                </a>
            @endcan
            <a href="{{ route('emergency-records.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
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
                        <div class="patient-avatar-box bg-light text-danger rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
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
                        <span class="fw-bold text-dark">Emergency Vitals &amp; Consciousness</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Blood Pressure</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->blood_pressure ?: 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Heart Rate</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pulse_rate ? $visit->pulse_rate.' bpm' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">SpO2</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->oxygen_saturation ? $visit->oxygen_saturation.' %' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Temp</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->temperature ? $visit->temperature.' °C' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Level of Consciousness:</span>
                            <span class="fw-bold text-dark ms-1">{{ $detail->consciousness_label }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Glasgow Coma Scale:</span>
                            <span class="fw-bold text-dark ms-1">{{ $detail->gcs_score ? $detail->gcs_score.' / 15' : 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Incident Location:</span>
                            <span class="fw-bold text-dark ms-1">{{ $detail->incident_location ?: 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Incident Report #:</span>
                            <span class="fw-bold text-dark ms-1 font-monospace">{{ $detail->incident_report_number ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-medical text-danger fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Emergency Incident Narrative &amp; Interventions</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">Emergency Nature</span>
                <div class="fw-bold text-danger fs-4 mt-1">{{ $detail->emergency_nature }}</div>
                <div class="text-dark mt-1">{{ $visit->chief_complaint ?: 'No complaint narrative provided.' }}</div>
            </div>

            @if($detail->incident_description)
                <div class="mb-4">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Incident Description / Mechanism</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->incident_description }}</div>
                </div>
            @endif

            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">Interventions Rendered</span>
                <div class="mt-2">
                    @forelse($detail->interventions ?? [] as $int)
                        <span class="badge badge-soft-red p-2 me-1 mb-1 fs-6"><i class="bi bi-check2-circle me-1"></i>{{ $int }}</span>
                    @empty
                        <span class="text-muted">No specific procedure checked.</span>
                    @endforelse
                </div>
            </div>

            @if($detail->intervention_notes)
                <div class="mb-4">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Intervention Notes</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->intervention_notes }}</div>
                </div>
            @endif

            <div class="row g-4 p-3 bg-light rounded border mb-3">
                <div class="col-md-6 border-end">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Ambulance &amp; Hospital Transfer</span>
                    <div>Ambulance Called: <strong>{{ $detail->ambulance_called ? 'Yes' : 'No' }}</strong></div>
                    <div>Hospital Transfer: <strong>{{ $detail->transferred_to_hospital ? 'Yes' : 'No' }}</strong></div>
                    @if($detail->transferred_to_hospital)
                        <div>Hospital: <strong>{{ $detail->hospital_name ?: 'N/A' }}</strong></div>
                        <div>Transport Mode: <strong>{{ $detail->transport_mode ?: 'N/A' }}</strong></div>
                        <div>Transfer Time: <strong>{{ $detail->transferred_at ? $detail->transferred_at->format('M d, Y h:i A') : 'N/A' }}</strong></div>
                    @endif
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Guardian Notification &amp; Outcome</span>
                    <div>Guardian Notified: <strong>{{ $detail->guardian_notified ? 'Yes' : 'No' }}</strong></div>
                    @if($detail->guardian_notified)
                        <div>Contact Person: <strong>{{ $detail->notified_person ?: 'N/A' }}</strong></div>
                        <div>Time Notified: <strong>{{ $detail->notified_at ? $detail->notified_at->format('M d, Y h:i A') : 'N/A' }}</strong></div>
                    @endif
                    <div class="mt-2">Outcome: <span class="badge badge-soft-gray fs-6">{{ $detail->outcome ?: 'Stabilized' }}</span></div>
                    <div>Disposition: <strong>{{ $visit->disposition_label }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

