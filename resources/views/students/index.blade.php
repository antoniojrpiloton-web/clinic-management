<x-layouts.app title="Student Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Students</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Student Registry</h1>
            <p class="dashboard-subheading mb-0">Operational student cohort directory with academic programs and clinical records.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.import') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Students
            </a>
            @can('students.create')
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Register Student
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Registered Students</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $students->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Enrolled</span>
                    <span class="trend-compare">all years</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Colleges Represented</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $colleges->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Faculties</span>
                    <span class="trend-compare">academic units</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Degree Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-book"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $programs->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Curricula</span>
                    <span class="trend-compare">active tracks</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Health Files</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-file-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $students->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">100%</span>
                    <span class="trend-compare">verified</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form method="GET" action="{{ route('students.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student ID, name, email...">
            </div>

            <div class="col-md-3">
                <select name="college_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Colleges</option>
                    @foreach ($colleges as $c)
                        <option value="{{ $c->id }}" @selected($collegeId == $c->id)>{{ $c->code }} &mdash; {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="year_level" class="form-select" onchange="this.form.submit()">
                    <option value="">All Year Levels</option>
                    <option value="1st Year" @selected($yearLevel === '1st Year')>1st Year</option>
                    <option value="2nd Year" @selected($yearLevel === '2nd Year')>2nd Year</option>
                    <option value="3rd Year" @selected($yearLevel === '3rd Year')>3rd Year</option>
                    <option value="4th Year" @selected($yearLevel === '4th Year')>4th Year</option>
                    <option value="5th Year" @selected($yearLevel === '5th Year')>5th Year</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                @if ($collegeId || $programId || $yearLevel || $search)
                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
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
                            <th>STUDENT ID</th>
                            <th>STUDENT NAME</th>
                            <th>COLLEGE &amp; PROGRAM</th>
                            <th>YEAR &amp; SECTION</th>
                            <th>CONTACT</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">{{ $student->student_id }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $student->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $student->patient->full_name }}
                                    </a>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $student->patient->gender }}, {{ $student->patient->age }} y/o</small>
                                </td>
                                <td>
                                    <span class="badge badge-pill badge-soft-blue">{{ $student->college->code }}</span>
                                    <small class="text-dark fw-medium ms-1">{{ $student->program->code }}</small>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $student->year_level }}</span>
                                    @if ($student->section)
                                        <small class="text-muted ms-1">({{ $student->section }})</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $student->patient->phone ?: $student->patient->email }}</span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $student->enrollment_status === 'enrolled' ? 'completed' : 'pending' }}">
                                        {{ $student->enrollment_status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('patients.show', $student->patient) }}" class="btn btn-sm btn-light border p-1" title="View Patient Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('students.edit')
                                            <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-light border p-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('students.delete')
                                            <form method="POST" action="{{ route('students.destroy', $student) }}" data-confirm="Are you sure you want to delete student {{ $student->patient->full_name }}?">
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
                                <td colspan="7" class="text-center text-muted py-4">No student records found matching the filter criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="p-3 border-top">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

