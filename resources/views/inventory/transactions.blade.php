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

    <div class="card mb-3 p-3">
        <form id="transactions-filter-form" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Transaction Type</label>
                <select name="type" id="filter-type" class="form-select form-select-sm">
                    <option value="">All Movement Types</option>
                    <option value="stock_in" @selected($type === 'stock_in')>Stock In (Deliveries)</option>
                    <option value="dispensed" @selected($type === 'dispensed')>Patient Dispensed</option>
                    <option value="stock_out" @selected($type === 'stock_out')>Stock Out / Adjustments</option>
                    <option value="disposal" @selected($type === 'disposal')>Expired Disposal</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Medicine Filter</label>
                <select name="medicine_id" id="filter-medicine" class="form-select form-select-sm">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $m)
                        <option value="{{ $m->id }}" @selected($medicineId == $m->id)>{{ $m->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">From Date</label>
                <input type="date" name="date_from" id="filter-date-from" value="{{ $dateFrom }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">To Date</label>
                <input type="date" name="date_to" id="filter-date-to" value="{{ $dateTo }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <button type="reset" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="transactions-table" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Txn Number</th>
                            <th>Type</th>
                            <th>Medicine</th>
                            <th>Batch #</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Balance After</th>
                            <th>Reason / Patient</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="9" :rows="8" />
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#transactions-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[0, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('inventory.transactions') }}",
                        data: function (d) {
                            d.type = $('#filter-type').val();
                            d.medicine_id = $('#filter-medicine').val();
                            d.date_from = $('#filter-date-from').val();
                            d.date_to = $('#filter-date-to').val();
                        }
                    },
                    columns: [
                        { data: 'date_col', name: 'transaction_date', searchable: false },
                        { data: 'txn_col', name: 'transaction_number', searchable: false },
                        { data: 'type_col', name: 'type', orderable: false, searchable: false },
                        { data: 'medicine_col', name: 'medicine_id', orderable: false, searchable: false },
                        { data: 'batch_col', name: 'batch_id', orderable: false, searchable: false },
                        { data: 'qty_col', name: 'quantity', className: 'text-center', searchable: false },
                        { data: 'balance_col', name: 'quantity_after', className: 'text-end', searchable: false },
                        { data: 'reason_col', name: 'reason', orderable: false, searchable: false },
                        { data: 'user_col', name: 'user_id', orderable: false, searchable: false }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search txn #, medicine, reason...",
                        emptyTable: "No inventory transactions found matching the filter criteria.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading ledger...'
                    }
                });

                $('#transactions-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#transactions-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-type, #filter-medicine, #filter-date-from, #filter-date-to').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>
