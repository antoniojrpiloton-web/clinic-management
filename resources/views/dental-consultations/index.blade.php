<x-layouts.app title="Dental Consultations">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Dental Consultations</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Dental Consultations</h1>
            <p class="dashboard-subheading mb-0">Oral health examinations, dental restorations, tooth extractions, and oral prophylaxis.</p>
        </div>

        @can('dental-consultations.create')
            <a href="{{ route('dental-consultations.create') }}" class="btn btn-primary px-3 py-2">
                <i class="bi bi-plus-circle me-1"></i> New Dental Consultation
            </a>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Dental Visits</span>
                    <span class="kpi-icon-box cyan">
                        <i class="bi bi-emoji-smile"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Cumulative</span>
                    <span class="trend-compare">dental registry</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Consulted Today</span>
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
                    <span class="kpi-label">Students Served</span>
                    <span class="kpi-icon-box blue">
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
                    <span class="kpi-label">Employees Served</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-briefcase"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">University</span>
                    <span class="trend-compare">personnel</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="filter-form" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search visit #, patient, diagnosis...">
                </div>
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

            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To date">
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
                <table id="operations-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 110px;">ACTIONS</th>
                            <th>VISIT NUMBER</th>
                            <th>PATIENT &amp; CLASSIFICATION</th>
                            <th>DIAGNOSIS &amp; CHIEF COMPLAINT</th>
                            <th>VITAL SIGNS</th>
                            <th>ATTENDING DENTIST</th>
                            <th>DISPOSITION</th>
                            <th>DATE &amp; TIME</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="8" :rows="8" />
                    </tbody>
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
                const table = $('#operations-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[7, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('dental-consultations.index') }}",
                        data: function (d) {
                            d.patient_classification = $('#filter-classification').val();
                            d.college_id = $('#filter-college').val();
                            d.date_from = $('input[name="date_from"]').val();
                            d.date_to = $('input[name="date_to"]').val();
                            const customSearch = $('input[name="search"]').val();
                            if (customSearch) {
                                d.search = customSearch;
                            }
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'visit_card', name: 'visit_number', searchable: false },
                        { data: 'patient_card', name: 'patient_id', orderable: false, searchable: false },
                        { data: 'summary_text', name: 'chief_complaint', searchable: false },
                        { data: 'vitals_summary', name: 'systolic', orderable: false, searchable: false },
                        { data: 'attending', name: 'clinician_id', orderable: false, searchable: false },
                        { data: 'disposition_badge', name: 'status', orderable: false, searchable: false },
                        { data: 'visit_date_formatted', name: 'visit_date', orderable: true, searchable: false }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search dental records...",
                        emptyTable: "No dental consultation records found",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...'
                    }
                });

                let searchTimer;
                $('input[name="search"]').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-classification, #filter-college, input[name="date_from"], input[name="date_to"]').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

