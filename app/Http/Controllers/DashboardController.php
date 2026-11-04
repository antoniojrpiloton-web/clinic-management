<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:dashboard.view')];
    }

    /**
     * Show the application dashboard.
     */
    public function __invoke(): View
    {
        $stats = Cache::remember('dashboard.stats', now()->addMinutes(5), fn (): array => [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'roles' => Role::count(),
            'backups' => Backup::where('status', Backup::STATUS_COMPLETED)->count(),
            'audit_events' => AuditLog::count(),
        ]);

        $charts = Cache::remember('dashboard.charts', now()->addMinutes(5), fn (): array => [
            'logins' => $this->loginTrend(14),
            'roles' => Role::withCount('users')->orderBy('name')->get(['id', 'name'])
                ->map(fn (Role $role): array => ['label' => $role->name, 'value' => $role->users_count])
                ->all(),
        ]);

        $recentActivity = AuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get(['id', 'user_id', 'event', 'description', 'created_at']);

        $latestBackup = Backup::where('status', Backup::STATUS_COMPLETED)->latest()->first(['id', 'filename', 'size', 'created_at']);

        return view('dashboard.index', compact('stats', 'charts', 'recentActivity', 'latestBackup'));
    }

    /**
     * Successful logins per day for the given number of days.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    protected function loginTrend(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = AuditLog::query()
            ->where('event', 'login')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date->lte(now()); $date->addDay()) {
            /** @var Carbon $date */
            $labels[] = $date->format('M d');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
