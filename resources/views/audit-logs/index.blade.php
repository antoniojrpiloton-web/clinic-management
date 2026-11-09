<x-layouts.app title="Audit Logs">
    <x-page-header title="Audit Logs" :breadcrumbs="['Audit Logs' => route('audit-logs.index')]" />

    <div class="card mb-3">
        <div class="card-body py-3">
            <form id="audit-logs-filter-form" class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label for="filter-event" class="form-label mb-1">Event</label>
                    <select id="filter-event" name="event" class="form-select form-select-sm" data-select2 data-allow-clear data-placeholder="All events">
                        <option value=""></option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}">{{ str($event)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-3">
                    <label for="filter-user" class="form-label mb-1">User</label>
                    <select id="filter-user" name="user_id" class="form-select form-select-sm" data-select2 data-ajax-url="{{ route('select2.users') }}" data-allow-clear="true" data-placeholder="All users">
                        <option value=""></option>
                    </select>
                </div>

                <div class="col-sm-6 col-md-2">
                    <label for="filter-from" class="form-label mb-1">From</label>
                    <input type="text" id="filter-from" name="date_from" class="form-control form-control-sm" data-flatpickr placeholder="Start date">
                </div>

                <div class="col-sm-6 col-md-2">
                    <label for="filter-to" class="form-label mb-1">To</label>
                    <input type="text" id="filter-to" name="date_to" class="form-control form-control-sm" data-flatpickr placeholder="End date">
                </div>

                <div class="col-auto">
                    <button type="reset" class="btn btn-sm btn-light">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="audit-logs-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>TIMESTAMP</th>
                            <th>USER</th>
                            <th>EVENT</th>
                            <th>DESCRIPTION</th>
                            <th>SUBJECT</th>
                            <th>IP ADDRESS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="8" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal id="audit-log-modal" title="Audit Log Details" size="lg" />

    @include('partials.plugins.select2')
    @include('partials.plugins.flatpickr')
    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;
                if (!$ || !$.fn.DataTable) {
                    return;
                }

                const table = $('#audit-logs-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: {{ $pageLength }},
                    lengthMenu: [10, 25, 50, 100],
                    order: [[0, 'desc']],
                    ajax: {
                        url: '{{ route('audit-logs.index') }}',
                        data: function (d) {
                            d.event = $('#filter-event').val();
                            d.user_id = $('#filter-user').val();
                            d.date_from = $('#filter-from').val();
                            d.date_to = $('#filter-to').val();
                        }
                    },
                    columns: [
                        { data: 'timestamp_col', name: 'created_at', orderable: true },
                        { data: 'user_col', name: 'user.name', orderable: false },
                        { data: 'event_col', name: 'event', orderable: true },
                        { data: 'description_col', name: 'description', orderable: false },
                        { data: 'subject_col', name: 'subject_type', orderable: false },
                        { data: 'ip_address_col', name: 'ip_address', orderable: false },
                        { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search...',
                        emptyTable: 'No audit log entries found',
                        processing: '<div class="spinner-border spinner-border-sm text-success" role="status"></div> Loading…'
                    }
                });

                const filterForm = document.getElementById('audit-logs-filter-form');
                if (filterForm) {
                    $('#filter-event, #filter-user').on('change', function () {
                        table.draw();
                    });
                    $('#filter-from, #filter-to').on('change', function () {
                        table.draw();
                    });
                    filterForm.addEventListener('reset', function () {
                        setTimeout(function () {
                            $('#filter-event').val('').trigger('change.select2');
                            $('#filter-user').val('').trigger('change.select2');
                            table.draw();
                        }, 50);
                    });
                }
            });
        </script>
    @endpush

    @pushOnce('scripts', 'audit-logs-scripts')
        @vite(['resources/js/pages/audit-logs.js'])
    @endPushOnce
</x-layouts.app>
