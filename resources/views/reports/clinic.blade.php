<x-layouts.app title="NVC Clinic Morbidity & Aggregated Reports">
    <style>
        @keyframes reportShimmer {
            0% { background-position: -600px 0; }
            100% { background-position: 600px 0; }
        }
        .report-skeleton {
            display: inline-block;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 600px 100%;
            animation: reportShimmer 1.4s infinite linear;
            border-radius: 6px;
        }
        .report-skeleton-lg {
            height: 32px;
            width: 110px;
        }
        .report-skeleton-sm {
            height: 16px;
            width: 140px;
        }
        .report-skeleton-row {
            height: 20px;
            width: 100%;
        }
    </style>

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
            <a href="{{ route('reports.clinic.print-medical-dental-records', request()->query()) }}" id="btn-export-medical-dental-pdf" target="_blank" class="btn btn-success btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Medical &amp; Dental (PDF)
            </a>
            <a href="{{ route('reports.clinic.print-login-records', request()->query()) }}" id="btn-export-login-pdf" target="_blank" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                <i class="bi bi-journal-check me-1"></i> Log-In Records (PDF)
            </a>
            <a href="{{ route('reports.clinic.print-communicable-diseases', request()->query()) }}" id="btn-export-communicable-pdf" target="_blank" class="btn btn-sm px-3 py-2 text-white" style="background-color: #6ca410; border-color: #5b8c0d;">
                <i class="bi bi-bar-chart-steps me-1"></i> Communicable Chart (PDF)
            </a>
            <a href="{{ route('reports.clinic.print-non-communicable-diseases', request()->query()) }}" id="btn-export-non-communicable-pdf" target="_blank" class="btn btn-sm px-3 py-2 text-white" style="background-color: #306cac; border-color: #27588c;">
                <i class="bi bi-bar-chart-fill me-1"></i> Non-Communicable Chart (PDF)
            </a>
            <a href="{{ route('reports.clinic.print-pdf', request()->query()) }}" id="btn-export-pdf" target="_blank" class="btn btn-primary btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-pdf me-1"></i> Morbidity Summary (PDF)
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    @php $activeTab = request('tab', 'all'); @endphp
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'all'])) }}" @class(['btn btn-sm px-3', 'btn-dark' => $activeTab === 'all', 'btn-outline-secondary' => $activeTab !== 'all'])>
            <i class="bi bi-collection me-1"></i> All Clinic Reports
        </a>
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'medical-dental'])) }}" @class(['btn btn-sm px-3', 'btn-success' => $activeTab === 'medical-dental', 'btn-outline-success' => $activeTab !== 'medical-dental'])>
            <i class="bi bi-table me-1"></i> 1. Medical &amp; Dental Records
        </a>
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'login-records'])) }}" @class(['btn btn-sm px-3', 'btn-warning text-dark fw-semibold' => $activeTab === 'login-records', 'btn-outline-secondary' => $activeTab !== 'login-records'])>
            <i class="bi bi-journal-check me-1"></i> 2. Medical &amp; Dental Log-In
        </a>
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'communicable'])) }}" @class(['btn btn-sm px-3', 'btn-success' => $activeTab === 'communicable', 'btn-outline-secondary' => $activeTab !== 'communicable'])>
            <i class="bi bi-virus me-1"></i> 3. Communicable Diseases
        </a>
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'non-communicable'])) }}" @class(['btn btn-sm px-3', 'btn-primary' => $activeTab === 'non-communicable', 'btn-outline-secondary' => $activeTab !== 'non-communicable'])>
            <i class="bi bi-heart-pulse me-1"></i> 4. Non-Communicable Diseases
        </a>
        <a href="{{ route('reports.clinic.index', array_merge(request()->query(), ['tab' => 'morbidity'])) }}" @class(['btn btn-sm px-3', 'btn-info text-white' => $activeTab === 'morbidity', 'btn-outline-secondary' => $activeTab !== 'morbidity'])>
            <i class="bi bi-activity me-1"></i> 5. Morbidity &amp; Utilization
        </a>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter &amp; Aggregation Parameters</span>
            <span id="report-loading-badge" class="badge badge-soft-blue d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="width: 12px; height: 12px;"></span>
                Aggregating dataset...
            </span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.clinic.index') }}" id="clinic-report-filter-form" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Report Year (Matrix)</label>
                    <select name="year" id="filter-year" data-default="{{ now()->year }}" class="form-select form-select-sm">
                        @for($y = (int) now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" @selected((int) ($filters['year'] ?? now()->year) === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" id="filter-date-from" value="{{ $filters['date_from'] }}" data-default="{{ now()->startOfYear()->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" id="filter-date-to" value="{{ $filters['date_to'] }}" data-default="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Academic Year</label>
                    <select name="academic_year_id" id="filter-academic-year" class="form-select form-select-sm">
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
                    <select name="college_id" id="filter-college" class="form-select form-select-sm">
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
                    <select name="patient_classification" id="filter-classification" class="form-select form-select-sm">
                        <option value="">All Classifications</option>
                        <option value="student" @selected($filters['patient_classification'] === 'student')>Students Only</option>
                        <option value="faculty" @selected($filters['patient_classification'] === 'faculty')>Faculty Only</option>
                        <option value="staff" @selected($filters['patient_classification'] === 'staff')>Staff Only</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('reports.clinic.index') }}" id="btn-reset-filters" class="btn btn-outline-secondary btn-sm px-3">
                        Reset Filters
                    </a>
                    <button type="submit" id="btn-aggregate-submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-arrow-repeat me-1" id="btn-aggregate-icon"></i>
                        <span id="btn-aggregate-label">Aggregate &amp; Recalculate</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="clinic-report-skeleton" class="d-none">
        <div class="row g-3 mb-4">
            @for($i = 0; $i < 3; $i++)
                <div class="col-sm-6 col-xl-4">
                    <div class="kpi-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="report-skeleton report-skeleton-sm"></span>
                            <span class="kpi-icon-box blue opacity-50"><i class="bi bi-hourglass-split"></i></span>
                        </div>
                        <div class="my-2"><span class="report-skeleton report-skeleton-lg"></span></div>
                        <div><span class="report-skeleton report-skeleton-sm"></span></div>
                    </div>
                </div>
            @endfor
        </div>
        <div class="row g-4 mb-4">
            @for($card = 0; $card < 4; $card++)
                <div class="col-lg-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <span class="report-skeleton report-skeleton-sm" style="width: 200px;"></span>
                            <span class="report-skeleton" style="width: 70px; height: 20px;"></span>
                        </div>
                        <div class="card-body">
                            @for($r = 0; $r < 5; $r++)
                                <div class="py-2"><span class="report-skeleton report-skeleton-row"></span></div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <div id="clinic-report-content">
        @include('reports.partials.clinic-content', ['reportData' => $reportData])
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('clinic-report-filter-form');
            const resetBtn = document.getElementById('btn-reset-filters');
            const submitBtn = document.getElementById('btn-aggregate-submit');
            const submitLabel = document.getElementById('btn-aggregate-label');
            const loadingBadge = document.getElementById('report-loading-badge');
            const skeletonEl = document.getElementById('clinic-report-skeleton');
            const contentEl = document.getElementById('clinic-report-content');
            const pdfBtn = document.getElementById('btn-export-pdf');
            const medDentalPdfBtn = document.getElementById('btn-export-medical-dental-pdf');
            const loginPdfBtn = document.getElementById('btn-export-login-pdf');
            const commPdfBtn = document.getElementById('btn-export-communicable-pdf');
            const nonCommPdfBtn = document.getElementById('btn-export-non-communicable-pdf');

            if (!form || !contentEl) {
                return;
            }

            async function fetchAggregatedReport(url) {
                if (submitBtn) {
                    submitBtn.disabled = true;
                }
                if (submitLabel) {
                    submitLabel.textContent = 'Aggregating...';
                }
                if (loadingBadge) {
                    loadingBadge.classList.remove('d-none');
                }
                if (skeletonEl && contentEl) {
                    contentEl.classList.add('d-none');
                    skeletonEl.classList.remove('d-none');
                }

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    const payload = await response.json();
                    if (payload.html) {
                        contentEl.innerHTML = payload.html;
                    }
                    if (payload.pdf_url && pdfBtn) {
                        pdfBtn.setAttribute('href', payload.pdf_url);
                    }
                    if (payload.pdf_medical_dental_url && medDentalPdfBtn) {
                        medDentalPdfBtn.setAttribute('href', payload.pdf_medical_dental_url);
                    }
                    if (payload.pdf_login_url && loginPdfBtn) {
                        loginPdfBtn.setAttribute('href', payload.pdf_login_url);
                    }
                    if (payload.pdf_communicable_url && commPdfBtn) {
                        commPdfBtn.setAttribute('href', payload.pdf_communicable_url);
                    }
                    if (payload.pdf_non_communicable_url && nonCommPdfBtn) {
                        nonCommPdfBtn.setAttribute('href', payload.pdf_non_communicable_url);
                    }
                    window.history.replaceState({}, '', url);
                } catch (err) {
                    console.error('Failed to aggregate clinic report:', err);
                    window.location.href = url;
                } finally {
                    if (skeletonEl && contentEl) {
                        skeletonEl.classList.add('d-none');
                        contentEl.classList.remove('d-none');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    if (submitLabel) {
                        submitLabel.textContent = 'Aggregate & Recalculate';
                    }
                    if (loadingBadge) {
                        loadingBadge.classList.add('d-none');
                    }
                }
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form));
                const targetUrl = form.getAttribute('action') + '?' + params.toString();
                fetchAggregatedReport(targetUrl);
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const yearSel = document.getElementById('filter-year');
                    const dateFrom = document.getElementById('filter-date-from');
                    const dateTo = document.getElementById('filter-date-to');
                    const ay = document.getElementById('filter-academic-year');
                    const col = document.getElementById('filter-college');
                    const classif = document.getElementById('filter-classification');

                    if (yearSel) yearSel.value = yearSel.dataset.default || '';
                    if (dateFrom) dateFrom.value = dateFrom.dataset.default || '';
                    if (dateTo) dateTo.value = dateTo.dataset.default || '';
                    if (ay) ay.value = '';
                    if (col) col.value = '';
                    if (classif) classif.value = '';

                    const params = new URLSearchParams(new FormData(form));
                    const targetUrl = form.getAttribute('action') + '?' + params.toString();
                    fetchAggregatedReport(targetUrl);
                });
            }
        });
    </script>
</x-layouts.app>
