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
            <x-datatable id="permissions-table">
                <thead>
                    <tr>
                        <th>Permission</th>
                        <th>Module</th>
                        <th>Assigned Roles</th>
                        <th class="no-sort text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td><code>{{ $permission->name }}</code></td>
                            <td class="text-capitalize">{{ str($permission->name)->before('.')->replace('-', ' ') }}</td>
                            <td>{{ $permission->roles_count }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @can('permissions.edit')
                                        <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-light" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan

                                    @can('permissions.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('permissions.destroy', $permission) }}"
                                            data-confirm="The permission {{ $permission->name }} will be permanently deleted."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-datatable>
        </div>
    </div>
</x-layouts.app>

