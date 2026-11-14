<x-layouts.app :title="$title ?? 'Module Under Development'">
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted" style="width: 72px; height: 72px;">
                            <i class="bi bi-cone-striped fs-2 text-warning"></i>
                        </span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1">{{ $title ?? 'Module Under Development' }}</h4>
                    <div class="mb-3">
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 small">In Development</span>
                    </div>

                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        {{ $description ?? 'This module is currently being developed and will be enabled in the upcoming system update.' }}
                    </p>

                    <div>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm px-4 py-2">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
