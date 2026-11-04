@props(['title', 'subtitle' => null, 'breadcrumbs' => []])
<div {{ $attributes->merge(['class' => 'page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4']) }}>
    <div>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-muted mb-0 small">{{ $subtitle }}</p>
        @elseif ($breadcrumbs !== [])
            <x-breadcrumb :items="$breadcrumbs" />
        @endif
    </div>

    @if (isset($actions) && $actions->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @elseif (! $slot->isEmpty())
        <div class="d-flex flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

