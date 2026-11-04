<x-layouts.app title="Audit Log Details">
    <x-page-header title="Audit Log Details" :breadcrumbs="['Audit Logs' => route('audit-logs.index'), '#'.$auditLog->id]" />

    <div class="card">
        <div class="card-body">
            @include('audit-logs.partials.details')
        </div>
    </div>
</x-layouts.app>

