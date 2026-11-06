<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDatabaseBackup;
use App\Models\Backup;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class BackupController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger, protected BackupService $backupService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:backups.view', only: ['index']),
            new Middleware('permission:backups.create', only: ['store']),
            new Middleware('permission:backups.download', only: ['download']),
            new Middleware('permission:backups.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $canDownload = $request->user()?->can('backups.download') ?? false;
            $canDelete = $request->user()?->can('backups.delete') ?? false;

            $query = Backup::query()
                ->with('creator:id,name')
                ->latest();

            return DataTables::eloquent($query)
                ->addColumn('filename_col', fn (Backup $b): string => '<span class="font-monospace fw-semibold text-dark">'.e($b->filename ?? 'Processing snapshot…').'</span>')
                ->addColumn('type_col', fn (Backup $b): string => '<span class="text-capitalize">'.e($b->type).'</span>')
                ->addColumn('size_col', fn (Backup $b): string => $b->isCompleted() ? e($b->humanSize()) : '—')
                ->addColumn('status_col', function (Backup $b): string {
                    $cls = $b->status === 'completed' ? 'completed' : ($b->status === 'pending' ? 'processing' : 'pending');
                    $status = e($b->status);
                    $err = $b->error ? ' <i class="bi bi-info-circle text-danger ms-1" data-bs-toggle="tooltip" title="'.e($b->error).'"></i>' : '';

                    return "<span class=\"status-badge {$cls}\">{$status}</span>{$err}";
                })
                ->addColumn('operator_col', fn (Backup $b): string => e($b->creator?->name ?? 'System'))
                ->addColumn('timestamp_col', fn (Backup $b): string => '<span class="text-muted small">'.$b->created_at->format('M d, Y h:i A').'</span>')
                ->addColumn('actions_col', function (Backup $b) use ($canDownload, $canDelete): string {
                    $html = '<div class="d-inline-flex gap-1">';

                    if ($b->isCompleted() && $canDownload) {
                        $dlUrl = route('backups.download', $b);
                        $html .= "<a href=\"{$dlUrl}\" class=\"btn btn-sm btn-light border p-1\" title=\"Download\"><i class=\"bi bi-download\"></i></a>";
                    }

                    if ($canDelete) {
                        $delUrl = route('backups.destroy', $b);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $label = e($b->filename ?? "#{$b->id}");
                        $html .= "<form method=\"POST\" action=\"{$delUrl}\" data-confirm=\"Backup {$label} will be permanently removed.\">{$csrf}{$method}<button type=\"submit\" class=\"btn btn-sm btn-light border text-danger p-1\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></form>";
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->orderColumn('filename_col', 'filename $1')
                ->orderColumn('type_col', 'type $1')
                ->orderColumn('size_col', 'size $1')
                ->orderColumn('status_col', 'status $1')
                ->orderColumn('timestamp_col', 'created_at $1')
                ->rawColumns(['filename_col', 'type_col', 'status_col', 'timestamp_col', 'actions_col'])
                ->make(true);
        }

        $agg = Backup::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        $dbName = config('database.connections.'.config('database.default').'.database');
        $dbStats = rescue(function () use ($dbName): array {
            $row = DB::selectOne(
                'SELECT COUNT(*) as table_count, COALESCE(SUM(data_length + index_length), 0) as total_size, COALESCE(SUM(table_rows), 0) as total_rows FROM information_schema.tables WHERE table_schema = ?',
                [$dbName]
            );
            $version = DB::selectOne('SELECT VERSION() as v')?->v ?? 'MySQL';

            return [
                'name' => (string) $dbName,
                'driver' => config('database.default'),
                'host' => config('database.connections.'.config('database.default').'.host', '127.0.0.1'),
                'port' => config('database.connections.'.config('database.default').'.port', 3306),
                'version' => (string) $version,
                'table_count' => (int) ($row->table_count ?? 0),
                'total_size' => (int) ($row->total_size ?? 0),
                'total_rows' => (int) ($row->total_rows ?? 0),
            ];
        }, [
            'name' => (string) $dbName,
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'version' => 'MySQL 8',
            'table_count' => 0,
            'total_size' => 0,
            'total_rows' => 0,
        ]);

        return view('backups.index', [
            'backupStats' => [
                'total' => (int) ($agg?->total ?? 0),
                'completed' => (int) ($agg?->completed ?? 0),
            ],
            'dbStats' => $dbStats,
            'hasPending' => ((int) ($agg?->pending ?? 0)) > 0,
            'queueIsSync' => config('queue.default') === 'sync',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $backup = Backup::create([
            'created_by' => $request->user()->id,
            'type' => 'manual',
            'status' => Backup::STATUS_PENDING,
        ]);

        $this->auditLogger->log('created', 'Requested a database backup', $backup);

        CreateDatabaseBackup::dispatch($backup);
        Cache::forget('dashboard.stats');

        return redirect()->route('backups.index')->with('success', 'Backup has been queued and will be available shortly.');
    }

    public function download(Backup $backup): StreamedResponse|RedirectResponse
    {
        if (! $backup->isCompleted() || ! $this->backupService->exists($backup)) {
            return back()->with('error', 'The backup file is not available.');
        }

        $this->auditLogger->log('downloaded', "Downloaded backup {$backup->filename}", $backup);

        return Storage::disk('local')->download($this->backupService->path($backup));
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        $this->auditLogger->log('deleted', 'Deleted backup '.($backup->filename ?? "#{$backup->id}"), $backup);

        $this->backupService->delete($backup);
        Cache::forget('dashboard.stats');

        return redirect()->route('backups.index')->with('success', 'Backup deleted successfully.');
    }
}
