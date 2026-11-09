@php
    $clinicName = $settings['clinic_name'] ?? 'Health Services Department';
    $universityName = $settings['university_name'] ?? 'Northwestern Visayan Colleges';
    $faviconUrl = ! empty($settings['logo_compact'])
        ? asset('storage/'.$settings['logo_compact'])
        : (! empty($settings['logo_primary'])
            ? asset('storage/'.$settings['logo_primary'])
            : asset('assets/images/logo.svg'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clinic Visitor Logbook — {{ $clinicName }}</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-icons/bootstrap-icons.min.css') }}">
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: #f4f6f4;
            min-height: 100vh;
        }
        .kiosk-header {
            background: #ffffff;
            border-bottom: 1px solid #dce3de;
            box-shadow: 0 1px 3px rgba(24, 38, 32, 0.04);
        }
        .kiosk-clock {
            font-variant-numeric: tabular-nums;
            background: #182620;
            color: #ffffff;
            border-radius: 0.5rem;
            padding: 0.45rem 0.95rem;
        }
    </style>
</head>
<body>
    <header class="kiosk-header py-3 mb-4">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $faviconUrl }}" alt="Logo" class="rounded" style="width: 44px; height: 44px; object-fit: contain;">
                    <div>
                        <div class="fw-bold text-dark fs-5 lh-sm">{{ $universityName }}</div>
                        <div class="text-primary fw-semibold small">{{ $clinicName }} &bull; Daily Attendance &amp; Tracing Logbook</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="kiosk-clock d-flex align-items-center gap-2">
                        <i class="bi bi-clock-fill text-info"></i>
                        <div>
                            <div id="live-clock-time" class="fw-bold" style="font-size: 0.95rem; line-height: 1.1;">{{ now()->format('h:i:s A') }}</div>
                            <div id="live-clock-date" class="text-white-50" style="font-size: 0.68rem;">{{ now()->format('l, F d, Y') }}</div>
                        </div>
                    </div>

                    @auth
                        <a href="{{ route('clinic-logbooks.index') }}" class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> Staff Logbook View
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                            <i class="bi bi-lock me-1"></i> Staff Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        @if (session('logbook_success'))
            <div class="alert alert-success border-success d-flex align-items-center justify-content-between shadow-sm mb-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    <span class="fw-bold fs-6">{{ session('logbook_success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border shadow-sm">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="kpi-icon-box blue"><i class="bi bi-journal-plus"></i></span>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Clinic Entry Logbook</h5>
                                <small class="text-muted">Please log in before proceeding inside the clinic (for health &amp; visit tracing).</small>
                            </div>
                        </div>
                        <span class="badge bg-primary text-white">Step 1</span>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('logbook.kiosk.store') }}" id="kiosk-logbook-form">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">1. Classification / Uri ng Bisita <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="pe-check-tile justify-content-center py-2" for="vt_student">
                                            <input class="form-check-input" type="radio" name="visitor_type" value="student" id="vt_student" @checked(old('visitor_type', 'student') === 'student')>
                                            <span class="pe-tile-text fw-semibold"><i class="bi bi-mortarboard me-1"></i> Student</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="pe-check-tile justify-content-center py-2" for="vt_employee">
                                            <input class="form-check-input" type="radio" name="visitor_type" value="employee" id="vt_employee" @checked(in_array(old('visitor_type'), ['employee', 'faculty', 'staff']))>
                                            <span class="pe-tile-text fw-semibold"><i class="bi bi-person-workspace me-1"></i> Employee (Teaching / Non-Teaching)</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="pe-check-tile justify-content-center py-2" for="vt_visitor">
                                            <input class="form-check-input" type="radio" name="visitor_type" value="visitor" id="vt_visitor" @checked(old('visitor_type') === 'visitor')>
                                            <span class="pe-tile-text fw-semibold"><i class="bi bi-person-badge me-1"></i> Visitor</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-5">
                                    <label class="form-label fw-bold text-dark" for="kiosk_id_number">2. Student / Employee ID</label>
                                    <div class="input-group">
                                        <input type="text" name="id_number" id="kiosk_id_number" value="{{ old('id_number') }}" class="form-control" placeholder="e.g. 2026-0001 (Optional)">
                                        <button type="button" id="btn-lookup-id" class="btn btn-outline-primary" title="Auto-fill from ID">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                    <div id="lookup-feedback" class="form-text small">Type ID to auto-fill, or enter name directly.</div>
                                </div>

                                <div class="col-sm-7">
                                    <label class="form-label fw-bold text-dark" for="kiosk_full_name">3. Full Name / Buong Pangalan <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" id="kiosk_full_name" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror" placeholder="e.g. Juan M. Dela Cruz" required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6" id="program-select-wrapper">
                                    <label class="form-label fw-bold text-dark" for="kiosk_program_id">4. Course / Program / Level</label>
                                    <select name="program_id" id="kiosk_program_id" class="form-select">
                                        <option value="">-- Select Program / Level --</option>
                                        @foreach ($programs as $prog)
                                            <option value="{{ $prog->id }}" data-code="{{ $prog->code }}" @selected(old('program_id') == $prog->id)>
                                                {{ $prog->code }} &mdash; {{ $prog->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-3">
                                    <label class="form-label fw-bold text-dark" for="kiosk_year_or_position">Year / Position</label>
                                    <input type="text" name="year_or_position" id="kiosk_year_or_position" value="{{ old('year_or_position') }}" class="form-control" placeholder="e.g. 2nd Year / Staff">
                                </div>

                                <div class="col-sm-3">
                                    <label class="form-label fw-bold text-dark" for="kiosk_contact_number">Contact No.</label>
                                    <input type="text" name="contact_number" id="kiosk_contact_number" value="{{ old('contact_number') }}" class="form-control" placeholder="09XX-XXX-XXXX">
                                </div>
                            </div>

                            <input type="hidden" name="course_or_department" id="kiosk_course_or_department" value="{{ old('course_or_department') }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">5. Purpose of Clinic Visit / Sadya sa Clinic <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    @foreach ($purposes as $idx => $pLabel)
                                        <div class="col-sm-6">
                                            <label class="pe-check-tile" for="purp_{{ $idx }}">
                                                <input class="form-check-input" type="radio" name="purpose" value="{{ $pLabel }}" id="purp_{{ $idx }}" @checked(old('purpose', $purposes[0]) === $pLabel) required>
                                                <span class="pe-tile-text small text-dark">{{ $pLabel }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('purpose')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark" for="kiosk_notes">6. Specific Concern / Remarks (Optional)</label>
                                <input type="text" name="notes" id="kiosk_notes" value="{{ old('notes') }}" class="form-control" placeholder="e.g. Headache, request medical slip, dental check...">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-6 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Record Clinic Log-In (Time In)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-success me-2"></i>Today's Clinic Logbook</h5>
                            <small class="text-muted">{{ now()->format('F d, Y') }} &bull; Click <strong>Time Out</strong> before leaving the clinic.</small>
                        </div>
                        <span class="badge badge-soft-green">{{ $todayLogs->count() }} Logged Today</span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time In</th>
                                        <th>Visitor / Client</th>
                                        <th>Course / Dept</th>
                                        <th>Purpose</th>
                                        <th class="text-end">Time Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($todayLogs as $log)
                                        <tr>
                                            <td class="fw-bold text-primary whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($log->time_in)->format('h:i A') }}
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $log->full_name }}</div>
                                                <div class="small text-muted">
                                                    <span class="badge {{ $log->visitor_type === 'student' ? 'badge-soft-blue' : ($log->visitor_type === 'employee' ? 'badge-soft-green' : 'badge-soft-gray') }}">
                                                        {{ ucfirst($log->visitor_type) }}
                                                    </span>
                                                    @if ($log->id_number)
                                                        <span class="ms-1">{{ $log->id_number }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="small fw-semibold text-dark">
                                                {{ $log->program?->code ?? ($log->course_or_department ?: '—') }}
                                                @if ($log->year_or_position)
                                                    <div class="text-muted fw-normal">{{ $log->year_or_position }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="small fw-semibold text-dark">{{ $log->purpose }}</span>
                                                @if ($log->notes)
                                                    <div class="small text-muted">{{ Str::limit($log->notes, 35) }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($log->time_out)
                                                    <span class="badge bg-light text-secondary border">
                                                        <i class="bi bi-check2-all text-success me-1"></i>{{ \Carbon\Carbon::parse($log->time_out)->format('h:i A') }}
                                                    </span>
                                                @else
                                                    <form method="POST" action="{{ route('logbook.kiosk.time-out', $log) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-2 py-1">
                                                            <i class="bi bi-box-arrow-right me-1"></i> Time Out
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-journal-text fs-2 d-block mb-2 opacity-50"></i>
                                                No visitors have logged in yet today.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const clockTime = document.getElementById('live-clock-time');
            if (clockTime) {
                setInterval(() => {
                    const now = new Date();
                    clockTime.textContent = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }, 1000);
            }

            const idInput = document.getElementById('kiosk_id_number');
            const lookupBtn = document.getElementById('btn-lookup-id');
            const feedback = document.getElementById('lookup-feedback');
            const fullNameInput = document.getElementById('kiosk_full_name');
            const programSelect = document.getElementById('kiosk_program_id');
            const yearInput = document.getElementById('kiosk_year_or_position');
            const contactInput = document.getElementById('kiosk_contact_number');
            const courseHidden = document.getElementById('kiosk_course_or_department');

            async function performLookup() {
                const idVal = idInput?.value?.trim();
                if (!idVal) {
                    return;
                }

                try {
                    const res = await fetch(`{{ route('logbook.lookup') }}?id_number=${encodeURIComponent(idVal)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    if (data.found) {
                        if (fullNameInput) fullNameInput.value = data.full_name || '';
                        if (yearInput) yearInput.value = data.year_or_position || '';
                        if (contactInput && data.contact_number) contactInput.value = data.contact_number;
                        if (courseHidden) courseHidden.value = data.course_or_department || '';

                        if (['employee', 'faculty', 'staff'].includes(data.visitor_type) && document.getElementById('vt_employee')) {
                            document.getElementById('vt_employee').checked = true;
                        } else if (data.visitor_type === 'visitor' && document.getElementById('vt_visitor')) {
                            document.getElementById('vt_visitor').checked = true;
                        } else if (document.getElementById('vt_student')) {
                            document.getElementById('vt_student').checked = true;
                        }

                        if (programSelect && data.program_id) {
                            programSelect.value = String(data.program_id);
                        }

                        if (feedback) {
                            feedback.className = 'form-text small text-success fw-semibold';
                            feedback.textContent = `Record matched: ${data.full_name}`;
                        }
                    } else if (feedback) {
                        feedback.className = 'form-text small text-muted';
                        feedback.textContent = 'No pre-registered profile found — please fill in your name below.';
                    }
                } catch (err) {
                }
            }

            lookupBtn?.addEventListener('click', performLookup);
            idInput?.addEventListener('blur', performLookup);
            idInput?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performLookup();
                }
            });
        });
    </script>
</body>
</html>

