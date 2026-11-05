<x-layouts.app title="New Medical Consultation">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medical-consultations.index') }}">Medical Consultations</a>
        <span>&gt;</span>
        <span>Record Consultation</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">New Medical Consultation</h1>
            <p class="dashboard-subheading mb-0">Record outpatient medical examination, clinical diagnosis, prescriptions, and health clearance.</p>
        </div>

        <a href="{{ route('medical-consultations.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Consultations
        </a>
    </div>

    <form method="POST" action="{{ route('medical-consultations.store') }}" novalidate>
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
                    <i class="bi bi-clipboard2-pulse text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Clinical Assessment &amp; Consultation Findings</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required">Chief Complaint</label>
                        <textarea name="chief_complaint" rows="2" class="form-control @error('chief_complaint') is-invalid @enderror" placeholder="Primary reason for consult or symptoms reported by patient (e.g. High fever, productive cough, headache for 3 days)..." required>{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                        @error('chief_complaint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">History of Present Illness (HPI)</label>
                        <textarea name="history_of_present_illness" rows="3" class="form-control @error('history_of_present_illness') is-invalid @enderror" placeholder="Onset, duration, severity, modifying factors, associated symptoms...">{{ old('history_of_present_illness', $detail->history_of_present_illness) }}</textarea>
                        @error('history_of_present_illness')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Physical Examination Findings</label>
                        <textarea name="physical_findings" rows="3" class="form-control @error('physical_findings') is-invalid @enderror" placeholder="HEENT, chest/lungs, cardiac, abdominal, neurological or local findings...">{{ old('physical_findings', $detail->physical_findings) }}</textarea>
                        @error('physical_findings')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-9">
                        <label class="form-label required">Clinical Diagnosis</label>
                        <input type="text" name="diagnosis" value="{{ old('diagnosis', $detail->diagnosis) }}" class="form-control @error('diagnosis') is-invalid @enderror" placeholder="e.g. Acute Pharyngitis, Tension-type Headache, Acute Gastroenteritis" required>
                        @error('diagnosis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ICD-10 Code (Optional)</label>
                        <input type="text" name="icd_code" value="{{ old('icd_code', $detail->icd_code) }}" class="form-control @error('icd_code') is-invalid @enderror" placeholder="e.g. J02.9, G44.2">
                        @error('icd_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Treatment Plan / Management</label>
                        <textarea name="treatment_plan" rows="3" class="form-control @error('treatment_plan') is-invalid @enderror" placeholder="Clinical management, non-pharmacological care, hydration, rest...">{{ old('treatment_plan', $detail->treatment_plan) }}</textarea>
                        @error('treatment_plan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Medications Prescribed (Rx)</label>
                        <textarea name="medications_prescribed" rows="3" class="form-control @error('medications_prescribed') is-invalid @enderror" placeholder="Drug name, dosage, frequency, and duration (e.g. Paracetamol 500mg tab q4h PRN #10)...">{{ old('medications_prescribed', $detail->medications_prescribed) }}</textarea>
                        @error('medications_prescribed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Patient Advice / Education</label>
                        <textarea name="advice" rows="2" class="form-control @error('advice') is-invalid @enderror" placeholder="Lifestyle advice, red flag warning signs, diet, infection control precautions...">{{ old('advice', $detail->advice) }}</textarea>
                        @error('advice')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check text-success fs-5"></i>
                    <span class="fw-bold text-dark">Medical Certificate &amp; Referral Details</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="med_cert" name="medical_certificate_issued" value="1" @checked(old('medical_certificate_issued', $detail->medical_certificate_issued))>
                            <label class="form-check-label fw-medium text-dark" for="med_cert">
                                Medical Certificate Issued (HSD-4)
                            </label>
                            <small class="text-muted d-block">Indicates official excuse slip or clearance was provided.</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Recommended Rest / Excuse Days</label>
                        <div class="input-group">
                            <input type="number" min="0" max="60" name="rest_days" value="{{ old('rest_days', $detail->rest_days) }}" class="form-control @error('rest_days') is-invalid @enderror" placeholder="0">
                            <span class="input-group-text bg-light text-muted">days</span>
                        </div>
                        @error('rest_days')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Follow-up Date (Optional)</label>
                        <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $detail->follow_up_date ? $detail->follow_up_date->format('Y-m-d') : '') }}" min="{{ date('Y-m-d') }}" class="form-control @error('follow_up_date') is-invalid @enderror">
                        @error('follow_up_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Referred To (Facility / Specialist)</label>
                        <input type="text" name="referred_to" value="{{ old('referred_to', $detail->referred_to) }}" class="form-control @error('referred_to') is-invalid @enderror" placeholder="e.g. Caraga Regional Hospital, Pulmonologist, ENT Clinic">
                        @error('referred_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Referral Reason / Clinical Indication</label>
                        <input type="text" name="referral_reason" value="{{ old('referral_reason', $detail->referral_reason) }}" class="form-control @error('referral_reason') is-invalid @enderror" placeholder="e.g. For tertiary diagnostic evaluation, persistent symptoms">
                        @error('referral_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @include('clinic-visits.partials.disposition-form', ['visit' => $visit])

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('medical-consultations.index') }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Save Medical Consultation Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>

