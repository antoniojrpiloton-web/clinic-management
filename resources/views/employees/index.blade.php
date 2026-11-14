<x-layouts.app title="Employee Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Employees</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Employee Registry</h1>
            <p class="dashboard-subheading mb-0">University faculty members, administrative staff, and personnel health files.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.import') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Employees
            </a>
            @can('employees.create')
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Register Employee
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Personnel</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($totalEmployees) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-building"></i> University-wide</span>
                    <span class="trend-compare">all campuses</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Teaching Faculty</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-award"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($facultyCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Instructors</span>
                    <span class="trend-compare">professors</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Non-Teaching Staff</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-briefcase"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($staffCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Administrative</span>
                    <span class="trend-compare">support staff</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Permanent Status</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($permanentCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Tenured</span>
                    <span class="trend-compare">plantilla items</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="employees-filter-form" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="filter-search" name="search" class="form-control border-start-0" placeholder="Search by employee ID, name, position...">
                </div>
            </div>

            <div class="col-md-3">
                <select name="category" id="filter-category" class="form-select">
                    <option value="">All Categories (Faculty / Staff)</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Non-Teaching / Staff">Non-Teaching / Staff</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="classification" id="filter-classification" class="form-select">
                    <option value="">All Employment Classifications</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Job Order">Job Order</option>
                    <option value="Part-time">Part-time</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                <button type="reset" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="employees-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ACTION</th>
                            <th>EMPLOYEE ID</th>
                            <th>EMPLOYEE NAME</th>
                            <th>CATEGORY &amp; POSITION</th>
                            <th>OFFICE / COLLEGE</th>
                            <th>CLASSIFICATION</th>
                            <th>CONTACT</th>
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

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#employees-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ (int) ($appSettings['table_page_length'] ?? 25) }},
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'asc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('employees.index') }}",
                        data: function (d) {
                            d.category = $('#filter-category').val();
                            d.classification = $('#filter-classification').val();
                            d.custom_search = $('#filter-search').val();
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'employee_id_col', name: 'school_id_number' },
                        { data: 'employee_name_col', name: 'last_name' },
                        { data: 'category_position_col', name: 'category' },
                        { data: 'office_college_col', name: 'office_department' },
                        { data: 'classification_col', name: 'employment_classification' },
                        { data: 'contact_col', name: 'phone', orderable: false, searchable: false },
                        { data: 'status_col', name: 'employment_status' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick filter...",
                        emptyTable: "No employee records found.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading employees...'
                    }
                });

                let searchTimer;
                $('#filter-search').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#employees-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#employees-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-category, #filter-classification').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>

