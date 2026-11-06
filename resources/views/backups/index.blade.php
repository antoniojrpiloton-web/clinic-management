<x-layouts.app title="Database Management & Backups">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Database Management</span>
        <span>&gt;</span>
        <span>Backups</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Database &amp; Backup Management</h1>
            <p class="dashboard-subheading mb-0">Monitor database storage metrics, trigger snapshot backups, and restore archive files.</p>
        </div>

        @can('backups.create')
            <form method="POST" action="{{ route('backups.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-loading-text="Queuing…">
                    <i class="bi bi-cloud-arrow-down"></i> Create Snapshot Backup
                </button>
            </form>
        @endcan
    </div>

    <div class="row g-3 mb-4" id="database-info">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Database Name</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-database"></i>
                    </span>
                </div>
                <div class="kpi-value" style="font-size: 1.35rem;">{{ $dbStats['name'] }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-hdd"></i> {{ $dbStats['host'] }}:{{ $dbStats['port'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Estimated Storage</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-pie-chart"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($dbStats['total_size'] / (1024 * 1024), 2) }} MB</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Data &amp; Indexes</span>
                    <span class="trend-compare">compressed</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Tables</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-table"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $dbStats['table_count'] }} Tables</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Engine</span>
                    <span class="trend-compare">InnoDB</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Backups</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($backupStats['total']) }} Snapshots</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Archives</span>
                    <span class="trend-compare">{{ number_format($backupStats['completed']) }} completed</span>
                </div>
            </div>
        </div>
    </div>

    @if (! $queueIsSync && $hasPending)
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-1"></i> A backup task is currently queued in the background.
        </div>
    @endif

    <div class="card border" data-has-pending="{{ (int) $hasPending }}">
        <div class="card-header border-bottom bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">Backup History Registry</span>
            <span class="text-muted small">Storage target: <code>storage/app/backups/</code></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="backups-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>ARCHIVE FILENAME</th>
                            <th>TRIGGER TYPE</th>
                            <th>FILE SIZE</th>
                            <th>STATUS</th>
                            <th>OPERATOR</th>
                            <th>TIMESTAMP</th>
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

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;
                if ($ && $.fn.DataTable) {
                    $('#backups-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('backups.index') }}',
                        pageLength: 25,
                        order: [[5, 'desc']],
                        columns: [
                            { data: 'filename_col', name: 'filename' },
                            { data: 'type_col', name: 'type' },
                            { data: 'size_col', name: 'size', searchable: false },
                            { data: 'status_col', name: 'status' },
                            { data: 'operator_col', name: 'creator.name', orderable: false },
                            { data: 'timestamp_col', name: 'created_at', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }
            });
        </script>
    @endpush

    @pushOnce('scripts', 'backups-scripts')
        @vite(['resources/js/pages/backups.js'])
    @endPushOnce
</x-layouts.app>
