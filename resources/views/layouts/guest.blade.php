@props(['title' => null, 'heading' => null, 'subheading' => null])
@php
    $systemName = config('app.name', 'ClinicAssist');
    $clinicUnitName = $appSettings['clinic_name'] ?? 'University Health Department';
    $universityName = $appSettings['university_name'] ?? 'Surigao del Norte State University';

    $faviconUrl = ! empty($appSettings['logo_compact'])
        ? asset('storage/'.$appSettings['logo_compact'])
        : (! empty($appSettings['logo_primary'])
            ? asset('storage/'.$appSettings['logo_primary'])
            : asset('assets/images/logo.svg'));

    $sealLogoUrl = ! empty($appSettings['logo_seal'])
        ? asset('storage/'.$appSettings['logo_seal'])
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
    <title>{{ $title ? "{$title} — {$systemName}" : "{$systemName} — {$universityName}" }}</title>

    <link rel="icon" href="{{ $faviconUrl }}">
    <script>
        (function () {
            const saved = localStorage.getItem('clinic_theme');
            const theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    @vite(['resources/css/app.css'])

    <style>
        body {
            background-color: var(--app-body-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--app-body-color);
        }
        .univ-top-stripe {
            height: 4px;
            background: linear-gradient(90deg, #696cff 0%, #696cff 78%, #71dd37 78%, #71dd37 100%);
            width: 100%;
        }
        .portal-wrapper {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.25rem 1.25rem 2.5rem;
        }
        .portal-header {
            text-align: center;
            margin-bottom: 1.5rem;
            max-width: 540px;
        }
        .portal-republic {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--app-muted-color);
            margin-bottom: 0.15rem;
        }
        .portal-univ-name {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--app-heading-color);
            line-height: 1.25;
            text-transform: uppercase;
        }
        .portal-dept-name {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--app-primary);
            margin-top: 0.15rem;
        }
        .portal-system-name {
            font-size: 0.74rem;
            color: var(--app-muted-color);
        }
        .portal-card {
            width: 100%;
            max-width: 440px;
            background: var(--app-card-bg);
            border: 1px solid var(--app-border-color);
            border-top: 4px solid var(--app-primary);
            border-radius: var(--app-card-radius);
            box-shadow: var(--app-card-shadow);
        }
        .portal-card-header {
            padding: 1.75rem 1.75rem 1.15rem;
            text-align: center;
            border-bottom: 1px solid var(--app-border-color);
        }
        .portal-seal-img {
            width: 76px;
            height: 76px;
            object-fit: contain;
            margin-bottom: 0.85rem;
        }
        .portal-auth-badge {
            display: inline-block;
            background: var(--app-primary-light);
            color: var(--app-primary);
            border: 1px solid rgba(105, 108, 255, 0.25);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }
        .portal-card-body {
            padding: 1.5rem 1.75rem 1.75rem;
        }
        .portal-legal-notice {
            max-width: 480px;
            text-align: center;
            margin-top: 1.35rem;
            font-size: 0.7rem;
            color: var(--app-muted-color);
            line-height: 1.45;
        }
        .portal-footer {
            flex-shrink: 0;
            text-align: center;
            padding: 1rem;
            font-size: 0.72rem;
            color: var(--app-muted-color);
            border-top: 1px solid var(--app-border-color);
            background: var(--app-card-bg);
        }
    </style>
</head>
<body>
    <div class="univ-top-stripe"></div>

    <main class="portal-wrapper">
        <div class="portal-header">
            <div class="portal-republic">Republic of the Philippines</div>
            <div class="portal-univ-name">{{ $universityName }}</div>
            <div class="portal-dept-name">{{ $clinicUnitName }}</div>
            <div class="portal-system-name">{{ $systemName }} &bull; Electronic Medical Records &amp; Clinical Management System</div>
        </div>

        <div class="portal-card">
            <div class="portal-card-header">
                <img src="{{ $sealLogoUrl }}" alt="{{ $universityName }} Seal" class="portal-seal-img">
                <div>
                    @if ($heading)
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">{{ $heading }}</h5>
                        @if ($subheading)
                            <p class="text-muted small mb-0">{{ $subheading }}</p>
                        @endif
                    @else
                        <span class="portal-auth-badge">
                            <i class="bi bi-shield-lock-fill me-1"></i>Authorized Personnel Sign In
                        </span>
                    @endif
                </div>
            </div>

            <div class="portal-card-body">
                <x-alert />
                {{ $slot }}
            </div>
        </div>

        <div class="portal-legal-notice">
            <i class="bi bi-shield-check me-1"></i>
            This is an official university health information system. Unauthorized access, alteration, or disclosure of confidential patient medical records is strictly prohibited and subject to legal prosecution under <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>.
        </div>
    </main>

    <footer class="portal-footer">
        <div>&copy; {{ now()->year }} {{ $universityName }} &bull; University Health &amp; Medical Services</div>
    </footer>

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @vite(['resources/js/app.js'])
</body>
</html>
