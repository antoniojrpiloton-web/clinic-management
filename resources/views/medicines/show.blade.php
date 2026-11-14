<x-layouts.app title="{{ $medicine->display_name }} - Medicine Details">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Medicines</a>
        <span>&gt;</span>
        <span>{{ $medicine->generic_name }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">{{ $medicine->generic_name }}</h1>
                @if($medicine->brand_name)
                    <span class="fs-5 text-primary fw-medium">({{ $medicine->brand_name }})</span>
                @endif
                @if($medicine->is_active)
                    <span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>
                @else
                    <span class="badge badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>
                @endif
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                <span class="badge badge-soft-gray me-2">{{ $medicine->code }}</span>
                <span>{{ $medicine->strength }} {{ $medicine->dosage_form }}</span>
                <span class="mx-2">•</span>
                <span class="text-primary fw-medium">{{ $medicine->category?->name ?? 'Uncategorized' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('inventory.stock-in')
                <a href="{{ route('inventory.stock-in', ['medicine_id' => $medicine->id]) }}" class="btn btn-success btn-sm px-3 py-2">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Receive Stock
                </a>
            @endcan

            @can('inventory.stock-out')
                <a href="{{ route('inventory.stock-out', ['medicine_id' => $medicine->id]) }}" class="btn btn-outline-danger btn-sm px-3 py-2">
                    <i class="bi bi-box-arrow-up me-1"></i> Deduct / Stock-Out
                </a>
            @endcan

            @can('medicines.edit')
                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan

            <a href="{{ route('medicines.index') }}" class="btn btn-light border btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Stock On Hand</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fs-2 fw-bold {{ $medicine->stock_on_hand <= 0 ? 'text-danger' : ($medicine->stock_on_hand <= $medicine->reorder_level ? 'text-warning' : 'text-success') }}">
                            {{ number_format($medicine->stock_on_hand) }}
                        </span>
                        <span class="text-muted small">{{ $medicine->unit }}s</span>
                    </div>
                    <div class="mt-2">
                        @if($medicine->stock_on_hand <= 0)
                            <span class="badge badge-soft-red"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                        @elseif($medicine->stock_on_hand <= $medicine->reorder_level)
                            <span class="badge badge-soft-amber"><i class="bi bi-exclamation-triangle me-1"></i>At/Below Reorder ({{ $medicine->reorder_level }})</span>
                        @else
                            <span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Healthy Stock</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Total Active Batches</span>
                    <div class="d-flex align-items-baseline gap-2 mt-2">
                        <span class="fs-2 fw-bold text-dark">{{ $medicine->batches->where('quantity_remaining', '>', 0)->count() }}</span>
                        <span class="text-muted small">lots</span>
                    </div>
                    <div class="mt-2 text-muted small">
                        Total recorded: {{ $medicine->batches->count() }} batches
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Dispensing Requirement</span>
                    <div class="mt-2">
                        @if($medicine->is_prescription_required)
                            <span class="badge badge-soft-amber fs-6 py-2 px-3">
                                <i class="bi bi-prescription me-1"></i> Prescription (Rx)
                            </span>
                        @else
                            <span class="badge badge-soft-green fs-6 py-2 px-3">
                                <i class="bi bi-check2 me-1"></i> Over-the-Counter (OTC)
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 text-muted small">
                        Reorder threshold: {{ $medicine->reorder_level }} {{ $medicine->unit }}s
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Dosage &amp; Storage</span>
                    <div class="fw-bold text-dark mt-2">{{ $medicine->dosage_form }} ({{ $medicine->unit }})</div>
                    <div class="text-muted small mt-1 text-truncate" title="{{ $medicine->storage_conditions }}">
                        {{ $medicine->storage_conditions ?: 'Store in cool dry place' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-boxes text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Batch &amp; Expiration Tracking</h2>
            </div>
            @can('inventory.stock-in')
                <a href="{{ route('inventory.stock-in', ['medicine_id' => $medicine->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Batch
                </a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Batch / Lot #</th>
                        <th>Supplier</th>
                        <th>Received Date</th>
                        <th>Expiration Date</th>
                        <th>Status</th>
                        <th class="text-end">Initial Qty</th>
                        <th class="text-end">Remaining Qty</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicine->batches as $batch)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                {{ $batch->batch_number }}
                            </td>
                            <td>{{ $batch->supplier ?? 'N/A' }}</td>
                            <td>{{ $batch->received_date ? $batch->received_date->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="fw-medium">{{ $batch->expiration_date->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($batch->is_expired)
                                    <span class="badge badge-soft-red"><i class="bi bi-exclamation-octagon me-1"></i>Expired</span>
                                @elseif($batch->days_until_expiration <= 30)
                                    <span class="badge badge-soft-red"><i class="bi bi-exclamation-triangle me-1"></i>{{ $batch->days_until_expiration }}d (Critical)</span>
                                @elseif($batch->days_until_expiration <= 90)
                                    <span class="badge badge-soft-amber"><i class="bi bi-hourglass-split me-1"></i>{{ $batch->days_until_expiration }}d (Warning)</span>
                                @else
                                    <span class="badge badge-soft-green"><i class="bi bi-shield-check me-1"></i>Good ({{ $batch->days_until_expiration }}d)</span>
                                @endif
                            </td>
                            <td class="text-end text-muted">{{ number_format($batch->quantity_received) }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $batch->quantity_remaining <= 0 ? 'text-muted' : ($batch->is_expired ? 'text-danger' : 'text-success') }}">
                                    {{ number_format($batch->quantity_remaining) }}
                                </span>
                                <small class="text-muted">{{ $medicine->unit }}s</small>
                            </td>
                            <td class="text-end text-muted">
                                {{ $batch->unit_cost ? '₱'.number_format($batch->unit_cost, 2) : 'N/A' }}
                            </td>
                            <td class="text-end pe-3">
                                @if($batch->quantity_remaining > 0)
                                    @can('inventory.stock-out')
                                        @if($batch->is_expired)
                                            <form method="POST" action="{{ route('inventory.batches.dispose', $batch) }}" class="d-inline" onsubmit="return confirm('Confirm safe medical disposal and write-off of remaining {{ $batch->quantity_remaining }} units?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                    <i class="bi bi-trash3 me-1"></i> Dispose
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('inventory.stock-out', ['medicine_id' => $medicine->id, 'batch_id' => $batch->id]) }}" class="btn btn-sm btn-light border py-1 px-2 text-danger">
                                                <i class="bi bi-box-arrow-up me-1"></i> Deduct
                                            </a>
                                        @endif
                                    @endcan
                                @else
                                    <span class="badge badge-soft-gray">Depleted</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                No stock batches recorded yet for this medicine.
                                @can('inventory.stock-in')
                                    <div class="mt-2">
                                        <a href="{{ route('inventory.stock-in', ['medicine_id' => $medicine->id]) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-box-arrow-in-down me-1"></i> Receive First Stock Batch
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Recent Movement Ledger</h2>
            </div>
            <a href="{{ route('inventory.transactions', ['medicine_id' => $medicine->id]) }}" class="btn btn-sm btn-link text-decoration-none">
                View Full Movement Ledger <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date / Time</th>
                        <th>Txn Number</th>
                        <th>Type</th>
                        <th>Batch #</th>
                        <th class="text-center">Change</th>
                        <th class="text-end">Balance After</th>
                        <th>Reason / Destination</th>
                        <th class="pe-3">Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicine->transactions as $txn)
                        <tr>
                            <td class="ps-3 small text-muted">
                                {{ $txn->transaction_date ? $txn->transaction_date->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="fw-medium font-monospace small">{{ $txn->transaction_number }}</td>
                            <td>
                                @if($txn->type === 'stock_in')
                                    <span class="badge badge-soft-green"><i class="bi bi-arrow-down-left me-1"></i>Stock In</span>
                                @elseif($txn->type === 'dispensed')
                                    <span class="badge badge-soft-blue"><i class="bi bi-person-fill-check me-1"></i>Dispensed</span>
                                @elseif($txn->type === 'disposal')
                                    <span class="badge badge-soft-red"><i class="bi bi-trash3 me-1"></i>Disposal</span>
                                @else
                                    <span class="badge badge-soft-amber"><i class="bi bi-arrow-up-right me-1"></i>Stock Out</span>
                                @endif
                            </td>
                            <td>{{ $txn->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="text-center fw-bold {{ $txn->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $txn->quantity > 0 ? '+'.$txn->quantity : $txn->quantity }}
                            </td>
                            <td class="text-end fw-semibold text-dark">{{ $txn->quantity_after }}</td>
                            <td class="small text-truncate" style="max-width: 260px;" title="{{ $txn->reason }}">
                                {{ $txn->reason ?: 'N/A' }}
                            </td>
                            <td class="pe-3 small text-muted">{{ $txn->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">No transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

