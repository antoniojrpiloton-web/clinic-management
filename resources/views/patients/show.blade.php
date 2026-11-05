<x-layouts.app title="{{ $patient->full_name }} — Patient Profile">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('patients.index') }}">Patients</a>
        <span>&gt;</span>
        <span>{{ $patient->full_name }}</span>
    </div>

    <div class="card mb-4 border">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar-initials" style="width: 52px; height: 52px; font-size: 1.25rem;">
                        {{ $patient->initials() }}
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="h4 fw-bold text-dark mb-0">{{ $patient->full_name }}</h2>
                            @if ($patient->type === 'student')
                                <span class="badge badge-pill badge-soft-blue"><i class="bi bi-mortarboard me-1"></i> Student</span>
                            @elseif ($patient->type === 'employee')
                                <span class="badge badge-pill badge-soft-green"><i class="bi bi-person-badge me-1"></i> Employee</span>
                            @else
                                <span class="badge badge-pill badge-soft-gray">Other</span>
                            @endif
                            <span class="status-badge {{ $patient->status === 'active' ? 'completed' : 'pending' }}">
                                {{ $patient->status }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-1 text-muted small flex-wrap">
                            <span><i class="bi bi-hash"></i> <strong>{{ $patient->patient_number }}</strong></span>
                            <span><i class="bi bi-person-vcard"></i> ID: <strong>{{ $patient->primary_identifier }}</strong></span>
                            <span><i class="bi bi-gender-ambiguous"></i> {{ $patient->gender }}, {{ $patient->age }} years old</span>
                            @if ($patient->blood_type)
                                <span class="badge bg-danger-subtle text-danger px-2"><i class="bi bi-droplet-fill me-1"></i>{{ $patient->blood_type }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('physical-examinations.create', ['patient_id' => $patient->getRouteKey()]) }}" class="btn btn-primary btn-sm px-3 py-2">
                        <i class="bi bi-file-earmark-plus me-1"></i> New Physical Exam
                    </a>
                    <a href="{{ route('medical-profiles.show', $patient) }}" class="btn btn-outline-primary btn-sm px-3 py-2">
                        <i class="bi bi-clipboard2-pulse me-1"></i> Medical Profile
                    </a>
                    @can('patients.edit')
                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                            <i class="bi bi-pencil me-1"></i> Edit Patient Record
                        </a>
                    @endcan
                    <a href="{{ route('patients.index') }}" class="btn btn-light btn-sm px-3 py-2 border">
                        <i class="bi bi-arrow-left me-1"></i> Back to Masterlist
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="bi bi-person text-primary"></i>
                    <span class="fw-bold text-dark">Personal &amp; Contact Details</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Full Legal Name</span>
                            <span class="fw-semibold text-dark">{{ $patient->full_name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Date of Birth</span>
                            <span class="fw-semibold text-dark">{{ $patient->date_of_birth->format('F d, Y') }} ({{ $patient->age }} y/o)</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Civil Status</span>
                            <span class="fw-semibold text-dark">{{ $patient->civil_status }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Blood Type</span>
                            <span class="fw-semibold text-dark">{{ $patient->blood_type ?: 'Not Recorded' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Email Address</span>
                            <span class="fw-semibold text-dark">{{ $patient->email ?: 'None' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Contact Phone</span>
                            <span class="fw-semibold text-dark">{{ $patient->phone ?: 'None' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Residential Address</span>
                            <span class="fw-semibold text-dark">{{ $patient->address ?: 'Not provided' }}</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-telephone-plus me-1 text-danger"></i> Emergency Contact</h6>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Contact Person</span>
                            <span class="fw-semibold text-dark">{{ $patient->emergency_contact_name ?: 'None listed' }}</span>
                            @if ($patient->emergency_contact_relationship)
                                <small class="text-muted">({{ $patient->emergency_contact_relationship }})</small>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Emergency Number</span>
                            <span class="fw-semibold text-dark">{{ $patient->emergency_contact_phone ?: 'None listed' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            @if ($patient->type === 'student' && $patient->studentProfile)
                <div class="card mb-4 border">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <i class="bi bi-mortarboard text-primary"></i>
                        <span class="fw-bold text-dark">University Academic Enrollment</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Student ID</span>
                                <span class="font-monospace fw-bold text-primary">{{ $patient->studentProfile->student_id }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Enrollment Status</span>
                                <span class="badge badge-pill badge-soft-green">{{ ucfirst($patient->studentProfile->enrollment_status) }}</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small text-uppercase fw-semibold d-block">College / Faculty</span>
                                <span class="fw-semibold text-dark">{{ $patient->studentProfile->college->name }} ({{ $patient->studentProfile->college->code }})</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Degree Program</span>
                                <span class="fw-semibold text-dark">{{ $patient->studentProfile->program->name }} ({{ $patient->studentProfile->program->code }})</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Year Level &amp; Section</span>
                                <span class="fw-semibold text-dark">{{ $patient->studentProfile->year_level }} &bull; {{ $patient->studentProfile->section ?: 'No Section' }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Academic Year</span>
                                <span class="fw-semibold text-dark">{{ $patient->studentProfile->academicYear?->code ?: 'Active Term' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($patient->type === 'employee' && $patient->employeeProfile)
                <div class="card mb-4 border">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <i class="bi bi-briefcase text-primary"></i>
                        <span class="fw-bold text-dark">Employment &amp; Faculty Profile</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Employee ID</span>
                                <span class="font-monospace fw-bold text-primary">{{ $patient->employeeProfile->employee_id }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Category</span>
                                <span class="badge badge-pill badge-soft-blue">{{ $patient->employeeProfile->category }}</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Position / Designation</span>
                                <span class="fw-semibold text-dark">{{ $patient->employeeProfile->position }}</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Office / Department / College</span>
                                <span class="fw-semibold text-dark">{{ $patient->employeeProfile->office_department ?: $patient->employeeProfile->college?->name }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Classification</span>
                                <span class="fw-semibold text-dark">{{ $patient->employeeProfile->employment_classification }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Service Status</span>
                                <span class="badge badge-pill badge-soft-green">{{ ucfirst($patient->employeeProfile->employment_status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-heart-pulse text-danger"></i>
                        <span class="fw-bold text-dark">Medical Notes &amp; Allergies</span>
                    </div>
                    <a href="{{ route('medical-profiles.show', $patient) }}" class="btn btn-sm btn-link text-decoration-none">Full Profile &rarr;</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small text-uppercase fw-semibold d-block">Known Allergies</span>
                        @if ($patient->medicalProfile?->allergies_summary || $patient->allergies)
                            <div class="alert alert-danger py-2 px-3 mt-1 mb-0 small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $patient->medicalProfile?->allergies_summary ?? $patient->allergies }}
                            </div>
                        @else
                            <span class="text-muted small">No known drug, food, or environmental allergies recorded.</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Medical Background / Pre-existing Conditions</span>
                        <p class="text-dark mb-0 small mt-1">
                            {{ $patient->medicalProfile?->existing_conditions ?? ($patient->medical_history ?: 'No prior chronic conditions or major surgeries recorded.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-medical text-primary"></i>
                        <span class="fw-bold text-dark">Physical Examination &amp; Clinical History</span>
                    </div>
                    <a href="{{ route('physical-examinations.create', ['patient_id' => $patient->getRouteKey()]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Examination
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($patient->physicalExaminations->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">ACTIONS</th>
                                        <th>PE NUMBER</th>
                                        <th>DATE</th>
                                        <th>PURPOSE</th>
                                        <th>BLOOD PRESSURE</th>
                                        <th>BMI</th>
                                        <th>FITNESS CLEARANCE</th>
                                        <th>EXAMINER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patient->physicalExaminations as $pe)
                                        <tr>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2" type="button" data-bs-toggle="dropdown">
                                                        Action
                                                    </button>
                                                    <ul class="dropdown-menu shadow-sm">
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.show', $pe) }}"><i class="bi bi-eye text-primary me-2"></i> View Exam</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.edit', $pe) }}"><i class="bi bi-pencil text-warning me-2"></i> Edit</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.print-pe', $pe) }}" target="_blank"><i class="bi bi-printer text-success me-2"></i> Print PE Form</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.print-certificate', $pe) }}" target="_blank"><i class="bi bi-file-earmark-check text-info me-2"></i> Print Medical Cert (HSD-4)</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.print-exemption', $pe) }}" target="_blank"><i class="bi bi-file-earmark-x text-warning me-2"></i> Print Exemption (HSD-6)</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('physical-examinations.print-referral', $pe) }}" target="_blank"><i class="bi bi-box-arrow-up-right text-secondary me-2"></i> Print Referral (HSD-3)</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-dark">{{ $pe->pe_number }}</td>
                                            <td>{{ $pe->exam_date->format('M d, Y') }}</td>
                                            <td>{{ $pe->purpose }}</td>
                                            <td>{{ $pe->blood_pressure ?: '—' }}</td>
                                            <td>
                                                @if ($pe->bmi)
                                                    <span class="badge {{ $pe->bmi_badge_class }}">{{ $pe->bmi }} ({{ $pe->bmi_category }})</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $pe->fitness_badge_class }} px-2 py-1">{{ $pe->fitness_assessment ?: 'Class A' }}</span>
                                            </td>
                                            <td>{{ $pe->examiner_name ?: 'Attending Physician' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-clipboard-pulse fs-1 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold">No physical examinations conducted yet.</div>
                            <div class="small mt-1">Record the initial annual or entrance physical examination for this patient.</div>
                            <a href="{{ route('physical-examinations.create', ['patient_id' => $patient->getRouteKey()]) }}" class="btn btn-sm btn-primary mt-3">
                                <i class="bi bi-plus-circle me-1"></i> Conduct Physical Examination
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

