<x-layouts.app title="User Management">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Users</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">User Management</h1>
            <p class="dashboard-subheading mb-0">Manage system users, credentials, and access roles.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-shield-check"></i> Roles
            </a>
            @can('users.create')
                <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> New User
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Users</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\User::count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-arrow-up-short"></i> Registered
                    </span>
                    <span class="trend-compare">system wide</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Users</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-patch-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\User::active()->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-arrow-up-short"></i> Active
                    </span>
                    <span class="trend-compare">can sign in</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Super Admins</span>
                    <span class="kpi-icon-box red">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\User::role(\App\Models\User::SUPER_ADMIN_ROLE)->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-shield-check"></i> Root
                    </span>
                    <span class="trend-compare">privileged</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Configured Roles</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \Spatie\Permission\Models\Role::count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-lock"></i> Enforced
                    </span>
                    <span class="trend-compare">RBAC active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="users-filter-form" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search[value]" class="form-control border-start-0" placeholder="Search by name, email, or phone...">
                </div>
            </div>

            <div class="col-md-3">
                <select id="filter-role" name="role" class="form-select" data-select2 data-ajax-url="{{ route('select2.roles') }}" data-allow-clear="true" data-placeholder="All Roles">
                    <option value=""></option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="filter-status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="users-table" class="table table-hover align-middle mb-0 w-100" data-page-length="{{ $pageLength }}">
                    <thead>
                        <tr>
                            <th style="width: 130px;">ACTIONS</th>
                            <th>USER / PERSONNEL</th>
                            <th>PHONE NUMBER</th>
                            <th>ASSIGNED ROLES</th>
                            <th>ACCOUNT STATUS</th>
                            <th>LAST ACTIVE</th>
                            <th>REGISTERED</th>
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
                const table = $('#users-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ $pageLength }},
                    lengthMenu: [10, 25, 50, 100],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('users.index') }}",
                        data: function (d) {
                            d.role = $('#filter-role').val();
                            d.status = $('#filter-status').val();
                            const customSearch = $('input[name="search[value]"]').val();
                            if (customSearch) {
                                d.search.value = customSearch;
                            }
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'user_card', name: 'name', orderable: true },
                        { data: 'phone', name: 'phone', orderable: false },
                        { data: 'roles_badges', name: 'roles.name', orderable: false },
                        { data: 'status_badge', name: 'is_active', orderable: true },
                        { data: 'last_login_formatted', name: 'last_login_at', orderable: true },
                        { data: 'created_formatted', name: 'created_at', orderable: true }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search table...",
                        emptyTable: "No user records found",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...'
                    }
                });

                $('#users-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#users-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-role, #filter-status').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPushOnce

    @include('partials.plugins.select2')
</x-layouts.app>
