<x-layouts.app title="Scheduled Physical Examinations">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Scheduled Physical Exams</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Scheduled Physical Examinations</h1>
            <p class="dashboard-subheading mb-0">Cohort-based institutional health screening, annual physical exams, college schedules, and roster management.</p>
        </div>

        @can('physical-examinations.create')
            <a href="{{ route('physical-exam-schedules.create') }}" class="btn btn-primary px-3 py-2">
                <i class="bi bi-calendar-plus me-1"></i> Create PE Schedule
            </a>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Schedules</span>
                    <span class="kpi-icon-box purple">
                        <i class="bi bi-calendar3"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Institutional</span>
                    <span class="trend-compare">cohort campaigns</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Upcoming Campaigns</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-calendar-event"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['upcoming']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-clock"></i> Upcoming</span>
                    <span class="trend-compare">scheduled dates</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Ongoing Today</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-play-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['ongoing']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active screening</span>
                    <span class="trend-compare">in progress</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Completed Campaigns</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-check-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['completed']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Finalized</span>
                    <span class="trend-compare">examined cohorts</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="filter-form" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" id="filter-status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $stKey => $stData)
                        <option value="{{ $stKey }}">{{ $stData['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="target_classification" id="filter-classification" class="form-select">
                    <option value="">All Target Groups</option>
                    <option value="student">Students</option>
                    <option value="employee">Employees / Staff</option>
                </select>
            </div>

            <div class="col-md-3">
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

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill" title="Apply filter"><i class="bi bi-funnel me-1"></i> Filter</button>
                <button type="reset" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="schedules-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th>CAMPAIGN / TITLE</th>
                            <th>TARGET COHORT</th>
                            <th>SCHEDULE &amp; VENUE</th>
                            <th>EXAMINATION PROGRESS</th>
                            <th>STATUS</th>
                            <th class="text-end pe-3" style="width: 110px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="6" :rows="8" />
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
                const table = $('#schedules-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[2, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('physical-exam-schedules.index') }}",
                        data: function (d) {
                            d.status = $('#filter-status').val();
                            d.target_classification = $('#filter-classification').val();
                            d.college_id = $('#filter-college').val();
                            d.date_from = $('input[name="date_from"]').val();
                        }
                    },
                    columns: [
                        { data: 'schedule_card', name: 'title' },
                        { data: 'target_group', name: 'target_classification', orderable: false, searchable: false },
                        { data: 'schedule_timing', name: 'scheduled_date', searchable: false },
                        { data: 'progress_summary', name: 'total_attendees', orderable: false, searchable: false },
                        { data: 'status_badge', name: 'status', searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search schedules...",
                        emptyTable: "No physical exam schedules found",
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

                $('#filter-status, #filter-classification, #filter-college, input[name="date_from"]').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

