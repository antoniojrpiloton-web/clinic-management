@props(['title' => null])
@php
    $clinicName = $appSettings['clinic_name'] ?? config('app.name');
    $flashMessages = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn (string $type): bool => session()->has($type))
        ->map(fn (string $type): array => ['type' => $type, 'message' => (string) session($type)])
        ->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? "{$title} — {$clinicName}" : $clinicName }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    @stack('styles')
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="app-wrapper">
        <x-sidebar :clinic-name="$clinicName" />
        <div class="sidebar-backdrop" data-toggle="sidebar"></div>

        <div class="app-main">
            <x-topbar />

            <main class="app-content">
                <x-alert />
                {{ $slot }}
            </main>

            <footer class="app-footer d-flex justify-content-between align-items-center border-top bg-white">
                <span>&copy; {{ now()->year }} {{ $clinicName }}. All rights reserved.</span>
                <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
            </footer>
        </div>
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

