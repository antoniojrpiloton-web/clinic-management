@pushOnce('styles', 'flatpickr-styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}">
@endPushOnce

@pushOnce('scripts', 'flatpickr-scripts')
    <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
    @vite(['resources/js/plugins/flatpickr.js'])
@endPushOnce

