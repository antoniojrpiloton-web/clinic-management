<x-layouts.app title="Student Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Students</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Student Registry</h1>
            <p class="dashboard-subheading mb-0">Operational student cohort directory with academic programs and clinical records.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.import') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Students
            </a>
            @can('students.create')
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Register Student
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Registered Students</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($totalStudents) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Enrolled</span>
                    <span class="trend-compare">all years</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Colleges Represented</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($colleges->count()) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Faculties</span>
                    <span class="trend-compare">academic units</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Degree Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-book"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($programs->count()) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Curricula</span>
                    <span class="trend-compare">active tracks</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Health Files</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-file-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($activeCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active</span>
                    <span class="trend-compare">verified</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="students-filter-form" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="filter-search" name="search" class="form-control border-start-0" placeholder="Search by student ID, name, email...">
                </div>
            </div>

            <div class="col-md-3">
                <select name="college_id" id="filter-college" class="form-select">
                    <option value="">All Colleges</option>
                    @foreach ($colleges as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} &mdash; {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="year_level" id="filter-year-level" class="form-select">
                    <option value="">All Year Levels</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                    <option value="5th Year">5th Year</option>
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
                <table id="students-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ACTION</th>
                            <th>STUDENT ID</th>
                            <th>STUDENT NAME</th>
                            <th>COLLEGE &amp; PROGRAM</th>
                            <th>YEAR &amp; SECTION</th>
                            <th>CONTACT</th>
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

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#students-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ (int) ($appSettings['table_page_length'] ?? 25) }},
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'asc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('students.index') }}",
                        data: function (d) {
                            d.college_id = $('#filter-college').val();
                            d.year_level = $('#filter-year-level').val();
                            d.custom_search = $('#filter-search').val();
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'student_id_col', name: 'school_id_number' },
                        { data: 'student_name_col', name: 'last_name' },
                        { data: 'college_program_col', name: 'college_id' },
                        { data: 'year_section_col', name: 'year_level' },
                        { data: 'contact_col', name: 'phone', orderable: false, searchable: false },
                        { data: 'status_col', name: 'status' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick filter...",
                        emptyTable: "No student records found.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading students...'
                    }
                });

                let searchTimer;
                $('#filter-search').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#students-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#students-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-college, #filter-year-level').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>

