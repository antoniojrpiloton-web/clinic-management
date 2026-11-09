<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.create', only: ['create', 'store']),
            new Middleware('permission:users.edit', only: ['edit', 'update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request, SettingService $settings): View|JsonResponse
    {
        if (! $request->ajax()) {
            return view('users.index', [
                'roles' => Role::orderBy('name')->pluck('name'),
                'pageLength' => (int) $settings->get('table_page_length', 25),
            ]);
        }

        $actor = $request->user();
        $canEdit = $actor->can('users.edit');
        $canDelete = $actor->can('users.delete');
        $dateFormat = $settings->get('date_format', 'M d, Y');

        $query = User::query()
            ->select(['id', 'name', 'email', 'phone', 'is_active', 'last_login_at', 'created_at'])
            ->with('roles:id,name');

        if (! $actor->isFeatureController()) {
            $query->where('email', '!=', User::FEATURE_CONTROLLER_EMAIL);
        }

        return DataTables::eloquent($query)
            ->filter(function (Builder $query) use ($request): void {
                if ($request->filled('role')) {
                    $query->role($request->string('role')->toString());
                }

                if ($request->filled('status')) {
                    $query->where('is_active', $request->input('status') === 'active');
                }

                if ($search = $request->input('search.value')) {
                    $query->where(function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('user_card', function (User $user): string {
                $initials = e($user->initials());
                $name = e($user->name);
                $email = e($user->email);

                return <<<HTML
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar">{$initials}</span>
                        <div class="lh-sm">
                            <div class="fw-bold text-dark">{$name}</div>
                            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{$email}</small>
                        </div>
                    </div>
                HTML;
            })
            ->addColumn('roles_badges', function (User $user): string {
                return $user->roles->map(function ($role): string {
                    $roleName = e($role->name);

                    return "<span class=\"badge badge-pill badge-gray me-1\">{$roleName}</span>";
                })->implode('') ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('status_badge', function (User $user): string {
                return $user->is_active
                    ? '<span class="badge badge-pill badge-forest"><i class="bi bi-check2-circle me-1"></i>Active</span>'
                    : '<span class="badge badge-pill badge-gray">Inactive</span>';
            })
            ->addColumn('last_login_formatted', function (User $user): string {
                if (! $user->last_login_at) {
                    return '<span class="text-muted">Never</span>';
                }

                return '<span class="text-dark fw-medium">'.e($user->last_login_at->diffForHumans()).'</span>';
            })
            ->addColumn('created_formatted', function (User $user) use ($dateFormat): string {
                return '<span class="text-muted small">'.e($user->created_at->format($dateFormat)).'</span>';
            })
            ->addColumn('actions', function (User $user) use ($actor, $canEdit, $canDelete): string {
                $editUrl = $canEdit ? route('users.edit', $user) : null;
                $deleteUrl = ($canDelete && $user->isNot($actor)) ? route('users.destroy', $user) : null;
                $csrf = csrf_field();
                $method = method_field('DELETE');
                $userName = e($user->name);

                $dropdownItems = '';
                if ($editUrl) {
                    $dropdownItems .= "<li><a class=\"dropdown-item py-1\" href=\"{$editUrl}\"><i class=\"bi bi-pencil-square me-2 text-primary\"></i>Edit Details</a></li>";
                }
                if ($deleteUrl) {
                    $dropdownItems .= <<<HTML
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{$deleteUrl}" data-confirm="{$userName} will be permanently removed.">
                                {$csrf}
                                {$method}
                                <button type="submit" class="dropdown-item py-1 text-danger">
                                    <i class="bi bi-trash me-2"></i>Delete User
                                </button>
                            </form>
                        </li>
                    HTML;
                }

                $primaryAction = $editUrl
                    ? "<a href=\"{$editUrl}\" class=\"btn btn-sm btn-outline-forest py-1 px-2 d-inline-flex align-items-center gap-1\"><i class=\"bi bi-eye\"></i> View</a>"
                    : '<button type="button" class="btn btn-sm btn-outline-forest py-1 px-2 d-inline-flex align-items-center gap-1"><i class="bi bi-eye"></i> View</button>';

                return <<<HTML
                    <div class="btn-group">
                        {$primaryAction}
                        <button type="button" class="btn btn-sm btn-outline-forest dropdown-toggle dropdown-toggle-split py-1 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="min-width: 170px; font-size: 0.82rem;">
                            {$dropdownItems}
                        </ul>
                    </div>
                HTML;
            })
            ->rawColumns(['user_card', 'roles_badges', 'status_badge', 'last_login_formatted', 'created_formatted', 'actions'])
            ->orderColumn('user_card', 'name $1')
            ->orderColumn('status_badge', 'is_active $1')
            ->orderColumn('last_login_formatted', 'last_login_at $1')
            ->orderColumn('created_formatted', 'created_at $1')
            ->make(true);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->safe()->except('roles'));
            $user->syncRoles($request->validated('roles'));

            return $user;
        });

        $this->auditLogger->log('created', "Created user {$user->name}", $user, [
            'attributes' => $user->only(['name', 'email', 'phone', 'is_active']),
            'roles' => $request->validated('roles'),
        ]);

        $this->flushDashboardCache();

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        if ($user->isFeatureController() && ! $request->user()?->isFeatureController()) {
            abort(403, 'The master developer account cannot be modified by other users.');
        }

        return view('users.edit', [
            'user' => $user->load('roles:id,name'),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->isFeatureController() && ! $request->user()?->isFeatureController()) {
            abort(403, 'The master developer account cannot be modified by other users.');
        }

        if ($user->is($request->user()) && ! $request->boolean('is_active')) {
            return back()->withInput()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $original = $user->getOriginal();
        $originalRoles = $user->roles()->pluck('name')->all();

        DB::transaction(function () use ($request, $user): void {
            $user->fill($request->safe()->except(['roles', 'password']));

            if ($request->filled('password')) {
                $user->password = $request->validated('password');
            }

            $user->save();
            $user->syncRoles($request->validated('roles'));
        });

        $this->auditLogger->logChanges($user, $original, "Updated user {$user->name}");

        if (array_diff($originalRoles, $request->validated('roles')) || array_diff($request->validated('roles'), $originalRoles)) {
            $this->auditLogger->log('updated', "Changed roles of {$user->name}", $user, [
                'old' => ['roles' => $originalRoles],
                'new' => ['roles' => $request->validated('roles')],
            ]);
        }

        $this->flushDashboardCache();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->isFeatureController()) {
            return back()->with('error', 'The master developer account cannot be deleted.');
        }

        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::role(User::SUPER_ADMIN_ROLE)->count() <= 1) {
            return back()->with('error', 'The last Super Admin account cannot be deleted.');
        }

        $this->auditLogger->log('deleted', "Deleted user {$user->name}", $user, [
            'attributes' => $user->only(['name', 'email']),
        ]);

        $user->delete();
        $this->flushDashboardCache();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    protected function flushDashboardCache(): void
    {
        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.charts');
    }
}
