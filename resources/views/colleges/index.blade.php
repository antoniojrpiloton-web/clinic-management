<x-layouts.app title="Colleges and Departments">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Colleges and Departments</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Colleges &amp; Departments</h1>
            <p class="dashboard-subheading mb-0">Manage university academic units, faculties, and department heads.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('programs.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-book"></i> View Programs
            </a>
            @can('colleges.create')
                <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-college-modal">
                    <i class="bi bi-plus-lg"></i> New College
                </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Colleges</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_colleges']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-building"></i> Academic units</span>
                    <span class="trend-compare">all campuses</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Colleges</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-patch-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['active_colleges']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Operational</span>
                    <span class="trend-compare">accepting records</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_programs']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Curricula</span>
                    <span class="trend-compare">active degree tracks</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Undergraduate Units</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-award"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['undergrad_programs']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Degree tracks</span>
                    <span class="trend-compare">bachelor programs</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-body">
            <div class="table-responsive">
                <table id="colleges-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>COLLEGE / FACULTY NAME</th>
                            <th>DEAN / HEAD OF UNIT</th>
                            <th>DEGREE PROGRAMS</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="6" :rows="6" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-college-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-college-form" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit College</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College Code</label>
                            <input type="text" name="code" id="edit_college_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">College Name</label>
                            <input type="text" name="name" id="edit_college_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dean / Head</label>
                            <input type="text" name="dean" id="edit_college_dean" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_college_desc" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="edit_college_active" class="form-check-input">
                            <label class="form-check-label" for="edit_college_active">Active status</label>
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

    <div class="modal fade" id="create-college-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('colleges.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New College / Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College Code</label>
                            <input type="text" name="code" placeholder="e.g. CAS" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">College Name</label>
                            <input type="text" name="name" placeholder="e.g. College of Arts and Sciences" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dean / Head of Unit</label>
                            <input type="text" name="dean" placeholder="e.g. Dr. Juan Dela Cruz" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" placeholder="Brief overview of programs and academic focus..." class="form-control"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="create_college_active" class="form-check-input" checked>
                            <label class="form-check-label" for="create_college_active">Active status</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create College</button>
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
                if ($ && $.fn.DataTable) {
                    $('#colleges-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('colleges.index') }}',
                        pageLength: 25,
                        order: [[0, 'asc']],
                        columns: [
                            { data: 'code_col', name: 'code' },
                            { data: 'name_col', name: 'name' },
                            { data: 'dean_col', name: 'dean' },
                            { data: 'programs_col', name: 'programs_count', searchable: false },
                            { data: 'status_col', name: 'is_active', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }

                const editModal = document.getElementById('edit-college-modal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const btn = event.relatedTarget;
                        document.getElementById('edit-college-form').action = btn.getAttribute('data-update-url');
                        document.getElementById('edit_college_code').value = btn.getAttribute('data-code') || '';
                        document.getElementById('edit_college_name').value = btn.getAttribute('data-name') || '';
                        document.getElementById('edit_college_dean').value = btn.getAttribute('data-dean') || '';
                        document.getElementById('edit_college_desc').value = btn.getAttribute('data-description') || '';
                        document.getElementById('edit_college_active').checked = btn.getAttribute('data-active') === '1';
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

