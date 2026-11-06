<x-layouts.app title="Courses and Programs">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Courses and Programs</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Courses &amp; Degree Programs</h1>
            <p class="dashboard-subheading mb-0">Configure academic curricula, undergraduate tracks, and department affiliations.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('colleges.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-diagram-3"></i> View Colleges
            </a>
            @can('programs.create')
                <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-program-modal">
                    <i class="bi bi-plus-lg"></i> New Program
                </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-book"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-mortarboard"></i> Curricula</span>
                    <span class="trend-compare">all degrees</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Degree Tracks</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-patch-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Enrolling</span>
                    <span class="trend-compare">for patients/students</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Affiliated Colleges</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['colleges']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Departments</span>
                    <span class="trend-compare">mother units</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Average Duration</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <div class="kpi-value">4 Years</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Standard</span>
                    <span class="trend-compare">bachelor curriculum</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <select id="filter-college-id" name="college_id" class="form-select">
                    <option value="">All Colleges &amp; Departments</option>
                    @foreach ($colleges as $c)
                        <option value="{{ $c->id }}" @selected($collegeFilter == $c->id)>
                            {{ $c->code }} &mdash; {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="button" id="btn-reset-programs" class="btn btn-outline-secondary">Reset Filter</button>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-body">
            <div class="table-responsive">
                <table id="programs-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>PROGRAM / DEGREE NAME</th>
                            <th>COLLEGE / DEPARTMENT</th>
                            <th>DEGREE LEVEL</th>
                            <th>DURATION</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="8" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-program-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-program-form" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Academic Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College / Department</label>
                            <select name="college_id" id="edit_prog_college_id" class="form-select" required>
                                @foreach ($colleges as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} &mdash; {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Code</label>
                            <input type="text" name="code" id="edit_prog_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Title</label>
                            <input type="text" name="name" id="edit_prog_name" class="form-control" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Degree Level</label>
                                <select name="degree_level" id="edit_prog_level" class="form-select" required>
                                    <option value="Undergraduate">Undergraduate</option>
                                    <option value="Postgraduate">Postgraduate</option>
                                    <option value="Diploma / Certificate">Diploma / Certificate</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Years of Study</label>
                                <input type="number" name="years" id="edit_prog_years" min="1" max="10" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="edit_prog_active" class="form-check-input">
                            <label class="form-check-label" for="edit_prog_active">Active status</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create-program-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('programs.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New Degree Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College / Department</label>
                            <select name="college_id" class="form-select" required>
                                <option value="">Select College</option>
                                @foreach ($colleges as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} &mdash; {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Code</label>
                            <input type="text" name="code" placeholder="e.g. BSIT" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Title</label>
                            <input type="text" name="name" placeholder="e.g. Bachelor of Science in Information Technology" class="form-control" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Degree Level</label>
                                <select name="degree_level" class="form-select" required>
                                    <option value="Undergraduate">Undergraduate</option>
                                    <option value="Postgraduate">Postgraduate</option>
                                    <option value="Diploma / Certificate">Diploma / Certificate</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Duration (Years)</label>
                                <input type="number" name="years" min="1" max="10" value="4" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="create_prog_active" class="form-check-input" checked>
                            <label class="form-check-label" for="create_prog_active">Active status</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;
                const collegeSelect = document.getElementById('filter-college-id');
                let dt = null;

                if ($ && $.fn.DataTable) {
                    dt = $('#programs-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route('programs.index') }}',
                            data: function (d) {
                                d.college_id = collegeSelect ? collegeSelect.value : '';
                            }
                        },
                        pageLength: 25,
                        order: [[0, 'asc']],
                        columns: [
                            { data: 'code_col', name: 'code' },
                            { data: 'name_col', name: 'name' },
                            { data: 'college_col', name: 'college.code', orderable: false },
                            { data: 'level_col', name: 'degree_level' },
                            { data: 'years_col', name: 'years', searchable: false },
                            { data: 'status_col', name: 'is_active', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }

                if (collegeSelect) {
                    collegeSelect.addEventListener('change', function () {
                        if (dt) dt.ajax.reload();
                    });
                }

                const resetBtn = document.getElementById('btn-reset-programs');
                if (resetBtn && collegeSelect) {
                    resetBtn.addEventListener('click', function () {
                        collegeSelect.value = '';
                        if (dt) dt.ajax.reload();
                    });
                }

                const editModal = document.getElementById('edit-program-modal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const btn = event.relatedTarget;
                        document.getElementById('edit-program-form').action = btn.getAttribute('data-update-url');
                        document.getElementById('edit_prog_college_id').value = btn.getAttribute('data-college-id') || '';
                        document.getElementById('edit_prog_code').value = btn.getAttribute('data-code') || '';
                        document.getElementById('edit_prog_name').value = btn.getAttribute('data-name') || '';
                        document.getElementById('edit_prog_level').value = btn.getAttribute('data-level') || 'Undergraduate';
                        document.getElementById('edit_prog_years').value = btn.getAttribute('data-years') || '4';
                        document.getElementById('edit_prog_active').checked = btn.getAttribute('data-active') === '1';
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

