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
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    @vite(['resources/css/app.css'])

    <style>
        body {
            background-color: #f2f5f3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #2c3a33;
        }
        .univ-top-stripe {
            height: 4px;
            background: linear-gradient(90deg, #005a36 0%, #005a36 78%, #d4a017 78%, #d4a017 100%);
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
            color: #617369;
            margin-bottom: 0.15rem;
        }
        .portal-univ-name {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #11231a;
            line-height: 1.25;
            text-transform: uppercase;
        }
        .portal-dept-name {
            font-size: 0.86rem;
            font-weight: 600;
            color: #005a36;
            margin-top: 0.15rem;
        }
        .portal-system-name {
            font-size: 0.74rem;
            color: #617369;
        }
        .portal-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #d0dcd4;
            border-top: 4px solid #005a36;
            border-radius: 6px;
            box-shadow: 0 4px 18px rgba(17, 35, 26, 0.05);
        }
        .portal-card-header {
            padding: 1.75rem 1.75rem 1.15rem;
            text-align: center;
            border-bottom: 1px solid #edf2ef;
        }
        .portal-seal-img {
            width: 76px;
            height: 76px;
            object-fit: contain;
            margin-bottom: 0.85rem;
        }
        .portal-auth-badge {
            display: inline-block;
            background: #eaf3ee;
            color: #005a36;
            border: 1px solid #bed6c8;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.25rem 0.65rem;
            border-radius: 3px;
        }
        .portal-card-body {
            padding: 1.5rem 1.75rem 1.75rem;
        }
        .portal-legal-notice {
            max-width: 480px;
            text-align: center;
            margin-top: 1.35rem;
            font-size: 0.7rem;
            color: #6c7c73;
            line-height: 1.45;
        }
        .portal-footer {
            flex-shrink: 0;
            text-align: center;
            padding: 1rem;
            font-size: 0.72rem;
            color: #798a80;
            border-top: 1px solid #e1e7e3;
            background: #ffffff;
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
