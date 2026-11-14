@extends('layouts.app')
@section('title', $title ?? 'Module Under Development')
@section('content')
<div class="container-fluid py-5">
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center mx-auto" style="max-width: 620px; background: #ffffff;">
        <div class="mb-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <i class="bi bi-tools fs-1"></i>
            </div>
        </div>
        <div class="mb-2">
            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 font-mono fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                MILESTONE {{ $phase ?? '3' }} &bull; IN ACTIVE DEVELOPMENT
            </span>
        </div>
        <h4 class="fw-bold text-dark mb-2">{{ $title ?? 'Module Currently in Development' }}</h4>
        <p class="text-muted small mb-4" style="line-height: 1.6;">
            {{ $description ?? 'This clinical module is scheduled for development in the upcoming milestone phase. Clinical records, baseline consultation logs, and patient masterlist remain fully accessible.' }}
        </p>
        <div class="d-flex align-items-center justify-content-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 py-2 rounded-2 fw-bold font-mono shadow-xs">
                <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
            </a>
            <a href="{{ route('medical-consultations.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-2 fw-bold font-mono">
                <i class="bi bi-clipboard2-pulse me-1"></i> View Consultations
            </a>
        </div>
    </div>
</div>
@endsection

