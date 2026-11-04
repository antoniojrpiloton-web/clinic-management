@props([
    'id' => 'datatable',
    'url' => null,
    'columns' => [],
    'filters' => null,
    'pageLength' => 25,
])
<div class="table-responsive">
    <table
        id="{{ $id }}"
        data-datatable
        data-page-length="{{ $pageLength }}"
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
            <tbody></tbody>
        @endif
    </table>
</div>

@pushOnce('styles', 'datatables-styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css') }}">
@endPushOnce

@pushOnce('scripts', 'datatables-scripts')
    <script src="{{ asset('assets/plugins/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    @vite(['resources/js/plugins/datatables.js'])
@endPushOnce

