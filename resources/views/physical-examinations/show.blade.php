<x-layouts.app title="Physical Examination: {{ $exam->pe_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('physical-examinations.index') }}">Physical Examinations</a>
        <span>&gt;</span>
        <span>{{ $exam->pe_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">{{ $exam->pe_number }}</h1>
                <span class="badge {{ $exam->fitness_badge_class }} px-2 py-1 fs-6">{{ $exam->fitness_assessment ?: 'Class A' }}</span>
                <span class="badge bg-light text-dark border">{{ $exam->purpose }}</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">Conducted on {{ $exam->exam_date->format('F d, Y') }} &bull; Examiner: {{ $exam->examiner_name ?: 'Attending Physician' }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle px-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF Documentation &amp; Forms
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('physical-examinations.print-pe', $exam) }}" target="_blank"><i class="bi bi-file-earmark-medical text-primary me-2"></i> Physical Exam Form (PDF)</a></li>
                    <li><a class="dropdown-item" href="{{ route('physical-examinations.print-certificate', $exam) }}" target="_blank"><i class="bi bi-file-earmark-check text-success me-2"></i> Medical Certificate (HSD-4 PDF)</a></li>
                    <li><a class="dropdown-item" href="{{ route('physical-examinations.print-exemption', $exam) }}" target="_blank"><i class="bi bi-file-earmark-x text-warning me-2"></i> Medical Exemption (HSD-6 PDF)</a></li>
                    <li><a class="dropdown-item" href="{{ route('physical-examinations.print-referral', $exam) }}" target="_blank"><i class="bi bi-box-arrow-up-right text-secondary me-2"></i> Referral Form (HSD-3 PDF)</a></li>
                </ul>
            </div>

            <a href="{{ route('physical-examinations.edit', $exam) }}" class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>

            <a href="{{ route('physical-examinations.index') }}" class="btn btn-light px-3 py-2 border">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-bold text-dark">Patient Profile</span>
                    <span class="badge {{ $patient->type === 'student' ? 'badge-blue' : 'badge-green' }}">{{ ucfirst($patient->type) }}</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar" style="width: 52px; height: 52px; font-size: 1.25rem;">
                            {{ $patient->initials() }}
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">
                                <a href="{{ route('patients.show', $patient) }}" class="text-dark text-decoration-none">{{ $patient->full_name }}</a>
                            </h5>
                            <div class="small text-muted mt-1">ID: {{ $patient->primary_identifier }}</div>
                            <div class="small text-muted">{{ $patient->gender }} &bull; {{ $patient->age }} y/o &bull; DOB: {{ $patient->date_of_birth->format('M d, Y') }}</div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="row g-2 small">
                            @if ($patient->type === 'student' && $patient->studentProfile)
                                <div class="col-12">
                                    <span class="text-muted">College:</span>
                                    <span class="fw-semibold text-dark">{{ $patient->studentProfile->college->name ?? '—' }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted">Program &amp; Year:</span>
                                    <span class="fw-semibold text-dark">{{ $patient->studentProfile->program->code ?? '—' }} ({{ $patient->studentProfile->year_level }})</span>
                                </div>
                            @elseif ($patient->type === 'employee' && $patient->employeeProfile)
                                <div class="col-12">
                                    <span class="text-muted">Position:</span>
                                    <span class="fw-semibold text-dark">{{ $patient->employeeProfile->position }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted">Department:</span>
                                    <span class="fw-semibold text-dark">{{ $patient->employeeProfile->office_department ?: ($patient->employeeProfile->college->name ?? '—') }}</span>
                                </div>
                            @endif
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

                    <div class="mt-3 pt-3 border-top d-flex gap-2">
                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-person-lines-fill me-1"></i> Unified Patient Record
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold text-dark">Examiner Information</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle bg-light-primary text-primary" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $exam->examiner_name ?: 'School Physician' }}</div>
                            @if ($exam->clinician)
                                <div class="small text-muted">{{ $exam->clinician->title }}{{ $exam->clinician->specialization ? ' &bull; ' . $exam->clinician->specialization : '' }}</div>
                            @endif
                            @if ($exam->examiner_license_number)
                                <div class="small text-muted">PRC Lic. No: {{ $exam->examiner_license_number }}</div>
                            @endif
                            <div class="small text-muted">Term: {{ $exam->academicYear?->code ?? 'Current Academic Year' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-heart-pulse text-danger"></i>
                        <span class="fw-bold text-dark">Vital Signs &amp; Anthropometrics</span>
                    </div>
                    <span class="badge {{ $exam->bmi_badge_class }} px-2 py-1">BMI: {{ $exam->bmi ?: '—' }} ({{ $exam->bmi_category ?: 'Not calculated' }})</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Blood Pressure</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->blood_pressure ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Pulse Rate</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->pulse_rate ? "{$exam->pulse_rate} bpm" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Respiratory Rate</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->respiratory_rate ? "{$exam->respiratory_rate} cpm" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Temperature</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->temperature ? "{$exam->temperature}°C" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Height</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->height_cm ? "{$exam->height_cm} cm" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Weight</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->weight_kg ? "{$exam->weight_kg} kg" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Oxygen Saturation</div>
                                <div class="fs-5 fw-bold text-dark mt-1">{{ $exam->oxygen_saturation ? "{$exam->oxygen_saturation}%" : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 border rounded text-center bg-light">
                                <div class="text-muted small">Visual Acuity</div>
                                <div class="fs-6 fw-bold text-dark mt-1">R: {{ $exam->visual_acuity_right }} | L: {{ $exam->visual_acuity_left }}</div>
                                @if ($exam->visual_acuity_is_corrected)
                                    <span class="badge bg-info text-white mt-1" style="font-size: 0.65rem;">With Glasses</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-clipboard2-pulse text-primary"></i>
                    <span class="fw-bold text-dark">Systematic Physical Examination Findings</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">ORGAN SYSTEM</th>
                                    <th style="width: 20%;">STATUS</th>
                                    <th style="width: 50%;">CLINICAL OBSERVATION / REMARKS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $systemsMap = [
                                        'skin' => 'Skin & Integument',
                                        'heent' => 'HEENT (Head, Eyes, Ears, Nose, Throat)',
                                        'neck' => 'Neck & Cervical Lymph Nodes',
                                        'chest_lungs' => 'Chest & Lungs',
                                        'heart' => 'Cardiovascular & Heart',
                                        'abdomen' => 'Abdomen & Viscera',
                                        'musculoskeletal' => 'Musculoskeletal & Spine',
                                        'neurological' => 'Neurological / Motor Reflexes',
                                        'dental' => 'Dental / Oral Cavity'
                                    ];
                                @endphp
                                @foreach ($systemsMap as $sysKey => $sysTitle)
                                    @php
                                        $data = $exam->{$sysKey} ?? null;
                                        $status = is_array($data) ? ($data['status'] ?? 'normal') : 'normal';
                                        $remarks = is_array($data) ? ($data['remarks'] ?? null) : null;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $sysTitle }}</td>
                                        <td>
                                            @if ($status === 'normal')
                                                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-lg me-1"></i> Normal</span>
                                            @elseif ($status === 'abnormal')
                                                <span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i> Abnormal</span>
                                            @else
                                                <span class="badge bg-secondary text-white px-2 py-1">Deferred</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $remarks ?: 'Essentially normal findings with no apparent pathology.' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-journal-medical text-primary"></i>
                    <span class="fw-bold text-dark">Diagnostics, Clinical Impression &amp; Recommendations</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Chest X-Ray Findings:</span>
                            <div class="fw-semibold text-dark mt-1">{{ $exam->chest_xray_findings ?: 'Normal chest radiograph / clear lung fields' }}</div>
                            @if ($exam->chest_xray_date)
                                <div class="small text-muted">Date: {{ $exam->chest_xray_date->format('M d, Y') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <span class="text-muted small d-block">Laboratory Summary:</span>
                            <div class="fw-semibold text-dark mt-1">{{ $exam->laboratory_notes ?: 'CBC, Urinalysis, and routine laboratory screening unremarkable.' }}</div>
                        </div>

                        <div class="col-12 border-top pt-3">
                            <span class="text-muted small d-block">Clinical Impression / Findings:</span>
                            <div class="fw-bold text-dark fs-6 mt-1">{{ $exam->clinical_findings ?: 'Essentially normal physical examination findings.' }}</div>
                        </div>

                        <div class="col-12 border-top pt-3">
                            <span class="text-muted small d-block">Medical Recommendations &amp; Health Advice:</span>
                            <div class="text-dark mt-1">{{ $exam->medical_recommendations ?: 'Maintain balanced diet, adequate hydration, regular physical activity. Annual routine follow-up.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-award text-success"></i>
                        <span class="fw-bold text-dark">Medical Fitness Assessment &amp; Clearance (HSD Form 4)</span>
                    </div>
                    <span class="badge {{ $exam->fitness_badge_class }} px-3 py-1 fs-6">{{ $exam->fitness_assessment ?: 'Class A' }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70%;">CLEARANCE SCOPE</th>
                                    <th style="width: 30%;" class="text-center">CLEARANCE STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $acts = [
                                        'physical_activities' => 'Undergo Physical Activities / Trainings / Sports',
                                        'on_the_job_training' => 'On-the-Job Training (OJT)',
                                        'work_immersion' => 'Work Immersion',
                                        'to_work' => 'Fit to Work',
                                        'to_travel' => 'Fit to Travel',
                                        'other_requirements' => 'Other Academic / Institutional Requirements'
                                    ];
                                    $fitnessActs = (array) ($exam->fitness_activities ?? []);
                                @endphp
                                @foreach ($acts as $k => $label)
                                    @php
                                        $val = $fitnessActs[$k] ?? 'fit';
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $label }}</td>
                                        <td class="text-center">
                                            @if ($val === 'fit')
                                                <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i> FIT</span>
                                            @else
                                                <span class="badge bg-danger text-white px-3 py-1"><i class="bi bi-x-circle me-1"></i> UNFIT</span>
                                            @endif
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
</x-layouts.app>

