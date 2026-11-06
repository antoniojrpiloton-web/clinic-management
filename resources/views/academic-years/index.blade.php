<x-layouts.app title="Academic Year Management">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Academic Year</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Academic Year Management</h1>
            <p class="dashboard-subheading mb-0">Configure university academic periods, active semester, and term schedules.</p>
        </div>

        @can('academic-years.create')
            <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-ay-modal">
                <i class="bi bi-plus-lg"></i> New Academic Year
            </button>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Terms</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-calendar-range"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Registered</span>
                    <span class="trend-compare">all records</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Current Active Term</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                </div>
                <div class="kpi-value" style="font-size: 1.25rem;">
                    {{ $stats['current_code'] }}
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active</span>
                    <span class="trend-compare">{{ $stats['current_semester'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">1st Semester Terms</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-1-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['first_sem']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Primary</span>
                    <span class="trend-compare">fall terms</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">2nd Semester Terms</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['second_sem']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Secondary</span>
                    <span class="trend-compare">spring terms</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-body">
            <div class="table-responsive">
                <table id="academic-years-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>ACADEMIC YEAR</th>
                            <th>SEMESTER</th>
                            <th>DURATION</th>
                            <th>CURRENT TERM</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="6" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-ay-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-ay-form" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Academic Year</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" id="edit_ay_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year Name</label>
                            <input type="text" name="name" id="edit_ay_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" id="edit_ay_semester" class="form-select" required>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer / Midyear">Summer / Midyear</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="edit_ay_start" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" id="edit_ay_end" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_ay_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_current" value="1" id="edit_ay_current" class="form-check-input">
                            <label class="form-check-label" for="edit_ay_current">Set as currently active term</label>
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

    <div class="modal fade" id="create-ay-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('academic-years.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New Academic Year</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" placeholder="e.g. AY-2027-2028-1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year Name</label>
                            <input type="text" name="name" placeholder="e.g. Academic Year 2027-2028" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select" required>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer / Midyear">Summer / Midyear</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_current" value="1" id="create_is_current" class="form-check-input">
                            <label class="form-check-label" for="create_is_current">Set as currently active term</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Term</button>
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
                    $('#academic-years-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('academic-years.index') }}',
                        pageLength: 25,
                        order: [[3, 'desc']],
                        columns: [
                            { data: 'code_col', name: 'code' },
                            { data: 'name_col', name: 'name' },
                            { data: 'semester_col', name: 'semester' },
                            { data: 'duration_col', name: 'start_date', searchable: false },
                            { data: 'current_col', name: 'is_current', searchable: false },
                            { data: 'status_col', name: 'status' },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }

                const editModal = document.getElementById('edit-ay-modal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const btn = event.relatedTarget;
                        document.getElementById('edit-ay-form').action = btn.getAttribute('data-update-url');
                        document.getElementById('edit_ay_code').value = btn.getAttribute('data-code') || '';
                        document.getElementById('edit_ay_name').value = btn.getAttribute('data-name') || '';
                        document.getElementById('edit_ay_semester').value = btn.getAttribute('data-semester') || '1st Semester';
                        document.getElementById('edit_ay_start').value = btn.getAttribute('data-start') || '';
                        document.getElementById('edit_ay_end').value = btn.getAttribute('data-end') || '';
                        document.getElementById('edit_ay_status').value = btn.getAttribute('data-status') || 'active';
                        document.getElementById('edit_ay_current').checked = btn.getAttribute('data-current') === '1';
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

