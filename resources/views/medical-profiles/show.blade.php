<x-layouts.app title="Medical Profile: {{ $patient->full_name }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('patients.index') }}">Patients</a>
        <span>&gt;</span>
        <a href="{{ route('patients.show', $patient) }}">{{ $patient->full_name }}</a>
        <span>&gt;</span>
        <span>Medical Profile</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Clinical Medical Profile</h1>
            <p class="dashboard-subheading mb-0">Longitudinal health history, allergies, chronic conditions, and familial background for {{ $patient->full_name }}.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('medical-profiles.edit', $patient) }}" class="btn btn-primary px-3 py-2">
                <i class="bi bi-pencil me-1"></i> Edit Medical Profile
            </a>
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Patient
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-bold text-dark">Patient Identification</span>
                    <span class="badge {{ $patient->type === 'student' ? 'badge-blue' : 'badge-green' }}">{{ ucfirst($patient->type) }}</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar" style="width: 50px; height: 50px; font-size: 1.25rem;">
                            {{ $patient->initials() }}
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ $patient->full_name }}</h5>
                            <div class="small text-muted mt-1">ID: {{ $patient->primary_identifier }}</div>
                            <div class="small text-muted">{{ $patient->gender }} &bull; {{ $patient->age }} y/o</div>
                        </div>
                    </div>

                    <div class="border-top pt-3 small">
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted">Blood Type:</span>
                                <span class="fw-bold text-danger">{{ $patient->blood_type ?: 'Unknown' }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Civil Status:</span>
                                <span class="fw-semibold text-dark">{{ $patient->civil_status }}</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted">Phone:</span>
                                <span class="fw-semibold text-dark">{{ $patient->phone ?: '—' }}</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted">Emergency Contact:</span>
                                <span class="fw-semibold text-dark">{{ $patient->emergency_contact_name ?: '—' }} ({{ $patient->emergency_contact_phone ?: '—' }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold text-dark">Lifestyle &amp; Social History</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Smoking Status:</span>
                        <span class="fw-semibold text-dark">{{ ucfirst($profile->smoking_status ?: 'Non-smoker') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted">Alcohol Intake:</span>
                        <span class="fw-semibold text-dark">{{ ucfirst($profile->alcohol_status ?: 'Non-drinker') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-shield-slash text-danger"></i>
                    <span class="fw-bold text-dark">Allergies &amp; Drug Sensitivities</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <span class="text-muted small fw-semibold d-block">Drug Allergies</span>
                                <span class="fw-bold text-danger fs-6">{{ $profile->allergies_drugs ?: 'None reported' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <span class="text-muted small fw-semibold d-block">Food Allergies</span>
                                <span class="fw-bold text-dark fs-6">{{ $profile->allergies_food ?: 'None reported' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <span class="text-muted small fw-semibold d-block">Other / Environmental</span>
                                <span class="fw-bold text-dark fs-6">{{ $profile->allergies_others ?: 'None reported' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span class="fw-bold text-dark">Past &amp; Family Medical History</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span class="text-muted small fw-semibold d-block mb-2">Past Medical Illnesses</span>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ((array) ($profile->past_medical_history ?? []) as $illness)
                                <span class="badge bg-light text-dark border px-3 py-2 fs-6">{{ $illness }}</span>
                            @empty
                                <span class="text-muted fst-italic">No past chronic illnesses reported.</span>
                            @endforelse
                        </div>
                        @if ($profile->past_medical_history_notes)
                            <div class="small text-muted mt-2 p-2 bg-light rounded">{{ $profile->past_medical_history_notes }}</div>
                        @endif
                    </div>

                    <div class="border-top pt-3">
                        <span class="text-muted small fw-semibold d-block mb-2">Family Medical History (Hereditary / Familial)</span>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ((array) ($profile->family_medical_history ?? []) as $fIllness)
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6">{{ $fIllness }}</span>
                            @empty
                                <span class="text-muted fst-italic">No significant hereditary diseases recorded.</span>
                            @endforelse
                        </div>
                        @if ($profile->family_medical_history_notes)
                            <div class="small text-muted mt-2 p-2 bg-light rounded">{{ $profile->family_medical_history_notes }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-heart-pulse text-danger"></i>
                    <span class="fw-bold text-dark">Active Conditions &amp; Ongoing Medications</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Existing Medical Conditions:</span>
                            <div class="fw-semibold text-dark mt-1">{{ $profile->existing_conditions ?: 'No active chronic condition.' }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Current Maintenance Medications:</span>
                            <div class="fw-semibold text-dark mt-1">{{ $profile->current_medications ?: 'None.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-hospital text-primary"></i>
                    <span class="fw-bold text-dark">Hospitalization &amp; Surgical History</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="text-muted small fw-semibold d-block mb-2">Hospitalization Record:</span>
                            @if (!empty($profile->hospitalization_history))
                                <ul class="list-group list-group-flush">
                                    @foreach ($profile->hospitalization_history as $hosp)
                                        <li class="list-group-item px-0">
                                            <strong>{{ $hosp['reason'] ?? 'Admission' }}</strong> &mdash; {{ $hosp['hospital'] ?? 'Hospital' }}
                                            <span class="text-muted small">({{ $hosp['year'] ?? '—' }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small fst-italic">No prior hospital admissions on record.</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <span class="text-muted small fw-semibold d-block mb-2">Surgical Operations:</span>
                            @if (!empty($profile->surgical_history))
                                <ul class="list-group list-group-flush">
                                    @foreach ($profile->surgical_history as $surg)
                                        <li class="list-group-item px-0">
                                            <strong>{{ $surg['procedure'] ?? 'Procedure' }}</strong> &mdash; {{ $surg['hospital'] ?? 'Hospital' }}
                                            <span class="text-muted small">({{ $surg['year'] ?? '—' }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small fst-italic">No prior surgical operations on record.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

