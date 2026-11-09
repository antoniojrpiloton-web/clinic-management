<x-layouts.app title="Patient Visit Logs & Central Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Patient Visit Logs &amp; Central Registry</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Patient Visit Logs &amp; Central Registry</h1>
                <span class="badge badge-soft-blue"><i class="bi bi-shield-check me-1"></i> Patient Records</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">List of clinical consultations, walk-in visits, and doctor appointments.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('logbook.kiosk') }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-display me-1"></i> Entrance Kiosk (<code>/logbook</code>)
            </a>
            <a href="{{ route('reports.clinic.login-records') }}" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                <i class="bi bi-bar-chart-line me-1"></i> 12-Month Log-In Report
            </a>
            <button type="button" class="btn btn-outline-success btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addLogbookModal">
                <i class="bi bi-journal-plus me-1"></i> Log Walk-In
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </button>
            @can('clinic-visits.create')
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm px-3 py-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus-circle me-1"></i> Log Clinical Visit
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('clinic-visits.create', ['type' => 'general']) }}"><i class="bi bi-journal-text text-secondary me-2"></i> General Visit / Walk-in</a></li>
                        <li><a class="dropdown-item" href="{{ route('medical-consultations.create') }}"><i class="bi bi-clipboard2-pulse text-primary me-2"></i> Medical Consultation</a></li>
                        <li><a class="dropdown-item" href="{{ route('dental-consultations.create') }}"><i class="bi bi-emoji-smile text-info me-2"></i> Dental Consultation</a></li>
                        <li><a class="dropdown-item" href="{{ route('emergency-records.create') }}"><i class="bi bi-truck-front text-danger me-2"></i> Emergency Incident</a></li>
                        <li><a class="dropdown-item" href="{{ route('first-aid-records.create') }}"><i class="bi bi-bandaid text-warning me-2"></i> First Aid Treatment</a></li>
                        <li><a class="dropdown-item" href="{{ route('physical-examinations.create') }}"><i class="bi bi-person-check text-purple me-2"></i> Physical Examination</a></li>
                    </ul>
                </div>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Visits Logged</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-journal-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> All encounters</span>
                    <span class="trend-compare">clinical consultations</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Consultations Today</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-clock"></i> Today</span>
                    <span class="trend-compare">{{ date('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Walk-Ins Today</span>
                    <span class="kpi-icon-box cyan">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($logbookStats['total_today'] ?? 0) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Entrance</span>
                    <span class="trend-compare">daily logbook visitors</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Doctor Appointments Today</span>
                    <span class="kpi-icon-box purple">
                        <i class="bi bi-calendar2-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($appointmentStats['total_today'] ?? 0) }}</div>
                <div class="kpi-trend">
                    @if (($appointmentStats['waiting_today'] ?? 0) > 0)
                        <span class="trend-badge neutral"><i class="bi bi-hourglass-split"></i> {{ $appointmentStats['waiting_today'] }} Waiting</span>
                        <span class="trend-compare">waiting for doctor</span>
                    @else
                        <span class="trend-badge up"><i class="bi bi-check2"></i> Scheduled</span>
                        <span class="trend-compare">appointments today</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-3 gap-2 p-1 bg-white border rounded shadow-xs" id="clinicRegistryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ in_array(($activeTab ?? 'visits'), ['logbook', 'appointments']) ? '' : 'active' }} px-3 py-2 fw-semibold" id="tab-visits-btn" data-bs-toggle="pill" data-bs-target="#tab-visits" type="button" role="tab" aria-controls="tab-visits" aria-selected="{{ in_array(($activeTab ?? 'visits'), ['logbook', 'appointments']) ? 'false' : 'true' }}">
                <i class="bi bi-clipboard2-pulse me-1"></i> Patient Clinical Visit Records
                <span class="badge bg-primary ms-1">{{ number_format($stats['total']) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? 'visits') === 'logbook' ? 'active' : '' }} px-3 py-2 fw-semibold" id="tab-logbook-btn" data-bs-toggle="pill" data-bs-target="#tab-logbook" type="button" role="tab" aria-controls="tab-logbook" aria-selected="{{ ($activeTab ?? 'visits') === 'logbook' ? 'true' : 'false' }}">
                <i class="bi bi-journal-check me-1"></i> Daily Clinic Logbook
                <span class="badge bg-secondary ms-1">{{ number_format($logbookStats['total_today'] ?? 0) }}</span>
                @if (($logbookStats['currently_inside'] ?? 0) > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $logbookStats['currently_inside'] }} Inside</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? 'visits') === 'appointments' ? 'active' : '' }} px-3 py-2 fw-semibold" id="tab-appointments-btn" data-bs-toggle="pill" data-bs-target="#tab-appointments" type="button" role="tab" aria-controls="tab-appointments" aria-selected="{{ ($activeTab ?? 'visits') === 'appointments' ? 'true' : 'false' }}">
                <i class="bi bi-calendar2-check me-1"></i> Doctor Appointments
                <span class="badge bg-purple text-white ms-1">{{ count($todayAppointments ?? []) }}</span>
                @if (($appointmentStats['waiting_today'] ?? 0) > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $appointmentStats['waiting_today'] }} Waiting</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content" id="clinicRegistryTabsContent">
        <div class="tab-pane fade {{ in_array(($activeTab ?? 'visits'), ['logbook', 'appointments']) ? '' : 'show active' }}" id="tab-visits" role="tabpanel" aria-labelledby="tab-visits-btn">
            <div class="card mb-3 p-3">
                <form id="filter-form" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search visit #, patient, complaint...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <select name="visit_type" id="filter-type" class="form-select">
                            <option value="">All Service Types</option>
                            @foreach($types as $tKey => $tData)
                                <option value="{{ $tKey }}">{{ $tData['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="patient_classification" id="filter-classification" class="form-select">
                            <option value="">All Classifications</option>
                            <option value="student">Student</option>
                            <option value="employee">Employee (Teaching / Non-Teaching)</option>
                            <option value="visitor">Visitor</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="college_id" id="filter-college" class="form-select">
                            <option value="">All Colleges</option>
                            @foreach($colleges as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From date">
                    </div>

                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                        <button type="reset" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </form>
            </div>

            <div class="card border">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="visits-table" class="table table-hover align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">ACTIONS</th>
                                    <th>VISIT #</th>
                                    <th>PATIENT</th>
                                    <th>TYPE</th>
                                    <th>COMPLAINT</th>
                                    <th>VITALS</th>
                                    <th>DOCTOR / NURSE</th>
                                    <th>STATUS</th>
                                    <th>DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <x-table-skeleton :cols="9" :rows="8" />
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? 'visits') === 'logbook' ? 'show active' : '' }}" id="tab-logbook" role="tabpanel" aria-labelledby="tab-logbook-btn">
            <div class="card mb-3 p-3">
                <form method="GET" action="{{ route('clinic-visits.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="logbook">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="log_search" value="{{ $logbookFilters['log_search'] ?? '' }}" class="form-control border-start-0" placeholder="Search name, ID number, notes...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <select name="visitor_type" class="form-select">
                            <option value="">All Visitor Types</option>
                            <option value="student" @selected(($logbookFilters['visitor_type'] ?? '') === 'student')>Students</option>
                            <option value="employee" @selected(($logbookFilters['visitor_type'] ?? '') === 'employee')>Employees</option>
                            <option value="visitor" @selected(($logbookFilters['visitor_type'] ?? '') === 'visitor')>Visitors / Guests</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="purpose" class="form-select">
                            <option value="">All Visit Purposes</option>
                            @foreach ($purposes as $purp)
                                <option value="{{ $purp }}" @selected(($logbookFilters['purpose'] ?? '') === $purp)>{{ $purp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="log_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="inside" @selected(($logbookFilters['log_status'] ?? '') === 'inside')>Currently Inside</option>
                            <option value="completed" @selected(($logbookFilters['log_status'] ?? '') === 'completed')>Completed (Timed Out)</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="log_date_from" value="{{ $logbookFilters['log_date_from'] ?? '' }}" class="form-control">
                    </div>

                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Apply filter"><i class="bi bi-funnel"></i></button>
                        <a href="{{ route('clinic-visits.index', ['tab' => 'logbook']) }}" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
            </div>

            <div class="card border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-journal-text me-1"></i> Daily Clinic Logbook</span>
                        <span class="text-muted small ms-2">Walk-in visitors and clinic attendance</span>
                    </div>
                    <span class="badge badge-soft-gray">{{ $logs->total() }} total entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>LOG # &amp; DATE</th>
                                    <th>TIME IN / OUT</th>
                                    <th>VISITOR / PATIENT</th>
                                    <th>TYPE &amp; PROGRAM / DEPT</th>
                                    <th>PURPOSE &amp; CLINICAL LINK</th>
                                    <th>STATUS</th>
                                    <th class="text-end" style="min-width: 170px;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>
                                            <span class="fw-mono text-dark fw-bold">{{ $log->log_number }}</span>
                                            <div class="small text-muted">{{ $log->log_date ? $log->log_date->format('M d, Y') : '—' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark"><i class="bi bi-box-arrow-in-right text-success me-1"></i>{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—' }}</div>
                                            <div class="small text-muted">
                                                @if ($log->time_out)
                                                    <i class="bi bi-box-arrow-right text-secondary me-1"></i>{{ \Carbon\Carbon::parse($log->time_out)->format('h:i A') }}
                                                @else
                                                    <span class="badge badge-soft-amber" style="font-size: 0.7rem;"><i class="bi bi-dot"></i> Inside</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $log->full_name }}</div>
                                            <div class="small text-muted">
                                                ID: {{ $log->id_number ?: 'N/A' }}
                                                @if ($log->contact_number)
                                                    &bull; {{ $log->contact_number }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $log->visitor_type === 'student' ? 'badge-soft-green' : ($log->visitor_type === 'faculty' ? 'badge-soft-purple' : ($log->visitor_type === 'staff' ? 'badge-soft-blue' : 'badge-soft-gray')) }}">
                                                {{ $log->visitor_type === 'faculty' ? 'Faculty' : ($log->visitor_type === 'staff' ? 'Staff' : ucfirst($log->visitor_type)) }}
                                            </span>
                                            <div class="small text-muted mt-1">{{ $log->course_or_department ?: '—' }} {{ $log->year_or_position ? '• '.$log->year_or_position : '' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $log->purpose }}</div>
                                            @if ($log->notes)
                                                <div class="small text-muted">{{ $log->notes }}</div>
                                            @endif
                                            @if ($log->clinicVisit)
                                                <div class="mt-1">
                                                    <a href="{{ route('clinic-visits.show', $log->clinicVisit) }}" class="badge badge-soft-purple text-decoration-none">
                                                        <i class="bi bi-link-45deg"></i> Linked: {{ $log->clinicVisit->visit_number }} ({{ $log->clinicVisit->type_label }})
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($log->status === 'inside')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-person-walking me-1"></i>Inside</span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Completed</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if (! $log->time_out)
                                                    <form method="POST" action="{{ route('clinic-logbooks.time-out', $log) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-2" title="Mark Time Out">
                                                            <i class="bi bi-box-arrow-right"></i> Out
                                                        </button>
                                                    </form>
                                                @endif

                                                @if (! $log->clinicVisit)
                                                    <a href="{{ route('clinic-visits.create', ['patient_id' => $log->patient_id, 'logbook_id' => $log->id, 'complaint' => $log->purpose]) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" title="Log Clinic Visit">
                                                        <i class="bi bi-plus-circle me-1"></i> Log Visit
                                                    </a>
                                                @else
                                                    <a href="{{ route('clinic-visits.show', $log->clinicVisit) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Linked Visit">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif

                                                <form method="POST" action="{{ route('clinic-logbooks.destroy', $log) }}" onsubmit="return confirm('Delete logbook entry {{ $log->log_number }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">No daily clinic logbook records found for this filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($logs->hasPages())
                    <div class="card-footer bg-white">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? 'visits') === 'appointments' ? 'show active' : '' }}" id="tab-appointments" role="tabpanel" aria-labelledby="tab-appointments-btn">
            <div class="card border mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-calendar2-check me-1"></i> Today's Appointments</span>
                        <span class="text-muted small ms-2">{{ date('F d, Y') }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                            <i class="bi bi-calendar-plus me-1"></i> Book Appointment
                        </button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="bi bi-calendar-month me-1"></i> View Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>TIME</th>
                                    <th>PRIORITY</th>
                                    <th>PATIENT</th>
                                    <th>DOCTOR</th>
                                    <th>REASON</th>
                                    <th>STATUS</th>
                                    <th class="text-end" style="min-width: 170px;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($todayAppointments as $app)
                                    <tr @class(['table-warning-subtle' => $app->priority_level === 'urgent' && $app->status === 'checked_in'])>
                                        <td>
                                            <div class="fw-bold text-dark"><i class="bi bi-clock me-1 text-primary"></i>{{ $app->appointment_time }}</div>
                                            <div class="small fw-mono text-muted">{{ $app->appointment_number }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $app->priority_badge_class }}">
                                                @if ($app->priority_level === 'urgent')
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                @endif
                                                {{ ucfirst($app->priority_level) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $app->patient?->full_name }}</div>
                                            <div class="small text-muted">{{ $app->patient?->primary_identifier }} &bull; {{ ucfirst($app->patient?->type ?? 'student') }}</div>
                                        </td>
                                        <td>
                                            @if ($app->clinician)
                                                <div class="fw-medium text-dark">{{ $app->clinician->name }}</div>
                                                <div class="small text-muted">{{ $app->clinician->title }}</div>
                                            @else
                                                <span class="text-muted fst-italic">Any Doctor</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $app->reason }}</div>
                                            <span class="badge badge-soft-blue" style="font-size: 0.7rem;">{{ $serviceTypes[$app->service_type] ?? ucfirst($app->service_type) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $app->status_badge_class }}">
                                                {{ $app->status === 'checked_in' ? 'Checked In' : ucfirst($app->status) }}
                                            </span>
                                            @if ($app->clinicVisit)
                                                <div class="mt-1">
                                                    <a href="{{ route('clinic-visits.show', $app->clinicVisit) }}" class="badge badge-soft-purple text-decoration-none">
                                                        <i class="bi bi-link-45deg"></i> Linked: {{ $app->clinicVisit->visit_number }}
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if ($app->status === 'scheduled')
                                                    <form method="POST" action="{{ route('appointments.check-in', $app) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-info text-dark fw-semibold py-1 px-2" title="Check In">
                                                             <i class="bi bi-check2-circle"></i> Check In
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($app->status === 'checked_in' || $app->status === 'scheduled')
                                                    <form method="POST" action="{{ route('appointments.start-consultation', $app) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" title="Start Consultation">
                                                            <i class="bi bi-clipboard2-pulse me-1"></i> Start
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($app->status === 'completed' && $app->clinicVisit)
                                                    <a href="{{ route('clinic-visits.show', $app->clinicVisit) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Linked Visit Record">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bi bi-calendar-check fs-1 d-block mb-2 text-secondary"></i>
                                            No appointments scheduled for today.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addLogbookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('clinic-logbooks.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus text-primary me-2"></i>New Walk-In Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Type</label>
                            <select name="visitor_type" class="form-select" required>
                                <option value="student">Student</option>
                                <option value="employee">Employee (Teaching / Non-Teaching)</option>
                                <option value="visitor">Visitor</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ID Number (Optional)</label>
                            <input type="text" name="id_number" class="form-control" placeholder="ID Number">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program / Department</label>
                            <select name="program_id" class="form-select">
                                <option value="">-- Select Program / N/A --</option>
                                @foreach ($programs as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->code }} &mdash; {{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year / Position</label>
                            <input type="text" name="year_or_position" class="form-control" placeholder="e.g. 1st Year / Staff">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="09XX-XXX-XXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Purpose of Visit</label>
                            <select name="purpose" class="form-select" required>
                                @foreach ($purposes as $pLabel)
                                    <option value="{{ $pLabel }}">{{ $pLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="bookAppointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('appointments.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>Book Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 border rounded bg-light mb-1">
                                <label class="form-label fw-bold text-dark mb-2">Patient Type</label>

                                <div class="intake-mode-toggle mb-3" role="group" aria-label="Patient Type">
                                    <input type="radio" class="btn-check" name="patient_mode" id="modeExistingVisit" value="existing" autocomplete="off" checked>
                                    <label class="btn fw-semibold" for="modeExistingVisit">
                                        <i class="bi bi-search me-1"></i> Existing Patient
                                    </label>

                                    <input type="radio" class="btn-check" name="patient_mode" id="modeNewVisit" value="new" autocomplete="off">
                                    <label class="btn fw-semibold" for="modeNewVisit">
                                        <i class="bi bi-person-plus-fill me-1"></i> New Patient / Walk-in
                                    </label>
                                </div>

                                <div id="existingPatientSectionVisit">
                                    <label class="form-label small fw-semibold text-muted required">Search Patient</label>
                                    <select name="patient_id" id="appointment_patient_id_visit" class="form-select" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search by ID number or name..."></select>
                                    <div class="form-text small text-muted">Search by ID number or name.</div>
                                </div>

                                <div id="newPatientSectionVisit" class="d-none">
                                    <div class="alert alert-soft-success py-2 px-3 small d-flex align-items-center mb-3">
                                        <i class="bi bi-info-circle-fill text-success fs-5 me-2"></i>
                                        <div>
                                            For new patients or walk-ins not yet registered. A record will be created automatically.
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold required">Classification</label>
                                            <select name="new_patient_type" class="form-select form-select-sm" id="newPatientTypeVisit">
                                                <option value="student" selected>Student</option>
                                                <option value="employee">Employee (Teaching / Non-Teaching)</option>
                                                <option value="visitor">Visitor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-none" id="empCategoryWrapperVisit">
                                            <label class="form-label small fw-semibold required">Employee Category</label>
                                            <select name="new_employee_category" class="form-select form-select-sm">
                                                <option value="Faculty" selected>Teaching (Faculty)</option>
                                                <option value="Non-Teaching / Staff">Non-Teaching (Staff)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">ID Number</label>
                                            <input type="text" name="new_id_number" class="form-control form-control-sm" placeholder="ID Number">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold required">Gender</label>
                                            <select name="new_gender" class="form-select form-select-sm">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold required">First Name</label>
                                            <input type="text" name="new_first_name" class="form-control form-control-sm" placeholder="First Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold required">Last Name</label>
                                            <input type="text" name="new_last_name" class="form-control form-control-sm" placeholder="Last Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Program / Department</label>
                                            <select name="new_program_id" class="form-select form-select-sm">
                                                <option value="">-- Select Program (Optional) --</option>
                                                @foreach ($programs as $prog)
                                                    <option value="{{ $prog->id }}">{{ $prog->code }} &mdash; {{ $prog->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Year / Level</label>
                                            <select name="new_year_level" class="form-select form-select-sm">
                                                <option value="1st Year" selected>1st Year</option>
                                                <option value="2nd Year">2nd Year</option>
                                                <option value="3rd Year">3rd Year</option>
                                                <option value="4th Year">4th Year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Contact Number</label>
                                            <input type="text" name="new_phone" class="form-control form-control-sm" placeholder="09XX-XXX-XXXX">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Doctor</label>
                            <select name="clinician_id" class="form-select">
                                <option value="">-- Any Available Doctor --</option>
                                @foreach ($clinicians as $clinician)
                                    <option value="{{ $clinician->id }}">{{ $clinician->name }} &mdash; {{ $clinician->title }} ({{ $clinician->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Service Type</label>
                            <select name="service_type" class="form-select" required>
                                @foreach ($serviceTypes as $stKey => $stLabel)
                                    <option value="{{ $stKey }}" @selected($stKey === 'medical')>{{ $stLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Date</label>
                            <input type="date" name="appointment_date" value="{{ now()->isWeekend() ? now()->next('Monday')->toDateString() : now()->toDateString() }}" class="form-control" min="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Time Slot</label>
                            <select name="appointment_time" class="form-select" required>
                                @foreach ($timeSlots as $slot)
                                    <option value="{{ $slot }}" @selected($slot === '09:00 AM')>{{ $slot }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Priority</label>
                            <select name="priority_level" class="form-select" required>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">Reason for Appointment</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Cough, consultation, medical clearance..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes or remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('styles', 'datatables-styles')
        <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css') }}">
    @endPushOnce

    @pushOnce('scripts', 'datatables-scripts')
        <script src="{{ asset('assets/plugins/datatables/dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#visits-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[8, 'desc']],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('clinic-visits.index') }}",
                        data: function (d) {
                            d.visit_type = $('#filter-type').val();
                            d.patient_classification = $('#filter-classification').val();
                            d.college_id = $('#filter-college').val();
                            d.date_from = $('input[name="date_from"]').val();
                            const customSearch = $('input[name="search"]').val();
                            if (customSearch) {
                                d.search = customSearch;
                            }
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'visit_card', name: 'visit_number', searchable: false },
                        { data: 'patient_card', name: 'patient_id', orderable: false, searchable: false },
                        { data: 'type_badge', name: 'visit_type', searchable: false },
                        { data: 'summary_text', name: 'chief_complaint', searchable: false },
                        { data: 'vitals_summary', name: 'systolic', orderable: false, searchable: false },
                        { data: 'attending', name: 'clinician_id', orderable: false, searchable: false },
                        { data: 'disposition_badge', name: 'status', orderable: false, searchable: false },
                        { data: 'visit_date_formatted', name: 'visit_date', orderable: true, searchable: false }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search visit logs...",
                        emptyTable: "No clinic visit records found",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...'
                    }
                });

                let searchTimer;
                $('input[name="search"]').on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => table.draw(), 300);
                });

                $('#filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-type, #filter-classification, #filter-college, input[name="date_from"]').on('change', function () {
                    table.draw();
                });

                const modeExistingVisit = document.getElementById('modeExistingVisit');
                const modeNewVisit = document.getElementById('modeNewVisit');
                const existingSec = document.getElementById('existingPatientSectionVisit');
                const newSec = document.getElementById('newStudentSectionVisit');
                const patientSelect = document.getElementById('appointment_patient_id_visit');
                const modal = document.getElementById('bookAppointmentModal');

                function updateVisitMode(mode) {
                    if (mode === 'new') {
                        existingSec.classList.add('d-none');
                        newSec.classList.remove('d-none');
                        if (patientSelect) {
                            patientSelect.removeAttribute('required');
                            if (window.jQuery) {
                                window.jQuery(patientSelect).val(null).trigger('change');
                            }
                        }
                        newSec.querySelectorAll('input[name="new_first_name"], input[name="new_last_name"]').forEach(i => i.setAttribute('required', 'required'));
                    } else {
                        existingSec.classList.remove('d-none');
                        newSec.classList.add('d-none');
                        if (patientSelect) {
                            patientSelect.setAttribute('required', 'required');
                        }
                        newSec.querySelectorAll('input[name="new_first_name"], input[name="new_last_name"]').forEach(i => i.removeAttribute('required'));
                    }
                }

                if (modeExistingVisit) modeExistingVisit.addEventListener('change', () => updateVisitMode('existing'));
                if (modeNewVisit) modeNewVisit.addEventListener('change', () => updateVisitMode('new'));

                const visitTypeSelect = document.getElementById('newPatientTypeVisit');
                const empWrapperVisit = document.getElementById('empCategoryWrapperVisit');
                if (visitTypeSelect && empWrapperVisit) {
                    visitTypeSelect.addEventListener('change', function () {
                        if (visitTypeSelect.value === 'employee') {
                            empWrapperVisit.classList.remove('d-none');
                        } else {
                            empWrapperVisit.classList.add('d-none');
                        }
                    });
                }

                if (modal && window.jQuery) {
                    window.jQuery(modal).on('shown.bs.modal', function () {
                        if (patientSelect && !window.jQuery(patientSelect).hasClass('select2-hidden-accessible')) {
                            window.jQuery(patientSelect).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                dropdownParent: window.jQuery(modal),
                                placeholder: window.jQuery(patientSelect).data('placeholder') || 'Search patient...',
                                allowClear: true,
                                ajax: {
                                    url: window.jQuery(patientSelect).data('ajax-url'),
                                    dataType: 'json',
                                    delay: 250,
                                    cache: true,
                                    data: params => ({ q: params.term || '', page: params.page || 1 }),
                                    processResults: (data, params) => ({
                                        results: data.results || [],
                                        pagination: { more: data.pagination ? data.pagination.more : false }
                                    })
                                }
                            });
                        }
                    });
                }
            });
        </script>
    @endPushOnce

    @include('partials.plugins.select2')
</x-layouts.app>
