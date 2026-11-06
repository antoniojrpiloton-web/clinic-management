<x-layouts.app title="Patient Masterlist">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Masterlist</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Patient Masterlist Registry</h1>
            <p class="dashboard-subheading mb-0">Unified master records for university students, faculty, and administrative personnel.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @can('patients.import')
                <a href="{{ route('patients.import') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import Excel/CSV
                </a>
            @endcan
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-person-plus-fill"></i> Add New Patient
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Patients</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check-circle"></i> Master records</span>
                    <span class="trend-compare">all profiles</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Enrolled Students</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['students']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Students</span>
                    <span class="trend-compare">enrolled cohort</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">University Personnel</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Employees</span>
                    <span class="trend-compare">faculty &amp; staff</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Health Files</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-file-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active</span>
                    <span class="trend-compare">verified</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="patients-filter-form" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="filter-search" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Search by name, ID number, email, or phone...">
                </div>
            </div>

            <div class="col-md-3">
                <select name="type" id="filter-type" class="form-select">
                    <option value="">All Patient Classifications</option>
                    <option value="student" @selected($type === 'student')>Students</option>
                    <option value="employee" @selected($type === 'employee')>Employees (Faculty &amp; Staff)</option>
                    <option value="other" @selected($type === 'other')>Dependents / Guests</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="college_id" id="filter-college" class="form-select">
                    <option value="">All Colleges</option>
                    @foreach ($colleges as $col)
                        <option value="{{ $col->id }}" @selected($collegeId == $col->id)>{{ $col->code }} - {{ $col->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" id="filter-status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                <button type="reset" class="btn btn-outline-secondary" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="patients-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th>ACTION</th>
                            <th>PATIENT #</th>
                            <th>PATIENT NAME</th>
                            <th>CLASSIFICATION</th>
                            <th>ID NUMBER</th>
                            <th>COLLEGE / UNIT</th>
                            <th>GENDER &amp; AGE</th>
                            <th>STATUS</th>
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
    @endPushOnce

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#patients-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('patients.index') }}",
                        data: function (d) {
                            d.type = $('#filter-type').val();
                            d.college_id = $('#filter-college').val();
                            d.status = $('#filter-status').val();
                            d.custom_search = $('#filter-search').val();
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'patient_number_col', name: 'patient_number', searchable: false },
                        { data: 'patient_name_col', name: 'last_name', searchable: false },
                        { data: 'classification_col', name: 'type', orderable: false, searchable: false },
                        { data: 'identifier_col', name: 'school_id_number', searchable: false },
                        { data: 'college_unit_col', name: 'college_id', orderable: false, searchable: false },
                        { data: 'gender_age_col', name: 'gender', orderable: false, searchable: false },
                        { data: 'status_col', name: 'status', searchable: false }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick table filter...",
                        emptyTable: "No patient records found in the masterlist.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading patients...'
                    }
                });

                let searchTimer;
                $('#filter-search').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#patients-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#patients-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-type, #filter-college, #filter-status').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>
