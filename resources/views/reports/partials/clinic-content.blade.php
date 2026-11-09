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
                <span class="trend-compare">{{ $reportData['period']['label'] }}</span>
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
                <span class="trend-badge neutral"><i class="bi bi-boxes"></i> {{ number_format($reportData['dispensing']['total_dispensings']) }} orders</span>
                <span class="trend-compare">dispensed via pharmacy</span>
            </div>
        </div>
    </div>
</div>

@php
    $serviceMatrix = $reportData['service_patient_matrix'] ?? null;
    $patientClassReport = $reportData['patient_classification_report'] ?? null;
@endphp

@if ($serviceMatrix)
    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-bold text-dark"><i class="bi bi-clipboard2-data text-primary me-2"></i>1. Medical and Dental Service Reports (By Patient Type &amp; Service Type)</span>
                <small class="text-muted d-block">Cross-tabulation of clinical consultations, emergency/first-aid records, scheduled physical exams, and total clinic visits</small>
            </div>
            <span class="badge badge-soft-blue">
                Total Clinic Visits: {{ number_format($serviceMatrix['visit_totals']['total'] ?? 0) }} &bull; All Services: {{ number_format($serviceMatrix['grand_totals']['total'] ?? 0) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Service Report Category</th>
                            <th class="text-end">Students</th>
                            <th class="text-end">Faculty</th>
                            <th class="text-end">Non-Teaching</th>
                            <th class="text-end">Other Personnel</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceMatrix['rows'] as $row)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $row['service'] }}</td>
                                <td class="text-end">{{ number_format($row['students']) }}</td>
                                <td class="text-end">{{ number_format($row['faculty']) }}</td>
                                <td class="text-end">{{ number_format($row['non_teaching']) }}</td>
                                <td class="text-end">{{ number_format($row['other_personnel']) }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($row['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-semibold">
                            <td>Total Clinic Visits (By Patient Type &amp; Service Type)</td>
                            <td class="text-end">{{ number_format($serviceMatrix['visit_totals']['students'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format($serviceMatrix['visit_totals']['faculty'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format($serviceMatrix['visit_totals']['non_teaching'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format($serviceMatrix['visit_totals']['other_personnel'] ?? 0) }}</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($serviceMatrix['visit_totals']['total'] ?? 0) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td class="text-primary">Grand Total Clinical Services (Including Scheduled PE)</td>
                            <td class="text-end text-primary">{{ number_format($serviceMatrix['grand_totals']['students'] ?? 0) }}</td>
                            <td class="text-end text-primary">{{ number_format($serviceMatrix['grand_totals']['faculty'] ?? 0) }}</td>
                            <td class="text-end text-primary">{{ number_format($serviceMatrix['grand_totals']['non_teaching'] ?? 0) }}</td>
                            <td class="text-end text-primary">{{ number_format($serviceMatrix['grand_totals']['other_personnel'] ?? 0) }}</td>
                            <td class="text-end text-primary">{{ number_format($serviceMatrix['grand_totals']['total'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif

@if ($patientClassReport)
    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-bold text-dark"><i class="bi bi-person-vcard text-success me-2"></i>2. Patient Classification Reports (Centralized Patients Registry)</span>
                <small class="text-muted d-block">Aggregated directly from the centralized <code>patients</code> table across classifications, active status, and new registrations</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-soft-green">Active: {{ number_format($patientClassReport['active_patients']) }}</span>
                <span class="badge badge-soft-gray">Inactive: {{ number_format($patientClassReport['inactive_patients']) }}</span>
                <span class="badge badge-soft-blue">New Registrations: {{ number_format($patientClassReport['new_registrations']) }}</span>
            </div>
        </div>
        <div class="card-body border-bottom bg-light py-3">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">Total Registered</div>
                        <div class="fw-bold fs-5 text-dark">{{ number_format($patientClassReport['total_registered']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">Total Students</div>
                        <div class="fw-bold fs-5 text-primary">{{ number_format($patientClassReport['total_students']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">Total Faculty</div>
                        <div class="fw-bold fs-5 text-dark">{{ number_format($patientClassReport['total_faculty']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">Non-Teaching</div>
                        <div class="fw-bold fs-5 text-dark">{{ number_format($patientClassReport['total_non_teaching']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">Other Personnel</div>
                        <div class="fw-bold fs-5 text-dark">{{ number_format($patientClassReport['total_other_personnel']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="p-2 rounded bg-white border h-100">
                        <div class="small text-muted">New Registrations</div>
                        <div class="fw-bold fs-5 text-success">+{{ number_format($patientClassReport['new_registrations']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient Classification</th>
                            <th class="text-end">Total Registered</th>
                            <th class="text-end">Active Patients</th>
                            <th class="text-end">Inactive Patients</th>
                            <th class="text-end">New Registrations</th>
                            <th class="text-end" style="width: 150px;">Registry Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patientClassReport['rows'] as $pRow)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $pRow['classification'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($pRow['total']) }}</td>
                                <td class="text-end text-success fw-semibold">{{ number_format($pRow['active']) }}</td>
                                <td class="text-end text-muted">{{ number_format($pRow['inactive']) }}</td>
                                <td class="text-end text-primary fw-semibold">+{{ number_format($pRow['new_registrations']) }}</td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $pRow['percentage'] }}%"></div>
                                        </div>
                                        <span class="small text-muted" style="min-width: 42px;">{{ $pRow['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Centralized Patients Registry</td>
                            <td class="text-end">{{ number_format($patientClassReport['total_registered']) }}</td>
                            <td class="text-end text-success">{{ number_format($patientClassReport['active_patients']) }}</td>
                            <td class="text-end text-muted">{{ number_format($patientClassReport['inactive_patients']) }}</td>
                            <td class="text-end text-primary">+{{ number_format($patientClassReport['new_registrations']) }}</td>
                            <td class="text-end">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif

@php
    $medDentalStats = $reportData['medical_dental_records'] ?? null;
    $annualServiceSummaries = $medDentalStats['service_summaries'] ?? [];
    $monthlyComparisonRows = $medDentalStats['monthly_comparison'] ?? [];
    $annualClientDist = $medDentalStats['client_distribution'] ?? ['student_total' => 0, 'employee_total' => 0, 'student_percentage' => 0, 'employee_percentage' => 0];
    $peakUtilMonth = $medDentalStats['peak_month'] ?? ['name' => 'N/A', 'total' => 0];
@endphp

@if ($medDentalStats && ! empty($annualServiceSummaries))
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>Annual Service Totals &amp; Student / Employee Distribution ({{ $medDentalStats['year'] }})</span>
                        <small class="text-muted d-block">Based on Medical and Dental Records annual forms</small>
                    </div>
                    <span class="badge badge-soft-blue">
                        Students: {{ $annualClientDist['student_percentage'] }}% &bull; Employees: {{ $annualClientDist['employee_percentage'] }}%
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Category</th>
                                    <th class="text-end">Students</th>
                                    <th class="text-end">Employees</th>
                                    <th class="text-end">Annual Total</th>
                                    <th class="text-end">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($annualServiceSummaries as $sRow)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $sRow['service'] }}</td>
                                        <td class="text-end">
                                            {{ number_format($sRow['student_total']) }}
                                            <small class="text-muted">({{ $sRow['student_percentage'] }}%)</small>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($sRow['employee_total']) }}
                                            <small class="text-muted">({{ $sRow['employee_percentage'] }}%)</small>
                                        </td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($sRow['annual_total']) }}</td>
                                        <td class="text-end fw-semibold text-muted">{{ $sRow['share_percentage'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>Annual Service Grand Total</td>
                                    <td class="text-end text-primary">{{ number_format($annualClientDist['student_total']) }} ({{ $annualClientDist['student_percentage'] }}%)</td>
                                    <td class="text-end text-success">{{ number_format($annualClientDist['employee_total']) }} ({{ $annualClientDist['employee_percentage'] }}%)</td>
                                    <td class="text-end text-dark">{{ number_format($medDentalStats['grand_total']) }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-bar-chart-steps text-success me-2"></i>Comparison of Monthly Service Utilization ({{ $medDentalStats['year'] }})</span>
                        <small class="text-muted d-block">Monthly Medical, Dental, Emergency, First Aid &amp; PE utilization</small>
                    </div>
                    <span class="badge badge-soft-green">
                        Peak: {{ $peakUtilMonth['name'] }} ({{ number_format($peakUtilMonth['total']) }})
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Med</th>
                                    <th class="text-end">Dent</th>
                                    <th class="text-end">Emerg</th>
                                    <th class="text-end">1st Aid</th>
                                    <th class="text-end">PE</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end" style="width: 105px;">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyComparisonRows as $mRow)
                                    <tr>
                                        <td class="fw-semibold text-dark small">{{ substr($mRow['month_name'], 0, 3) }}</td>
                                        <td class="text-end small">{{ number_format($mRow['medical']) }}</td>
                                        <td class="text-end small">{{ number_format($mRow['dental']) }}</td>
                                        <td class="text-end small">{{ number_format($mRow['emergency']) }}</td>
                                        <td class="text-end small">{{ number_format($mRow['first_aid']) }}</td>
                                        <td class="text-end small">{{ number_format($mRow['scheduled_pe']) }}</td>
                                        <td class="text-end fw-bold small">{{ number_format($mRow['total']) }}</td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 5px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $mRow['percentage'] }}%"></div>
                                                </div>
                                                <span class="small text-muted" style="min-width: 34px; font-size: 0.7rem;">{{ $mRow['percentage'] }}%</span>
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
@endif

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
                <span class="badge badge-soft-red">{{ number_format($reportData['morbidity']['total_communicable']) }} Cases</span>
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
                                    <td class="text-end fw-bold">{{ number_format($comm['count']) }}</td>
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
                <span class="badge badge-soft-blue">{{ number_format($reportData['morbidity']['total_non_communicable']) }} Cases</span>
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
                                    <td class="text-end fw-bold">{{ number_format($nonComm['count']) }}</td>
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
                                    <td class="text-end fw-bold">{{ number_format($comp['count']) }}</td>
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
