<x-layouts.app title="{{ $schedule->title }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('physical-exam-schedules.index') }}">Scheduled Physical Exams</a>
        <span>&gt;</span>
        <span>{{ $schedule->schedule_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">{{ $schedule->title }}</h1>
                <span class="badge {{ $schedule->status_badge }}">{{ $schedule->status_label }}</span>
                <span class="badge badge-soft-gray font-monospace">{{ $schedule->schedule_number }}</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Target: <span class="fw-semibold text-dark">{{ $schedule->target_group_label }}</span>
                • Date: <span class="fw-semibold text-dark">{{ $schedule->scheduled_date->format('F d, Y') }} ({{ $schedule->time_range }})</span>
                • Venue: <span class="fw-semibold text-dark">{{ $schedule->venue ?: 'Clinic Room' }}</span>
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('physical-exam-schedules.print-roster', $schedule) }}" target="_blank" class="btn btn-outline-success btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Attendee Roster
            </a>
            @can('physical-examinations.create')
                <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modal-add-attendee">
                    <i class="bi bi-person-plus me-1"></i> Add Patient to Roster
                </button>
            @endcan
            @can('physical-examinations.edit')
                <a href="{{ route('physical-exam-schedules.edit', $schedule) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                    <i class="bi bi-pencil me-1"></i> Edit Schedule
                </a>
            @endcan
            <a href="{{ route('physical-exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Total Registered</span>
                    <div class="fs-2 fw-bold text-dark mt-1">{{ number_format($attendees->count()) }}</div>
                    <div class="small text-muted mt-1">Capacity: {{ $schedule->capacity ? number_format($schedule->capacity).' patients' : 'Open' }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-success small fw-medium text-uppercase">Completed / Examined</span>
                    <div class="fs-2 fw-bold text-success mt-1">{{ number_format($attendees->where('status', 'attended')->count()) }}</div>
                    <div class="small text-muted mt-1">Physical exams recorded</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-warning small fw-medium text-uppercase">Pending Examination</span>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ number_format($attendees->where('status', 'pending')->count()) }}</div>
                    <div class="small text-muted mt-1">Awaiting clinical checkup</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-danger small fw-medium text-uppercase">No-Show / Absent</span>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ number_format($attendees->where('status', 'no_show')->count()) }}</div>
                    <div class="small text-muted mt-1">Missed cohort schedule</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Attendee Roster &amp; Screening Status ({{ $attendees->count() }})</h2>
            </div>

            @can('physical-examinations.create')
                <form method="POST" action="{{ route('physical-exam-schedules.populate-attendees', $schedule) }}" class="d-inline" data-confirm="Auto-populate remaining eligible patients from target group?">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-magic me-1"></i> Auto-Populate Cohort Attendees
                    </button>
                </form>
            @endcan
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th>PATIENT &amp; ID</th>
                        <th>COLLEGE &amp; PROGRAM / DEPARTMENT</th>
                        <th>STATUS</th>
                        <th>EXAMINATION RECORD</th>
                        <th class="text-end pe-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendees as $idx => $att)
                        @php
                            $pat = $att->patient;
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div>
                                    <a href="{{ route('patients.show', $pat) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $pat->full_name }}
                                    </a>
                                </div>
                                <div class="small text-muted d-flex align-items-center gap-1 mt-1">
                                    <span class="badge {{ $pat->type === 'student' ? 'badge-blue' : 'badge-green' }} py-0 px-2" style="font-size: 0.7rem;">{{ ucfirst($pat->type) }}</span>
                                    <span>ID: {{ $pat->primary_identifier }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium text-dark">{{ $pat->college?->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $pat->program?->name ?? ($pat->office_department ?: ($pat->position ?: 'N/A')) }} • {{ $pat->year_level ?: 'Staff' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $att->status_badge }}">{{ $att->status_label }}</span>
                                @if($att->attended_at)
                                    <small class="text-muted d-block mt-1">{{ $att->attended_at->format('M d, h:i A') }}</small>
                                @endif
                            </td>
                            <td>
                                @if($att->physicalExamination)
                                    <div>
                                        <a href="{{ route('physical-examinations.show', $att->physicalExamination) }}" class="fw-semibold text-primary text-decoration-none font-monospace small">
                                            {{ $att->physicalExamination->pe_number }}
                                        </a>
                                    </div>
                                    <span class="badge {{ $att->physicalExamination->fitness_badge_class }} py-0 px-2" style="font-size: 0.7rem;">
                                        {{ $att->physicalExamination->fitness_assessment ?: 'Class A' }}
                                    </span>
                                @else
                                    <span class="text-muted small">Not yet examined</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    @if(!$att->physicalExamination)
                                        <a href="{{ route('physical-examinations.create', ['patient_id' => $pat->id]) }}" class="btn btn-sm btn-success py-1 px-2" title="Conduct Physical Exam">
                                            <i class="bi bi-heart-pulse me-1"></i> Conduct PE
                                        </a>
                                    @endif

                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-light border py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form method="POST" action="{{ route('physical-exam-schedules.update-attendee-status', [$schedule, $att]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="attended">
                                                    <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Mark Examined</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('physical-exam-schedules.update-attendee-status', [$schedule, $att]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="no_show">
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> Mark No Show</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('physical-exam-schedules.update-attendee-status', [$schedule, $att]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item text-secondary"><i class="bi bi-clock me-2"></i> Reset to Pending</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('physical-exam-schedules.remove-attendee', [$schedule, $att]) }}" data-confirm="Remove {{ $pat->full_name }} from roster?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Remove from Roster</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-1 text-secondary"></i>
                                No patients registered on this schedule roster yet. Click <strong>Auto-Populate Cohort Attendees</strong> or <strong>Add Patient to Roster</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('physical-examinations.create')
        <div class="modal fade" id="modal-add-attendee" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('physical-exam-schedules.add-attendee', $schedule) }}" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Patient to Schedule Roster</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Select Patient</label>
                                <select name="patient_id" class="form-select" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search patient by ID or name..." required></select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add to Roster</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @include('partials.plugins.select2')
</x-layouts.app>

