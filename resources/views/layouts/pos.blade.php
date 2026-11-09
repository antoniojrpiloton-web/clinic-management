@props(['title' => 'Pharmacy POS Terminal', 'slipNumber' => null])
@php
    $clinicName = $appSettings['clinic_name'] ?? config('app.name');
    $universityName = $appSettings['university_name'] ?? 'Northwestern Visayan Colleges';
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
    <title>{{ $title }} — {{ $clinicName }}</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    @stack('styles')
    @vite(['resources/css/app.css'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background: #e6ebe8;
            overflow-x: hidden;
        }
        @media (min-width: 992px) {
            html, body {
                overflow: hidden;
            }
        }
        .pos-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        .pos-topbar {
            height: 52px;
            flex-shrink: 0;
            background: #182620;
            color: #ffffff;
            border-bottom: 1px solid #243830;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            z-index: 1040;
            white-space: nowrap;
        }
        .pos-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: #ffffff;
            white-space: nowrap;
        }
        .pos-brand-badge:hover {
            color: #b5d2c1;
        }
        .pos-divider {
            width: 1px;
            height: 22px;
            background: rgba(255, 255, 255, 0.16);
        }
        .pos-slip-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #dce3de;
            font-size: 0.75rem;
            padding: 0.2rem 0.65rem;
            border-radius: 0.4rem;
            font-family: var(--bs-font-monospace);
        }
        .pos-top-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.3rem 0.7rem;
            border-radius: 0.4rem;
            white-space: nowrap;
            text-decoration: none;
        }
        .pos-main {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        @media (min-width: 992px) {
            .pos-main {
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="pos-shell">
        <header class="pos-topbar">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard') }}" class="pos-brand-badge" title="Return to Clinic Dashboard">
                    <img src="{{ $faviconUrl }}" alt="Logo" class="rounded bg-white p-1" style="width: 30px; height: 30px; object-fit: contain;">
                    <span class="fw-bold" style="font-size: 0.92rem;">{{ $clinicName }}</span>
                </a>

                <div class="pos-divider d-none d-sm-block"></div>

                <span class="text-info fw-semibold small d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-capsule-pill"></i>
                    <span>Pharmacy POS Dispensing Terminal</span>
                </span>

                @if ($slipNumber)
                    <span class="pos-slip-pill d-none d-md-inline-block">
                        {{ $slipNumber }}
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small d-none d-lg-inline-flex align-items-center gap-2 me-2">
                    <i class="bi bi-clock text-info"></i>
                    <span id="pos-live-clock" class="text-light fw-semibold">{{ now()->format('h:i A') }}</span>
                    <span>&bull; {{ now()->format('M d, Y') }}</span>
                </span>

                <a href="{{ route('inventory.dispensings.index') }}" class="pos-top-btn btn btn-outline-light border-secondary text-light">
                    <i class="bi bi-clock-history"></i>
                    <span class="d-none d-sm-inline">History</span>
                </a>

                <a href="{{ route('dashboard') }}" class="pos-top-btn btn btn-danger">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Exit POS</span>
                </a>
            </div>
        </header>

        <main class="pos-main">
            {{ $slot }}
        </main>
    </div>

    @if ($flashMessages->isNotEmpty())
        <script id="flash-messages" type="application/json">@json($flashMessages)</script>
    @endif

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const clockEl = document.getElementById('pos-live-clock');
            if (clockEl) {
                setInterval(() => {
                    clockEl.textContent = new Date().toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }, 1000);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

