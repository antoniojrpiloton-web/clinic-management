<x-layouts.app title="NVC Clinic Morbidity & Aggregated Reports">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Reports Generation</span>
        <span>&gt;</span>
        <span>Clinic Morbidity &amp; Utilization</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">NVC Clinic Morbidity &amp; Utilization Reports</h1>
                <span class="badge badge-soft-blue"><i class="bi bi-cpu me-1"></i> Data Aggregation Algorithm</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Multi-dimensional rollups of clinical encounters, communicable/non-communicable morbidity trends, and pharmaceutical dispensing.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.clinic.print-pdf', request()->query()) }}" target="_blank" class="btn btn-primary px-3 py-2">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export Official PDF Report
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter &amp; Aggregation Parameters</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.clinic.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" @selected($filters['academic_year_id'] == $ay->id)>
                                {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">College / Department</label>
                    <select name="college_id" class="form-select form-select-sm">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $col)
                            <option value="{{ $col->id }}" @selected($filters['college_id'] == $col->id)>
                                {{ $col->code ?: $col->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Classification</label>
                    <select name="patient_classification" class="form-select form-select-sm">
                        <option value="">All Classifications</option>
                        <option value="student" @selected($filters['patient_classification'] === 'student')>Students Only</option>
                        <option value="faculty" @selected($filters['patient_classification'] === 'faculty')>Faculty Only</option>
                        <option value="staff" @selected($filters['patient_classification'] === 'staff')>Staff Only</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('reports.clinic.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        Reset Filters
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-arrow-repeat me-1"></i> Aggregate &amp; Recalculate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Aggregated Clinic Encounters</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-clipboard2-pulse"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($reportData['summary']['total_visits']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-clock-history"></i> Encounters</span>
                    <span class="trend-compare">within reporting period</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Unduplicated Patients Served</span>
                    <span class="kpi-icon-box green"><i class="bi bi-people"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($reportData['summary']['unique_patients']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-person-check"></i> Unique IDs</span>
                    <span class="trend-compare">distinct individuals</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Pharmaceuticals Issued</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-capsule"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($reportData['summary']['total_medicines_dispensed']) }} <small class="fs-6 text-muted fw-normal">units</small></div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-boxes"></i> {{ $reportData['dispensing']['total_dispensings'] }} orders</span>
                    <span class="trend-compare">dispensed via pharmacy</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-heart-pulse text-primary me-2"></i>Service Category Rollup</span>
                    <span class="badge badge-soft-gray">5 Categories</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Clinic Service Type</th>
                                    <th class="text-end">Visits</th>
                                    <th class="text-end" style="width: 140px;">Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalV = max(1, $reportData['summary']['total_visits']); @endphp
                                @foreach($reportData['service_distribution'] as $serv => $cnt)
                                    @php $pct = round(($cnt / $totalV) * 100, 1); @endphp
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $serv }}</td>
                                        <td class="text-end fw-bold">{{ number_format($cnt) }}</td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="small text-muted" style="min-width: 40px;">{{ $pct }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-people text-success me-2"></i>Patient Classification Rollup</span>
                    <span class="badge badge-soft-gray">Demographics</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Clientele Classification</th>
                                    <th class="text-end">Visits</th>
                                    <th class="text-end" style="width: 140px;">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['classification_rollup'] as $classif => $cnt)
                                    @php $pct = round(($cnt / $totalV) * 100, 1); @endphp
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $classif }}</td>
                                        <td class="text-end fw-bold">{{ number_format($cnt) }}</td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="small text-muted" style="min-width: 40px;">{{ $pct }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-virus text-danger me-2"></i>Communicable Morbidity Aggregation</span>
                        <small class="text-muted d-block">Respiratory, viral, and transmissible conditions</small>
                    </div>
                    <span class="badge badge-soft-red">{{ $reportData['morbidity']['total_communicable'] }} Cases</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aggregated Morbidity Group</th>
                                    <th class="text-end">Cases</th>
                                    <th class="text-end">Morbidity %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['morbidity']['communicable'] as $comm)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $comm['name'] }}</td>
                                        <td class="text-end fw-bold">{{ $comm['count'] }}</td>
                                        <td class="text-end text-danger fw-semibold">{{ $comm['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No communicable illnesses recorded in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-activity text-primary me-2"></i>Non-Communicable Morbidity Aggregation</span>
                        <small class="text-muted d-block">Chronic, lifestyle, and physiological disorders</small>
                    </div>
                    <span class="badge badge-soft-blue">{{ $reportData['morbidity']['total_non_communicable'] }} Cases</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aggregated Chronic Condition</th>
                                    <th class="text-end">Cases</th>
                                    <th class="text-end">Morbidity %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['morbidity']['non_communicable'] as $nonComm)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $nonComm['name'] }}</td>
                                        <td class="text-end fw-bold">{{ $nonComm['count'] }}</td>
                                        <td class="text-end text-primary fw-semibold">{{ $nonComm['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No chronic conditions recorded in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold text-dark"><i class="bi bi-chat-square-text text-purple me-2"></i>Leading Chief Complaints</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Reported Concern</th>
                                    <th class="text-end">Encounters</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['complaints'] as $comp)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $comp['name'] }}</td>
                                        <td class="text-end fw-bold">{{ $comp['count'] }}</td>
                                        <td class="text-end fw-semibold text-muted">{{ $comp['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No complaints recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold text-dark"><i class="bi bi-capsule text-amber me-2"></i>Leading Dispensed Pharmaceuticals</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Formulary Medicine</th>
                                    <th class="text-end">Units Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['dispensing']['top_medicines'] as $med)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $med['name'] }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($med['quantity']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No medicine dispensings in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

