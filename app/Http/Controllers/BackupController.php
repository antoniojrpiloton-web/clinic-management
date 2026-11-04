<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDatabaseBackup;
use App\Models\Backup;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function index(): View
    {
        $backups = Backup::query()
            ->with('creator:id,name')
            ->latest()
            ->paginate(15);

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
            'backups' => $backups,
            'dbStats' => $dbStats,
            'hasPending' => $backups->getCollection()->contains('status', Backup::STATUS_PENDING),
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
