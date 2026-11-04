@pushOnce('styles', 'select2-styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2-bootstrap-5-theme.min.css') }}">
@endPushOnce

@pushOnce('scripts', 'select2-scripts')
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    @vite(['resources/js/plugins/select2.js'])
@endPushOnce

