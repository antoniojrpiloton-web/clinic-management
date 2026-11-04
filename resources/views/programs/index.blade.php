<x-layouts.app title="Courses and Programs">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Courses and Programs</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Courses &amp; Degree Programs</h1>
            <p class="dashboard-subheading mb-0">Configure academic curricula, undergraduate tracks, and department affiliations.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('colleges.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-diagram-3"></i> View Colleges
            </a>
            @can('programs.create')
                <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-program-modal">
                    <i class="bi bi-plus-lg"></i> New Program
                </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-book"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $programs->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-mortarboard"></i> Curricula</span>
                    <span class="trend-compare">all degrees</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Degree Tracks</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-patch-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Program::active()->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Enrolling</span>
                    <span class="trend-compare">for patients/students</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Affiliated Colleges</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $colleges->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Departments</span>
                    <span class="trend-compare">mother units</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Average Duration</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <div class="kpi-value">4 Years</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Standard</span>
                    <span class="trend-compare">bachelor curriculum</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form method="GET" action="{{ route('programs.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <select name="college_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Colleges &amp; Departments</option>
                    @foreach ($colleges as $c)
                        <option value="{{ $c->id }}" @selected($collegeFilter == $c->id)>
                            {{ $c->code }} &mdash; {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($collegeFilter)
                <div class="col-auto">
                    <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary">Reset Filter</a>
                </div>
            @endif
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>PROGRAM / DEGREE NAME</th>
                            <th>COLLEGE / DEPARTMENT</th>
                            <th>DEGREE LEVEL</th>
                            <th>DURATION</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $program->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $program->name }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-pill badge-soft-blue">
                                        {{ $program->college->code }}
                                    </span>
                                    <small class="text-muted ms-1">{{ $program->college->name }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-pill badge-soft-gray">{{ $program->degree_level }}</span>
                                </td>
                                <td>{{ $program->years }} Years</td>
                                <td>
                                    <span class="status-badge {{ $program->is_active ? 'completed' : 'pending' }}">
                                        {{ $program->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @can('programs.edit')
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border p-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#edit-program-modal-{{ $program->id }}"
                                                title="Edit"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('programs.delete')
                                            <form method="POST" action="{{ route('programs.destroy', $program) }}" data-confirm="Are you sure you want to delete this program?" class="d-inline">
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

                            <div class="modal fade" id="edit-program-modal-{{ $program->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('programs.update', $program) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Academic Program</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">College / Department</label>
                                                    <select name="college_id" class="form-select" required>
                                                        @foreach ($colleges as $c)
                                                            <option value="{{ $c->id }}" @selected($program->college_id == $c->id)>
                                                                {{ $c->code }} &mdash; {{ $c->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Program Code</label>
                                                    <input type="text" name="code" value="{{ old('code', $program->code) }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Program Title</label>
                                                    <input type="text" name="name" value="{{ old('name', $program->name) }}" class="form-control" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Degree Level</label>
                                                        <select name="degree_level" class="form-select" required>
                                                            <option value="Undergraduate" @selected($program->degree_level === 'Undergraduate')>Undergraduate</option>
                                                            <option value="Postgraduate" @selected($program->degree_level === 'Postgraduate')>Postgraduate</option>
                                                            <option value="Diploma / Certificate" @selected($program->degree_level === 'Diploma / Certificate')>Diploma / Certificate</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Years of Study</label>
                                                        <input type="number" name="years" min="1" max="10" value="{{ old('years', $program->years) }}" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" id="prog_active_{{ $program->id }}" class="form-check-input" @checked($program->is_active)>
                                                    <label class="form-check-label" for="prog_active_{{ $program->id }}">Active status</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No academic programs registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($programs->hasPages())
                <div class="p-3 border-top">
                    {{ $programs->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="create-program-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('programs.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New Degree Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College / Department</label>
                            <select name="college_id" class="form-select" required>
                                <option value="">Select College</option>
                                @foreach ($colleges as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} &mdash; {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Code</label>
                            <input type="text" name="code" placeholder="e.g. BSIT" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Title</label>
                            <input type="text" name="name" placeholder="e.g. Bachelor of Science in Information Technology" class="form-control" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Degree Level</label>
                                <select name="degree_level" class="form-select" required>
                                    <option value="Undergraduate">Undergraduate</option>
                                    <option value="Postgraduate">Postgraduate</option>
                                    <option value="Diploma / Certificate">Diploma / Certificate</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Duration (Years)</label>
                                <input type="number" name="years" min="1" max="10" value="4" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="create_prog_active" class="form-check-input" checked>
                            <label class="form-check-label" for="create_prog_active">Active status</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

