<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AuditLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:audit-logs.view')];
    }

    public function index(Request $request, SettingService $settings): View|JsonResponse
    {
        if ($request->ajax()) {
            $dateFormat = $settings->get('date_format', 'M d, Y').' h:i A';

            $query = AuditLog::query()
                ->select(['id', 'user_id', 'event', 'description', 'subject_type', 'ip_address', 'created_at'])
                ->with('user:id,name');

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request): void {
                    if ($request->filled('event')) {
                        $query->where('event', $request->input('event'));
                    }
                    if ($request->filled('user_id')) {
                        $query->where('user_id', $request->integer('user_id'));
                    }
                    if ($request->filled('date_from')) {
                        $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
                    }
                    if ($request->filled('date_to')) {
                        $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
                    }
                    if ($request->filled('search.value')) {
                        $search = trim((string) $request->input('search.value'));
                        $query->where(function ($q) use ($search): void {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhere('ip_address', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('timestamp_col', fn (AuditLog $log): string => '<span class="text-muted small">'.e($log->created_at->format($dateFormat)).'</span>')
                ->addColumn('user_col', fn (AuditLog $log): string => '<span class="fw-semibold text-dark">'.e($log->user?->name ?? 'System').'</span>')
                ->addColumn('event_col', function (AuditLog $log): string {
                    $badgeClass = match ($log->event) {
                        'created' => 'badge-pill badge-green',
                        'updated' => 'badge-pill badge-blue',
                        'deleted' => 'badge-pill badge-red',
                        'login' => 'badge-pill badge-forest',
                        'logout' => 'badge-pill badge-gray',
                        'login_failed' => 'badge-pill badge-amber',
                        default => 'badge-pill badge-gray',
                    };
                    $label = e(str($log->event)->replace('_', ' ')->title());

                    return "<span class=\"badge {$badgeClass}\">{$label}</span>";
                })
                ->addColumn('description_col', fn (AuditLog $log): string => '<span class="text-break">'.e($log->description).'</span>')
                ->addColumn('subject_col', fn (AuditLog $log): string => $log->subject_type ? '<span class="badge badge-pill badge-gray font-monospace">'.e(class_basename($log->subject_type)).'</span>' : '<span class="text-muted">—</span>')
                ->addColumn('ip_address_col', fn (AuditLog $log): string => '<span class="text-muted small font-monospace">'.e($log->ip_address ?? '—').'</span>')
                ->addColumn('actions_col', function (AuditLog $log): string {
                    $url = route('audit-logs.show', $log);

                    return '<button type="button" class="btn btn-sm btn-outline-forest" data-view-url="'.e($url).'" title="View Audit Details"><i class="bi bi-eye me-1"></i>View</button>';
                })
                ->rawColumns(['timestamp_col', 'user_col', 'event_col', 'description_col', 'subject_col', 'ip_address_col', 'actions_col'])
                ->make(true);
        }

        return view('audit-logs.index', [
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'pageLength' => (int) $settings->get('table_page_length', 25),
        ]);
    }

    public function show(Request $request, AuditLog $auditLog): View
    {
        $auditLog->load('user:id,name,email');

        return view($request->ajax() ? 'audit-logs.partials.details' : 'audit-logs.show', compact('auditLog'));
    }
}
