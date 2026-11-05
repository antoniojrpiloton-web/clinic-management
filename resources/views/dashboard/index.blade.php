<x-layouts.app title="Health Services Dashboard">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <span>Dashboard Overview</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Health Services Executive Dashboard</h1>
            <p class="dashboard-subheading mb-0">Integrated monitoring of clinical encounters, patient records, pharmacy inventory, and physical examination metrics.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clinic-visits.create') }}" class="btn btn-primary btn-sm px-3 py-2">
                <i class="bi bi-plus-circle me-1"></i> New Clinic Visit
            </a>
            <a href="{{ route('inventory.dispensings.create') }}" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-capsule me-1"></i> Dispense Medicine
            </a>
            <a href="{{ route('reports.clinic.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Morbidity Reports
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Clinic Encounters</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-clipboard2-pulse"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_visits']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up"><i class="bi bi-calendar-check"></i> {{ $stats['today_visits'] }} today</span>
                    <span class="trend-compare">{{ $stats['this_month_visits'] }} this month</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Clientele Masterlist</span>
                    <span class="kpi-icon-box green"><i class="bi bi-people"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_patients']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-mortarboard"></i> {{ number_format($stats['students_count']) }} students</span>
                    <span class="trend-compare">{{ number_format($stats['employees_count']) }} employees</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Physical Examinations</span>
                    <span class="kpi-icon-box cyan"><i class="bi bi-person-check"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total_pe']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge info"><i class="bi bi-check2-circle"></i> {{ number_format($stats['completed_pe']) }} completed</span>
                    <span class="trend-compare">annual physical exams</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Medicines Dispensed</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-capsule"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($stats['medicines_dispensed']) }} <small class="fs-6 text-muted fw-normal">units</small></div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral"><i class="bi bi-boxes"></i> {{ number_format($stats['dispensing_orders']) }} orders</span>
                    <span class="trend-compare">total issued</span>
                </div>
            </div>
        </div>
    </div>

    @if($stats['low_stock_count'] > 0 || $stats['expiring_count'] > 0 || $stats['expired_count'] > 0)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-warning border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-3 mb-0 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <div>
                            <strong>Pharmacy Inventory Alerts:</strong>
                            @if($stats['low_stock_count'] > 0)
                                <span class="badge badge-soft-amber me-1">{{ $stats['low_stock_count'] }} item(s) below reorder level</span>
                            @endif
                            @if($stats['expiring_count'] > 0)
                                <span class="badge badge-soft-red me-1">{{ $stats['expiring_count'] }} batch(es) expiring in &le; 30 days</span>
                            @endif
                            @if($stats['expired_count'] > 0)
                                <span class="badge bg-danger me-1">{{ $stats['expired_count'] }} batch(es) already expired</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.expirations') }}" class="btn btn-warning btn-sm">Inspect Expirations</a>
                        <a href="{{ route('inventory.reports') }}" class="btn btn-outline-dark btn-sm">Stock Reports</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Monthly Clinic Utilization (By Service)</h5>
                        <small class="text-muted">6-month trend across Medical, Dental, and Emergency consults</small>
                    </div>
                    <span class="badge badge-soft-blue">Last 6 Months</span>
                </div>
                <div class="card-body px-4 pt-3 pb-3">
                    <div style="height: 310px; position: relative;">
                        <canvas id="monthly-utilization-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Student vs. Employee Consults</h5>
                    <small class="text-muted">Clientele classification distribution</small>
                </div>
                <div class="card-body px-4 pt-3 pb-3 d-flex flex-column align-items-center justify-content-center">
                    <div style="height: 240px; width: 100%; position: relative;">
                        <canvas id="classification-donut-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Medical vs. Dental Encounters</h5>
                    <small class="text-muted">Proportion across all clinical departments</small>
                </div>
                <div class="card-body px-4 pt-3 pb-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="services-breakdown-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Leading Chief Complaints</h5>
                    <small class="text-muted">Symptom classification on check-in</small>
                </div>
                <div class="card-body px-4 pt-3 pb-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="top-complaints-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Annual PE Fitness Assessments</h5>
                    <small class="text-muted">Medical clearance classes (A to E)</small>
                </div>
                <div class="card-body px-4 pt-3 pb-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="fitness-assessment-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                            <i class="bi bi-virus text-danger me-2"></i>Communicable Morbidity Breakdown
                        </h5>
                        <small class="text-muted">Infectious, respiratory &amp; viral cases</small>
                    </div>
                    <span class="badge badge-soft-red">{{ $charts['disease_classification']['communicable_count'] }} total cases</span>
                </div>
                <div class="card-body px-4 py-3">
                    @if(empty($charts['disease_classification']['top_communicable']))
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-shield-check fs-2 text-success d-block mb-2"></i>
                            No communicable illnesses recorded in registry.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Infectious Condition</th>
                                        <th class="text-end">Cases</th>
                                        <th class="text-end" style="width: 140px;">Proportion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalComm = max(1, $charts['disease_classification']['communicable_count']); @endphp
                                    @foreach($charts['disease_classification']['top_communicable'] as $comm)
                                        @php $pct = round(($comm['count'] / $totalComm) * 100, 1); @endphp
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $comm['name'] }}</td>
                                            <td class="text-end fw-bold">{{ $comm['count'] }}</td>
                                            <td class="text-end">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                    <span class="small text-muted" style="min-width: 38px;">{{ $pct }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                            <i class="bi bi-heart-pulse text-primary me-2"></i>Non-Communicable Morbidity Breakdown
                        </h5>
                        <small class="text-muted">Lifestyle, chronic &amp; degenerative cases</small>
                    </div>
                    <span class="badge badge-soft-blue">{{ $charts['disease_classification']['non_communicable_count'] }} total cases</span>
                </div>
                <div class="card-body px-4 py-3">
                    @if(empty($charts['disease_classification']['top_non_communicable']))
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-shield-check fs-2 text-primary d-block mb-2"></i>
                            No chronic conditions recorded in registry.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Chronic Condition</th>
                                        <th class="text-end">Cases</th>
                                        <th class="text-end" style="width: 140px;">Proportion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalNonComm = max(1, $charts['disease_classification']['non_communicable_count']); @endphp
                                    @foreach($charts['disease_classification']['top_non_communicable'] as $nonComm)
                                        @php $pct = round(($nonComm['count'] / $totalNonComm) * 100, 1); @endphp
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $nonComm['name'] }}</td>
                                            <td class="text-end fw-bold">{{ $nonComm['count'] }}</td>
                                            <td class="text-end">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                    <span class="small text-muted" style="min-width: 38px;">{{ $pct }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                            <i class="bi bi-building text-info me-2"></i>Utilization by College / Department
                        </h5>
                        <small class="text-muted">Top institutional origins of patient consultations</small>
                    </div>
                </div>
                <div class="card-body px-4 py-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="college-utilization-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                            <i class="bi bi-capsule text-success me-2"></i>Leading Pharmaceutical Utilization
                        </h5>
                        <small class="text-muted">Highest volume dispensed medicines (in units)</small>
                    </div>
                </div>
                <div class="card-body px-4 py-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="medicine-utilization-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Recent Clinic Visits</h5>
                    <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm">All Visits</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit #</th>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Disposition</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVisits as $visit)
                                    <tr>
                                        <td>
                                            <a href="{{ $visit->detail_url }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                                {{ $visit->visit_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $visit->patient?->full_name ?? '—' }}</div>
                                            <small class="text-muted">{{ ucfirst($visit->patient_classification) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $visit->type_badge }}">{{ $visit->type_label }}</span>
                                        </td>
                                        <td class="small">{{ $visit->visit_date->format('M d, h:i A') }}</td>
                                        <td>
                                            <span class="badge badge-soft-gray">{{ $visit->disposition_label }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No recent clinic visits recorded.</td>
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
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Recent Physical Examination Assessments</h5>
                    <a href="{{ route('physical-examinations.index') }}" class="btn btn-outline-secondary btn-sm">All PE Records</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>PE #</th>
                                    <th>Patient</th>
                                    <th>Exam Date</th>
                                    <th>Fitness Class</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPhysicalExams as $pe)
                                    <tr>
                                        <td>
                                            <a href="{{ route('physical-examinations.show', $pe) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                                {{ $pe->pe_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $pe->patient?->full_name ?? '—' }}</div>
                                            <small class="text-muted">{{ $pe->patient?->primary_identifier }}</small>
                                        </td>
                                        <td class="small">{{ $pe->exam_date->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge {{ $pe->fitness_badge_class }}">{{ $pe->fitness_assessment ?: 'Class A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-gray">{{ ucfirst($pe->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No physical exam assessments recorded yet.</td>
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
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Upcoming Mass PE &amp; Checkup Schedules</h5>
                    <a href="{{ route('physical-exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm">All Schedules</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Schedule #</th>
                                    <th>Title / Target</th>
                                    <th>Date &amp; Venue</th>
                                    <th>Attendees</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingSchedules as $sched)
                                    <tr>
                                        <td>
                                            <a href="{{ route('physical-exam-schedules.show', $sched) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                                {{ $sched->schedule_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $sched->title }}</div>
                                            <small class="text-muted">{{ ucfirst($sched->target_classification) }}</small>
                                        </td>
                                        <td>
                                            <div class="small fw-medium">{{ $sched->scheduled_date->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $sched->venue ?: 'Main Clinic' }}</small>
                                        </td>
                                        <td class="small">
                                            <span class="badge badge-soft-blue">{{ $sched->attendees()->count() }} / {{ $sched->capacity ?: '∞' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $sched->status === 'completed' ? 'badge-soft-green' : 'badge-soft-amber' }}">{{ ucfirst($sched->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No scheduled mass checkups pending.</td>
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
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Recent Pharmacy Dispensary Logs</h5>
                    <a href="{{ route('inventory.dispensings.index') }}" class="btn btn-outline-secondary btn-sm">All Logs</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Dispensing #</th>
                                    <th>Patient</th>
                                    <th>Items Issued</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDispensings as $disp)
                                    <tr>
                                        <td>
                                            <a href="{{ route('inventory.dispensings.show', $disp) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                                {{ $disp->dispensing_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $disp->patient?->full_name ?? 'Walk-in' }}</div>
                                            <small class="text-muted">{{ $disp->items->count() }} item(s)</small>
                                        </td>
                                        <td>
                                            @foreach($disp->items->take(2) as $it)
                                                <span class="badge badge-soft-gray me-1">{{ $it->medicine?->generic_name }} ({{ $it->quantity }})</span>
                                            @endforeach
                                            @if($disp->items->count() > 2)
                                                <small class="text-muted">+{{ $disp->items->count() - 2 }} more</small>
                                            @endif
                                        </td>
                                        <td class="small">{{ $disp->dispensed_date?->format('M d, Y') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No recent dispensary records.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script id="dashboard-chart-data" type="application/json">@json($charts)</script>

    @pushOnce('scripts', 'chartjs-scripts')
        <script src="{{ asset('assets/plugins/chartjs/chart.umd.min.js') }}"></script>
        @vite(['resources/js/pages/dashboard.js'])
    @endPushOnce
</x-layouts.app>
