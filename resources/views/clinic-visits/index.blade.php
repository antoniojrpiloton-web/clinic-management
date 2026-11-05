<x-layouts.app title="Patient Visit Logs">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Patient Visit Logs</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Patient Visit Logs &amp; Central Registry</h1>
            <p class="dashboard-subheading mb-0">Unified daily intake register of all student, faculty, and staff encounters across all clinical services.</p>
        </div>

        @can('clinic-visits.create')
            <div class="dropdown">
                <button class="btn btn-primary px-3 py-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-circle me-1"></i> Log Patient Visit
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('clinic-visits.create', ['type' => 'general']) }}"><i class="bi bi-journal-text text-secondary me-2"></i> General Visit / Walk-in</a></li>
                    <li><a class="dropdown-item" href="{{ route('medical-consultations.create') }}"><i class="bi bi-clipboard2-pulse text-primary me-2"></i> Medical Consultation</a></li>
                    <li><a class="dropdown-item" href="{{ route('dental-consultations.create') }}"><i class="bi bi-emoji-smile text-info me-2"></i> Dental Consultation</a></li>
                    <li><a class="dropdown-item" href="{{ route('emergency-records.create') }}"><i class="bi bi-truck-front text-danger me-2"></i> Emergency Incident</a></li>
                    <li><a class="dropdown-item" href="{{ route('first-aid-records.create') }}"><i class="bi bi-bandaid text-warning me-2"></i> First Aid Treatment</a></li>
                    <li><a class="dropdown-item" href="{{ route('physical-examinations.create') }}"><i class="bi bi-person-check text-purple me-2"></i> Physical Examination</a></li>
                </ul>
            </div>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Visits Logged</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-journal-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> All encounters</span>
                    <span class="trend-compare">cumulative total</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Visits Today</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-clock"></i> Today</span>
                    <span class="trend-compare">{{ date('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Student Visits</span>
                    <span class="kpi-icon-box cyan">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['students']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Enrolled</span>
                    <span class="trend-compare">students</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Employee Visits</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-briefcase"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Faculty / Staff</span>
                    <span class="trend-compare">university personnel</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="filter-form" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search visit #, patient, complaint...">
                </div>
            </div>

            <div class="col-md-2">
                <select name="visit_type" id="filter-type" class="form-select">
                    <option value="">All Service Types</option>
                    @foreach($types as $tKey => $tData)
                        <option value="{{ $tKey }}">{{ $tData['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="patient_classification" id="filter-classification" class="form-select">
                    <option value="">All Classifications</option>
                    <option value="student">Students</option>
                    <option value="employee">Employees / Staff</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="college_id" id="filter-college" class="form-select">
                    <option value="">All Colleges</option>
                    @foreach($colleges as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From date">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                <button type="reset" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="visits-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 110px;">ACTIONS</th>
                            <th>VISIT NUMBER</th>
                            <th>PATIENT &amp; CLASSIFICATION</th>
                            <th>SERVICE TYPE</th>
                            <th>COMPLAINT / SUMMARY</th>
                            <th>VITAL SIGNS</th>
                            <th>ATTENDING CLINICIAN</th>
                            <th>STATUS &amp; DISPOSITION</th>
                            <th>DATE &amp; TIME</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @pushOnce('styles', 'datatables-styles')
        <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css') }}">
    @endPushOnce

    @pushOnce('scripts', 'datatables-scripts')
        <script src="{{ asset('assets/plugins/datatables/dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#visits-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('clinic-visits.index') }}",
                        data: function (d) {
                            d.visit_type = $('#filter-type').val();
                            d.patient_classification = $('#filter-classification').val();
                            d.college_id = $('#filter-college').val();
                            d.date_from = $('input[name="date_from"]').val();
                            const customSearch = $('input[name="search"]').val();
                            if (customSearch) {
                                d.search = customSearch;
                            }
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'visit_card', name: 'visit_number' },
                        { data: 'patient_card', name: 'patient.first_name', orderable: false },
                        { data: 'type_badge', name: 'visit_type' },
                        { data: 'summary_text', name: 'chief_complaint' },
                        { data: 'vitals_summary', name: 'systolic', orderable: false },
                        { data: 'attending', name: 'clinician.name', orderable: false },
                        { data: 'disposition_badge', name: 'status', orderable: false },
                        { data: 'visit_date_formatted', name: 'visit_date', orderable: true }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search visit logs...",
                        emptyTable: "No clinic visit records found",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...'
                    }
                });

                $('#filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-type, #filter-classification, #filter-college').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

