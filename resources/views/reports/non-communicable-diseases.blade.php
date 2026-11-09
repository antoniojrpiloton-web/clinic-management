<x-layouts.app title="Non-Communicable Diseases Report">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Reports &amp; Analytics</span>
        <span>&gt;</span>
        <span>Non-Communicable Diseases</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">{{ $nonCommChart['title'] }}</h1>
                <span class="badge badge-soft-blue"><i class="bi bi-heart-pulse me-1"></i> DB-Driven Morbidity Chart</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Official NVC Health Services Department Non-Communicable Diseases Horizontal Bar Chart &amp; Case Breakdown sourced from Database Disease Categories.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('disease-categories.index', ['type' => 'non_communicable']) }}" class="btn btn-outline-primary btn-sm px-3 py-2">
                <i class="bi bi-sliders me-1"></i> Manage Disease Categories
            </a>
            <a href="{{ route('reports.clinic.print-non-communicable-diseases', request()->query()) }}" target="_blank" class="btn btn-sm px-3 py-2 text-white" style="background-color: #306cac; border-color: #27588c;">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export Official PDF (Portrait)
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter Report Parameters</span>
            <span class="badge badge-soft-blue">Total Cases: {{ number_format($nonCommChart['total']) }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.clinic.non-communicable') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-2">
                    <label class="form-label small fw-semibold">Report Year</label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = (int) now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" @selected((int) ($filters['year'] ?? now()->year) === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
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
                <div class="col-sm-6 col-md-3">
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
                <div class="col-sm-6 col-md-2">
                    <label class="form-label small fw-semibold">Classification</label>
                    <select name="patient_classification" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        <option value="student" @selected($filters['patient_classification'] === 'student')>Students Only</option>
                        <option value="faculty" @selected($filters['patient_classification'] === 'faculty')>Faculty Only</option>
                        <option value="staff" @selected($filters['patient_classification'] === 'staff')>Staff Only</option>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1">
                        <i class="bi bi-arrow-repeat me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.clinic.non-communicable') }}" class="btn btn-outline-secondary btn-sm px-3">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border mb-4 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>{{ $nonCommChart['title'] }}
                </h5>
                <small class="text-muted">Horizontal Bar Chart representation of Non-Communicable Diseases ({{ number_format($nonCommChart['total']) }} Total Cases)</small>
            </div>
            <span class="badge bg-primary text-white fw-bold px-3 py-2">
                {{ count($nonCommChart['items']) }} Active Categories
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="border rounded p-3 p-md-4 bg-white mx-auto" style="max-width: 960px;">
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

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-list-ul text-primary me-2"></i>Non-Communicable Diseases Tabular Breakdown</span>
            <span class="badge badge-soft-gray">{{ $nonCommChart['year'] }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Disease Category Label</th>
                            <th class="text-end">Recorded Cases</th>
                            <th class="text-end" style="width: 180px;">Share of Non-Communicable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalNonComm = max(1, (int) $nonCommChart['total']); @endphp
                        @foreach($nonCommChart['items'] as $item)
                            @php $share = $nonCommChart['total'] > 0 ? round(($item['count'] / $totalNonComm) * 100, 1) : 0; @endphp
                            <tr>
                                <td class="fw-semibold text-dark">{{ $item['label'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($item['count']) }}</td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $share }}%"></div>
                                        </div>
                                        <span class="small text-muted" style="min-width: 42px;">{{ $share }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

