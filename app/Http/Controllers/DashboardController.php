<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Models\EmergencyRecord;
use App\Models\FirstAidRecord;
use App\Models\MedicalConsultation;
use App\Models\MedicineDispensing;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return view('dashboard.index', [
            'stats' => [],
            'charts' => [],
            'recentVisits' => collect(),
            'recentDispensings' => collect(),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        if ($request->boolean('refresh')) {
            Cache::forget('dashboard.stats');
            Cache::forget('dashboard.charts');
        }

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
            ->withCount('attendees')
            ->whereDate('scheduled_date', '>=', now())
            ->orderBy('scheduled_date')
            ->limit(4)
            ->get();

        $recentMedicalConsultations = MedicalConsultation::query()
            ->with(['visit.patient'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recentDentalConsultations = DentalConsultation::query()
            ->with(['visit.patient'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recentEmergencyRecords = EmergencyRecord::query()
            ->with(['visit.patient'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recentFirstAidRecords = FirstAidRecord::query()
            ->with(['visit.patient'])
            ->latest('id')
            ->limit(5)
            ->get();

        $recentActivity = AuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'user_id', 'event', 'description', 'ip_address', 'created_at']);

        $shared = compact(
            'stats',
            'charts',
            'recentVisits',
            'recentDispensings',
            'recentPhysicalExams',
            'upcomingSchedules',
            'recentMedicalConsultations',
            'recentDentalConsultations',
            'recentEmergencyRecords',
            'recentFirstAidRecords',
            'recentActivity'
        );

        $sections = [];
        $parts = [
            'alerts',
            'communicable',
            'non_communicable',
            'recent_visits',
            'recent_pe',
            'upcoming_schedules',
            'recent_dispensings',
            'recent_medical',
            'recent_dental',
            'recent_emergency',
            'recent_first_aid',
            'recent_audit',
        ];

        foreach ($parts as $part) {
            $sections[$part] = view('dashboard.partials.table-rows', array_merge($shared, ['part' => $part]))->render();
        }

        return response()->json([
            'stats' => $stats,
            'charts' => $charts,
            'sections' => $sections,
            'generated_at' => now()->format('h:i:s A'),
        ]);
    }
}
