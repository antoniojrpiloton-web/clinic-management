<x-layouts.app title="Health Services Dashboard">
    <style>
        @keyframes dashboardShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .dash-skeleton {
            display: inline-block;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: dashboardShimmer 1.4s infinite linear;
            border-radius: 6px;
            vertical-align: middle;
        }
        .dash-skeleton-lg {
            height: 30px;
            width: 110px;
            border-radius: 8px;
        }
        .dash-skeleton-sm {
            height: 16px;
            width: 85px;
        }
        .dash-skeleton-row {
            height: 14px;
            width: 100%;
        }
        .chart-loader-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(2px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 5;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .chart-loader-overlay.is-loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .db-stat-tile {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            text-decoration: none;
            display: block;
            height: 100%;
        }
        .db-stat-tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="breadcrumb-nav mb-0">
            <i class="bi bi-house"></i>
            <span>&gt;</span>
            <span>Dashboard Overview</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="dashboard-sync-status" class="badge badge-soft-blue d-inline-flex align-items-center gap-1 py-1 px-2">
                <span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;" role="status"></span>
                <span>Loading live clinic data...</span>
            </span>
            <button type="button" id="btn-refresh-dashboard" class="btn btn-light border btn-sm px-2 py-1" title="Refresh Live Metrics">
                <i class="bi bi-arrow-clockwise me-1" id="refresh-icon"></i>Refresh
            </button>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">NVC Health Services Executive Dashboard</h1>
            <p class="dashboard-subheading mb-0">Data aggregation &amp; real-time clinical monitoring across patient registries, consultations, dental cards, and pharmacy inventory.</p>
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

    <div class="row g-3 mb-4" id="dashboard-kpi-cards" data-metrics-url="{{ route('dashboard.metrics') }}">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Clinic Encounters</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-clipboard2-pulse"></i></span>
                </div>
                <div class="kpi-value" data-kpi="total_visits">
                    <span class="dash-skeleton dash-skeleton-lg"></span>
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge up" data-kpi="today_visits_badge">
                        <span class="dash-skeleton dash-skeleton-sm"></span>
                    </span>
                    <span class="trend-compare" data-kpi="this_month_visits_label">
                        <span class="dash-skeleton dash-skeleton-sm" style="width: 65px;"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Clientele Masterlist</span>
                    <span class="kpi-icon-box green"><i class="bi bi-people"></i></span>
                </div>
                <div class="kpi-value" data-kpi="total_patients">
                    <span class="dash-skeleton dash-skeleton-lg"></span>
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral" data-kpi="students_count_badge">
                        <span class="dash-skeleton dash-skeleton-sm"></span>
                    </span>
                    <span class="trend-compare" data-kpi="employees_count_label">
                        <span class="dash-skeleton dash-skeleton-sm" style="width: 65px;"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Physical Examinations</span>
                    <span class="kpi-icon-box cyan"><i class="bi bi-person-check"></i></span>
                </div>
                <div class="kpi-value" data-kpi="total_pe">
                    <span class="dash-skeleton dash-skeleton-lg"></span>
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge info" data-kpi="completed_pe_badge">
                        <span class="dash-skeleton dash-skeleton-sm"></span>
                    </span>
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
                <div class="kpi-value" data-kpi="medicines_dispensed">
                    <span class="dash-skeleton dash-skeleton-lg"></span>
                </div>
                <div class="kpi-trend">
                    <span class="trend-badge neutral" data-kpi="dispensing_orders_badge">
                        <span class="dash-skeleton dash-skeleton-sm"></span>
                    </span>
                    <span class="trend-compare">total issued</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('medical-consultations.index') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">1. Medical Consults</span>
                    <i class="bi bi-heart-pulse text-primary"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="medical_consultations_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('dental-consultations.index') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">2. Dental Consults</span>
                    <i class="bi bi-emoji-smile text-info"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="dental_consultations_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('emergency-records.index') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">3. Emergency Triage</span>
                    <i class="bi bi-truck-flatbed text-danger"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="emergency_records_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('first-aid-records.index') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">4. First Aid Logs</span>
                    <i class="bi bi-bandaid text-warning"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="first_aid_records_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('physical-exam-schedules.index') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">5. Scheduled PE</span>
                    <i class="bi bi-calendar2-week text-success"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="pe_schedules_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('inventory.transactions') }}" class="db-stat-tile">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-medium">Inventory Ledger</span>
                    <i class="bi bi-arrow-left-right text-secondary"></i>
                </div>
                <div class="fs-5 fw-bold text-dark" data-kpi="inventory_transactions_count">
                    <span class="dash-skeleton dash-skeleton-sm" style="width: 55px; height: 22px;"></span>
                </div>
            </a>
        </div>
    </div>

    <div id="dashboard-alerts-container"></div>

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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Aggregating 6-month visit trend...</span>
                        </div>
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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Loading clientele breakdown...</span>
                        </div>
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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-info spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Loading service distribution...</span>
                        </div>
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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Ranking chief complaints...</span>
                        </div>
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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-success spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Loading fitness classifications...</span>
                        </div>
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
                    <span class="badge badge-soft-red" data-kpi="communicable_badge">
                        <span class="dash-skeleton dash-skeleton-sm" style="width: 65px;"></span>
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Infectious Condition</th>
                                    <th class="text-end">Cases</th>
                                    <th class="text-end" style="width: 140px;">Proportion</th>
                                </tr>
                            </thead>
                            <tbody id="communicable-morbidity-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 75%;"></span></td>
                                        <td class="text-end"><span class="dash-skeleton dash-skeleton-row" style="width: 45px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
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
                    <span class="badge badge-soft-blue" data-kpi="non_communicable_badge">
                        <span class="dash-skeleton dash-skeleton-sm" style="width: 65px;"></span>
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Chronic Condition</th>
                                    <th class="text-end">Cases</th>
                                    <th class="text-end" style="width: 140px;">Proportion</th>
                                </tr>
                            </thead>
                            <tbody id="non-communicable-morbidity-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 75%;"></span></td>
                                        <td class="text-end"><span class="dash-skeleton dash-skeleton-row" style="width: 45px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
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
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                            <i class="bi bi-building text-info me-2"></i>Utilization by College / Department
                        </h5>
                        <small class="text-muted">Top institutional origins of patient consultations</small>
                    </div>
                </div>
                <div class="card-body px-4 py-3">
                    <div style="height: 250px; position: relative;">
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-info spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Aggregating college visits...</span>
                        </div>
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
                        <div class="chart-loader-overlay">
                            <div class="spinner-border text-success spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted fw-medium">Calculating dispensed medicines...</span>
                        </div>
                        <canvas id="medicine-utilization-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    <i class="bi bi-layers text-primary me-2"></i>Clinical Operations &amp; System Activity Explorer
                </h5>
                <small class="text-muted">Live records across Medical, Dental, Emergency, First Aid, and System Audit tables</small>
            </div>
            <ul class="nav nav-pills nav-fill gap-1" id="clinicalExplorerTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1 px-3 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-medical" type="button" role="tab">
                        1. Medical
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-dental" type="button" role="tab">
                        2. Dental (DMFT)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-emergency" type="button" role="tab">
                        3. Emergency
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-firstaid" type="button" role="tab">
                        4. First Aid
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-audit" type="button" role="tab">
                        Audit Logs
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-medical" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit #</th>
                                    <th>Patient</th>
                                    <th>Diagnosis</th>
                                    <th>Issued Documents</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recent-medical-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td colspan="5"><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dental" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit #</th>
                                    <th>Patient &amp; Hygiene</th>
                                    <th>Dental Diagnosis</th>
                                    <th>DMFT Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recent-dental-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td colspan="5"><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-emergency" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Incident #</th>
                                    <th>Patient &amp; Location</th>
                                    <th>Emergency Nature</th>
                                    <th>Triage</th>
                                    <th>Outcome</th>
                                </tr>
                            </thead>
                            <tbody id="recent-emergency-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td colspan="5"><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-firstaid" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit #</th>
                                    <th>Patient &amp; Location</th>
                                    <th>Injury &amp; Body Part</th>
                                    <th>Supplies Used</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recent-first-aid-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td colspan="5"><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-audit" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Actor</th>
                                    <th>Activity Description</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody id="recent-audit-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td colspan="5"><span class="dash-skeleton dash-skeleton-row"></span></td>
                                    </tr>
                                @endfor
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
                            <tbody id="recent-visits-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 80px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 120px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 65px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 75px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 65px;"></span></td>
                                    </tr>
                                @endfor
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
                            <tbody id="recent-pe-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 80px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 120px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 75px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 65px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 65px;"></span></td>
                                    </tr>
                                @endfor
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
                            <tbody id="upcoming-schedules-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 80px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 130px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 85px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 55px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 65px;"></span></td>
                                    </tr>
                                @endfor
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
                            <tbody id="recent-dispensings-body">
                                @for($r = 0; $r < 4; $r++)
                                    <tr>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 80px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 120px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 140px;"></span></td>
                                        <td><span class="dash-skeleton dash-skeleton-row" style="width: 75px;"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts', 'chartjs-scripts')
        <script src="{{ asset('assets/plugins/chartjs/chart.umd.min.js') }}"></script>
        @vite(['resources/js/pages/dashboard.js'])
    @endPushOnce
</x-layouts.app>
