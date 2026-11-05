<x-layouts.app title="Record First Aid Treatment">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('first-aid-records.index') }}">First Aid Records</a>
        <span>&gt;</span>
        <span>Record Treatment</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1 text-warning">Record First Aid Treatment</h1>
            <p class="dashboard-subheading mb-0">Record campus minor injuries, wound dressings, cold/warm compress, and first aid care.</p>
        </div>

        <a href="{{ route('first-aid-records.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to First Aid Log
        </a>
    </div>

    <form method="POST" action="{{ route('first-aid-records.store') }}" novalidate>
        @csrf

        @include('clinic-visits.partials.patient-select', [
            'preselectedPatient' => $preselectedPatient,
            'defaultClinician' => $defaultClinician,
            'visit' => $visit,
        ])

        @include('clinic-visits.partials.vitals-form', ['visit' => $visit])

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bandaid text-warning fs-5"></i>
                    <span class="fw-bold text-dark">Injury Details &amp; First Aid Rendered</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Injury / Condition Type</label>
                        <select name="injury_type" class="form-select @error('injury_type') is-invalid @enderror" required>
                            <option value="">-- Select Injury Type --</option>
                            @foreach($injuryTypes as $type)
                                <option value="{{ $type }}" @selected(old('injury_type', $detail->injury_type) === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('injury_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Body Part / Anatomical Site</label>
                        <input type="text" name="body_part" value="{{ old('body_part', $detail->body_part) }}" class="form-control" placeholder="e.g. Left knee, Right index finger, Forehead">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Incident Location (Campus)</label>
                        <input type="text" name="incident_location" value="{{ old('incident_location', $detail->incident_location) }}" class="form-control" placeholder="e.g. Gym basketball court, Chemical Lab, Quadrangle">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Cause / Mechanism of Injury</label>
                        <input type="text" name="incident_cause" value="{{ old('incident_cause', $detail->incident_cause) }}" class="form-control" placeholder="e.g. Tripped during PE class, Cut by laboratory glassware">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Chief Complaint / Initial Presentation</label>
                        <textarea name="chief_complaint" rows="2" class="form-control" placeholder="Patient's reported sensation or presenting symptoms...">{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small text-uppercase">First Aid Measures Applied</label>
                        <div class="row g-2">
                            @php
                                $selectedActions = old('first_aid_given', $detail->first_aid_given ?? []);
                            @endphp
                            @foreach($actionOptions as $act)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="first_aid_given[]" value="{{ $act }}" id="act_{{ md5($act) }}" @checked(in_array($act, (array)$selectedActions))>
                                        <label class="form-check-label small" for="act_{{ md5($act) }}">
                                            {{ $act }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Treatment Notes &amp; Observations</label>
                        <textarea name="treatment_notes" rows="3" class="form-control" placeholder="Clinical observations, wound size, bleeding controlled, pain decreased...">{{ old('treatment_notes', $detail->treatment_notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Clinic Supplies Used</label>
                        <textarea name="supplies_used" rows="3" class="form-control" placeholder="e.g. Sterile gauze 4x4 (2 pcs), Povidone-iodine, Micropore, Ice pack (1)...">{{ old('supplies_used', $detail->supplies_used) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="was_ref" name="was_referred" value="1" @checked(old('was_referred', $detail->was_referred))>
                            <label class="form-check-label fw-medium text-dark" for="was_ref">
                                Referred to Hospital / Outside Physician
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Referred To (if applicable)</label>
                        <input type="text" name="referred_to" value="{{ old('referred_to', $detail->referred_to) }}" class="form-control" placeholder="e.g. Caraga Regional Hospital (X-ray evaluation)">
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('first-aid-records.index') }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-warning px-4 py-2 fw-semibold text-dark">
                <i class="bi bi-check2-circle me-1"></i> Save First Aid Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>

