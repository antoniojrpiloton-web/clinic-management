@php
    $activeRate = $stats['total_users'] > 0
        ? (int) round(($stats['active_users'] / $stats['total_users']) * 100)
        : 100;
@endphp
<x-layouts.app title="Executive Dashboard">
    {{-- 1. Executive Deep Forest Green Intelligence Banner --}}
    <div class="executive-hero-banner mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
            <div>
                <span class="hero-badge">
                    <i class="bi bi-shield-check"></i> Clinic Intelligence &amp; Record Aggregation
                </span>
                <h2>Executive Clinic Intelligence Matrix</h2>
                <p>
                    Real-time oversight for institutional clinical data aggregation, patient &amp; staff records, RBAC governance, and medical report generation.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @can('users.create')
                    <a href="{{ route('users.create') }}" class="btn btn-emerald btn-sm rounded-pill px-3 py-2 d-flex align-items-center gap-1">
                        <i class="bi bi-plus-circle"></i> New Personnel
                    </a>
                @endcan
                @can('backups.create')
                    <form method="POST" action="{{ route('backups.store') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-forest btn-sm rounded-pill px-3 py-2 d-flex align-items-center gap-1">
                            <i class="bi bi-cloud-arrow-down"></i> Sync Database
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Scope & Perspective Pills inside Hero --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top border-success border-opacity-25 gap-3" style="font-size: 0.78rem;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-white-50 text-uppercase fw-bold">Time Scope:</span>
                <span class="badge rounded-pill bg-success px-3 py-1">All-Time</span>
                <span class="badge rounded-pill bg-white text-dark px-2 py-1">FY {{ now()->year }}</span>
                <span class="badge rounded-pill bg-white text-dark px-2 py-1">FY {{ now()->subYear()->year }}</span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-white-50 text-uppercase fw-bold">Perspective:</span>
                <span class="badge rounded-pill bg-success px-3 py-1">Medical Director</span>
                <span class="badge rounded-pill bg-white text-dark px-2 py-1">Head Physician</span>
                <span class="badge rounded-pill bg-white text-dark px-2 py-1">Nurse Supervisor</span>
            </div>
        </div>
    </div>

    {{-- 2. Quick Filter Bar with Counts --}}
    <div class="quick-filters-bar mb-4 justify-content-between">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 0.72rem;">Quick Filter:</span>
            <a href="{{ route('users.index') }}" class="filter-pill active">
                <span>All Users</span>
                <span class="badge rounded-pill bg-white text-dark ms-1">{{ $stats['total_users'] }}</span>
            </a>
            <a href="{{ route('users.index', ['status' => 'active']) }}" class="filter-pill" style="background: #e2f1e9; color: #005a36;">
                <i class="bi bi-check2-circle"></i> Active Personnel
                <span class="badge rounded-pill bg-success ms-1">{{ $stats['active_users'] }}</span>
            </a>
            <a href="{{ route('roles.index') }}" class="filter-pill" style="background: #dbeafe; color: #1d4ed8;">
                <i class="bi bi-shield-check"></i> System Roles
                <span class="badge rounded-pill bg-primary ms-1">{{ $stats['roles'] }}</span>
            </a>
            <a href="{{ route('backups.index') }}" class="filter-pill" style="background: #fef3c7; color: #b45309;">
                <i class="bi bi-database-check"></i> Backups
                <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $stats['backups'] }}</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-1 small text-success fw-semibold">
            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Live Aggregation Engine Active
        </div>
    </div>

    {{-- 3. Executive KPI Tiles with Top Color Line --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-tile border-accent-green h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tile-title">Personnel &amp; Users</div>
                        <div class="tile-value">{{ number_format($stats['total_users']) }}</div>
                        <div class="small text-muted mt-2">
                            Active Rate: <span class="text-success fw-bold">{{ $activeRate }}%</span>
                        </div>
                    </div>
                    <span class="tile-icon" style="background: #005a36;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-tile border-accent-blue h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tile-title">Active Clinicians</div>
                        <div class="tile-value">{{ number_format($stats['active_users']) }}</div>
                        <div class="small text-muted mt-2">
                            Verified Status: <span class="text-primary fw-bold">Active</span>
                        </div>
                    </div>
                    <span class="tile-icon" style="background: #2563eb;">
                        <i class="bi bi-person-check-fill"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-tile border-accent-amber h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tile-title">Configured Roles</div>
                        <div class="tile-value">{{ number_format($stats['roles']) }}</div>
                        <div class="small text-muted mt-2">
                            Access Matrix: <span class="text-warning fw-bold">Enforced</span>
                        </div>
                    </div>
                    <span class="tile-icon" style="background: #d97706;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-tile border-accent-purple h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tile-title">Completed Backups</div>
                        <div class="tile-value">{{ number_format($stats['backups']) }}</div>
                        <div class="small text-muted mt-2">
                            Last: <span class="text-purple fw-bold">{{ $latestBackup ? $latestBackup->created_at->diffForHumans(short: true) : 'None' }}</span>
                        </div>
                    </div>
                    <span class="tile-icon" style="background: #9333ea;">
                        <i class="bi bi-database-fill-check"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Analytics Trajectory Chart & Role Distribution --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success p-1"><i class="bi bi-activity text-white"></i></span>
                        <span class="fw-bold">Sign-In Trajectory &amp; System Utilization</span>
                    </div>
                    <span class="badge badge-pill badge-forest">14-Day Trajectory</span>
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="login-trend-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold">Role Distribution</div>
                    @can('roles.view')
                        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-forest py-0 px-2" style="font-size: 0.75rem;">Matrix</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="mb-3" style="height: 180px;">
                        <canvas id="roles-chart"></canvas>
                    </div>
                    <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                        @foreach (array_slice($charts['roles'], 0, 4) as $roleItem)
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-dark"><i class="bi bi-person-fill text-muted me-1"></i>{{ $roleItem['label'] }}</span>
                                <span class="badge badge-pill badge-gray">{{ $roleItem['value'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Recent Audit Activity Registry --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-bold">Recent Clinical Audit Registry</div>
            @can('audit-logs.view')
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-forest" style="font-size: 0.8rem;">
                    View All Entries <i class="bi bi-arrow-right"></i>
                </a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User / Operator</th>
                        <th>Event</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentActivity as $log)
                        <tr>
                            <td class="text-muted small text-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->user?->name ?? 'System' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-forest">{{ str($log->event)->replace('_', ' ')->title() }}</span>
                            </td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No audit records logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script id="dashboard-chart-data" type="application/json">@json($charts)</script>

    @pushOnce('scripts', 'chartjs-scripts')
        <script src="{{ asset('assets/plugins/chartjs/chart.umd.min.js') }}"></script>
        @vite(['resources/js/pages/dashboard.js'])
    @endPushOnce
</x-layouts.app>
