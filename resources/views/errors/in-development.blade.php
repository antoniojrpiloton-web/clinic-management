<x-layouts.app :title="$title ?? 'Ongoing Development'">
    <div class="container-fluid py-4 px-md-4">
        {{-- Breadcrumb --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small text-muted">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item text-secondary">Milestone Roadmap</li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $title }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ $title }}</h4>
            </div>
            <div>
                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-clock-history me-1"></i> {{ $phaseBadge ?? 'Ongoing Development' }}
                </span>
            </div>
        </div>

        {{-- Main Feature Card --}}
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center mb-4">
                    {{-- Soft Modern Icon --}}
                    <div class="mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 86px; height: 86px; background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                            <i class="bi {{ $icon ?? 'bi-cone-striped' }} fs-1"></i>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-3">
                        <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1 font-mono fw-bold" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                            <i class="bi bi-arrow-repeat me-1"></i> SPRINT STATUS: ONGOING DEVELOPMENT
                        </span>
                    </div>

                    <h3 class="fw-bold text-dark mb-2">{{ $title }}</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 620px; font-size: 0.95rem; line-height: 1.65;">
                        {{ $description }}
                    </p>

                    {{-- Notice Callout --}}
                    <div class="alert alert-light border rounded-3 p-3 text-start mx-auto mb-4" style="max-width: 640px; background: #f8fafc;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5 mt-1 flex-shrink-0"></i>
                            <div class="small text-secondary" style="line-height: 1.55;">
                                <strong class="text-dark d-block mb-1">Paalala sa Project Scope:</strong>
                                Ang module na ito ay <strong>hindi pa kasama sa kasalukuyang 50% Clinical Consultations release</strong>. Ito ay kasalukuyang ginagawa sa susunod na scheduled sprint turnover ({{ $phase }}).
                            </div>
                        </div>
                    </div>

                    {{-- Available Modules Checklist --}}
                    <div class="card border bg-light-subtle rounded-3 p-3 text-start mx-auto mb-4" style="max-width: 640px;">
                        <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle text-success fs-5"></i>
                            <span>Mga Features na 100% Functional sa Kasalukuyang 50% Build:</span>
                        </div>
                        <div class="row g-2 small text-muted">
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Executive Clinic Dashboard
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Patient Masterlist &amp; CSV Import
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Daily Reception Logbook Kiosk
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Appointments &amp; Calendar
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Outpatient Medical Consultations
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Dental Consultations (FDI Odontogram)
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> Acute Emergency Case Records
                            </div>
                            <div class="col-sm-6">
                                <i class="bi bi-check-lg text-success me-1"></i> First Aid Logs &amp; Patient Visit History
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Actions --}}
                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 py-2 rounded-2 fw-semibold shadow-xs">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                        <a href="{{ route('medical-consultations.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-2 fw-semibold">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Medical Consultations
                        </a>
                        <a href="{{ route('dental-consultations.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-2 fw-semibold">
                            <i class="bi bi-emoji-smile me-1"></i> Dental Consultations
                        </a>
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-2 fw-semibold">
                            <i class="bi bi-person-vcard me-1"></i> Patient Masterlist
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
