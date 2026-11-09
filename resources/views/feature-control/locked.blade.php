<x-layouts.app title="Module Locked — Pending Turn-Over Activation">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-sm text-center p-4 p-md-5">
                    <div class="mx-auto mb-3 rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                        <i class="bi bi-lock-fill fs-2"></i>
                    </div>

                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mx-auto mb-2 px-3 py-1 rounded-pill">
                        {{ $feature['milestone_label'] ?? 'Pending Milestone Activation' }}
                    </span>

                    <h4 class="fw-bold text-dark mb-2">{{ $feature['title'] }} is Currently Locked</h4>
                    <p class="text-muted mb-4">
                        {{ $feature['description'] }}
                    </p>

                    <div class="bg-light rounded-3 p-3 mb-4 text-start border">
                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                            <span class="fw-semibold text-dark">Current System Turn-Over Progress</span>
                            <span class="fw-bold text-primary">{{ $progress['percentage'] }}% Unlocked ({{ $progress['enabled_count'] }}/{{ $progress['total_count'] }} Modules)</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $progress['percentage'] }}%;"></div>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            This feature is scheduled under the <strong>{{ $feature['milestone'] }}% Turn-Over Milestone</strong>. Please coordinate with the system developer (<code>apiloton1@snsu.edu.ph</code>) to unlock this module.
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                            <i class="bi bi-arrow-left me-1"></i>Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

