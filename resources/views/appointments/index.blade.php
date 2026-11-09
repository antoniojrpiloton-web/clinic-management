<x-layouts.app title="Doctor Appointments">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Doctor Appointments</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Doctor Appointments</h1>
                <span class="badge badge-soft-blue"><i class="bi bi-calendar2-check me-1"></i> Appointments</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">Manage scheduled patient appointments and clinic queue.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('appointments.calendar') }}" class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-calendar3 me-1"></i> Calendar View
            </a>
            <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-journal-medical me-1"></i> Patient Visits
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Appointments Today</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-calendar-event"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-calendar-check"></i> Today</span>
                    <span class="trend-compare">{{ now()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Waiting in Queue</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['waiting_today']) }}</div>
                <div class="kpi-trend">
                    @if ($stats['waiting_today'] > 0)
                        <span class="trend-badge neutral"><i class="bi bi-person-walking"></i> Checked In</span>
                        <span class="trend-compare">ready for doctor</span>
                    @else
                        <span class="trend-badge up"><i class="bi bi-check2"></i> Clear</span>
                        <span class="trend-compare">no waiting patients</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Completed Consults</span>
                    <span class="kpi-icon-box green"><i class="bi bi-check2-circle"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['completed_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Fulfilled</span>
                    <span class="trend-compare">treated today</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Urgent / High Priority</span>
                    <span class="kpi-icon-box red"><i class="bi bi-exclamation-triangle-fill"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['urgent_today']) }}</div>
                <div class="kpi-trend">
                    @if ($stats['urgent_today'] > 0)
                        <span class="trend-badge down"><i class="bi bi-bell-fill"></i> Priority 1</span>
                        <span class="trend-compare">Urgent</span>
                    @else
                        <span class="trend-badge up"><i class="bi bi-check2"></i> Normal</span>
                        <span class="trend-compare">routine</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter Appointments</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('appointments.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Date</label>
                    <div class="input-group">
                        <input type="date" name="date" value="{{ $currentDate !== 'all' ? $currentDate : '' }}" class="form-control">
                        <a href="{{ route('appointments.index', ['date' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm" title="Today">Today</a>
                        <a href="{{ route('appointments.index', ['date' => 'all']) }}" class="btn btn-outline-secondary btn-sm" title="All Dates">All</a>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Doctor</label>
                    <select name="clinician_id" class="form-select">
                        <option value="">All Doctors</option>
                        @foreach ($clinicians as $cl)
                            <option value="{{ $cl->id }}" @selected(request('clinician_id') == $cl->id)>{{ $cl->name }} ({{ $cl->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Service</label>
                    <select name="service_type" class="form-select">
                        <option value="">All Services</option>
                        @foreach ($serviceTypes as $sKey => $sLabel)
                            <option value="{{ $sKey }}" @selected(request('service_type') === $sKey)>{{ $sLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $stKey => $stLabel)
                            <option value="{{ $stKey }}" @selected(request('status') === $stKey)>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-dark"><i class="bi bi-list-check me-1"></i> Appointments List</span>
                <span class="text-muted small ms-2">{{ $currentDate !== 'all' ? \Carbon\Carbon::parse($currentDate)->format('F d, Y') : 'All Dates' }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('appointments.calendar') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calendar3 me-1"></i> Calendar View
                </a>
                <span class="badge badge-soft-gray">{{ $appointments->total() }} appointments</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th>TIME / REF #</th>
                            <th>PRIORITY</th>
                            <th>PATIENT</th>
                            <th>DOCTOR</th>
                            <th>REASON</th>
                            <th>STATUS</th>
                            <th class="text-end" style="min-width: 190px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $app)
                            <tr @class(['table-warning-subtle' => $app->priority_level === 'urgent' && $app->status === 'checked_in'])>
                                <td>
                                    <div class="fw-bold text-dark"><i class="bi bi-clock me-1 text-primary"></i>{{ $app->appointment_time }}</div>
                                    <div class="fw-mono small text-muted">{{ $app->appointment_number }} &bull; {{ $app->appointment_date ? $app->appointment_date->format('M d, Y') : '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $app->priority_badge_class }}">
                                        @if ($app->priority_level === 'urgent')
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        @endif
                                        {{ ucfirst($app->priority_level) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $app->patient?->full_name }}</div>
                                    <div class="small text-muted">
                                        {{ $app->patient?->primary_identifier }} &bull;
                                        <span class="badge badge-soft-blue">
                                            {{ $app->patient?->type === 'employee' ? ($app->patient?->category === 'Faculty' ? 'Faculty' : 'Staff') : ($app->patient?->type === 'visitor' ? 'Visitor' : 'Student') }}
                                        </span>
                                        @if ($app->patient?->program)
                                            &bull; {{ $app->patient->program->code }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($app->clinician)
                                        <div class="fw-medium text-dark">{{ $app->clinician->name }}</div>
                                        <div class="small text-muted">{{ $app->clinician->title }} ({{ $app->clinician->type }})</div>
                                    @else
                                        <span class="text-muted italic">Any Available Doctor</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $app->reason }}</div>
                                    <span class="badge badge-soft-blue" style="font-size: 0.7rem;">{{ $serviceTypes[$app->service_type] ?? ucfirst($app->service_type) }}</span>
                                    @if ($app->notes)
                                        <div class="small text-muted mt-1">{{ Str::limit($app->notes, 60) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $app->status_badge_class }}">
                                        {{ $statuses[$app->status] ?? ucfirst($app->status) }}
                                    </span>
                                    @if ($app->clinicVisit)
                                        <div class="mt-1">
                                            <a href="{{ route('clinic-visits.show', $app->clinicVisit) }}" class="badge badge-soft-purple text-decoration-none">
                                                <i class="bi bi-link-45deg"></i> {{ $app->clinicVisit->visit_number }}
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if ($app->status === 'scheduled')
                                            <form method="POST" action="{{ route('appointments.check-in', $app) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info text-dark fw-semibold py-1 px-2" title="Mark Patient Arrived &amp; Waiting">
                                                    <i class="bi bi-check2-circle"></i> Check In
                                                </button>
                                            </form>
                                        @endif

                                        @if ($app->status === 'checked_in' || $app->status === 'scheduled')
                                            <form method="POST" action="{{ route('appointments.start-consultation', $app) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" title="Doctor Call: Start Consultation">
                                                    <i class="bi bi-clipboard2-pulse me-1"></i> Start
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $app->id }}" title="Cancel Appointment">
                                                <i class="bi bi-x-circle"></i>
                                            </button>

                                            <div class="modal fade" id="cancelModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-sm">
                                                    <form method="POST" action="{{ route('appointments.cancel', $app) }}" class="modal-content text-start">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold">Cancel Appointment</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label small fw-semibold">Cancellation Reason</label>
                                                            <textarea name="cancellation_reason" class="form-control form-control-sm" rows="3" placeholder="Reason for patient cancellation..." required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger btn-sm">Confirm Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-check fs-1 d-block mb-2 text-secondary"></i>
                                    No doctor appointments found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($appointments->hasPages())
            <div class="card-footer bg-white">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="bookAppointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('appointments.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>Book Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light-subtle">
                                <label class="form-label fw-bold text-dark d-block mb-2">Patient Type</label>
                                <div class="intake-mode-toggle mb-3" role="group">
                                    <input type="radio" class="btn-check" name="patient_mode" id="modeExisting" value="existing" autocomplete="off" checked>
                                    <label class="btn fw-semibold" for="modeExisting">
                                        <i class="bi bi-person-check me-1"></i> Existing Patient
                                    </label>

                                    <input type="radio" class="btn-check" name="patient_mode" id="modeNew" value="new" autocomplete="off">
                                    <label class="btn fw-semibold" for="modeNew">
                                        <i class="bi bi-person-plus-fill me-1"></i> New Patient / Walk-in
                                    </label>
                                </div>

                                <div id="existingPatientSection">
                                    <label class="form-label small fw-semibold text-muted required">Search Patient</label>
                                    <select name="patient_id" id="appointment_patient_id" class="form-select" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search existing student or employee by ID number, first or last name..."></select>
                                    <div class="form-text small text-muted">Search by ID number or name.</div>
                                </div>

                                <div id="newPatientSection" class="d-none">
                                    <div class="alert alert-soft-success py-2 px-3 small d-flex align-items-center mb-3">
                                        <i class="bi bi-info-circle-fill text-success fs-5 me-2"></i>
                                        <div>
                                            <strong>Bagong Pasyente:</strong> Para sa mga walk-in o bagong pasyente na wala pa sa listahan.
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold required">Classification</label>
                                            <select name="new_patient_type" class="form-select form-select-sm" id="newPatientTypeSelect">
                                                <option value="student" selected>Student</option>
                                                <option value="employee">Employee (Teaching / Non-Teaching)</option>
                                                <option value="visitor">Visitor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-none" id="employeeCategoryWrapper">
                                            <label class="form-label small fw-semibold required">Employee Category</label>
                                            <select name="new_employee_category" class="form-select form-select-sm">
                                                <option value="Faculty" selected>Teaching (Faculty)</option>
                                                <option value="Non-Teaching / Staff">Non-Teaching (Staff)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">ID / Applicant / Examinee #</label>
                                            <input type="text" name="new_id_number" class="form-control form-control-sm" placeholder="e.g. 2026-APP-001 or temp ID">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold required">Gender</label>
                                            <select name="new_gender" class="form-select form-select-sm">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold required">First Name</label>
                                            <input type="text" name="new_first_name" class="form-control form-control-sm" placeholder="First Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold required">Last Name</label>
                                            <input type="text" name="new_last_name" class="form-control form-control-sm" placeholder="Last Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Program / Course / Dept</label>
                                            <select name="new_program_id" class="form-select form-select-sm">
                                                <option value="">-- Select Degree / Program (Optional) --</option>
                                                @foreach ($programs as $prog)
                                                    <option value="{{ $prog->id }}">{{ $prog->code }} &mdash; {{ $prog->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Year / Level</label>
                                            <select name="new_year_level" class="form-select form-select-sm">
                                                <option value="1st Year" selected>1st Year (Freshman)</option>
                                                <option value="2nd Year">2nd Year</option>
                                                <option value="3rd Year">3rd Year</option>
                                                <option value="4th Year">4th Year</option>
                                                <option value="Applicant">Applicant / Examinee</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Contact Phone</label>
                                            <input type="text" name="new_phone" class="form-control form-control-sm" placeholder="09XX-XXX-XXXX">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Doctor</label>
                            <select name="clinician_id" class="form-select">
                                <option value="">-- Any Available Doctor --</option>
                                @foreach ($clinicians as $clinician)
                                    <option value="{{ $clinician->id }}">{{ $clinician->name }} &mdash; {{ $clinician->title }} ({{ $clinician->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Service</label>
                            <select name="service_type" class="form-select" required>
                                @foreach ($serviceTypes as $stKey => $stLabel)
                                    <option value="{{ $stKey }}" @selected($stKey === 'medical')>{{ $stLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Appointment Date</label>
                            <input type="date" name="appointment_date" value="{{ now()->isWeekend() ? now()->next('Monday')->toDateString() : now()->toDateString() }}" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Time Slot</label>
                            <select name="appointment_time" class="form-select" required>
                                @foreach ($timeSlots as $slot)
                                    <option value="{{ $slot }}" @selected($slot === '09:00 AM')>{{ $slot }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Priority</label>
                            <select name="priority_level" class="form-select" required>
                                @foreach ($priorities as $prKey => $prLabel)
                                    <option value="{{ $prKey }}" @selected($prKey === 'normal')>{{ $prLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">Reason for Appointment</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Persistent cough, toothache, medical clearance..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Symptoms, instructions, or clinic notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('partials.plugins.select2')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modeExisting = document.getElementById('modeExisting');
                const modeNew = document.getElementById('modeNew');
                const existingSection = document.getElementById('existingPatientSection');
                const newSection = document.getElementById('newPatientSection');
                const patientSelect = document.getElementById('appointment_patient_id');
                const typeSelect = document.getElementById('newPatientTypeSelect');
                const empWrapper = document.getElementById('employeeCategoryWrapper');

                function togglePatientMode() {
                    if (modeNew && modeNew.checked) {
                        existingSection?.classList.add('d-none');
                        newSection?.classList.remove('d-none');
                        if (patientSelect) patientSelect.required = false;
                    } else {
                        existingSection?.classList.remove('d-none');
                        newSection?.classList.add('d-none');
                        if (patientSelect) patientSelect.required = true;
                    }
                }

                function toggleEmployeeCategory() {
                    if (typeSelect && empWrapper) {
                        if (typeSelect.value === 'employee') {
                            empWrapper.classList.remove('d-none');
                        } else {
                            empWrapper.classList.add('d-none');
                        }
                    }
                }

                modeExisting?.addEventListener('change', togglePatientMode);
                modeNew?.addEventListener('change', togglePatientMode);
                typeSelect?.addEventListener('change', toggleEmployeeCategory);

                togglePatientMode();
                toggleEmployeeCategory();
            });
        </script>
    @endpush
</x-layouts.app>
