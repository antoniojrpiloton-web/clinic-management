<x-layouts.app title="Daily Clinic Logbook">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Clinic Operations</span>
        <span>&gt;</span>
        <span>Daily Clinic Logbook</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Daily Clinic Logbook</h1>
                <span class="badge badge-soft-green"><i class="bi bi-journal-check me-1"></i> Attendance Log</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Attendance and visit log for students, employees, and visitors.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('logbook.kiosk') }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-display me-1"></i> Open Public Kiosk Page (<code>/logbook</code>)
            </a>
            <a href="{{ route('reports.clinic.login-records') }}" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                <i class="bi bi-bar-chart-line me-1"></i> 12-Month Log-In Report
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addLogbookModal">
                <i class="bi bi-plus-lg me-1"></i> Add Logbook Entry
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Logged In Today</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-journal-bookmark-fill"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-calendar-check"></i> Today</span>
                    <span class="trend-compare">{{ now()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Currently Inside Clinic</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-person-walking"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['currently_inside']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-clock"></i> Active</span>
                    <span class="trend-compare">awaiting time-out</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Students Today</span>
                    <span class="kpi-icon-box green"><i class="bi bi-mortarboard"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['students_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Students</span>
                    <span class="trend-compare">all academic programs</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Employees Today</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-person-workspace"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees_today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral">Faculty &amp; Staff</span>
                    <span class="trend-compare">logged today</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter Clinic Logbook</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('clinic-logbooks.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search Name / ID / Log #</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search visitor name, ID, purpose...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Classification</label>
                    <select name="visitor_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="student" @selected(($filters['visitor_type'] ?? '') === 'student')>Student</option>
                        <option value="employee" @selected(($filters['visitor_type'] ?? '') === 'employee')>Employee (Teaching / Non-Teaching)</option>
                        <option value="visitor" @selected(($filters['visitor_type'] ?? '') === 'visitor')>Visitor</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Program / Level</label>
                    <select name="program_id" class="form-select form-select-sm">
                        <option value="">All Programs</option>
                        @foreach ($programs as $prog)
                            <option value="{{ $prog->id }}" @selected(($filters['program_id'] ?? '') == $prog->id)>{{ $prog->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Filter">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('clinic-logbooks.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Logbook Entries</span>
            <span class="badge badge-soft-gray">{{ number_format($logs->total()) }} Total Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Log # / Date</th>
                            <th>Time In / Out</th>
                            <th>Visitor Name &amp; ID</th>
                            <th>Classification &amp; Program</th>
                            <th>Purpose / Remarks</th>
                            <th>Contact No.</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->log_number }}</div>
                                    <div class="small text-muted">{{ $log->log_date->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">In: {{ \Carbon\Carbon::parse($log->time_in)->format('h:i A') }}</div>
                                    @if ($log->time_out)
                                        <div class="small text-success fw-semibold">Out: {{ \Carbon\Carbon::parse($log->time_out)->format('h:i A') }}</div>
                                    @else
                                        <span class="badge bg-warning text-dark" style="font-size: 0.68rem;">Inside Clinic</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        @if ($log->patient)
                                            <a href="{{ route('patients.show', $log->patient) }}" class="text-dark text-decoration-none">{{ $log->full_name }}</a>
                                        @else
                                            {{ $log->full_name }}
                                        @endif
                                    </div>
                                    @if ($log->id_number)
                                        <div class="small text-muted">ID: {{ $log->id_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $log->visitor_type === 'student' ? 'badge-soft-blue' : ($log->visitor_type === 'faculty' ? 'badge-soft-purple' : ($log->visitor_type === 'staff' ? 'badge-soft-green' : 'badge-soft-gray')) }}">
                                        {{ $log->visitor_type === 'faculty' ? 'Faculty' : ($log->visitor_type === 'staff' ? 'Staff' : ucfirst($log->visitor_type)) }}
                                    </span>
                                    <div class="small fw-semibold text-dark mt-1">
                                        {{ $log->program?->code ?? ($log->course_or_department ?: '—') }}
                                        @if ($log->year_or_position)
                                            <span class="text-muted fw-normal">({{ $log->year_or_position }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->purpose }}</div>
                                    @if ($log->notes)
                                        <div class="small text-muted">{{ $log->notes }}</div>
                                    @endif
                                    @if ($log->clinicVisit)
                                        <div class="mt-1">
                                            <a href="{{ route('clinic-visits.show', $log->clinicVisit) }}" class="badge badge-soft-purple text-decoration-none">
                                                <i class="bi bi-link-45deg"></i> {{ $log->clinicVisit->visit_number }} ({{ $log->clinicVisit->type_label }})
                                            </a>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="badge badge-soft-gray" style="font-size: 0.65rem;">Logbook Entry Only</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->contact_number ?: '—' }}</td>
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
                                        @if ($log->patient_id)
                                            <a href="{{ route('clinic-visits.create', ['patient_id' => $log->patient_id]) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Log Clinic Visit">
                                                <i class="bi bi-clipboard2-pulse"></i>
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
                                <td colspan="7" class="text-center text-muted py-5">No clinic logbook entries found for the selected filter.</td>
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

    <div class="modal fade" id="addLogbookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('clinic-logbooks.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus text-primary me-2"></i>Add Clinic Logbook Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Visitor Type</label>
                            <select name="visitor_type" class="form-select" required>
                                <option value="student">Student</option>
                                <option value="employee">Employee</option>
                                <option value="visitor">Visitor / Guest</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ID Number (Optional)</label>
                            <input type="text" name="id_number" class="form-control" placeholder="Student / Employee ID">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program / Course / Level</label>
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
                            <label class="form-label">Remarks / Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional details...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Logbook Entry</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

