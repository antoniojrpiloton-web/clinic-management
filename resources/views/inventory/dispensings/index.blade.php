<x-layouts.app title="Medicine Dispensing Log">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Medicine Dispensing</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Medicine Dispensing Log</h1>
            <p class="dashboard-subheading mb-0">Record and track pharmaceutical supplies and medicines issued to students, faculty, and staff.</p>
        </div>

        @can('inventory.dispense')
            <a href="{{ route('inventory.dispensings.create') }}" class="btn btn-primary btn-sm px-3 py-2">
                <i class="bi bi-plus-lg me-1"></i> Issue / Dispense Medicine
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-muted small fw-medium text-uppercase">Total Dispensings</span>
                    <div class="fs-2 fw-bold text-dark mt-1">{{ number_format($stats['total_dispensed']) }}</div>
                    <div class="small text-muted mt-1">Dispensing slips issued to date</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-primary small fw-medium text-uppercase">Dispensed Today</span>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ number_format($stats['today_dispensed']) }}</div>
                    <div class="small text-muted mt-1">Patients served today</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border h-100">
                <div class="card-body">
                    <span class="text-success small fw-medium text-uppercase">Total Units Issued</span>
                    <div class="fs-2 fw-bold text-success mt-1">{{ number_format($stats['total_items_dispensed']) }}</div>
                    <div class="small text-muted mt-1">Cumulative medicine doses released</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('inventory.dispensings.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by slip #, patient name, ID number, or diagnosis...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    @if($search)
                        <a href="{{ route('inventory.dispensings.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Slip Number</th>
                        <th>Dispensed Date</th>
                        <th>Patient</th>
                        <th>Prescribing Clinician</th>
                        <th>Items Count</th>
                        <th>Diagnosis / Chief Complaint</th>
                        <th>Dispensed By</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispensings as $d)
                        <tr>
                            <td class="ps-3 font-monospace fw-bold text-dark">
                                <a href="{{ route('inventory.dispensings.show', $d) }}" class="text-decoration-none">
                                    {{ $d->dispensing_number }}
                                </a>
                            </td>
                            <td class="small text-muted">
                                {{ $d->dispensed_date ? $d->dispensed_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('patients.show', $d->patient) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $d->patient->full_name }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $d->patient->primary_identifier }} • {{ ucfirst($d->patient->type) }}</small>
                            </td>
                            <td>
                                @if($d->clinician)
                                    <span class="fw-medium text-dark">{{ $d->clinician->name }}</span>
                                    <small class="text-muted d-block">{{ $d->clinician->title }}</small>
                                @else
                                    <span class="text-muted">Direct / OTC</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-blue">{{ $d->total_items }} items</span>
                            </td>
                            <td class="small text-truncate" style="max-width: 220px;" title="{{ $d->diagnosis }}">
                                {{ $d->diagnosis ?: 'N/A' }}
                            </td>
                            <td class="small text-muted">{{ $d->dispenser?->name ?? 'System' }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="{{ route('inventory.dispensings.show', $d) }}" class="btn btn-sm btn-light border p-1 px-2" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.dispensings.print', $d) }}" target="_blank" class="btn btn-sm btn-light border p-1 px-2 text-primary" title="Print Dispensing Slip">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                No dispensing records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispensings->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $dispensings->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>

