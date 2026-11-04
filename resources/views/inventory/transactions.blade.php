<x-layouts.app title="Inventory Transaction Movement Ledger">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Transaction Ledger</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Inventory Transaction Movement Ledger</h1>
            <p class="dashboard-subheading mb-0">Complete audit log of all stock receipts, patient dispensings, adjustments, and medical disposals.</p>
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

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('inventory.transactions') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Transaction Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Movement Types</option>
                        <option value="stock_in" @selected($type === 'stock_in')>Stock In (Deliveries)</option>
                        <option value="dispensed" @selected($type === 'dispensed')>Patient Dispensed</option>
                        <option value="stock_out" @selected($type === 'stock_out')>Stock Out / Adjustments</option>
                        <option value="disposal" @selected($type === 'disposal')>Expired Disposal</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Medicine Filter</label>
                    <select name="medicine_id" class="form-select form-select-sm">
                        <option value="">All Medicines</option>
                        @foreach($medicines as $m)
                            <option value="{{ $m->id }}" @selected($medicineId == $m->id)>{{ $m->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 d-flex gap-2 align-items-end mt-auto">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('inventory.transactions') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date &amp; Time</th>
                        <th>Txn Number</th>
                        <th>Type</th>
                        <th>Medicine</th>
                        <th>Batch #</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Balance After</th>
                        <th>Reason / Patient</th>
                        <th class="pe-3">Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td class="ps-3 small text-muted text-nowrap">
                                {{ $txn->transaction_date ? $txn->transaction_date->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="font-monospace small fw-medium">{{ $txn->transaction_number }}</td>
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
                            <td>
                                @if($txn->medicine)
                                    <a href="{{ route('medicines.show', $txn->medicine) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $txn->medicine->generic_name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $txn->medicine->dosage_form }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="font-monospace small">{{ $txn->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="text-center fw-bold {{ $txn->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $txn->quantity > 0 ? '+'.$txn->quantity : $txn->quantity }}
                            </td>
                            <td class="text-end fw-semibold text-dark">
                                {{ number_format($txn->quantity_after) }}
                            </td>
                            <td class="small" style="max-width: 280px;">
                                @if($txn->patient)
                                    <div>
                                        <i class="bi bi-person me-1 text-primary"></i>
                                        <a href="{{ route('patients.show', $txn->patient) }}" class="text-decoration-none fw-medium text-dark">
                                            {{ $txn->patient->full_name }}
                                        </a>
                                    </div>
                                    <span class="text-muted small">{{ $txn->patient->primary_identifier }}</span>
                                @else
                                    <span class="text-muted text-truncate d-block" title="{{ $txn->reason }}">{{ $txn->reason ?: 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="pe-3 small text-muted">{{ $txn->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No inventory transactions found matching the filter criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>

