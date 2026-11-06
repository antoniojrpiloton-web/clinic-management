<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

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
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canEdit = $request->user()?->can('permissions.edit') ?? false;
            $canDelete = $request->user()?->can('permissions.delete') ?? false;

            $query = Permission::query()
                ->withCount('roles')
                ->orderBy('name');

            return DataTables::eloquent($query)
                ->addColumn('permission_col', fn (Permission $permission): string => '<code>'.e($permission->name).'</code>')
                ->addColumn('module_col', fn (Permission $permission): string => '<span class="text-capitalize">'.e(str($permission->name)->before('.')->replace('-', ' ')->toString()).'</span>')
                ->addColumn('roles_col', fn (Permission $permission): string => (string) $permission->roles_count)
                ->addColumn('actions_col', function (Permission $permission) use ($canEdit, $canDelete): string {
                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEdit) {
                        $editUrl = route('permissions.edit', $permission);
                        $html .= "<a href=\"{$editUrl}\" class=\"btn btn-sm btn-light\" title=\"Edit\"><i class=\"bi bi-pencil-square\"></i></a>";
                    }

                    if ($canDelete) {
                        $deleteUrl = route('permissions.destroy', $permission);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $confirm = e("The permission {$permission->name} will be permanently deleted.");
                        $html .= "<form method=\"POST\" action=\"{$deleteUrl}\" data-confirm=\"{$confirm}\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light text-danger\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('permission_col', 'name $1')
                ->orderColumn('module_col', 'name $1')
                ->orderColumn('roles_col', 'roles_count $1')
                ->rawColumns(['permission_col', 'module_col', 'actions_col'])
                ->make(true);
        }

        return view('permissions.index');
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
