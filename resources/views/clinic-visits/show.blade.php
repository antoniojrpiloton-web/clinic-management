<x-layouts.app title="Patient Visit: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('clinic-visits.index') }}">Patient Visit Logs</a>
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
                Encounter on {{ $visit->visit_date->format('F d, Y \a\t h:i A') }}
                by <span class="fw-semibold text-dark">{{ $visit->clinician?->name ?? 'Attending Clinician' }}</span>
                • Logged by {{ $visit->recorder?->name ?? 'System' }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('clinic-visits.print', $visit) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Visit Slip
            </a>

            @if($visit->visit_type === 'medical' && $visit->medicalConsultation)
                <a href="{{ route('medical-consultations.show', $visit) }}" class="btn btn-outline-primary btn-sm px-3 py-2">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Medical Record
                </a>
            @elseif($visit->visit_type === 'dental' && $visit->dentalConsultation)
                <a href="{{ route('dental-consultations.show', $visit) }}" class="btn btn-outline-info btn-sm px-3 py-2">
                    <i class="bi bi-emoji-smile me-1"></i> Dental Record
                </a>
            @elseif($visit->visit_type === 'emergency' && $visit->emergencyRecord)
                <a href="{{ route('emergency-records.show', $visit) }}" class="btn btn-outline-danger btn-sm px-3 py-2">
                    <i class="bi bi-truck-front me-1"></i> Emergency Record
                </a>
            @elseif($visit->visit_type === 'first_aid' && $visit->firstAidRecord)
                <a href="{{ route('first-aid-records.show', $visit) }}" class="btn btn-outline-warning btn-sm px-3 py-2 text-dark">
                    <i class="bi bi-bandaid me-1"></i> First Aid Record
                </a>
            @endif

            @can('clinic-visits.edit')
                <a href="{{ route('clinic-visits.edit', $visit) }}" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit Visit
                </a>
            @endcan
            <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
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
                        <span class="fw-bold text-dark">Patient Classification at Visit</span>
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
                            <span class="text-muted">Recorded Classification:</span>
                            <span class="fw-bold text-dark ms-1">{{ $visit->classification_label ?: 'N/A' }}</span>
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
                        <span class="fw-bold text-dark">Vitals on Intake</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center mb-3">
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
                                <span class="fw-bold fs-5 text-dark">{{ $visit->temperature ? $visit->temperature.' °C' : 'N/A' }}</span>
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
                                <small class="text-muted d-block text-uppercase">Respiratory</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->respiratory_rate ? $visit->respiratory_rate.' cpm' : 'N/A' }}</span>
                                <small class="text-muted d-block">Breathing</small>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">SpO2</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->oxygen_saturation ? $visit->oxygen_saturation.' %' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Pain Scale</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->pain_scale !== null ? $visit->pain_scale.' / 10' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Weight</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->weight_kg ? $visit->weight_kg.' kg' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block text-uppercase">Height</small>
                                <span class="fw-bold fs-5 text-dark">{{ $visit->height_cm ? $visit->height_cm.' cm' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 small border-top pt-2">
                        <div class="col-6">
                            <span class="text-muted">Visit Category:</span>
                            <span class="fw-bold text-dark ms-1">{{ $visit->category_label }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Duration:</span>
                            <span class="fw-bold text-dark ms-1">{{ $visit->duration_label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chat-left-text text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Chief Complaint &amp; Clinical Encounter Notes</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <span class="text-muted small fw-semibold text-uppercase d-block">Chief Complaint / Purpose of Visit</span>
                <div class="fw-bold text-dark fs-5 mt-1">{{ $visit->chief_complaint ?: 'None recorded.' }}</div>
            </div>

            @if($visit->summary)
                <div class="mb-4 p-3 bg-light rounded border">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Clinical Outcome / Diagnosis Summary</span>
                    <div class="fw-bold text-dark fs-5">{{ $visit->summary }}</div>
                </div>
            @endif

            @if($visit->remarks)
                <div class="mb-4">
                    <span class="text-muted small fw-semibold text-uppercase d-block">Disposition &amp; General Remarks</span>
                    <div class="text-dark mt-1">{{ $visit->remarks }}</div>
                </div>
            @endif

            <hr class="my-3">

            <div class="row g-3 small">
                <div class="col-md-4">
                    <span class="text-muted d-block">Disposition:</span>
                    <span class="fw-bold text-dark">{{ $visit->disposition_label }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block">Encounter Status:</span>
                    <span class="badge {{ $visit->status_badge }}">{{ $visit->status_label }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block">Attending Provider:</span>
                    <span class="fw-bold text-dark">{{ $visit->clinician?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($previousVisits->isNotEmpty())
        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary fs-5"></i>
                    <h2 class="h6 mb-0 fw-bold text-dark">Patient's Previous Clinic Visits ({{ $previousVisits->count() }})</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">VISIT #</th>
                            <th>DATE</th>
                            <th>TYPE</th>
                            <th>CHIEF COMPLAINT / SUMMARY</th>
                            <th>DISPOSITION</th>
                            <th class="text-end pe-3">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previousVisits as $pv)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold">{{ $pv->visit_number }}</td>
                                <td class="small">{{ $pv->visit_date->format('M d, Y') }}</td>
                                <td><span class="badge {{ $pv->type_badge }}">{{ $pv->type_label }}</span></td>
                                <td class="small text-truncate" style="max-width: 280px;">{{ $pv->summary ?: $pv->chief_complaint }}</td>
                                <td class="small">{{ $pv->disposition_label }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ $pv->detail_url }}" class="btn btn-sm btn-light border py-1 px-2">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>

