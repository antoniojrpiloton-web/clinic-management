<x-layouts.app title="Feature Release & Turn-Over Control">
    <div class="container-fluid py-3">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-dark text-white px-2.5 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                        <i class="bi bi-key-fill text-warning me-1"></i>MASTER DEVELOPER CONTROL
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                        <i class="bi bi-person-check-fill me-1"></i>{{ auth()->user()->email }}
                    </span>
                </div>
                <h4 class="fw-bold text-dark mb-0">System Turn-Over &amp; Feature Release Control</h4>
                <p class="text-muted small mb-0">
                    Control which modules are unlocked for client accounts based on turn-over / payment milestones. Your account (<code>apiloton1@snsu.edu.ph</code>) always retains 100% access.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <form method="POST" action="{{ route('feature-control.preset') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="milestone" value="25">
                    <button type="submit" class="btn btn-sm {{ $progress['percentage'] === 25 ? 'btn-primary fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-pie-chart me-1"></i>25% Preset (Initial)
                    </button>
                </form>

                <form method="POST" action="{{ route('feature-control.preset') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="milestone" value="50">
                    <button type="submit" class="btn btn-sm {{ $progress['percentage'] === 50 ? 'btn-primary fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-pie-chart-fill me-1"></i>50% Preset
                    </button>
                </form>

                <form method="POST" action="{{ route('feature-control.preset') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="milestone" value="75">
                    <button type="submit" class="btn btn-sm {{ $progress['percentage'] === 75 ? 'btn-primary fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-lightning-charge me-1"></i>75% Preset
                    </button>
                </form>

                <form method="POST" action="{{ route('feature-control.preset') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="milestone" value="100">
                    <button type="submit" class="btn btn-sm {{ $progress['percentage'] === 100 ? 'btn-success fw-bold shadow-sm' : 'btn-outline-success' }}">
                        <i class="bi bi-unlock-fill me-1"></i>100% Full Turn-Over
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark">
                                <i class="bi bi-sliders2-vertical text-primary me-1"></i>
                                Active Client Release Progress: <span class="text-primary">{{ $progress['percentage'] }}%</span>
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                {{ $progress['enabled_count'] }} of {{ $progress['total_count'] }} Modules Unlocked
                            </span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 999px;">
                            <div
                                class="progress-bar {{ $progress['percentage'] === 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                style="width: {{ $progress['percentage'] }}%;"
                                aria-valuenow="{{ $progress['percentage'] }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.73rem;">
                            <span>0%</span>
                            <span class="{{ $progress['percentage'] >= 25 ? 'fw-bold text-primary' : '' }}">25% (Core Registry &amp; Logbook)</span>
                            <span class="{{ $progress['percentage'] >= 50 ? 'fw-bold text-primary' : '' }}">50% (Consultations)</span>
                            <span class="{{ $progress['percentage'] >= 75 ? 'fw-bold text-primary' : '' }}">75% (PE &amp; POS Pharmacy)</span>
                            <span class="{{ $progress['percentage'] === 100 ? 'fw-bold text-success' : '' }}">100% (Reports &amp; Admin)</span>
                        </div>
                    </div>
                    <div class="col-md-4 border-start-md ps-md-4">
                        <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.04em;">
                            Account Protection Status
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-check text-success fs-5 mt-0.5"></i>
                            <div class="small text-dark">
                                Your master account (<code>apiloton1@snsu.edu.ph</code>) is hidden from other users and protected from deletion or modification.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('feature-control.update') }}">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-bold text-dark">
                            <i class="bi bi-layout-sidebar-inset me-1 text-primary"></i>Client Sidebar Behavior for Locked Modules
                        </div>
                        <div class="text-muted small">
                            Choose whether locked modules appear with a padlock badge in the sidebar or are completely hidden from client accounts.
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="feature_lock_display_mode"
                                id="mode_badge"
                                value="badge"
                                @checked($displayMode === 'badge')
                            >
                            <label class="form-check-label small fw-semibold" for="mode_badge">
                                <i class="bi bi-lock-fill text-warning me-1"></i>Show with "Locked" Badge
                            </label>
                        </div>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="feature_lock_display_mode"
                                id="mode_hide"
                                value="hide"
                                @checked($displayMode === 'hide')
                            >
                            <label class="form-check-label small fw-semibold" for="mode_hide">
                                <i class="bi bi-eye-slash text-secondary me-1"></i>Hide Completely from Sidebar
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $groupedFeatures = collect($features)->groupBy('milestone_label');
            @endphp

            <div class="row g-4">
                @foreach ($groupedFeatures as $milestoneLabel => $groupItems)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-layers-half text-primary me-2"></i>{{ $milestoneLabel }}
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    {{ $groupItems->where('enabled', true)->count() }} / {{ $groupItems->count() }} Enabled
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    @foreach ($groupItems as $key => $feature)
                                        <div class="col-md-4">
                                            <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between {{ $feature['enabled'] ? 'border-primary-subtle bg-primary-subtle bg-opacity-10' : 'bg-light bg-opacity-50' }}">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="rounded-2 d-inline-flex align-items-center justify-content-center {{ $feature['enabled'] ? 'bg-primary text-white' : 'bg-secondary bg-opacity-25 text-secondary' }}" style="width: 34px; height: 34px;">
                                                                <i class="bi {{ $feature['icon'] }}"></i>
                                                            </span>
                                                            <div>
                                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $feature['title'] }}</div>
                                                                <span class="badge {{ $feature['enabled'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }}" style="font-size: 0.68rem;">
                                                                    <i class="bi {{ $feature['enabled'] ? 'bi-unlock-fill' : 'bi-lock-fill' }} me-1"></i>
                                                                    {{ $feature['enabled'] ? 'Unlocked for Client' : 'Locked for Client' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="form-check form-switch m-0">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                role="switch"
                                                                id="toggle_{{ $key }}"
                                                                name="features[{{ $key }}]"
                                                                value="1"
                                                                style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                                                @checked($feature['enabled'])
                                                            >
                                                        </div>
                                                    </div>
                                                    <p class="text-muted small mb-0 mt-2">
                                                        {{ $feature['description'] }}
                                                    </p>
                                                </div>
                                                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                                                    <span class="text-muted">Milestone Tranche:</span>
                                                    <span class="fw-semibold text-dark">{{ $feature['milestone'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i>Save Feature Release Configuration
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

