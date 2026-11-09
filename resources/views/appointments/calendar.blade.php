<x-layouts.calendar title="Doctor Appointments Calendar">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('appointments.index') }}">Doctor Appointments</a>
        <span>&gt;</span>
        <span>Appointment Calendar</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Appointment Calendar</h1>
                <span class="badge badge-soft-blue"><i class="bi bi-calendar2-range me-1"></i> Calendar</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">View and schedule patient appointments.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Back to Appointments
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card border shadow-xs mb-3">
                <div class="card-header bg-white py-2 px-3 fw-bold small text-dark d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-speedometer2 text-primary me-1.5"></i>Today's Summary</span>
                    <span class="badge badge-soft-green" style="font-size: 0.65rem;">{{ now()->format('M d, Y') }}</span>
                </div>
                <div class="card-body p-2.5">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light-subtle">
                                <div class="text-muted fw-semibold" style="font-size: 0.7rem;">Booked Today</div>
                                <div class="fs-4 fw-bold text-dark lh-1 my-1">{{ number_format($stats['total_today']) }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;"><span class="text-success fw-bold">&bull;</span> Today</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-2 border rounded bg-light-subtle">
                                <div class="text-muted fw-semibold" style="font-size: 0.7rem;">In Queue</div>
                                <div class="fs-4 fw-bold text-dark lh-1 my-1">{{ number_format($stats['waiting_today']) }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;"><span class="text-warning fw-bold">&bull;</span> Waiting</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-2 border rounded bg-light-subtle">
                                <div class="text-muted fw-semibold" style="font-size: 0.7rem;">Completed</div>
                                <div class="fs-4 fw-bold text-dark lh-1 my-1">{{ number_format($stats['completed_today']) }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;"><span class="text-success fw-bold">&bull;</span> Done</div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="p-2 border rounded bg-light-subtle">
                                <div class="text-muted fw-semibold" style="font-size: 0.7rem;">Urgent / High</div>
                                <div class="fs-4 fw-bold text-danger lh-1 my-1">{{ number_format($stats['urgent_today']) }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;"><span class="text-danger fw-bold">&bull;</span> Urgent</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-xs mb-3">
                <div class="card-header bg-white py-2 px-3 fw-bold small text-dark d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-funnel text-primary me-1.5"></i>Filter Appointments</span>
                    <a href="{{ route('appointments.calendar') }}" class="btn btn-link btn-sm p-0 text-decoration-none small text-muted" title="Reset Filters">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('appointments.calendar') }}" class="d-flex flex-column gap-2">
                        <div>
                            <label class="form-label small fw-semibold text-muted mb-1">Doctor</label>
                            <select name="clinician_id" class="form-select form-select-sm">
                                <option value="">-- All Doctors --</option>
                                @foreach ($clinicians as $cl)
                                    <option value="{{ $cl->id }}" @selected(request('clinician_id') == $cl->id)>{{ $cl->name }} ({{ $cl->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label small fw-semibold text-muted mb-1">Service</label>
                            <select name="service_type" class="form-select form-select-sm">
                                <option value="">-- All Services --</option>
                                @foreach ($serviceTypes as $sKey => $sLabel)
                                    <option value="{{ $sKey }}" @selected(request('service_type') === $sKey)>{{ $sLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">-- All Priorities --</option>
                                @foreach ($priorities as $pKey => $pLabel)
                                    <option value="{{ $pKey }}" @selected(request('priority') === $pKey)>{{ $pLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- All Statuses --</option>
                                @foreach ($statuses as $stKey => $stLabel)
                                    <option value="{{ $stKey }}" @selected(request('status') === $stKey)>{{ $stLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold mt-1">
                            <i class="bi bi-funnel me-1"></i> Apply Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border shadow-xs mb-3">
                <div class="card-header bg-white py-2 px-3 fw-bold small text-dark">
                    <i class="bi bi-palette text-primary me-1.5"></i>Legend
                </div>
                <div class="card-body p-2.5 small">
                    <div class="d-flex flex-column gap-1.5">
                        <div class="d-flex align-items-center justify-content-between p-1 rounded bg-light-subtle">
                            <span class="d-inline-flex align-items-center"><span class="badge rounded-circle p-1 bg-danger me-2">&bull;</span> Urgent</span>
                            <span class="badge badge-soft-red" style="font-size: 0.65rem;">Priority</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-1 rounded bg-light-subtle">
                            <span class="d-inline-flex align-items-center"><span class="badge rounded-circle p-1 bg-warning me-2">&bull;</span> High</span>
                            <span class="badge badge-soft-amber" style="font-size: 0.65rem;">Priority</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-1 rounded bg-light-subtle">
                            <span class="d-inline-flex align-items-center"><span class="badge rounded-circle p-1 bg-primary me-2">&bull;</span> Normal</span>
                            <span class="badge badge-soft-green" style="font-size: 0.65rem;">Routine</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-1 rounded bg-light-subtle">
                            <span class="d-inline-flex align-items-center"><span class="badge rounded-circle p-1 bg-info me-2">&bull;</span> Checked In</span>
                            <span class="badge badge-soft-blue" style="font-size: 0.65rem;">Waiting</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-1 rounded bg-light-subtle">
                            <span class="d-inline-flex align-items-center"><span class="badge rounded-circle p-1 bg-secondary me-2">&bull;</span> Completed</span>
                            <span class="badge badge-soft-gray" style="font-size: 0.65rem;">Done</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary w-100 py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                <i class="bi bi-calendar-plus-fill"></i>
                <span>Book Appointment</span>
            </button>
        </div>

        <div class="col-12 col-lg-8 col-xl-9">
            <div class="card border shadow-xs h-100">
                <div class="card-body p-3">
                    <div id="fullcalendar-view"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="appointmentDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header">
                    <div>
                        <span id="modal-priority-badge" class="badge mb-1">Priority</span>
                        <h5 class="modal-title fw-bold text-dark" id="modal-appointment-number">APT-0000</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 p-3 bg-light-subtle rounded-3 mb-3 border">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;" id="modal-avatar">
                            P
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="modal-patient-name">—</h6>
                            <div class="small text-muted">
                                <span id="modal-patient-id">—</span> &bull;
                                <span class="badge badge-soft-blue" id="modal-category">Student</span>
                                <span id="modal-program"></span>
                            </div>
                            <div class="small text-muted mt-0.5" id="modal-phone-wrapper">
                                <i class="bi bi-telephone me-1"></i><span id="modal-phone">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Scheduled Date:</span>
                            <span class="fw-bold text-dark" id="modal-date">—</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Time Slot:</span>
                            <span class="fw-bold text-dark" id="modal-time">—</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Service Requested:</span>
                            <span class="badge badge-soft-purple" id="modal-service">—</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Attending Doctor:</span>
                            <span class="fw-semibold text-dark" id="modal-doctor">—</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Chief Complaint / Reason:</label>
                        <div class="p-2 border rounded bg-white small text-dark fw-medium" id="modal-reason">
                            —
                        </div>
                    </div>

                    <div class="mb-3" id="modal-notes-wrapper">
                        <label class="form-label small fw-semibold text-muted">Nurse Intake Notes:</label>
                        <div class="p-2 border rounded bg-white small text-muted" id="modal-notes">
                            —
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light-subtle border">
                        <span class="small fw-semibold text-muted">Current Queue Status:</span>
                        <span class="badge" id="modal-status-badge">Scheduled</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="modal-checkin-form" class="d-none">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Check In Patient
                        </button>
                    </form>

                    <form method="POST" id="modal-startconsult-form" class="d-none">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Start Consultation
                        </button>
                    </form>

                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
                                    <input type="radio" class="btn-check" name="patient_mode" id="modeExistingCal" value="existing" autocomplete="off" checked>
                                    <label class="btn fw-semibold" for="modeExistingCal">
                                        <i class="bi bi-person-check me-1"></i> Existing Patient
                                    </label>

                                    <input type="radio" class="btn-check" name="patient_mode" id="modeNewCal" value="new" autocomplete="off">
                                    <label class="btn fw-semibold" for="modeNewCal">
                                        <i class="bi bi-person-plus-fill me-1"></i> New Patient / Walk-in
                                    </label>
                                </div>

                                <div id="existingPatientSectionCal">
                                    <label class="form-label small fw-semibold text-muted required">Search Patient</label>
                                    <select name="patient_id" id="appointment_patient_id_cal" class="form-select" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search existing student or employee by ID number, first or last name..."></select>
                                    <div class="form-text small text-muted">Search by ID number or name.</div>
                                </div>

                                <div id="newPatientSectionCal" class="d-none">
                                    <div class="alert alert-soft-success py-2 px-3 small d-flex align-items-center mb-3">
                                        <i class="bi bi-info-circle-fill text-success fs-5 me-2"></i>
                                        <div>
                                            <strong>Bagong Pasyente:</strong> Para sa mga walk-in o bagong pasyente na wala pa sa listahan.
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold required">Classification</label>
                                            <select name="new_patient_type" class="form-select form-select-sm" id="newPatientTypeSelectCal">
                                                <option value="student" selected>Student</option>
                                                <option value="employee">Employee (Teaching / Non-Teaching)</option>
                                                <option value="visitor">Visitor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-none" id="employeeCategoryWrapperCal">
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
                            <input type="date" name="appointment_date" id="appointmentDateInput" value="{{ now()->isWeekend() ? now()->next('Monday')->toDateString() : now()->toDateString() }}" class="form-control" required>
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

    @push('styles')
        <style>
            .fc {
                --fc-border-color: #e2e8e5;
                --fc-button-text-color: #ffffff;
                --fc-button-bg-color: #1b5e3a;
                --fc-button-border-color: #1b5e3a;
                --fc-button-hover-bg-color: #14472c;
                --fc-button-hover-border-color: #14472c;
                --fc-button-active-bg-color: #14472c;
                --fc-button-active-border-color: #14472c;
                --fc-today-bg-color: #f2f7f4;
                font-family: var(--app-font);
            }
            .fc .fc-toolbar {
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            .fc .fc-toolbar-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #182620;
            }
            .fc .fc-button {
                font-size: 0.8rem;
                font-weight: 600;
                padding: 0.35rem 0.75rem;
                border-radius: 0.35rem;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }
            .fc .fc-button-primary:not(:disabled).fc-button-active,
            .fc .fc-button-primary:not(:disabled):active {
                background-color: #14472c;
                border-color: #14472c;
            }
            .fc .fc-col-header-cell {
                background-color: #f7faf8;
            }
            .fc .fc-col-header-cell-cushion {
                font-size: 0.75rem;
                font-weight: 700;
                color: #5a6b63;
                text-decoration: none;
                padding: 6px 0;
                letter-spacing: 0.04em;
            }
            .fc .fc-daygrid-day-number {
                font-size: 0.8rem;
                font-weight: 600;
                color: #182620;
                text-decoration: none;
                padding: 4px 6px;
            }
            .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
                background: #1b5e3a;
                color: #ffffff;
                border-radius: 4px;
                padding: 2px 6px;
            }
            .fc .fc-daygrid-day:hover {
                background-color: #f8fbf9;
            }
            .fc .fc-daygrid-day-frame {
                cursor: pointer;
                min-height: 110px;
            }
            .fc-event {
                cursor: pointer;
                border-radius: 4px;
                padding: 2px 4px;
                font-size: 0.72rem;
                font-weight: 500;
                margin-bottom: 2px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('assets/plugins/fullcalendar/index.global.min.js') }}"></script>
        @include('partials.plugins.select2')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendarEl = document.getElementById('fullcalendar-view');
                const events = @json($calendarEvents);

                if (calendarEl && typeof FullCalendar !== 'undefined') {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        initialDate: '{{ $currentMonth->toDateString() }}',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                        },
                        events: events,
                        navLinks: true,
                        editable: false,
                        dayMaxEvents: 3,
                        dateClick: function (info) {
                            const dateInput = document.getElementById('appointmentDateInput');
                            if (dateInput) {
                                dateInput.value = info.dateStr;
                            }
                            const bookModalEl = document.getElementById('bookAppointmentModal');
                            if (bookModalEl) {
                                const modal = bootstrap.Modal.getInstance(bookModalEl) || new bootstrap.Modal(bookModalEl);
                                modal.show();
                            }
                        },
                        eventClick: function (info) {
                            const p = info.event.extendedProps;
                            document.getElementById('modal-appointment-number').textContent = p.appointment_number || 'APT';
                            document.getElementById('modal-patient-name').textContent = p.patient_name || 'Patient';
                            document.getElementById('modal-avatar').textContent = (p.patient_name || 'P').charAt(0).toUpperCase();
                            document.getElementById('modal-patient-id').textContent = p.patient_identifier || '—';
                            document.getElementById('modal-category').textContent = p.category || 'Student';
                            document.getElementById('modal-program').textContent = p.program ? (' • ' + p.program) : '';

                            const phoneWrap = document.getElementById('modal-phone-wrapper');
                            const phoneEl = document.getElementById('modal-phone');
                            if (p.phone) {
                                phoneEl.textContent = p.phone;
                                phoneWrap.classList.remove('d-none');
                            } else {
                                phoneWrap.classList.add('d-none');
                            }

                            document.getElementById('modal-date').textContent = p.date || '—';
                            document.getElementById('modal-time').textContent = p.time || '—';
                            document.getElementById('modal-service').textContent = p.service_type || 'General';
                            document.getElementById('modal-doctor').textContent = p.doctor || 'Duty Doctor';
                            document.getElementById('modal-reason').textContent = p.reason || 'No chief complaint recorded.';

                            const notesWrap = document.getElementById('modal-notes-wrapper');
                            const notesEl = document.getElementById('modal-notes');
                            if (p.notes) {
                                notesEl.textContent = p.notes;
                                notesWrap.classList.remove('d-none');
                            } else {
                                notesWrap.classList.add('d-none');
                            }

                            const pBadge = document.getElementById('modal-priority-badge');
                            pBadge.className = 'badge mb-1 ' + (p.priority_badge || 'badge-soft-green');
                            pBadge.textContent = (p.priority ? p.priority.toUpperCase() : 'NORMAL') + ' PRIORITY';

                            const sBadge = document.getElementById('modal-status-badge');
                            sBadge.className = 'badge px-2 py-1 ' + (p.status_badge || 'badge-soft-blue');
                            sBadge.textContent = p.status_label || 'Scheduled';

                            const checkinForm = document.getElementById('modal-checkin-form');
                            if (p.can_check_in && p.check_in_url) {
                                checkinForm.action = p.check_in_url;
                                checkinForm.classList.remove('d-none');
                            } else {
                                checkinForm.classList.add('d-none');
                            }

                            const startForm = document.getElementById('modal-startconsult-form');
                            if (p.can_start_consult && p.start_consult_url) {
                                startForm.action = p.start_consult_url;
                                startForm.classList.remove('d-none');
                            } else {
                                startForm.classList.add('d-none');
                            }

                            const detailModalEl = document.getElementById('appointmentDetailModal');
                            const detailModal = bootstrap.Modal.getInstance(detailModalEl) || new bootstrap.Modal(detailModalEl);
                            detailModal.show();
                        }
                    });
                    calendar.render();
                }

                const modeExisting = document.getElementById('modeExistingCal');
                const modeNew = document.getElementById('modeNewCal');
                const existingSection = document.getElementById('existingPatientSectionCal');
                const newSection = document.getElementById('newPatientSectionCal');
                const patientSelect = document.getElementById('appointment_patient_id_cal');
                const typeSelect = document.getElementById('newPatientTypeSelectCal');
                const empWrapper = document.getElementById('employeeCategoryWrapperCal');

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
</x-layouts.calendar>