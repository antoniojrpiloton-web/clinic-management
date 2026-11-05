@props(['title' => null, 'heading' => null, 'subheading' => null])
@php
    $clinicName = $appSettings['clinic_name'] ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? "{$title} — {$clinicName}" : $clinicName }}</title>

    @php
        $faviconUrl = ! empty($appSettings['logo_compact'])
            ? asset('storage/'.$appSettings['logo_compact'])
            : (! empty($appSettings['logo_primary'])
                ? asset('storage/'.$appSettings['logo_primary'])
                : asset('assets/images/logo.svg'));
        $loginLogoUrl = ! empty($appSettings['logo_primary'])
            ? asset('storage/'.$appSettings['logo_primary'])
            : (! empty($appSettings['logo_transparent'])
                ? asset('storage/'.$appSettings['logo_transparent'])
                : asset('assets/images/logo.svg'));
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    @vite(['resources/css/app.css'])
</head>
<body>
    <main class="auth-wrapper">
        <div class="card auth-card shadow-lg border-0">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <img src="{{ $loginLogoUrl }}" alt="{{ $clinicName }}" style="max-height: 58px; max-width: 140px; object-fit: contain;" class="mb-3">
                    <h1 class="h4 fw-bold mb-1">{{ $heading ?? $clinicName }}</h1>
                    @if ($subheading)
                        <p class="text-muted small mb-0">{{ $subheading }}</p>
                    @endif
                </div>

                <x-alert />

                {{ $slot }}
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @vite(['resources/js/app.js'])
</body>
</html>

