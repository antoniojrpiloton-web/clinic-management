<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
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
