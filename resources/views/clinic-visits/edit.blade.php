<x-layouts.app title="Edit Visit Log: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('clinic-visits.index') }}">Patient Visit Logs</a>
        <span>&gt;</span>
        <a href="{{ route('clinic-visits.show', $visit) }}">{{ $visit->visit_number }}</a>
        <span>&gt;</span>
        <span>Edit</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Patient Visit Log: {{ $visit->visit_number }}</h1>
            <p class="dashboard-subheading mb-0">Update patient visit details, vital signs, and disposition.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('clinic-visits.show', $visit) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Record
            </a>
            <a href="{{ route('clinic-visits.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('clinic-visits.update', $visit) }}" novalidate>
        @csrf
        @method('PUT')

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
                    <span class="fw-bold text-dark">Complaint &amp; Notes</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required">Chief Complaint / Reason for Visit</label>
                        <textarea name="chief_complaint" rows="3" class="form-control @error('chief_complaint') is-invalid @enderror" required>{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                        @error('chief_complaint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('clinic-visits.show', $visit) }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Update Visit Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>

