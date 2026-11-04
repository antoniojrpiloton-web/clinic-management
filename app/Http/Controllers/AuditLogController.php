<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\SettingService;
use App\Support\DataTableResponse;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AuditLogController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:audit-logs.view')];
    }

    /**
     * List audit log entries (HTML shell or DataTables JSON).
     */
    public function index(Request $request, SettingService $settings): View|JsonResponse
    {
        if (! $request->ajax()) {
            return view('audit-logs.index', [
                'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
                'users' => User::orderBy('name')->get(['id', 'name']),
                'pageLength' => (int) $settings->get('table_page_length', 25),
            ]);
        }

        $dateFormat = $settings->get('date_format', 'M d, Y').' h:i A';

        $query = AuditLog::query()
            ->select(['id', 'user_id', 'event', 'description', 'subject_type', 'ip_address', 'created_at'])
            ->with('user:id,name');

        return DataTableResponse::make($request, $query, fn (AuditLog $log): array => [
            'created_at' => $log->created_at->format($dateFormat),
            'user' => $log->user?->name ?? 'System',
            'event' => ['text' => str($log->event)->replace('_', ' ')->title()->toString(), 'color' => $log->eventColor()],
            'description' => $log->description,
            'subject' => $log->subject_type ? class_basename($log->subject_type) : '—',
            'ip_address' => $log->ip_address ?? '—',
            'actions' => ['view' => route('audit-logs.show', $log), 'label' => 'entry'],
        ])
            ->searchable(['description', 'ip_address'])
            ->orderable(['created_at' => 'created_at', 'event' => 'event'])
            ->defaultOrder('created_at', 'desc')
            ->filter(function (Builder $query, Request $request): void {
                $query
                    ->when($request->filled('event'), fn (Builder $q) => $q->where('event', $request->input('event')))
                    ->when($request->filled('user_id'), fn (Builder $q) => $q->where('user_id', $request->integer('user_id')))
                    ->when($request->filled('date_from'), fn (Builder $q) => $q->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay()))
                    ->when($request->filled('date_to'), fn (Builder $q) => $q->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay()));
            })
            ->toResponse();
    }

    /**
     * Show a single audit log entry (modal fragment for AJAX requests).
     */
    public function show(Request $request, AuditLog $auditLog): View
    {
        $auditLog->load('user:id,name,email');

        return view($request->ajax() ? 'audit-logs.partials.details' : 'audit-logs.show', compact('auditLog'));
    }
}
