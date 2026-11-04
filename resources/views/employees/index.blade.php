<x-layouts.app title="Employee Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Patient Management</span>
        <span>&gt;</span>
        <span>Employees</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Employee Registry</h1>
            <p class="dashboard-subheading mb-0">University faculty members, administrative staff, and personnel health files.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.import') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Employees
            </a>
            @can('employees.create')
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Register Employee
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Personnel</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-person-badge"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $employees->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-building"></i> University-wide</span>
                    <span class="trend-compare">all campuses</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Teaching Faculty</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-award"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Patient::employees()->where('category', 'Faculty')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Instructors</span>
                    <span class="trend-compare">professors</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Non-Teaching Staff</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-briefcase"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Patient::employees()->where('category', 'Non-Teaching / Staff')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Administrative</span>
                    <span class="trend-compare">support staff</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Permanent Status</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Patient::employees()->where('employment_classification', 'Permanent')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Tenured</span>
                    <span class="trend-compare">plantilla items</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by employee ID, name, position...">
            </div>

            <div class="col-md-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories (Faculty / Staff)</option>
                    <option value="Faculty" @selected($category === 'Faculty')>Faculty</option>
                    <option value="Non-Teaching / Staff" @selected($category === 'Non-Teaching / Staff')>Non-Teaching / Staff</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="classification" class="form-select" onchange="this.form.submit()">
                    <option value="">All Employment Classifications</option>
                    <option value="Permanent" @selected($classification === 'Permanent')>Permanent</option>
                    <option value="Contractual" @selected($classification === 'Contractual')>Contractual</option>
                    <option value="Job Order" @selected($classification === 'Job Order')>Job Order</option>
                    <option value="Part-time" @selected($classification === 'Part-time')>Part-time</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                @if ($category || $collegeId || $classification || $search)
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
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
                            <th>EMPLOYEE ID</th>
                            <th>EMPLOYEE NAME</th>
                            <th>CATEGORY &amp; POSITION</th>
                            <th>OFFICE / COLLEGE</th>
                            <th>CLASSIFICATION</th>
                            <th>CONTACT</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">{{ $employee->employee_id }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $employee->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $employee->patient->full_name }}
                                    </a>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $employee->patient->gender }}, {{ $employee->patient->age }} y/o</small>
                                </td>
                                <td>
                                    <span class="badge badge-pill badge-soft-{{ $employee->category === 'Faculty' ? 'blue' : 'green' }}">
                                        {{ $employee->category }}
                                    </span>
                                    <div class="fw-medium text-dark small mt-1">{{ $employee->position }}</div>
                                </td>
                                <td>
                                    <span class="text-dark small">{{ $employee->office_department ?: $employee->college?->name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-pill badge-soft-gray">{{ $employee->employment_classification }}</span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $employee->patient->phone ?: $employee->patient->email }}</span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $employee->employment_status === 'active' ? 'completed' : 'pending' }}">
                                        {{ $employee->employment_status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('patients.show', $employee->patient) }}" class="btn btn-sm btn-light border p-1" title="View Patient Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('employees.edit')
                                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-light border p-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('employees.delete')
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm="Are you sure you want to delete employee {{ $employee->patient->full_name }}?">
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
                                <td colspan="8" class="text-center text-muted py-4">No employee records found matching the filter criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="p-3 border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

