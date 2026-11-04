@props(['type' => null, 'message' => null, 'dismissible' => true])
@php
    $alerts = [
        'success' => ['class' => 'success', 'icon' => 'bi-check-circle-fill'],
        'error' => ['class' => 'danger', 'icon' => 'bi-exclamation-octagon-fill'],
        'warning' => ['class' => 'warning', 'icon' => 'bi-exclamation-triangle-fill'],
        'info' => ['class' => 'info', 'icon' => 'bi-info-circle-fill'],
    ];
@endphp

@if ($message || ! $slot->isEmpty())
    @php($config = $alerts[$type ?? 'info'] ?? $alerts['info'])
    <div {{ $attributes->merge(['class' => 'alert alert-'.$config['class'].($dismissible ? ' alert-dismissible fade show' : ''), 'role' => 'alert']) }}>
        <i class="bi {{ $config['icon'] }} me-2"></i>{{ $message ?? $slot }}
        @if ($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
@else
    @foreach ($alerts as $key => $config)
        @if (session()->has($key))
            <div class="alert alert-{{ $config['class'] }} alert-dismissible fade show" role="alert">
                <i class="bi {{ $config['icon'] }} me-2"></i>{{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-octagon-fill me-2"></i>Please correct the errors below:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endif

