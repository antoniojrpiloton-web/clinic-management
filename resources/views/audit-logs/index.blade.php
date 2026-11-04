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
                    <select id="filter-user" name="user_id" class="form-select form-select-sm" data-select2 data-allow-clear data-placeholder="All users">
                        <option value=""></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
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
            <x-datatable
                id="audit-logs-table"
                :url="route('audit-logs.index')"
                filters="audit-logs-filter-form"
                :page-length="$pageLength"
                :columns="[
                    ['data' => 'created_at', 'title' => 'Timestamp', 'orderable' => true],
                    ['data' => 'user', 'title' => 'User'],
                    ['data' => 'event', 'title' => 'Event', 'renderType' => 'badge', 'orderable' => true],
                    ['data' => 'description', 'title' => 'Description'],
                    ['data' => 'subject', 'title' => 'Subject'],
                    ['data' => 'ip_address', 'title' => 'IP Address'],
                    ['data' => 'actions', 'title' => 'Actions', 'renderType' => 'actions', 'className' => 'text-end'],
                ]"
            />
        </div>
    </div>

    <x-modal id="audit-log-modal" title="Audit Log Details" size="lg" />

    @include('partials.plugins.select2')
    @include('partials.plugins.flatpickr')

    @pushOnce('scripts', 'audit-logs-scripts')
        @vite(['resources/js/pages/audit-logs.js'])
    @endPushOnce
</x-layouts.app>

