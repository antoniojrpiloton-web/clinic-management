@php
    $activeTab = request('tab', 'all');
    $showMorbidity = in_array($activeTab, ['all', 'morbidity', ''], true);
    $showMedDental = in_array($activeTab, ['all', 'medical-dental', ''], true);
    $showLoginRec = in_array($activeTab, ['all', 'login-records', ''], true);
    $showCommChart = in_array($activeTab, ['all', 'communicable', ''], true);
    $showNonCommChart = in_array($activeTab, ['all', 'non-communicable', ''], true);
@endphp

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

@if($showMorbidity)
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
@endif

@php
    $medDental = $reportData['medical_dental_records'] ?? null;
    $loginRec = $reportData['login_records'] ?? null;
    $commChart = $reportData['morbidity']['communicable_categories'] ?? null;
    $nonCommChart = $reportData['morbidity']['non_communicable_categories'] ?? null;
    $groupBgMap = [
        'blue' => '#daeef3',
        'pink' => '#e6b8b7',
        'peach' => '#fcd5b4',
        'slate' => '#b8b4c4',
        'rose' => '#f2dcdb',
    ];
    $totalMedDentalRows = 0;
    if ($medDental) {
        foreach ($medDental['groups'] as $grp) {
            $totalMedDentalRows += count($grp['rows']);
        }
    }
@endphp

