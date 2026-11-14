@props([
    'id' => 'datatable',
    'url' => null,
    'columns' => [],
    'filters' => null,
    'pageLength' => null,
])
@php
    $resolvedPageLength = (int) ($pageLength ?? $appSettings['table_page_length'] ?? 25);
@endphp
<div class="table-responsive">
    <table
        id="{{ $id }}"
        data-datatable
        data-page-length="{{ $resolvedPageLength }}"
        @if ($url)
            data-url="{{ $url }}"
            data-columns='@json($columns)'
        @endif
        @if ($filters)
            data-filters="{{ $filters }}"
        @endif
        {{ $attributes->merge(['class' => 'table table-hover align-middle w-100 mb-0']) }}
    >
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column['title'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <x-table-skeleton :cols="max(1, count($columns))" :rows="8" />
            </tbody>
        @endif
    </table>
</div>

@include('partials.plugins.datatables')

