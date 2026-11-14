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
                    <span class="trend-compare">{{ now()->format($appSettings['date_format'] ?? 'M d, Y') }}</span>
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
            <form id="appointments-filter-form" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Date</label>
                    <div class="input-group">
                        <input type="date" name="date" id="filter-date" value="{{ $currentDate !== 'all' ? $currentDate : '' }}" class="form-control">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-date-today" title="Today">Today</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-date-all" title="All Dates">All</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Doctor</label>
                    <select name="clinician_id" id="filter-clinician" class="form-select">
                        <option value="">All Doctors</option>
                        @foreach ($clinicians as $cl)
                            <option value="{{ $cl->id }}">{{ $cl->name }} ({{ $cl->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Service</label>
                    <select name="service_type" id="filter-service-type" class="form-select">
                        <option value="">All Services</option>
                        @foreach ($serviceTypes as $sKey => $sLabel)
                            <option value="{{ $sKey }}">{{ $sLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Status</label>
                    <select name="status" id="filter-status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $stKey => $stLabel)
                            <option value="{{ $stKey }}">{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-1">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Search patient, ref #...">
                    </div>
                    <button type="reset" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-dark"><i class="bi bi-list-check me-1"></i> Appointments List</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('appointments.calendar') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calendar3 me-1"></i> Calendar View
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="appointments-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ACTION</th>
                            <th>TIME / REF #</th>
                            <th>PRIORITY</th>
                            <th>PATIENT</th>
                            <th>DOCTOR</th>
                            <th>REASON</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="8" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelAppointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" action="" id="cancelAppointmentForm" class="modal-content text-start">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Cancel Appointment</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Cancel appointment <strong id="cancelAppointmentRef"></strong>?</p>
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

    @include('partials.plugins.datatables')

    @push('scripts')
        @include('partials.plugins.select2')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const appointmentsTable = $('#appointments-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ (int) ($appSettings['table_page_length'] ?? 25) }},
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'asc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('appointments.index') }}",
                        data: function (d) {
                            d.date = $('#filter-date').val();
                            d.clinician_id = $('#filter-clinician').val();
                            d.service_type = $('#filter-service-type').val();
                            d.status = $('#filter-status').val();
                            d.custom_search = $('#filter-search').val();
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'time_ref_col', name: 'appointment_time' },
                        { data: 'priority_col', name: 'priority_level' },
                        { data: 'patient_col', name: 'patient_id', orderable: false },
                        { data: 'doctor_col', name: 'clinician_id', orderable: false },
                        { data: 'reason_col', name: 'reason' },
                        { data: 'status_col', name: 'status' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick filter...",
                        emptyTable: "No appointments found.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading appointments...'
                    }
                });

                let appSearchTimer;
                $('#filter-search').on('input', function () {
                    clearTimeout(appSearchTimer);
                    appSearchTimer = setTimeout(() => appointmentsTable.draw(), 300);
                });

                $('#appointments-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    appointmentsTable.draw();
                });

                $('#appointments-filter-form').on('reset', function () {
                    setTimeout(() => {
                        $('#filter-date').val('');
                        appointmentsTable.draw();
                    }, 50);
                });

                $('#filter-date, #filter-clinician, #filter-service-type, #filter-status').on('change', function () {
                    appointmentsTable.draw();
                });

                $('#btn-date-today').on('click', function () {
                    $('#filter-date').val('{{ now()->toDateString() }}');
                    appointmentsTable.draw();
                });

                $('#btn-date-all').on('click', function () {
                    $('#filter-date').val('all');
                    appointmentsTable.draw();
                });

                $(document).on('click', '.btn-open-cancel-modal', function () {
                    const action = $(this).data('action');
                    const ref = $(this).data('ref');
                    $('#cancelAppointmentForm').attr('action', action);
                    $('#cancelAppointmentRef').text(ref);
                    $('#cancelAppointmentModal').modal('show');
                });

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
