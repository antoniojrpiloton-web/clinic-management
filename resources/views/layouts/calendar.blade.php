@props(['title' => 'Doctor Appointments Calendar'])
@php
    $systemName = config('app.name', 'ClinicAssist');
    $user = auth()->user();
    $flashMessages = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn (string $type): bool => session()->has($type))
        ->map(fn (string $type): array => ['type' => $type, 'message' => (string) session($type)])
        ->values();
    $faviconUrl = ! empty($appSettings['logo_compact'])
        ? asset('storage/'.$appSettings['logo_compact'])
        : (! empty($appSettings['logo_primary'])
            ? asset('storage/'.$appSettings['logo_primary'])
            : asset('assets/images/logo.svg'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ $systemName }}</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    @stack('styles')
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="min-vh-100 d-flex flex-column" style="background-color: var(--app-body-bg);">
        <header class="app-topbar d-flex align-items-center justify-content-between px-3 px-lg-4 border-bottom bg-white" style="position: sticky; top: 0; z-index: 1020;">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="{{ $faviconUrl }}" alt="Logo" class="rounded p-0.5" style="width: 28px; height: 28px; object-fit: contain;">
                    <span class="fw-bold fs-6 text-dark mb-0">{{ $systemName }}</span>
                </a>

                <div class="vr text-muted opacity-25 d-none d-sm-block" style="height: 20px;"></div>

                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Appointments</span>
                </a>

                <span class="badge badge-soft-blue d-none d-md-inline-flex align-items-center gap-1 py-1 px-2.5">
                    <i class="bi bi-calendar2-range"></i> Calendar
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 d-none d-lg-inline-flex align-items-center gap-1">
                    <i class="bi bi-journal-medical"></i> Patient Visits
                </a>

                <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                    <i class="bi bi-calendar-plus me-1"></i> Book Appointment
                </button>

                <div class="vr text-muted opacity-25 d-none d-sm-block mx-1" style="height: 20px;"></div>

                @if ($user)
                    <div class="d-flex align-items-center gap-2 ps-1">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="d-none d-xl-block lh-1 text-start">
                            <div class="fw-semibold small text-dark">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($user->role_names[0] ?? 'Staff') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        <main class="flex-grow-1 w-100 px-3 px-lg-4 py-3">
            <x-alert />
            {{ $slot }}
        </main>

        <footer class="app-footer d-flex justify-content-between align-items-center border-top bg-white px-3 px-lg-4 py-2 mt-auto">
            <span>&copy; {{ now()->year }} {{ $systemName }}. All rights reserved.</span>
            <span>Appointment Calendar</span>
        </footer>
    </div>

    @if ($flashMessages->isNotEmpty())
        <script id="flash-messages" type="application/json">@json($flashMessages)</script>
    @endif

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
