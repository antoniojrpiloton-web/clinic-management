<x-layouts.app title="Medical & Dental Records Report">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Reports &amp; Analytics</span>
        <span>&gt;</span>
        <span>Medical &amp; Dental Records</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Medical &amp; Dental Records ({{ $medDental['year'] }})</h1>
                <span class="badge badge-soft-green"><i class="bi bi-table me-1"></i> Official 12-Month Matrix</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Dynamic annual monthly tally of clinical services rendered across Employee and Student Academic Programs.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.clinic.print-medical-dental-records', request()->query()) }}" target="_blank" class="btn btn-success btn-sm px-3 py-2">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export Official PDF (Landscape)
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print View
            </button>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-funnel me-1"></i> Filter Report Parameters</span>
            <span class="badge badge-soft-blue">Grand Total: {{ number_format($medDental['grand_total']) }} Services</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.clinic.medical-dental') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
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
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1">
                        <i class="bi bi-arrow-repeat me-1"></i> Generate Matrix
                    </button>
                    <a href="{{ route('reports.clinic.medical-dental') }}" class="btn btn-outline-secondary btn-sm px-3">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @php
        $groupBgMap = [
            'blue' => '#daeef3',
            'pink' => '#e6b8b7',
            'peach' => '#fcd5b4',
            'slate' => '#b8b4c4',
            'rose' => '#f2dcdb',
        ];
        $totalMedDentalRows = 0;
        foreach ($medDental['groups'] as $grp) {
            $totalMedDentalRows += count($grp['rows']);
        }
    @endphp

    <div class="card border mb-4 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table text-success me-2"></i>MEDICAL AND DENTAL RECORDS ({{ $medDental['year'] }})
                </h5>
                <small class="text-muted">Official Annual 12-Month Breakdown by Clinical Service Category &amp; Student Department / Classification</small>
            </div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                Annual Total: {{ number_format($medDental['grand_total']) }}
            </span>
        </div>
        <div class="card-body p-2 p-md-3">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 text-dark report-matrix-table" style="border-color: #1e293b;">
                    <thead>
                        <tr class="text-center fw-bold">
                            <th style="width: 48px;">YEAR</th>
                            <th colspan="2" style="width: 195px;">SERVICES</th>
                            @foreach($medDental['months'] as $mName)
                                <th title="{{ $mName }}">{{ substr($mName, 0, 3) }}</th>
                            @endforeach
                            <th style="width: 64px;">TOTAL FOR<br>YEAR</th>
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
                                        <td rowspan="{{ $gSpan }}" class="fw-bold text-center align-middle" style="background-color: {{ $gBg }}; border-color: #1e293b; width: 92px; font-size: 0.68rem;">
                                            {{ $group['group'] }}
                                        </td>
                                    @endif

                                    <td class="fw-semibold text-center" style="background-color: {{ $gBg }}; border-color: #1e293b; width: 105px; font-size: 0.7rem;">
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
</x-layouts.app>

