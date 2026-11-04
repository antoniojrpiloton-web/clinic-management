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
                <div class="kpi-value">{{ $backups->total() }} Snapshots</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Archives</span>
                    <span class="trend-compare">{{ $backups->where('status', \App\Models\Backup::STATUS_COMPLETED)->count() }} completed</span>
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="font-monospace fw-semibold text-dark">{{ $backup->filename ?? 'Processing snapshot…' }}</td>
                            <td class="text-capitalize">{{ $backup->type }}</td>
                            <td>{{ $backup->isCompleted() ? $backup->humanSize() : '—' }}</td>
                            <td>
                                <span class="status-badge {{ $backup->status === 'completed' ? 'completed' : ($backup->status === 'pending' ? 'processing' : 'pending') }}">
                                    {{ $backup->status }}
                                </span>
                                @if ($backup->error)
                                    <i class="bi bi-info-circle text-danger ms-1" data-bs-toggle="tooltip" title="{{ $backup->error }}"></i>
                                @endif
                            </td>
                            <td>{{ $backup->creator?->name ?? 'System' }}</td>
                            <td class="text-muted small">{{ $backup->created_at->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if ($backup->isCompleted())
                                        @can('backups.download')
                                            <a href="{{ route('backups.download', $backup) }}" class="btn btn-sm btn-light border p-1" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endcan
                                    @endif

                                    @can('backups.delete')
                                        <form method="POST" action="{{ route('backups.destroy', $backup) }}" data-confirm="Backup {{ $backup->filename ?? '#'.$backup->id }} will be permanently removed.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger p-1" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No backups have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($backups->hasPages())
            <div class="card-footer bg-transparent">
                {{ $backups->links() }}
            </div>
        @endif
    </div>

    @pushOnce('scripts', 'backups-scripts')
        @vite(['resources/js/pages/backups.js'])
    @endPushOnce
</x-layouts.app>
