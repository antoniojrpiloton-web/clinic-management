@props([
    'preselectedPatient' => null,
    'defaultClinician' => null,
    'visit' => null,
])

<div class="card border mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-vcard text-primary fs-5"></i>
            <span class="fw-bold text-dark">Patient &amp; Doctor / Nurse</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label required">Patient</label>
                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search patient by ID number, first name, or last name..." required>
                    @if(old('patient_id') || $preselectedPatient)
                        @php
                            $p = $preselectedPatient ?? \App\Models\Patient::find(old('patient_id'));
                        @endphp
                        @if($p)
                            <option value="{{ $p->id }}" selected>
                                {{ $p->full_name }} ({{ $p->primary_identifier }} • {{ ucfirst($p->type) }})
                            </option>
                        @endif
                    @endif
                </select>
                @error('patient_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted d-block mt-1">Patient details will be saved with this record.</small>
            </div>

            <div class="col-md-5">
                <label class="form-label">Doctor or Nurse</label>
                <select name="clinician_id" id="clinician_id" class="form-select @error('clinician_id') is-invalid @enderror" data-ajax-url="{{ route('select2.clinicians') }}" data-placeholder="Search doctor or nurse...">
                    @php
                        $cId = old('clinician_id', $visit?->clinician_id ?? $defaultClinician?->id);
                        $c = $cId ? \App\Models\Clinician::find($cId) : null;
                    @endphp
                    @if($c)
                        <option value="{{ $c->id }}" selected>
                            {{ $c->name }} ({{ $c->type }}{{ $c->license_number ? ' - Lic: '.$c->license_number : '' }})
                        </option>
                    @endif
                </select>
                @error('clinician_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Visit Category</label>
                <select name="visit_category" class="form-select @error('visit_category') is-invalid @enderror" required>
                    @foreach(\App\Models\ClinicVisit::CATEGORIES as $key => $lbl)
                        <option value="{{ $key }}" @selected(old('visit_category', $visit?->visit_category ?? 'walk_in') === $key)>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
                @error('visit_category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Date &amp; Time In</label>
                <input type="datetime-local" name="visit_date" value="{{ old('visit_date', $visit?->visit_date ? $visit->visit_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="form-control @error('visit_date') is-invalid @enderror" required>
                @error('visit_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Time Out (Optional)</label>
                <input type="datetime-local" name="time_out" value="{{ old('time_out', $visit?->time_out ? $visit->time_out->format('Y-m-d\TH:i') : '') }}" class="form-control @error('time_out') is-invalid @enderror">
                @error('time_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

