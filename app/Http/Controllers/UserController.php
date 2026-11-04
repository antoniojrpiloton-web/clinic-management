<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SettingService;
use App\Support\DataTableResponse;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.create', only: ['create', 'store']),
            new Middleware('permission:users.edit', only: ['edit', 'update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    /**
     * List users (HTML shell or DataTables JSON).
     */
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

        return DataTableResponse::make($request, $query, fn (User $user): array => [
            'name' => ['name' => $user->name, 'email' => $user->email, 'initials' => $user->initials()],
            'phone' => $user->phone ?? '—',
            'roles' => $user->roles->pluck('name'),
            'status' => $user->is_active
                ? ['text' => 'Active', 'color' => 'success']
                : ['text' => 'Inactive', 'color' => 'secondary'],
            'last_login_at' => $user->last_login_at?->diffForHumans() ?? 'Never',
            'created_at' => $user->created_at->format($dateFormat),
            'actions' => [
                'edit' => $canEdit ? route('users.edit', $user) : null,
                'delete' => $canDelete && $user->isNot($actor) ? route('users.destroy', $user) : null,
                'label' => $user->name,
            ],
        ])
            ->searchable(['name', 'email', 'phone'])
            ->orderable(['name' => 'name', 'status' => 'is_active', 'last_login_at' => 'last_login_at', 'created_at' => 'created_at'])
            ->defaultOrder('created_at', 'desc')
            ->filter(function (Builder $query, Request $request): void {
                $query
                    ->when($request->filled('role'), fn (Builder $q) => $q->role($request->string('role')->toString()))
                    ->when($request->filled('status'), fn (Builder $q) => $q->where('is_active', $request->input('status') === 'active'));
            })
            ->toResponse();
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Store a newly created user.
     */
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

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user->load('roles:id,name'),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Update the user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
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

    /**
     * Delete the user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
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
