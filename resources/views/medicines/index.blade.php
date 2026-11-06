<x-layouts.app title="Medicine Masterlist">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Pharmacy &amp; Inventory</span>
        <span>&gt;</span>
        <span>Medicines</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Medicine Masterlist &amp; Inventory</h1>
            <p class="dashboard-subheading mb-0">Track pharmaceutical supplies, stock levels, reorder thresholds, and active lots.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @can('inventory.dispense')
                <a href="{{ route('inventory.dispensings.create') }}" class="btn btn-sm btn-outline-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-capsule"></i> Dispense Medicine
                </a>
            @endcan
            @can('inventory.stock-in')
                <a href="{{ route('inventory.stock-in') }}" class="btn btn-sm btn-outline-success px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-in-down"></i> Stock-In Delivery
                </a>
            @endcan
            @can('medicines.create')
                <a href="{{ route('medicines.create') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add New Medicine
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Registered Medicines</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-capsule-pill"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $totalMedicines }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Catalog items</span>
                    <span class="trend-compare">all dosage forms</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Stock Units</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-boxes"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($totalStockUnits) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Available units</span>
                    <span class="trend-compare">unexpired inventory</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Low / Out of Stock</span>
                    <span class="kpi-icon-box {{ $lowStockCount + $outOfStockCount > 0 ? 'red' : 'green' }}">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="kpi-value {{ $lowStockCount + $outOfStockCount > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $lowStockCount + $outOfStockCount }}
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge {{ $lowStockCount + $outOfStockCount > 0 ? 'down' : 'up' }}">
                        {{ $lowStockCount }} Low &bull; {{ $outOfStockCount }} Out
                    </span>
                    <span class="trend-compare">at reorder point</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Expiration Risk (&le;60d)</span>
                    <span class="kpi-icon-box {{ $expiringSoonCount + $expiredCount > 0 ? 'amber' : 'green' }}">
                        <i class="bi bi-calendar-x"></i>
                    </span>
                </div>
                <div class="kpi-value {{ $expiredCount > 0 ? 'text-danger' : ($expiringSoonCount > 0 ? 'text-warning' : 'text-success') }}">
                    {{ $expiringSoonCount + $expiredCount }}
                </div>
                <div class="kpi-trend">
                    <a href="{{ route('inventory.expirations') }}" class="trend-badge down text-decoration-none">
                        {{ $expiredCount }} Expired &bull; {{ $expiringSoonCount }} Soon
                    </a>
                    <span class="trend-compare">lots needing attention</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="medicine-search" class="form-control border-start-0" placeholder="Search by generic name, brand, code, or strength...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filter-category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter-form" class="form-select form-select-sm">
                        <option value="">All Dosage Forms</option>
                        @foreach (['Tablet', 'Capsule', 'Syrup', 'Suspension', 'Ointment', 'Ampule', 'Nebule', 'Drops', 'Sachet', 'Bottle'] as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" id="btn-reset-filters" class="btn btn-sm btn-light border w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="medicines-table" class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine &amp; Details</th>
                            <th>Category</th>
                            <th>Current Stock on Hand</th>
                            <th>Reorder Level</th>
                            <th>Classification</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="8" />
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

    @pushOnce('scripts', 'medicines-table-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.jQuery || !window.jQuery.fn.DataTable) return;

                const table = window.jQuery('#medicines-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: @json(route('medicines.index')),
                        data: function (d) {
                            d.search.value = document.getElementById('medicine-search').value;
                            d.category_id = document.getElementById('filter-category').value;
                            d.dosage_form = document.getElementById('filter-form').value;
                        }
                    },
                    columns: [
                        { data: 'medicine_card', name: 'generic_name' },
                        { data: 'category_badge', name: 'category.name' },
                        { data: 'stock_summary', name: 'id', orderable: false, searchable: false },
                        { data: 'reorder_level_formatted', name: 'reorder_level', className: 'text-center' },
                        { data: 'prescription_badge', name: 'is_prescription_required', className: 'text-center' },
                        { data: 'status_badge', name: 'is_active', className: 'text-center' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    order: [[0, 'asc']],
                    pageLength: 10,
                    dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center p-3 border-top"ip>',
                    language: {
                        emptyTable: 'No medicines found in the pharmacy catalog.',
                        zeroRecords: 'No matching medicines found.'
                    }
                });

                document.getElementById('medicine-search').addEventListener('input', () => table.draw());
                document.getElementById('filter-category').addEventListener('change', () => table.draw());
                document.getElementById('filter-form').addEventListener('change', () => table.draw());
                document.getElementById('btn-reset-filters').addEventListener('click', () => {
                    document.getElementById('medicine-search').value = '';
                    document.getElementById('filter-category').value = '';
                    document.getElementById('filter-form').value = '';
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

