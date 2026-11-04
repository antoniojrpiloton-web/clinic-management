<x-layouts.app title="Backups">
    <x-page-header title="Database Backups" :breadcrumbs="['Backups' => route('backups.index')]">
        @can('backups.create')
            <form method="POST" action="{{ route('backups.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" data-loading-text="Starting…">
                    <i class="bi bi-cloud-arrow-down me-1"></i>Create Backup Now
                </button>
            </form>
        @endcan
    </x-page-header>

    @if (! $queueIsSync && $hasPending)
        <x-alert type="info" :dismissible="false">
            A backup is queued. Ensure a queue worker is running (<code>php artisan queue:work</code>) or run <code>php artisan backup:run --force</code> directly.
        </x-alert>
    @endif

    <div class="card" data-has-pending="{{ (int) $hasPending }}">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="font-monospace small">{{ $backup->filename ?? 'Generating…' }}</td>
                            <td class="text-capitalize">{{ $backup->type }}</td>
                            <td>{{ $backup->isCompleted() ? $backup->humanSize() : '—' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $backup->statusColor() }}">{{ ucfirst($backup->status) }}</span>
                                @if ($backup->error)
                                    <i
                                        class="bi bi-info-circle text-danger ms-1"
                                        data-bs-toggle="tooltip"
                                        title="{{ $backup->error }}"
                                    ></i>
                                @endif
                            </td>
                            <td>{{ $backup->creator?->name ?? 'Scheduler' }}</td>
                            <td>{{ $backup->created_at->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if ($backup->isCompleted())
                                        @can('backups.download')
                                            <a
                                                href="{{ route('backups.download', $backup) }}"
                                                class="btn btn-sm btn-light"
                                                title="Download"
                                            >
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endcan
                                    @endif

                                    @can('backups.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('backups.destroy', $backup) }}"
                                            data-confirm="Backup {{ $backup->filename ?? '#'.$backup->id }} will be permanently deleted."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No backups have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($backups->hasPages())
            <div class="card-footer bg-transparent">
                {{ $backups->links() }}
            </div>
        @endif
    </div>

    @pushOnce('scripts', 'backups-scripts')
        @vite(['resources/js/pages/backups.js'])
    @endPushOnce
</x-layouts.app>

