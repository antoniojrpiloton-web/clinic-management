<x-layouts.app title="Users">
    <x-page-header title="Users" :breadcrumbs="['Users' => route('users.index')]">
        @can('users.create')
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i>New User
            </a>
        @endcan
    </x-page-header>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form id="users-filter-form" class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label for="filter-role" class="form-label mb-1">Role</label>
                    <select id="filter-role" name="role" class="form-select form-select-sm" data-select2 data-allow-clear data-placeholder="All roles">
                        <option value=""></option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-4 col-md-3">
                    <label for="filter-status" class="form-label mb-1">Status</label>
                    <select id="filter-status" name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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
                id="users-table"
                :url="route('users.index')"
                filters="users-filter-form"
                :page-length="$pageLength"
                :columns="[
                    ['data' => 'name', 'title' => 'User', 'renderType' => 'user', 'orderable' => true],
                    ['data' => 'phone', 'title' => 'Phone'],
                    ['data' => 'roles', 'title' => 'Roles', 'renderType' => 'badges'],
                    ['data' => 'status', 'title' => 'Status', 'renderType' => 'badge', 'orderable' => true],
                    ['data' => 'last_login_at', 'title' => 'Last Login', 'orderable' => true],
                    ['data' => 'created_at', 'title' => 'Created', 'orderable' => true],
                    ['data' => 'actions', 'title' => 'Actions', 'renderType' => 'actions', 'className' => 'text-end'],
                ]"
            />
        </div>
    </div>

    @include('partials.plugins.select2')
</x-layouts.app>

