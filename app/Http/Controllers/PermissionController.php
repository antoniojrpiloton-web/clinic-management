<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:permissions.view', only: ['index']),
            new Middleware('permission:permissions.create', only: ['create', 'store']),
            new Middleware('permission:permissions.edit', only: ['edit', 'update']),
            new Middleware('permission:permissions.delete', only: ['destroy']),
        ];
    }

    /**
     * List permissions.
     */
    public function index(): View
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): View
    {
        return view('permissions.create', ['permission' => new Permission]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        $permission = Permission::create(['name' => $request->validated('name'), 'guard_name' => 'web']);

        $this->auditLogger->log('created', "Created permission {$permission->name}", $permission);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the permission.
     */
    public function edit(Permission $permission): View
    {
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Update the permission.
     */
    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $oldName = $permission->name;
        $permission->update(['name' => $request->validated('name')]);

        $this->auditLogger->log('updated', "Renamed permission {$oldName} to {$permission->name}", $permission, [
            'old' => ['name' => $oldName],
            'new' => ['name' => $permission->name],
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Delete the permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $this->auditLogger->log('deleted', "Deleted permission {$permission->name}", $permission, ['name' => $permission->name]);

        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
