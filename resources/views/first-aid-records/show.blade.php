<x-layouts.app title="First Aid Record: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('first-aid-records.index') }}">First Aid Records</a>
        <span>&gt;</span>
        <span>{{ $visit->visit_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0 text-warning">{{ $visit->visit_number }}</h1>
                <span class="badge {{ $visit->type_badge }}"><i class="bi {{ $visit->type_icon }} me-1"></i>{{ $visit->type_label }}</span>
                <span class="badge {{ $visit->status_badge }}">{{ $visit->status_label }}</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Treated on {{ $visit->visit_date->format('F d, Y \a\t h:i A') }}
                by <span class="fw-semibold text-dark">{{ $visit->clinician?->name ?? 'First Aid Responder' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('clinic-visits.print', $visit) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Treatment Slip
            </a>
            @can('first-aid-records.edit')
                <a href="{{ route('first-aid-records.edit', $visit) }}" class="btn btn-warning btn-sm px-3 py-2 text-dark">
                    <i class="bi bi-pencil me-1"></i> Edit Record
                </a>
            @endcan
            <a href="{{ route('first-aid-records.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
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
                        <div class="patient-avatar-box bg-light text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
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
                            <span class="text-muted">Classification:</span>
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-heart-pulse text-danger fs-5"></i>
                        <span class="fw-bold text-dark">Vital Signs &amp; Pain Level</span>
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
                                <small class="text-muted d-block text-uppercase">Pulse Rate</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pulse_rate ? $visit->pulse_rate.' bpm' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Temperature</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->temperature ? $visit->temperature.' °C' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Pain Scale</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pain_scale !== null ? $visit->pain_scale.' / 10' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Incident Location:</span>
                            <span class="fw-bold text-dark ms-1">{{ $detail->incident_location ?: 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Cause of Injury:</span>
                            <span class="fw-bold text-dark ms-1">{{ $detail->incident_cause ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bandaid text-warning fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">First Aid Care &amp; Clinical Documentation</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">Injury / Condition</span>
                <div class="fw-bold text-dark fs-4 mt-1">
                    {{ $detail->injury_type }}
                    @if($detail->body_part)
                        <span class="badge badge-soft-amber ms-2">{{ $detail->body_part }}</span>
                    @endif
                </div>
                @if($visit->chief_complaint)
                    <div class="text-muted mt-1">{{ $visit->chief_complaint }}</div>
                @endif
            </div>

            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">First Aid Measures Rendered</span>
                <div class="mt-2">
                    @forelse($detail->first_aid_given ?? [] as $act)
                        <span class="badge badge-soft-amber p-2 me-1 mb-1 fs-6"><i class="bi bi-check2 me-1"></i>{{ $act }}</span>
                    @empty
                        <span class="text-muted">No specific measure checked.</span>
                    @endforelse
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Treatment Notes &amp; Observations</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded">{{ $detail->treatment_notes ?: 'Care rendered and tolerated well.' }}</div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Supplies Used</span>
                    <div class="text-dark mt-1 p-3 bg-light rounded font-monospace">{{ $detail->supplies_used ?: 'None logged.' }}</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3 small">
                <div class="col-md-4">
                    <span class="text-muted d-block">External Referral:</span>
                    <span class="fw-bold text-dark">{{ $detail->was_referred ? 'Referred to '.$detail->referred_to : 'Managed on site' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block">Disposition:</span>
                    <span class="fw-bold text-dark">{{ $visit->disposition_label }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block">Visit Status:</span>
                    <span class="badge {{ $visit->status_badge }}">{{ $visit->status_label }}</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

