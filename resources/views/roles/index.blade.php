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
            <x-datatable id="roles-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th class="no-sort text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td class="fw-medium">
                                {{ $role->name }}
                                @if ($role->name === App\Models\User::SUPER_ADMIN_ROLE)
                                    <span class="badge text-bg-primary ms-1">Protected</span>
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                @if ($role->name === App\Models\User::SUPER_ADMIN_ROLE)
                                    <span class="badge text-bg-success">All permissions</span>
                                @else
                                    {{ $role->permissions_count }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($role->name !== App\Models\User::SUPER_ADMIN_ROLE)
                                    <div class="d-inline-flex gap-1">
                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-light" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endcan

                                        @can('roles.delete')
                                            <form
                                                method="POST"
                                                action="{{ route('roles.destroy', $role) }}"
                                                data-confirm="The role {{ $role->name }} will be permanently deleted."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-datatable>
        </div>
    </div>
</x-layouts.app>

