<x-layouts.app title="Healthcare Providers & Staff">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinicians</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Healthcare Providers &amp; Clinicians</h1>
            <p class="dashboard-subheading mb-0">Manage university physicians, nurses, dentists, and examining clinic staff.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @can('clinicians.create')
                <a href="{{ route('clinicians.create') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add Healthcare Provider
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Providers</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $totalCount }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-check2"></i> Registered
                    </span>
                    <span class="trend-compare">all clinic staff</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Physicians / Doctors</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-heart-pulse"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $doctorsCount }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Medical</span>
                    <span class="trend-compare">school physicians</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Registered Nurses</span>
                    <span class="kpi-icon-box cyan">
                        <i class="bi bi-bandaid"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $nursesCount }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Nursing</span>
                    <span class="trend-compare">school nurses</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Dentists &amp; Others</span>
                    <span class="kpi-icon-box purple">
                        <i class="bi bi-hospital"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $dentistsCount }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Specialists</span>
                    <span class="trend-compare">dental &amp; allied</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="clinician-search" class="form-control border-start-0" placeholder="Search provider by name, license #, specialization...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filter-type" class="form-select form-select-sm">
                        <option value="">All Provider Roles</option>
                        <option value="Doctor">Doctors / Physicians</option>
                        <option value="Nurse">Nurses</option>
                        <option value="Dentist">Dentists</option>
                        <option value="Other">Other Healthcare Staff</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter-status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" selected>Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" id="btn-reset-filters" class="btn btn-sm btn-light border w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="clinicians-table" class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th>Healthcare Provider</th>
                            <th>PRC License / PTR</th>
                            <th>Contact Details</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="5" :rows="6" />
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

    @pushOnce('scripts', 'clinicians-table-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.jQuery || !window.jQuery.fn.DataTable) return;

                const table = window.jQuery('#clinicians-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: @json(route('clinicians.index')),
                        data: function (d) {
                            d.search.value = document.getElementById('clinician-search').value;
                            d.type = document.getElementById('filter-type').value;
                            d.status = document.getElementById('filter-status').value;
                        }
                    },
                    columns: [
                        { data: 'provider_card', name: 'name' },
                        { data: 'license_badge', name: 'license_number' },
                        { data: 'contact_info', name: 'email', orderable: false },
                        { data: 'status_badge', name: 'is_active', className: 'text-center' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    order: [[0, 'asc']],
                    pageLength: 10,
                    dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center p-3 border-top"ip>',
                    language: {
                        emptyTable: 'No healthcare providers found in the registry.',
                        zeroRecords: 'No matching healthcare providers found.'
                    }
                });

                document.getElementById('clinician-search').addEventListener('input', () => table.draw());
                document.getElementById('filter-type').addEventListener('change', () => table.draw());
                document.getElementById('filter-status').addEventListener('change', () => table.draw());
                document.getElementById('btn-reset-filters').addEventListener('click', () => {
                    document.getElementById('clinician-search').value = '';
                    document.getElementById('filter-type').value = '';
                    document.getElementById('filter-status').value = 'active';
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

