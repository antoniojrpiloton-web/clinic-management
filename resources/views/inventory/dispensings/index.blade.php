<x-layouts.app title="Medicine Dispensing Log">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Medicine Dispensing</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Medicine Dispensing Log</h1>
            <p class="dashboard-subheading mb-0">Record and track pharmaceutical supplies and medicines issued to students, faculty, and staff.</p>
        </div>

        @can('inventory.dispense')
            <a href="{{ route('inventory.dispensings.create') }}" class="btn btn-primary btn-sm px-3 py-2">
                <i class="bi bi-plus-lg me-1"></i> Issue / Dispense Medicine
            </a>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Dispensings</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_dispensed']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Issued</span>
                    <span class="trend-compare">dispensing slips to date</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Dispensed Today</span>
                    <span class="kpi-icon-box green"><i class="bi bi-calendar-check"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['today_dispensed']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-clock"></i> Today</span>
                    <span class="trend-compare">patients served today</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Units Issued</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-capsule"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_items_dispensed']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Doses released</span>
                    <span class="trend-compare">cumulative units</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="dispensings-filter-form" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="filter-search" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Search by slip #, patient name, ID number, or diagnosis...">
                </div>
            </div>

            <div class="col-md-3">
                <input type="date" id="filter-date-from" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control" placeholder="From date">
            </div>

            <div class="col-md-3">
                <input type="date" id="filter-date-to" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control" placeholder="To date">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                <button type="reset" class="btn btn-outline-secondary" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="dispensings-table" class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Slip Number</th>
                            <th>Dispensed Date</th>
                            <th>Patient</th>
                            <th>Prescribing Clinician</th>
                            <th class="text-center">Items Count</th>
                            <th>Diagnosis / Chief Complaint</th>
                            <th>Dispensed By</th>
                            <th class="text-end">Actions</th>
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
                const table = $('#dispensings-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('inventory.dispensings.index') }}",
                        data: function (d) {
                            d.custom_search = $('#filter-search').val();
                            d.date_from = $('#filter-date-from').val();
                            d.date_to = $('#filter-date-to').val();
                        }
                    },
                    columns: [
                        { data: 'slip_col', name: 'dispensing_number', searchable: false },
                        { data: 'date_col', name: 'dispensed_date', searchable: false },
                        { data: 'patient_col', name: 'patient_id', orderable: false, searchable: false },
                        { data: 'clinician_col', name: 'clinician_id', orderable: false, searchable: false },
                        { data: 'items_col', name: 'total_items', className: 'text-center', searchable: false },
                        { data: 'diagnosis_col', name: 'diagnosis', orderable: false, searchable: false },
                        { data: 'dispenser_col', name: 'dispensed_by', orderable: false, searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Quick table filter...",
                        emptyTable: "No dispensing records found.",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading dispensings...'
                    }
                });

                let searchTimer;
                $('#filter-search').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#dispensings-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#dispensings-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-date-from, #filter-date-to').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPush
</x-layouts.app>
