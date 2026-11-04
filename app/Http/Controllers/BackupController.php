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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger, protected BackupService $backupService) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:backups.view', only: ['index']),
            new Middleware('permission:backups.create', only: ['store']),
            new Middleware('permission:backups.download', only: ['download']),
            new Middleware('permission:backups.delete', only: ['destroy']),
        ];
    }

    /**
     * List backups with server-side pagination.
     */
    public function index(): View
    {
        $backups = Backup::query()
            ->with('creator:id,name')
            ->latest()
            ->paginate(15);

        return view('backups.index', [
            'backups' => $backups,
            'hasPending' => $backups->getCollection()->contains('status', Backup::STATUS_PENDING),
            'queueIsSync' => config('queue.default') === 'sync',
        ]);
    }

    /**
     * Queue a new database backup.
     */
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

    /**
     * Download a completed backup file.
     */
    public function download(Backup $backup): StreamedResponse|RedirectResponse
    {
        if (! $backup->isCompleted() || ! $this->backupService->exists($backup)) {
            return back()->with('error', 'The backup file is not available.');
        }

        $this->auditLogger->log('downloaded', "Downloaded backup {$backup->filename}", $backup);

        return Storage::disk('local')->download($this->backupService->path($backup));
    }

    /**
     * Delete a backup and its file.
     */
    public function destroy(Backup $backup): RedirectResponse
    {
        $this->auditLogger->log('deleted', 'Deleted backup '.($backup->filename ?? "#{$backup->id}"), $backup);

        $this->backupService->delete($backup);
        Cache::forget('dashboard.stats');

        return redirect()->route('backups.index')->with('success', 'Backup deleted successfully.');
    }
}
