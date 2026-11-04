<x-layouts.app title="Colleges and Departments">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Colleges and Departments</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Colleges &amp; Departments</h1>
            <p class="dashboard-subheading mb-0">Manage university academic units, faculties, and department heads.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('programs.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-flex align-items-center gap-1">
                <i class="bi bi-book"></i> View Programs
            </a>
            @can('colleges.create')
                <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-college-modal">
                    <i class="bi bi-plus-lg"></i> New College
                </button>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Colleges</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $colleges->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-building"></i> Academic units</span>
                    <span class="trend-compare">all campuses</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Colleges</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-patch-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\College::active()->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Operational</span>
                    <span class="trend-compare">accepting records</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Programs</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Program::count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Curricula</span>
                    <span class="trend-compare">active degree tracks</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Undergraduate Units</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-award"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\Program::where('degree_level', 'Undergraduate')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Degree tracks</span>
                    <span class="trend-compare">bachelor programs</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>COLLEGE / FACULTY NAME</th>
                            <th>DEAN / HEAD OF UNIT</th>
                            <th>DEGREE PROGRAMS</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($colleges as $college)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $college->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $college->name }}</div>
                                    @if ($college->description)
                                        <small class="text-muted text-truncate d-block" style="max-width: 320px;">{{ $college->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $college->dean ?: 'Not Assigned' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('programs.index', ['college_id' => $college->id]) }}" class="badge badge-pill badge-soft-blue text-decoration-none">
                                        {{ $college->programs_count }} Programs
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge {{ $college->is_active ? 'completed' : 'pending' }}">
                                        {{ $college->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @can('colleges.edit')
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border p-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#edit-college-modal-{{ $college->id }}"
                                                title="Edit"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('colleges.delete')
                                            <form method="POST" action="{{ route('colleges.destroy', $college) }}" data-confirm="Are you sure you want to delete this college?" class="d-inline">
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

                            <div class="modal fade" id="edit-college-modal-{{ $college->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('colleges.update', $college) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit College</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">College Code</label>
                                                    <input type="text" name="code" value="{{ old('code', $college->code) }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">College Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $college->name) }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Dean / Head</label>
                                                    <input type="text" name="dean" value="{{ old('dean', $college->dean) }}" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" rows="2" class="form-control">{{ old('description', $college->description) }}</textarea>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" id="college_active_{{ $college->id }}" class="form-check-input" @checked($college->is_active)>
                                                    <label class="form-check-label" for="college_active_{{ $college->id }}">Active status</label>
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
                                <td colspan="6" class="text-center text-muted py-4">No colleges registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($colleges->hasPages())
                <div class="p-3 border-top">
                    {{ $colleges->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="create-college-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('colleges.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New College / Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">College Code</label>
                            <input type="text" name="code" placeholder="e.g. CAS" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">College Name</label>
                            <input type="text" name="name" placeholder="e.g. College of Arts and Sciences" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dean / Head of Unit</label>
                            <input type="text" name="dean" placeholder="e.g. Dr. Juan Dela Cruz" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" placeholder="Brief overview of programs and academic focus..." class="form-control"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" id="create_college_active" class="form-check-input" checked>
                            <label class="form-check-label" for="create_college_active">Active status</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create College</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

