<x-layouts.app title="Daily Clinic Logbook">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Daily Clinic Logbook</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Daily Clinic Logbook</h1>
                <span class="badge badge-soft-green"><i class="bi bi-journal-check me-1"></i> Attendance Log</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Attendance and visit log for students, employees, and visitors.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('logbook.kiosk') }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-display me-1"></i> Open Public Kiosk Page (<code>/logbook</code>)
            </a>
            <a href="{{ route('reports.clinic.login-records') }}" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                <i class="bi bi-bar-chart-line me-1"></i> 12-Month Log-In Report
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addLogbookModal">
                <i class="bi bi-plus-lg me-1"></i> Add Logbook Entry
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Logged In Today</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-journal-bookmark-fill"></i></span>
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
                    <span class="kpi-label">Currently Inside Clinic</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-person-walking"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['currently_inside']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-clock"></i> Active</span>
                    <span class="trend-compare">awaiting time-out</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Students Today</span>
                    <span class="kpi-icon-box green"><i class="bi bi-mortarboard"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['students_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Students</span>
                    <span class="trend-compare">all academic programs</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Employees Today</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-person-workspace"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral">Faculty &amp; Staff</span>
                    <span class="trend-compare">logged today</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-3">
        <div class="card-header bg-white py-3">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-2 text-primary"></i>Filter Logbook</span>
        </div>
        <div class="card-body">
            <form id="logbook-filter-form" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search Visitor</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-log-search" class="form-control" placeholder="Search name, ID...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Visitor Type</label>
                    <select name="visitor_type" id="filter-log-visitor-type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                        <option value="visitor">Visitor</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Program / Level</label>
                    <select name="program_id" id="filter-log-program" class="form-select form-select-sm">
                        <option value="">All Programs</option>
                        @foreach ($programs as $prog)
                            <option value="{{ $prog->id }}">{{ $prog->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" id="filter-log-date-from" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" id="filter-log-date-to" class="form-control form-control-sm">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Filter">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Logbook Entries</span>
            <span class="badge badge-soft-gray">{{ number_format($totalLogs) }} Total Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="logbook-table" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">ACTION</th>
                            <th>LOG # &amp; DATE</th>
                            <th>TIME IN / OUT</th>
                            <th>VISITOR / PATIENT</th>
                            <th>TYPE &amp; PROGRAM / DEPT</th>
                            <th>PURPOSE</th>
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

    <div class="modal fade" id="addLogbookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('clinic-logbooks.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus text-primary me-2"></i>Add Clinic Logbook Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Visitor Type</label>
                            <select name="visitor_type" class="form-select" required>
                                <option value="student">Student</option>
                                <option value="employee">Employee</option>
                                <option value="visitor">Visitor / Guest</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ID Number (Optional)</label>
                            <input type="text" name="id_number" class="form-control" placeholder="Student / Employee ID">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program / Course / Level</label>
                            <select name="program_id" class="form-select">
                                <option value="">-- Select Program / N/A --</option>
                                @foreach ($programs as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->code }} &mdash; {{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year / Position</label>
                            <input type="text" name="year_or_position" class="form-control" placeholder="e.g. 1st Year / Staff">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="09XX-XXX-XXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Purpose of Visit</label>
                            <select name="purpose" class="form-select" required>
                                @foreach ($purposes as $pLabel)
                                    <option value="{{ $pLabel }}">{{ $pLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Remarks / Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional details...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Logbook Entry</button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const logbookTable = $('#logbook-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ (int) ($appSettings['table_page_length'] ?? 25) }},
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('clinic-logbooks.index') }}",
                        data: function (d) {
                            d.visitor_type = $('#filter-log-visitor-type').val();
                            d.program_id = $('#filter-log-program').val();
                            d.date_from = $('#filter-log-date-from').val();
                            d.date_to = $('#filter-log-date-to').val();
                            d.custom_search = $('#filter-log-search').val();
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'log_number_col', name: 'log_number' },
                        { data: 'time_col', name: 'time_in' },
                        { data: 'visitor_col', name: 'full_name' },
                        { data: 'type_dept_col', name: 'visitor_type' },
                        { data: 'purpose_col', name: 'purpose' },
                        { data: 'status_col', name: 'status' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick filter...",
                        emptyTable: "No clinic logbook entries found.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading logbook...'
                    }
                });

                let logSearchTimer;
                $('#filter-log-search').on('input', function () {
                    clearTimeout(logSearchTimer);
                    logSearchTimer = setTimeout(() => logbookTable.draw(), 300);
                });

                $('#logbook-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    logbookTable.draw();
                });

                $('#logbook-filter-form').on('reset', function () {
                    setTimeout(() => {
                        $('#filter-log-date-from').val('');
                        $('#filter-log-date-to').val('');
                        logbookTable.draw();
                    }, 50);
                });

                $('#filter-log-visitor-type, #filter-log-program, #filter-log-date-from, #filter-log-date-to').on('change', function () {
                    logbookTable.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>

