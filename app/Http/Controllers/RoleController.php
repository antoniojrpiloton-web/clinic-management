<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles.view', only: ['index']),
            new Middleware('permission:roles.create', only: ['create', 'store']),
            new Middleware('permission:roles.edit', only: ['edit', 'update']),
            new Middleware('permission:roles.delete', only: ['destroy']),
        ];
    }

    /**
     * List roles.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('roles.edit') ?? false;
            $canDelete = $request->user()?->can('roles.delete') ?? false;

            $query = Role::query()
                ->withCount(['users', 'permissions'])
                ->orderBy('name');

            return DataTables::eloquent($query)
                ->addColumn('role_col', function (Role $role): string {
                    $name = e($role->name);
                    if ($role->name === User::SUPER_ADMIN_ROLE) {
                        return "<span class=\"fw-medium\">{$name}</span> <span class=\"badge text-bg-primary ms-1\">Protected</span>";
                    }

                    return "<span class=\"fw-medium\">{$name}</span>";
                })
                ->addColumn('users_col', fn (Role $role): string => (string) $role->users_count)
                ->addColumn('permissions_col', function (Role $role): string {
                    if ($role->name === User::SUPER_ADMIN_ROLE) {
                        return '<span class="badge text-bg-success">All permissions</span>';
                    }

                    return (string) $role->permissions_count;
                })
                ->addColumn('actions_col', function (Role $role) use ($canEdit, $canDelete): string {
                    if ($role->name === User::SUPER_ADMIN_ROLE) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEdit) {
                        $editUrl = route('roles.edit', $role);
                        $html .= "<a href=\"{$editUrl}\" class=\"btn btn-sm btn-light\" title=\"Edit\"><i class=\"bi bi-pencil-square\"></i></a>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('roles.destroy', $role);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $confirm = e("The role {$role->name} will be permanently deleted.");
                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"{$confirm}\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light text-danger\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('role_col', 'name $1')
                ->orderColumn('users_col', 'users_count $1')
                ->orderColumn('permissions_col', 'permissions_count $1')
                ->rawColumns(['role_col', 'permissions_col', 'actions_col'])
                ->make(true);
        }

        return view('roles.index');
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        return view('roles.create', [
            'role' => new Role,
            'permissionGroups' => $this->groupedPermissions(),
            'assigned' => [],
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $role = DB::transaction(function () use ($request): Role {
            $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            $role->syncPermissions($request->validated('permissions', []));

            return $role;
        });

        $this->auditLogger->log('created', "Created role {$role->name}", $role, [
            'permissions' => $request->validated('permissions', []),
        ]);

        Cache::forget('dashboard.charts');

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the role.
     */
    public function edit(Role $role): View
    {
        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->groupedPermissions(),
            'assigned' => $role->permissions()->pluck('name')->all(),
        ]);
    }

    /**
     * Update the role and its permissions.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return back()->with('error', 'The Super Admin role is protected and cannot be modified.');
        }

        $oldName = $role->name;
        $oldPermissions = $role->permissions()->pluck('name')->all();
        $newPermissions = $request->validated('permissions', []);

        DB::transaction(function () use ($request, $role, $newPermissions): void {
            $role->update(['name' => $request->validated('name')]);
            $role->syncPermissions($newPermissions);
        });

        $this->auditLogger->log('updated', "Updated role {$role->name}", $role, [
            'old' => ['name' => $oldName, 'permissions' => $oldPermissions],
            'new' => ['name' => $role->name, 'permissions' => $newPermissions],
        ]);

        Cache::forget('dashboard.charts');

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Delete the role when no users are assigned to it.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Reassign the users of this role before deleting it.');
        }

        $this->auditLogger->log('deleted', "Deleted role {$role->name}", $role, ['name' => $role->name]);

        $role->delete();
        Cache::forget('dashboard.charts');

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Permissions grouped by their module prefix (e.g. "users" for "users.create").
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    protected function groupedPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (Permission $permission): string => str($permission->name)->before('.')->toString());
    }
}
