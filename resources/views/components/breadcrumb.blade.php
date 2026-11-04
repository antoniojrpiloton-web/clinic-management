@props(['items' => []])
@if ($items !== [])
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a>
            </li>
            @foreach ($items as $label => $url)
                @if ($loop->last || ! is_string($label))
                    <li class="breadcrumb-item active" aria-current="page">{{ is_string($label) ? $label : $url }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif

