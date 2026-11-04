<x-layouts.app title="Expiration Monitoring">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Expiration Monitoring</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Expiration Monitoring &amp; Disposal</h1>
            <p class="dashboard-subheading mb-0">Track shelf life, expiry alerts (critical &lt;30d, warning &lt;90d), and safely write off expired medicine batches.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('inventory.reports') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Inventory Reports
            </a>
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Medicine Masterlist
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border border-danger h-100">
                <div class="card-body">
                    <span class="text-danger small fw-medium text-uppercase">Already Expired</span>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ number_format($expiredCount) }}</div>
                    <div class="small text-muted mt-1">Batches requiring safe medical disposal</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border border-warning h-100">
                <div class="card-body">
                    <span class="text-warning small fw-medium text-uppercase">Critical (&le; 30 Days)</span>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ number_format($criticalCount) }}</div>
                    <div class="small text-muted mt-1">High risk of expiring this month</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-primary small fw-medium text-uppercase">Warning (&le; 90 Days)</span>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ number_format($warningCount) }}</div>
                    <div class="small text-muted mt-1">Monitor for upcoming dispensing prioritization</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-success small fw-medium text-uppercase">Good Shelf Life (&gt; 90 Days)</span>
                    <div class="fs-2 fw-bold text-success mt-1">{{ number_format($goodCount) }}</div>
                    <div class="small text-muted mt-1">Adequate expiration window</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-2">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'all_risk' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'all_risk']) }}">
                        All at Risk (&le; 90d &amp; Expired)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'expired' ? 'active text-white' : 'text-danger' }}" href="{{ route('inventory.expirations', ['status' => 'expired']) }}">
                        Expired Only ({{ $expiredCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'critical' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'critical']) }}">
                        Critical &le; 30d ({{ $criticalCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'warning' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'warning']) }}">
                        Warning 31-90d ({{ $warningCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'all']) }}">
                        All Active Batches
                    </a>
                </li>
            </ul>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Medicine</th>
                        <th>Category</th>
                        <th>Batch / Lot #</th>
                        <th>Expiration Date</th>
                        <th>Risk Assessment</th>
                        <th class="text-end">Remaining Qty</th>
                        <th>Supplier</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td class="ps-3">
                                <div>
                                    <a href="{{ route('medicines.show', $batch->medicine) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $batch->medicine->generic_name }}
                                        @if($batch->medicine->brand_name)
                                            <span class="text-primary fw-medium">({{ $batch->medicine->brand_name }})</span>
                                        @endif
                                    </a>
                                </div>
                                <small class="text-muted">{{ $batch->medicine->code }} • {{ $batch->medicine->dosage_form }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-blue">{{ $batch->medicine->category?->name ?? 'N/A' }}</span>
                            </td>
                            <td class="font-monospace fw-semibold">{{ $batch->batch_number }}</td>
                            <td class="fw-medium">
                                {{ $batch->expiration_date->format('M d, Y') }}
                            </td>
                            <td>
                                @if($batch->is_expired)
                                    <span class="badge badge-soft-red"><i class="bi bi-exclamation-octagon me-1"></i>EXPIRED ({{ abs($batch->days_until_expiration) }}d ago)</span>
                                @elseif($batch->days_until_expiration <= 30)
                                    <span class="badge badge-soft-red"><i class="bi bi-exclamation-triangle me-1"></i>{{ $batch->days_until_expiration }} days left (Critical)</span>
                                @elseif($batch->days_until_expiration <= 90)
                                    <span class="badge badge-soft-amber"><i class="bi bi-hourglass-split me-1"></i>{{ $batch->days_until_expiration }} days left</span>
                                @else
                                    <span class="badge badge-soft-green"><i class="bi bi-shield-check me-1"></i>Good ({{ $batch->days_until_expiration }} days)</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $batch->is_expired ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($batch->quantity_remaining) }}
                                </span>
                                <small class="text-muted">{{ $batch->medicine->unit }}s</small>
                            </td>
                            <td class="text-muted small">{{ $batch->supplier ?? 'N/A' }}</td>
                            <td class="text-end pe-3">
                                @can('inventory.stock-out')
                                    @if($batch->is_expired)
                                        <form method="POST" action="{{ route('inventory.batches.dispose', $batch) }}" class="d-inline" onsubmit="return confirm('Confirm safe medical disposal and write-off of remaining {{ $batch->quantity_remaining }} units?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger py-1 px-2">
                                                <i class="bi bi-trash3 me-1"></i> Dispose Batch
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('inventory.stock-out', ['medicine_id' => $batch->medicine_id, 'batch_id' => $batch->id]) }}" class="btn btn-sm btn-light border py-1 px-2 text-danger">
                                            <i class="bi bi-box-arrow-up me-1"></i> Deduct
                                        </a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
                                No batches found matching the selected expiration filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>

