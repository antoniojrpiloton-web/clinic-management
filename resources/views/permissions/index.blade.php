<x-layouts.app title="Permissions">
    <x-page-header title="Permissions" :breadcrumbs="['Permissions' => route('permissions.index')]">
        @can('permissions.create')
            <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Permission
            </a>
        @endcan
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="permissions-table" class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th>Module</th>
                            <th>Assigned Roles</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="4" :rows="8" />
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
                    $('#permissions-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('permissions.index') }}',
                        pageLength: 25,
                        order: [[0, 'asc']],
                        columns: [
                            { data: 'permission_col', name: 'name' },
                            { data: 'module_col', name: 'name' },
                            { data: 'roles_col', name: 'roles_count', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

