<x-layouts.app title="Roles">
    <x-page-header title="Roles" :breadcrumbs="['Roles' => route('roles.index')]">
        @can('roles.create')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Role
            </a>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="4" :rows="6" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;
                if ($ && $.fn.DataTable) {
                    $('#roles-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('roles.index') }}',
                        pageLength: 25,
                        order: [[0, 'asc']],
                        columns: [
                            { data: 'role_col', name: 'name' },
                            { data: 'users_col', name: 'users_count', searchable: false },
                            { data: 'permissions_col', name: 'permissions_count', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

