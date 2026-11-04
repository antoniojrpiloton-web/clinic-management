<x-layouts.app title="Academic Year Management">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Academic Year</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Academic Year Management</h1>
            <p class="dashboard-subheading mb-0">Configure university academic periods, active semester, and term schedules.</p>
        </div>

        @can('academic-years.create')
            <button type="button" class="btn btn-primary btn-sm px-3 py-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#create-ay-modal">
                <i class="bi bi-plus-lg"></i> New Academic Year
            </button>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Terms</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-calendar-range"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $academicYears->total() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-check2"></i> Registered</span>
                    <span class="trend-compare">all records</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Current Active Term</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                </div>
                <div class="kpi-value" style="font-size: 1.25rem;">
                    {{ $academicYears->firstWhere('is_current', true)?->code ?? 'Not Set' }}
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Active</span>
                    <span class="trend-compare">{{ $academicYears->firstWhere('is_current', true)?->semester ?? 'None' }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">1st Semester Terms</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-1-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\AcademicYear::where('semester', '1st Semester')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Primary</span>
                    <span class="trend-compare">fall terms</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">2nd Semester Terms</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-2-circle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ \App\Models\AcademicYear::where('semester', '2nd Semester')->count() }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">Secondary</span>
                    <span class="trend-compare">spring terms</span>
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
                            <th>ACADEMIC YEAR</th>
                            <th>SEMESTER</th>
                            <th>DURATION</th>
                            <th>CURRENT TERM</th>
                            <th>STATUS</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($academicYears as $ay)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $ay->code }}</span>
                                </td>
                                <td>{{ $ay->name }}</td>
                                <td>
                                    <span class="badge badge-pill badge-soft-blue">{{ $ay->semester }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $ay->start_date->format('M d, Y') }} &mdash; {{ $ay->end_date->format('M d, Y') }}
                                </td>
                                <td>
                                    @if ($ay->is_current)
                                        <span class="badge badge-pill badge-soft-green">
                                            <i class="bi bi-check-circle me-1"></i> Current Active
                                        </span>
                                    @else
                                        @can('academic-years.edit')
                                            <form method="POST" action="{{ route('academic-years.set-current', $ay) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">
                                                    Set as Current
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $ay->status === 'active' ? 'completed' : 'pending' }}">
                                        {{ $ay->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @can('academic-years.edit')
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border p-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#edit-ay-modal-{{ $ay->id }}"
                                                title="Edit"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('academic-years.delete')
                                            @if (! $ay->is_current)
                                                <form method="POST" action="{{ route('academic-years.destroy', $ay) }}" data-confirm="Are you sure you want to delete this academic year?" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border text-danger p-1" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="edit-ay-modal-{{ $ay->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('academic-years.update', $ay) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Academic Year</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Code</label>
                                                    <input type="text" name="code" value="{{ old('code', $ay->code) }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Academic Year Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $ay->name) }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Semester</label>
                                                    <select name="semester" class="form-select" required>
                                                        <option value="1st Semester" @selected($ay->semester === '1st Semester')>1st Semester</option>
                                                        <option value="2nd Semester" @selected($ay->semester === '2nd Semester')>2nd Semester</option>
                                                        <option value="Summer / Midyear" @selected($ay->semester === 'Summer / Midyear')>Summer / Midyear</option>
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" name="start_date" value="{{ $ay->start_date->format('Y-m-d') }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" name="end_date" value="{{ $ay->end_date->format('Y-m-d') }}" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="active" @selected($ay->status === 'active')>Active</option>
                                                        <option value="closed" @selected($ay->status === 'closed')>Closed</option>
                                                    </select>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_current" value="1" id="is_current_{{ $ay->id }}" class="form-check-input" @checked($ay->is_current)>
                                                    <label class="form-check-label" for="is_current_{{ $ay->id }}">Set as currently active term</label>
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
                                <td colspan="7" class="text-center text-muted py-4">No academic years defined yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($academicYears->hasPages())
                <div class="p-3 border-top">
                    {{ $academicYears->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="create-ay-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('academic-years.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">New Academic Year</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" placeholder="e.g. AY-2027-2028-1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year Name</label>
                            <input type="text" name="name" placeholder="e.g. Academic Year 2027-2028" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select" required>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer / Midyear">Summer / Midyear</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_current" value="1" id="create_is_current" class="form-check-input">
                            <label class="form-check-label" for="create_is_current">Set as currently active term</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Term</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

