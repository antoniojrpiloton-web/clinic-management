<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\ClinicVisit;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Services\DashboardAnalyticsService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public function __construct(protected DashboardAnalyticsService $analyticsService) {}

    public static function middleware(): array
    {
        return [new Middleware('permission:dashboard.view')];
    }

    public function __invoke(): View
    {
        $stats = Cache::remember('dashboard.stats', now()->addMinutes(2), fn (): array => $this->analyticsService->stats());

        $charts = Cache::remember('dashboard.charts', now()->addMinutes(2), fn (): array => $this->analyticsService->charts());

        $recentVisits = ClinicVisit::query()
            ->with(['patient', 'clinician'])
            ->latest('visit_date')
            ->limit(5)
            ->get();

        $recentDispensings = MedicineDispensing::query()
            ->with(['patient', 'items.medicine'])
            ->latest('dispensed_date')
            ->limit(5)
            ->get();

        $recentPhysicalExams = PhysicalExamination::query()
            ->with('patient')
            ->latest('exam_date')
            ->limit(5)
            ->get();

        $upcomingSchedules = PhysicalExamSchedule::query()
            ->whereDate('scheduled_date', '>=', now())
            ->orderBy('scheduled_date')
            ->limit(4)
            ->get();

        $expiringBatches = MedicineBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiration_date', '<=', now()->addDays(60))
            ->orderBy('expiration_date')
            ->with('medicine:id,generic_name,brand_name,dosage_form')
            ->limit(5)
            ->get();

        $lowStockMedicines = Medicine::query()
            ->where('is_active', true)
            ->with(['batches' => fn ($q) => $q->where('expiration_date', '>', now())])
            ->get()
            ->filter(fn (Medicine $med): bool => $med->stock_on_hand <= $med->reorder_level)
            ->take(5);

        $recentActivity = AuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'user_id', 'event', 'description', 'created_at']);

        $latestBackup = Backup::where('status', Backup::STATUS_COMPLETED)->latest()->first(['id', 'filename', 'size', 'created_at']);

        return view('dashboard.index', compact(
            'stats',
            'charts',
            'recentVisits',
            'recentDispensings',
            'recentPhysicalExams',
            'upcomingSchedules',
            'expiringBatches',
            'lowStockMedicines',
            'recentActivity',
            'latestBackup'
        ));
    }
}
