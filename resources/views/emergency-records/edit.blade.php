<x-layouts.app title="Edit Emergency Record: {{ $visit->visit_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('emergency-records.index') }}">Emergency Records</a>
        <span>&gt;</span>
        <a href="{{ route('emergency-records.show', $visit) }}">{{ $visit->visit_number }}</a>
        <span>&gt;</span>
        <span>Edit</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1 text-danger">Edit Emergency Record: {{ $visit->visit_number }}</h1>
            <p class="dashboard-subheading mb-0">Update triage details, emergency interventions, and transfer documentation.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('emergency-records.show', $visit) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Record
            </a>
            <a href="{{ route('emergency-records.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('emergency-records.update', $visit) }}" novalidate>
        @csrf
        @method('PUT')

        @include('clinic-visits.partials.patient-select', [
            'preselectedPatient' => $preselectedPatient,
            'defaultClinician' => $defaultClinician,
            'visit' => $visit,
        ])

        @include('clinic-visits.partials.vitals-form', ['visit' => $visit])

        <div class="card border border-danger mb-4">
            <div class="card-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span class="fw-bold">Triage Level &amp; Emergency Classification</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required fw-bold text-dark">Triage Priority Level</label>
                        <select name="triage_level" class="form-select @error('triage_level') is-invalid @enderror" required>
                            <option value="">-- Select Triage Category --</option>
                            @foreach($triageLevels as $tKey => $tData)
                                <option value="{{ $tKey }}" @selected(old('triage_level', $detail->triage_level) === $tKey)>
                                    {{ $tData['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('triage_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required fw-bold text-dark">Emergency Nature</label>
                        <select name="emergency_nature" class="form-select @error('emergency_nature') is-invalid @enderror" required>
                            <option value="">-- Select Emergency Nature --</option>
                            @foreach($natures as $nat)
                                <option value="{{ $nat }}" @selected(old('emergency_nature', $detail->emergency_nature) === $nat)>
                                    {{ $nat }}
                                </option>
                            @endforeach
                        </select>
                        @error('emergency_nature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Level of Consciousness (AVPU)</label>
                        <select name="level_of_consciousness" class="form-select">
                            <option value="">-- Select Consciousness --</option>
                            @foreach($consciousnessLevels as $cKey => $cLbl)
                                <option value="{{ $cKey }}" @selected(old('level_of_consciousness', $detail->level_of_consciousness) === $cKey)>
                                    {{ $cLbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Glasgow Coma Scale (GCS: 3 - 15)</label>
                        <input type="number" name="gcs_score" min="3" max="15" value="{{ old('gcs_score', $detail->gcs_score) }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Incident Location (Campus)</label>
                        <input type="text" name="incident_location" value="{{ old('incident_location', $detail->incident_location) }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date &amp; Time of Incident</label>
                        <input type="datetime-local" name="incident_datetime" value="{{ old('incident_datetime', $detail->incident_datetime ? $detail->incident_datetime->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label required">Primary Complaint / Presenting Emergency Symptoms</label>
                        <textarea name="chief_complaint" rows="2" class="form-control @error('chief_complaint') is-invalid @enderror" required>{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                        @error('chief_complaint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Detailed Incident Narrative / Description</label>
                        <textarea name="incident_description" rows="3" class="form-control">{{ old('incident_description', $detail->incident_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bandaid text-danger fs-5"></i>
                    <span class="fw-bold text-dark">Emergency Interventions Rendered</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    @php
                        $selectedInterventions = old('interventions', $detail->interventions ?? []);
                    @endphp
                    @foreach($interventionOptions as $interv)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="interventions[]" value="{{ $interv }}" id="int_{{ md5($interv) }}" @checked(in_array($interv, (array)$selectedInterventions))>
                                <label class="form-check-label small" for="int_{{ md5($interv) }}">
                                    {{ $interv }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label class="form-label">Intervention Notes / Medication Given</label>
                    <textarea name="intervention_notes" rows="3" class="form-control">{{ old('intervention_notes', $detail->intervention_notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-hospital text-danger fs-5"></i>
                    <span class="fw-bold text-dark">Ambulance, Hospital Transfer &amp; Guardian Notification</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="amb_call" name="ambulance_called" value="1" @checked(old('ambulance_called', $detail->ambulance_called))>
                                <label class="form-check-label fw-bold" for="amb_call">Ambulance Requested / Dispatched</label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="hosp_trans" name="transferred_to_hospital" value="1" @checked(old('transferred_to_hospital', $detail->transferred_to_hospital))>
                                <label class="form-check-label fw-bold" for="hosp_trans">Transferred to Tertiary Hospital</label>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Hospital Name</label>
                                <input type="text" name="hospital_name" value="{{ old('hospital_name', $detail->hospital_name) }}" class="form-control form-control-sm">
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Transport Mode</label>
                                    <input type="text" name="transport_mode" value="{{ old('transport_mode', $detail->transport_mode) }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Transfer Time</label>
                                    <input type="datetime-local" name="transferred_at" value="{{ old('transferred_at', $detail->transferred_at ? $detail->transferred_at->format('Y-m-d\TH:i') : '') }}" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="guard_notif" name="guardian_notified" value="1" @checked(old('guardian_notified', $detail->guardian_notified))>
                                <label class="form-check-label fw-bold" for="guard_notif">Guardian / Family Notified</label>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Person Notified (Name &amp; Relationship)</label>
                                <input type="text" name="notified_person" value="{{ old('notified_person', $detail->notified_person) }}" class="form-control form-control-sm">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Notification Time</label>
                                <input type="datetime-local" name="notified_at" value="{{ old('notified_at', $detail->notified_at ? $detail->notified_at->format('Y-m-d\TH:i') : '') }}" class="form-control form-control-sm">
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Incident Report #</label>
                                    <input type="text" name="incident_report_number" value="{{ old('incident_report_number', $detail->incident_report_number) }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Emergency Outcome</label>
                                    <select name="outcome" class="form-select form-select-sm">
                                        <option value="">-- Select Outcome --</option>
                                        @foreach($outcomes as $outc)
                                            <option value="{{ $outc }}" @selected(old('outcome', $detail->outcome) === $outc)>{{ $outc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('emergency-records.show', $visit) }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Update Emergency Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>

