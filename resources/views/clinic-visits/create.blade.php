<x-layouts.app title="Log Patient Visit">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('clinic-visits.index') }}">Patient Visit Logs</a>
        <span>&gt;</span>
        <span>Log Visit</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Log Patient Visit (Intake Register)</h1>
            <p class="dashboard-subheading mb-0">Record student or employee clinic walk-in, routine health service, blood pressure check, or clearance.</p>
        </div>

        <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Visit Logs
        </a>
    </div>

    <form method="POST" action="{{ route('clinic-visits.store') }}" novalidate>
        @csrf

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-tag text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Service &amp; Encounter Type</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Encounter / Service Type</label>
                        <select name="visit_type" class="form-select @error('visit_type') is-invalid @enderror" required>
                            @foreach(\App\Models\ClinicVisit::TYPES as $tKey => $tData)
                                <option value="{{ $tKey }}" @selected(old('visit_type', $visit->visit_type) === $tKey)>
                                    {{ $tData['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('visit_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Specific Visit Purpose (Preset)</label>
                        <select id="preset_purpose" class="form-select">
                            <option value="">-- Choose Common Purpose (or type below) --</option>
                            @foreach($purposes as $purp)
                                <option value="{{ $purp }}">{{ $purp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.patient-select', [
            'preselectedPatient' => $preselectedPatient,
            'defaultClinician' => $defaultClinician,
            'visit' => $visit,
        ])

        @include('clinic-visits.partials.vitals-form', ['visit' => $visit])

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Chief Complaint &amp; Intake Notes</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required">Chief Complaint / Visit Purpose</label>
                        <textarea name="chief_complaint" id="chief_complaint" rows="3" class="form-control @error('chief_complaint') is-invalid @enderror" placeholder="Describe the reason for visit, reported symptoms, or service requested..." required>{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                        @error('chief_complaint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Save Patient Visit Log
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const preset = document.getElementById('preset_purpose');
                const complaint = document.getElementById('chief_complaint');
                if (preset && complaint) {
                    preset.addEventListener('change', () => {
                        if (preset.value) {
                            complaint.value = preset.value;
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

