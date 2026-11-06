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

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card border-danger">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label text-danger">Already Expired</span>
                    <span class="kpi-icon-box red"><i class="bi bi-exclamation-octagon"></i></span>
                </div>
                <div class="kpi-value text-danger">{{ number_format($expiredCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge down">Expired</span>
                    <span class="trend-compare">batches requiring safe disposal</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card border-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label text-warning">Critical (&le; 30 Days)</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="kpi-value text-warning">{{ number_format($criticalCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge down">&le; 30d</span>
                    <span class="trend-compare">high risk of expiring soon</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Warning (&le; 90 Days)</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($warningCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">31-90d</span>
                    <span class="trend-compare">prioritize dispensing</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Good Shelf Life (&gt; 90 Days)</span>
                    <span class="kpi-icon-box green"><i class="bi bi-shield-check"></i></span>
                </div>
                <div class="kpi-value text-success">{{ number_format($goodCount) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Good</span>
                    <span class="trend-compare">adequate expiration window</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-2">
            <input type="hidden" id="expiration-status-filter" value="{{ $filter }}">
            <ul class="nav nav-pills card-header-pills" id="expiration-tabs">
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'all_risk' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'all_risk']) }}" data-status="all_risk">
                        All at Risk (&le; 90d &amp; Expired)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'expired' ? 'active text-white' : 'text-danger' }}" href="{{ route('inventory.expirations', ['status' => 'expired']) }}" data-status="expired">
                        Expired Only ({{ $expiredCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'critical' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'critical']) }}" data-status="critical">
                        Critical &le; 30d ({{ $criticalCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'warning' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'warning']) }}" data-status="warning">
                        Warning 31-90d ({{ $warningCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('inventory.expirations', ['status' => 'all']) }}" data-status="all">
                        All Active Batches
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="expirations-table" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th>Batch / Lot #</th>
                            <th>Expiration Date</th>
                            <th>Risk Assessment</th>
                            <th class="text-end">Remaining Qty</th>
                            <th>Supplier</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="8" :rows="8" />
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
                const statusInput = document.getElementById('expiration-status-filter');
                const table = $('#expirations-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[3, 'asc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('inventory.expirations') }}",
                        data: function (d) {
                            d.status = statusInput ? statusInput.value : 'all_risk';
                        }
                    },
                    columns: [
                        { data: 'medicine_col', name: 'medicine_id', orderable: false, searchable: false },
                        { data: 'category_col', name: 'medicine_id', orderable: false, searchable: false },
                        { data: 'batch_col', name: 'batch_number', searchable: false },
                        { data: 'expiration_col', name: 'expiration_date', searchable: false },
                        { data: 'risk_col', name: 'expiration_date', orderable: false, searchable: false },
                        { data: 'remaining_col', name: 'quantity_remaining', className: 'text-end', searchable: false },
                        { data: 'supplier_col', name: 'supplier', orderable: false, searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search batch #, medicine, supplier...",
                        emptyTable: "No batches found matching the selected expiration filter.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading batches...'
                    }
                });

                document.querySelectorAll('#expiration-tabs a[data-status]').forEach((tab) => {
                    tab.addEventListener('click', (e) => {
                        e.preventDefault();
                        const newStatus = tab.dataset.status;
                        if (statusInput) {
                            statusInput.value = newStatus;
                        }
                        document.querySelectorAll('#expiration-tabs a[data-status]').forEach((el) => {
                            el.classList.remove('active', 'text-white');
                            if (el.dataset.status === 'expired') {
                                el.classList.add('text-danger');
                            }
                        });
                        tab.classList.add('active');
                        if (newStatus === 'expired') {
                            tab.classList.remove('text-danger');
                            tab.classList.add('text-white');
                        }
                        window.history.replaceState({}, '', tab.getAttribute('href'));
                        table.draw();
                    });
                });
            });
        </script>
    @endPush
</x-layouts.app>
