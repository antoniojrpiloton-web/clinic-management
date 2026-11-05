<x-layouts.app title="Inventory & Stock Valuation Reports">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Inventory Reports</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Pharmacy &amp; Inventory Reports</h1>
            <p class="dashboard-subheading mb-0">Total stock-on-hand valuation, replenishment reorder warnings, and shelf-life forecasts.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Medicine Masterlist
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Total Stock Valuation</span>
                    <div class="fs-2 fw-bold text-success mt-1">₱{{ number_format($totalInventoryValuation, 2) }}</div>
                    <div class="small text-muted mt-1">Based on active batch unit costs</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Total Unexpired Units</span>
                    <div class="fs-2 fw-bold text-dark mt-1">{{ number_format($totalStockUnits) }}</div>
                    <div class="small text-muted mt-1">Across {{ $totalItems }} formulary items</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border {{ $lowStockList->count() > 0 ? 'border-warning' : '' }} h-100">
                <div class="card-body">
                    <span class="text-warning small fw-medium text-uppercase">Reorder Required</span>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ $lowStockList->count() }}</div>
                    <div class="small text-muted mt-1">Medicines at or below reorder level</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border {{ $expiredBatches->count() > 0 ? 'border-danger' : '' }} h-100">
                <div class="card-body">
                    <span class="text-danger small fw-medium text-uppercase">Expired Batches</span>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ $expiredBatches->count() }}</div>
                    <div class="small text-muted mt-1">Batches needing disposal write-off</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Low Stock Replenishment Action List ({{ $lowStockList->count() }})</h2>
            </div>
            @can('inventory.stock-in')
                <a href="{{ route('inventory.stock-in') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Receive Stock
                </a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Medicine</th>
                        <th>Category</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-center">Deficit</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockList as $med)
                        @php
                            $deficit = max(0, $med->reorder_level - $med->stock_on_hand);
                        @endphp
                        <tr>
                            <td class="ps-3 font-monospace small">{{ $med->code }}</td>
                            <td>
                                <a href="{{ route('medicines.show', $med) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $med->generic_name }}
                                    @if($med->brand_name)
                                        <span class="text-primary fw-medium">({{ $med->brand_name }})</span>
                                    @endif
                                </a>
                                <small class="text-muted d-block">{{ $med->strength }} {{ $med->dosage_form }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-blue">{{ $med->category?->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold {{ $med->stock_on_hand <= 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ number_format($med->stock_on_hand) }}
                                </span>
                                <small class="text-muted">{{ $med->unit }}s</small>
                            </td>
                            <td class="text-center text-muted">
                                {{ number_format($med->reorder_level) }} {{ $med->unit }}s
                            </td>
                            <td class="text-center fw-semibold text-danger">
                                -{{ number_format($deficit) }} {{ $med->unit }}s
                            </td>
                            <td class="text-center">
                                @if($med->stock_on_hand <= 0)
                                    <span class="badge badge-soft-red">Out of Stock</span>
                                @else
                                    <span class="badge badge-soft-amber">Low Stock</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @can('inventory.stock-in')
                                    <a href="{{ route('inventory.stock-in', ['medicine_id' => $med->getRouteKey()]) }}" class="btn btn-sm btn-success py-1 px-2">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Stock-In
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                                Excellent! All active medicines are currently above their minimum reorder thresholds.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-hourglass-split text-danger fs-5"></i>
                        <h2 class="h6 mb-0 fw-bold text-dark">Upcoming Expiry Forecast (&le; 60 Days)</h2>
                    </div>
                    <a href="{{ route('inventory.expirations') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Medicine</th>
                                <th>Batch #</th>
                                <th>Expiry</th>
                                <th class="text-end pe-3">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringSoonBatches as $batch)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-semibold text-dark">{{ $batch->medicine->generic_name }}</span>
                                    </td>
                                    <td class="font-monospace small">{{ $batch->batch_number }}</td>
                                    <td>
                                        <span class="badge badge-soft-amber">{{ $batch->expiration_date->format('M d, Y') }} ({{ $batch->days_until_expiration }}d)</span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-dark">
                                        {{ $batch->quantity_remaining }} <small class="text-muted">{{ $batch->medicine->unit }}s</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No batches expiring within the next 60 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart text-primary fs-5"></i>
                        <h2 class="h6 mb-0 fw-bold text-dark">Formulary Category Distribution</h2>
                    </div>
                    <a href="{{ route('medicine-categories.index') }}" class="btn btn-sm btn-link text-decoration-none">Manage</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Category</th>
                                <th>Code</th>
                                <th class="text-end pe-3">Medicines</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td class="ps-3 fw-medium text-dark">{{ $cat->name }}</td>
                                    <td><span class="badge badge-soft-blue">{{ $cat->code }}</span></td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('medicines.index', ['category_id' => $cat->id]) }}" class="badge badge-soft-gray text-decoration-none">
                                            {{ $cat->medicines_count }} items
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No categories recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

