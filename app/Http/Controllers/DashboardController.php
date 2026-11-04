<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:dashboard.view')];
    }

    public function __invoke(): View
    {
        $stats = Cache::remember('dashboard.stats', now()->addMinutes(5), fn (): array => [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'roles' => Role::count(),
            'backups' => Backup::where('status', Backup::STATUS_COMPLETED)->count(),
            'audit_events' => AuditLog::count(),
            'total_revenue' => '$45,231.89',
            'revenue_growth' => '+20.1%',
            'users_display' => '2,350',
            'users_growth' => '+15.3%',
            'orders_display' => '1,234',
            'orders_growth' => '-4.2%',
            'conversion_rate' => '3.42%',
            'conversion_growth' => '+8.7%',
        ]);

        $charts = Cache::remember('dashboard.charts', now()->addMinutes(5), fn (): array => [
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'revenue_overview' => [3800, 2600, 2800, 2200, 2600, 3100, 3900, 3600, 4600, 5000, 5300, 5900],
            'expenses' => [1500, 1350, 9500, 1100, 1200, 1300, 1500, 1600, 1450, 1700, 2100, 2500],
            'profit' => [2400, 1200, 1500, 3800, 4700, 3700, 4200, 5100, 4000, 5300, 6100, 7000],
            'logins' => $this->loginTrend(14),
            'roles' => Role::withCount('users')->orderBy('name')->get(['id', 'name'])
                ->map(fn (Role $role): array => ['label' => $role->name, 'value' => $role->users_count])
                ->all(),
        ]);

        $recentOrders = [
            ['name' => 'John Doe', 'item' => 'Wireless Headphones', 'amount' => '$129.99', 'status' => 'completed'],
            ['name' => 'Jane Smith', 'item' => 'Smart Watch', 'amount' => '$299.99', 'status' => 'pending'],
            ['name' => 'Bob Johnson', 'item' => 'Laptop Stand', 'amount' => '$49.99', 'status' => 'completed'],
            ['name' => 'Alice Williams', 'item' => 'USB-C Cable', 'amount' => '$19.99', 'status' => 'processing'],
            ['name' => 'Charlie Brown', 'item' => 'Keyboard', 'amount' => '$89.99', 'status' => 'completed'],
        ];

        $topProducts = [
            ['name' => 'Wireless Headphones', 'sales' => '1234 sales', 'amount' => '$160,410.00', 'trend' => '12.5%', 'is_up' => true],
            ['name' => 'Smart Watch', 'sales' => '987 sales', 'amount' => '$296,003.00', 'trend' => '8.3%', 'is_up' => true],
            ['name' => 'Laptop Stand', 'sales' => '856 sales', 'amount' => '$42,784.00', 'trend' => '3.2%', 'is_up' => false],
            ['name' => 'USB-C Hub', 'sales' => '743 sales', 'amount' => '$51,901.00', 'trend' => '15.7%', 'is_up' => true],
            ['name' => 'Mechanical Keyboard', 'sales' => '621 sales', 'amount' => '$55,890.00', 'trend' => '6.4%', 'is_up' => true],
        ];

        $recentActivity = AuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get(['id', 'user_id', 'event', 'description', 'created_at']);

        $latestBackup = Backup::where('status', Backup::STATUS_COMPLETED)->latest()->first(['id', 'filename', 'size', 'created_at']);

        return view('dashboard.index', compact('stats', 'charts', 'recentOrders', 'topProducts', 'recentActivity', 'latestBackup'));
    }

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
            $labels[] = $date->format('M d');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
