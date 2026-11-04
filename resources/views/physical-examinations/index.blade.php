<x-layouts.app title="Physical Examination Registry">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Physical Examinations</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Physical Examination Registry</h1>
            <p class="dashboard-subheading mb-0">Institutional health profiles, vital signs, physical diagnostics, and medical fitness clearances.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('physical-examinations.create') }}" class="btn btn-primary px-3 py-2">
                <i class="bi bi-plus-circle me-1"></i> New Physical Examination
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Examinations</span>
                    <span class="kpi-icon-box blue">
                        <i class="bi bi-file-earmark-medical"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-check-circle"></i> Recorded
                    </span>
                    <span class="trend-compare">all clinical exams</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Conducted Today</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['today']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-clock"></i> Today
                    </span>
                    <span class="trend-compare">{{ date('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Class A (Fully Fit)</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['class_a']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-patch-check"></i> Cleared
                    </span>
                    <span class="trend-compare">all physical activities</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Conditional / Needs Care</span>
                    <span class="kpi-icon-box amber">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($stats['needs_attention']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge down">
                        <i class="bi bi-flag"></i> Class B - E
                    </span>
                    <span class="trend-compare">under monitoring</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 p-3">
        <form id="pe-filter-form" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search PE #, patient, ID...">
                </div>
            </div>

            <div class="col-md-2">
                <select id="filter-purpose" name="purpose" class="form-select">
                    <option value="">All Examination Purposes</option>
                    <option value="Annual Physical Exam">Annual Physical Exam</option>
                    <option value="Entrance Examination">Entrance Examination</option>
                    <option value="OJT Medical Clearance">OJT Medical Clearance</option>
                    <option value="Sports Clearance">Sports Clearance</option>
                    <option value="Pre-Employment">Pre-Employment</option>
                    <option value="General Medical">General Medical</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="filter-fitness" name="fitness_assessment" class="form-select">
                    <option value="">All Fitness Classes</option>
                    <option value="Class A">Class A (Fully Fit)</option>
                    <option value="Class B">Class B (Minor Defect)</option>
                    <option value="Class C">Class C (Restricted)</option>
                    <option value="Class D">Class D (Temporarily Unfit)</option>
                    <option value="Class E">Class E (Unfit)</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="filter-ay" name="academic_year_id" class="form-select">
                    <option value="">All Academic Years</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->code }} ({{ $ay->semester }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From date">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </form>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="pe-table" class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 110px;">ACTIONS</th>
                            <th>PE NUMBER</th>
                            <th>PATIENT &amp; ID</th>
                            <th>VITAL SIGNS</th>
                            <th>BMI</th>
                            <th>FITNESS</th>
                            <th>PURPOSE</th>
                            <th>EXAM DATE</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @pushOnce('styles', 'datatables-styles')
        <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css') }}">
    @endPushOnce

    @pushOnce('scripts', 'datatables-scripts')
        <script src="{{ asset('assets/plugins/datatables/dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const table = $('#pe-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                    ajax: {
                        url: "{{ route('physical-examinations.index') }}",
                        data: function (d) {
                            d.purpose = $('#filter-purpose').val();
                            d.fitness_assessment = $('#filter-fitness').val();
                            d.academic_year_id = $('#filter-ay').val();
                            d.date_from = $('input[name="date_from"]').val();
                            const customSearch = $('input[name="search"]').val();
                            if (customSearch) {
                                d.search = customSearch;
                            }
                        }
                    },
                    columns: [
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                        { data: 'pe_number', name: 'pe_number', className: 'fw-bold text-dark' },
                        { data: 'patient_card', name: 'patient.first_name', orderable: false },
                        { data: 'vitals_summary', name: 'systolic', orderable: false },
                        { data: 'bmi_badge', name: 'bmi', orderable: true },
                        { data: 'fitness_badge', name: 'fitness_assessment', orderable: true },
                        { data: 'purpose', name: 'purpose', orderable: true },
                        { data: 'exam_date_formatted', name: 'exam_date', orderable: true }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search examinations...",
                        emptyTable: "No physical examination records found",
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...'
                    }
                });

                $('#pe-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#pe-filter-form').on('reset', function () {
                    setTimeout(() => table.draw(), 50);
                });

                $('#filter-purpose, #filter-fitness, #filter-ay').on('change', function () {
                    table.draw();
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