@if($commChart && $showCommChart)
    <div class="card border mb-4 shadow-sm" id="section-communicable-diseases">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-virus text-success me-2"></i>{{ $commChart['title'] }}
                </h5>
                <small class="text-muted">Official NVC Health Services Department Communicable Diseases Horizontal Bar Chart Report ({{ number_format($commChart['total']) }} Total Cases)</small>
            </div>
            <a href="{{ route('reports.clinic.print-communicable-diseases', array_merge(request()->query(), ['year' => $commChart['year']])) }}" target="_blank" class="btn btn-sm px-3 text-white" style="background-color: #6ca410; border-color: #5b8c0d;">
                <i class="bi bi-file-earmark-pdf me-1"></i> Print Communicable Diseases (PDF)
            </a>
        </div>
        <div class="card-body p-4">
            <div class="border rounded p-4 bg-white mx-auto" style="max-width: 920px;">
                <h5 class="fw-bold text-center text-dark mb-4">{{ $commChart['title'] }}</h5>
                @php
                    $cMax = max(10, (int) ($commChart['max_scale'] ?? 50));
                @endphp
                <div class="d-flex flex-column gap-4 my-3">
                    @foreach($commChart['items'] as $item)
                        @php
                            $cnt = (int) $item['count'];
                            $pct = $cMax > 0 ? min(100, round(($cnt / $cMax) * 100, 2)) : 0;
                        @endphp
                        <div class="row align-items-center g-2">
                            <div class="col-md-5 text-md-end fw-bold text-dark" style="font-size: 0.82rem;">
                                {{ $item['label'] }}
                            </div>
                            <div class="col-md-7">
                                <div class="position-relative d-flex align-items-center" style="height: 36px; border-left: 2px solid #cbd5e1; background: repeating-linear-gradient(to right, transparent, transparent calc(10% - 1px), #f1f5f9 calc(10% - 1px), #f1f5f9 10%);">
                                    @if($cnt > 0)
                                        <div class="d-flex align-items-center justify-content-end px-2 fw-bold text-white shadow-sm" style="width: {{ max(3.5, $pct) }}%; height: 28px; background: linear-gradient(180deg, #84cc16 0%, #65a30d 100%); border: 1px solid #3f6212; font-size: 0.8rem;">
                                            {{ $cnt }}
                                        </div>
                                    @else
                                        <span class="ms-2 small text-muted fw-semibold">0</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row mt-2">
                    <div class="col-md-5"></div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between text-muted fw-bold" style="font-size: 0.75rem;">
                            @for($i = 0; $i <= 10; $i++)
                                <span>{{ (int) round(($cMax / 10) * $i) }}</span>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top d-flex flex-column align-items-center">
                    <div class="d-flex flex-column gap-1">
                        @foreach(array_reverse($commChart['items']) as $item)
                            <div class="d-flex align-items-center gap-2 fw-bold text-dark" style="font-size: 0.82rem;">
                                <span style="display: inline-block; width: 12px; height: 12px; background-color: #65a30d; border: 1px solid #3f6212;"></span>
                                <span>{{ $item['label'] }} ({{ $item['count'] }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($nonCommChart && $showNonCommChart)
    <div class="card border mb-4 shadow-sm" id="section-non-communicable-diseases">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-heart-pulse text-primary me-2"></i>{{ $nonCommChart['title'] }}
                </h5>
                <small class="text-muted">Official NVC Health Services Department Non-Communicable Diseases Horizontal Bar Chart Report ({{ number_format($nonCommChart['total']) }} Total Cases)</small>
            </div>
            <a href="{{ route('reports.clinic.print-non-communicable-diseases', array_merge(request()->query(), ['year' => $nonCommChart['year']])) }}" target="_blank" class="btn btn-sm px-3 text-white" style="background-color: #306cac; border-color: #27588c;">
                <i class="bi bi-file-earmark-pdf me-1"></i> Print Non-Communicable Diseases (PDF)
            </a>
        </div>
        <div class="card-body p-4">
            <div class="border rounded p-4 bg-white mx-auto" style="max-width: 960px;">
                <h5 class="fw-bold text-center text-dark mb-4">{{ $nonCommChart['title'] }}</h5>
                @php
                    $ncMax = max(8, (int) ($nonCommChart['max_scale'] ?? 16));
                @endphp
                <div class="d-flex flex-column gap-3 my-2">
                    @foreach($nonCommChart['items'] as $item)
                        @php
                            $cnt = (int) $item['count'];
                            $pct = $ncMax > 0 ? min(100, round(($cnt / $ncMax) * 100, 2)) : 0;
                        @endphp
                        <div class="row align-items-center g-2">
                            <div class="col-md-6 text-md-center fw-bold text-dark" style="font-size: 0.78rem; line-height: 1.25;">
                                {{ $item['label'] }}
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative d-flex align-items-center" style="height: 28px; border-left: 2px solid #cbd5e1; background: repeating-linear-gradient(to right, transparent, transparent calc(12.5% - 1px), #f1f5f9 calc(12.5% - 1px), #f1f5f9 12.5%);">
                                    @if($cnt > 0)
                                        <div class="d-flex align-items-center justify-content-end px-2 fw-bold text-white shadow-sm" style="width: {{ max(4.5, $pct) }}%; height: 22px; background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); border: 1px solid #1e3a8a; font-size: 0.76rem;">
                                            {{ $cnt }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between text-muted fw-bold" style="font-size: 0.75rem;">
                            @for($i = 0; $i <= 8; $i++)
                                <span>{{ (int) round(($ncMax / 8) * $i) }}</span>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($medDental && $showMedDental)
    <div class="card border mb-4 shadow-sm" id="section-medical-dental-records">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table text-success me-2"></i>MEDICAL AND DENTAL RECORDS ({{ $medDental['year'] }})
                </h5>
                <small class="text-muted">Official Annual 12-Month Breakdown by Clinical Service Category &amp; Student Department / Classification</small>
            </div>
            <a href="{{ route('reports.clinic.print-medical-dental-records', array_merge(request()->query(), ['year' => $medDental['year']])) }}" target="_blank" class="btn btn-sm btn-success px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> Print Medical &amp; Dental Records (PDF)
            </a>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 text-dark" style="font-size: 0.78rem; border-color: #1e293b;">
                    <thead>
                        <tr style="background-color: #76923c; color: #000; font-weight: 700; text-align: center;">
                            <th style="width: 65px; border-color: #1e293b;">YEAR</th>
                            <th colspan="2" style="min-width: 220px; border-color: #1e293b;">SERVICES</th>
                            @foreach($medDental['months'] as $mName)
                                <th style="min-width: 68px; border-color: #1e293b;">{{ $mName }}</th>
                            @endforeach
                            <th style="min-width: 82px; border-color: #1e293b;">TOTAL FOR<br>YEAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $renderedYearCell = false; @endphp
                        @foreach($medDental['groups'] as $group)
                            @php
                                $gRows = $group['rows'];
                                $gSpan = count($gRows);
                                $gBg = $groupBgMap[$group['color']] ?? '#e2e8f0';
                            @endphp
                            @foreach($gRows as $rIndex => $row)
                                <tr>
                                    @if(! $renderedYearCell)
                                        <td rowspan="{{ $totalMedDentalRows + 1 }}" class="fw-bold align-bottom text-start" style="background-color: #76923c; color: #000; border-color: #1e293b;">
                                            {{ $medDental['year'] }}.0
                                        </td>
                                        @php $renderedYearCell = true; @endphp
                                    @endif

                                    @if($rIndex === 0)
                                        <td rowspan="{{ $gSpan }}" class="fw-bold text-center align-middle" style="background-color: {{ $gBg }}; border-color: #1e293b; width: 115px;">
                                            {{ $group['group'] }}
                                        </td>
                                    @endif

                                    <td class="fw-semibold text-center" style="background-color: {{ $gBg }}; border-color: #1e293b; width: 125px;">
                                        {{ $row['label'] }}
                                    </td>

                                    @for($m = 1; $m <= 12; $m++)
                                        <td class="text-end fw-semibold" style="border-color: #1e293b;">
                                            {{ number_format($row['months'][$m] ?? 0) }}
                                        </td>
                                    @endfor

                                    <td class="text-end fw-bold" style="background-color: #f5cc26; border-color: #1e293b;">
                                        {{ number_format($row['total']) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach

                        <tr class="fw-bold">
                            <td colspan="2" class="text-center" style="background-color: #76923c; color: #000; border-color: #1e293b;">
                                TOTAL FOR THE MONTH
                            </td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="text-end" style="background-color: #f5cc26; border-color: #1e293b;">
                                    {{ number_format($medDental['monthly_totals'][$m] ?? 0) }}
                                </td>
                            @endfor
                            <td class="text-end" style="background-color: #946c1e; color: #000; border-color: #1e293b;">
                                {{ number_format($medDental['grand_total']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@if($loginRec && $showLoginRec)
    <div class="card border mb-4 shadow-sm" id="section-login-records">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-journal-check text-warning me-2"></i>MEDICAL AND DENTAL LOG-IN RECORDS ({{ $loginRec['year'] }})
                </h5>
                <small class="text-muted">Official Annual 12-Month Log-In Tally Across All Employee &amp; Student Academic Programs</small>
            </div>
            <a href="{{ route('reports.clinic.print-login-records', array_merge(request()->query(), ['year' => $loginRec['year']])) }}" target="_blank" class="btn btn-sm btn-warning text-dark fw-semibold px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> Print Log-In Records (PDF)
            </a>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 text-dark" style="font-size: 0.78rem; border-color: #1e293b;">
                    <thead>
                        <tr style="background-color: #76923c; color: #000; font-weight: 700; text-align: center;">
                            <th style="width: 65px; border-color: #1e293b;">YEAR</th>
                            <th colspan="2" style="min-width: 220px; border-color: #1e293b;">SERVICES</th>
                            @foreach($loginRec['months'] as $mName)
                                <th style="min-width: 68px; border-color: #1e293b;">{{ $mName }}</th>
                            @endforeach
                            <th style="min-width: 82px; border-color: #1e293b;">TOTAL FOR<br>YEAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $loginRowCount = count($loginRec['rows']); @endphp
                        @foreach($loginRec['rows'] as $rIndex => $row)
                            <tr>
                                @if($rIndex === 0)
                                    <td rowspan="{{ $loginRowCount + 1 }}" class="fw-bold align-bottom text-start" style="background-color: #76923c; color: #000; border-color: #1e293b;">
                                        {{ $loginRec['year'] }}.0
                                    </td>
                                    <td rowspan="{{ $loginRowCount }}" style="background-color: #d9acaa; border-color: #1e293b; width: 95px;"></td>
                                @endif

                                <td class="fw-semibold text-center" style="background-color: #e6b8b7; border-color: #1e293b; width: 135px;">
                                    {{ $row['label'] }}
                                </td>

                                @for($m = 1; $m <= 12; $m++)
                                    <td class="text-center fw-semibold" style="border-color: #1e293b;">
                                        {{ number_format($row['months'][$m] ?? 0) }}
                                    </td>
                                @endfor

                                <td class="text-center fw-bold" style="background-color: #f5cc26; border-color: #1e293b;">
                                    {{ number_format($row['total']) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="fw-bold">
                            <td colspan="2" class="text-center" style="background-color: #76923c; color: #000; border-color: #1e293b;">
                                TOTAL FOR THE MONTH
                            </td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="text-center" style="background-color: #f5cc26; border-color: #1e293b;">
                                    {{ number_format($loginRec['monthly_totals'][$m] ?? 0) }}
                                </td>
                            @endfor
                            <td class="text-center" style="background-color: #946c1e; color: #000; border-color: #1e293b;">
                                {{ number_format($loginRec['grand_total']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif


