<x-layouts.app title="Patient Masterlist">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Masterlist</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Patient Masterlist Registry</h1>
            <p class="dashboard-subheading mb-0">Unified master records for university students, faculty, and administrative personnel.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @can('patients.import')
                <a href="{{ route('patients.import') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import Excel/CSV
                </a>
            @endcan
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-person-plus-fill"></i> Add New Patient
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Patients</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check-circle"></i> Master records</span>
                    <span class="trend-compare">all profiles</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Enrolled Students</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['students']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Students</span>
                    <span class="trend-compare">enrolled cohort</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">University Personnel</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['employees']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Employees</span>
                    <span class="trend-compare">faculty &amp; staff</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Health Files</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-file-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active</span>
                    <span class="trend-compare">verified</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form method="GET" action="{{ route('patients.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Search by name, ID number, email, or phone...">
                </div>
            </div>

            <div class="col-md-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Patient Classifications</option>
                    <option value="student" @selected($type === 'student')>Students</option>
                    <option value="employee" @selected($type === 'employee')>Employees (Faculty &amp; Staff)</option>
                    <option value="other" @selected($type === 'other')>Dependents / Guests</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                @if ($type || $status || $search)
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>PATIENT #</th>
                            <th>PATIENT NAME</th>
                            <th>CLASSIFICATION</th>
                            <th>ID NUMBER</th>
                            <th>COLLEGE / UNIT</th>
                            <th>GENDER &amp; AGE</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-dark">{{ $patient->patient_number }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-initials" style="width: 28px; height: 28px; font-size: 0.7rem;">{{ $patient->initials() }}</span>
                                        <div>
                                            <a href="{{ route('patients.show', $patient) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $patient->full_name }}
                                            </a>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $patient->email ?: ($patient->phone ?: 'N/A') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($patient->type === 'student')
                                        <span class="badge badge-pill badge-soft-blue"><i class="bi bi-mortarboard me-1"></i> Student</span>
                                    @elseif ($patient->type === 'employee')
                                        <span class="badge badge-pill badge-soft-green"><i class="bi bi-person-badge me-1"></i> Employee</span>
                                    @else
                                        <span class="badge badge-pill badge-soft-gray">Other</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold">{{ $patient->primary_identifier ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    @if ($patient->type === 'student')
                                        @if ($patient->college || $patient->program)
                                            <span class="badge badge-pill badge-soft-gray">{{ $patient->college?->code ?? 'N/A' }}</span>
                                            <small class="text-muted ms-1">{{ $patient->program?->code ?? 'N/A' }}</small>
                                        @else
                                            <span class="badge badge-soft-gray">N/A</span>
                                        @endif
                                    @elseif ($patient->type === 'employee')
                                        @if ($patient->office_department || $patient->college)
                                            <small class="text-dark fw-medium">{{ $patient->office_department ?: $patient->college?->name }}</small>
                                        @else
                                            <span class="badge badge-soft-gray">N/A</span>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-gray">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-dark">{{ $patient->gender }}</span>, <small class="text-muted">{{ $patient->age }} y/o</small>
                                </td>
                                <td>
                                    <span class="status-badge {{ $patient->status === 'active' ? 'completed' : 'pending' }}">
                                        {{ $patient->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-light border p-1" title="View Patient Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('patients.edit')
                                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-light border p-1" title="Edit Patient Record">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('patients.delete')
                                            <form method="POST" action="{{ route('patients.destroy', $patient) }}" data-confirm="Are you sure you want to delete patient record for {{ $patient->full_name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger p-1" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No patient records found in the masterlist.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($patients->hasPages())
                <div class="p-3 border-top">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

