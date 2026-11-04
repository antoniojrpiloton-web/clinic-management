<dl class="row mb-0">
    <dt class="col-sm-3 text-muted">Timestamp</dt>
    <dd class="col-sm-9">{{ $auditLog->created_at->format('M d, Y h:i:s A') }} ({{ $auditLog->created_at->diffForHumans() }})</dd>

    <dt class="col-sm-3 text-muted">User</dt>
    <dd class="col-sm-9">
        @if ($auditLog->user)
            {{ $auditLog->user->name }} <small class="text-muted">({{ $auditLog->user->email }})</small>
        @else
            System
        @endif
    </dd>

    <dt class="col-sm-3 text-muted">Event</dt>
    <dd class="col-sm-9">
        <span class="badge text-bg-{{ $auditLog->eventColor() }}">{{ str($auditLog->event)->replace('_', ' ')->title() }}</span>
    </dd>

    <dt class="col-sm-3 text-muted">Description</dt>
    <dd class="col-sm-9">{{ $auditLog->description }}</dd>

    @if ($auditLog->subject_type)
        <dt class="col-sm-3 text-muted">Subject</dt>
        <dd class="col-sm-9"><code>{{ class_basename($auditLog->subject_type) }} #{{ $auditLog->subject_id }}</code></dd>
    @endif

    <dt class="col-sm-3 text-muted">IP Address</dt>
    <dd class="col-sm-9">{{ $auditLog->ip_address ?? '—' }}</dd>

    <dt class="col-sm-3 text-muted">User Agent</dt>
    <dd class="col-sm-9 small text-break">{{ $auditLog->user_agent ?: '—' }}</dd>

    @if ($auditLog->properties)
        <dt class="col-sm-3 text-muted">Properties</dt>
        <dd class="col-sm-9 mb-0">
            <pre class="json-view mb-0">{{ json_encode($auditLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </dd>
    @endif
</dl>

