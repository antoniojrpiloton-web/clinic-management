@pushOnce('styles', 'datatables-styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css') }}">
@endPushOnce

@pushOnce('scripts', 'datatables-scripts')
    <script src="{{ asset('assets/plugins/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    @vite(['resources/js/plugins/datatables.js'])
@endPushOnce

