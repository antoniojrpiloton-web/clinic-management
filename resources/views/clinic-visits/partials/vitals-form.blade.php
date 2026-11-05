@props(['visit' => null])

<div class="card border mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-heart-pulse text-danger fs-5"></i>
            <span class="fw-bold text-dark">Vital Signs &amp; Triage Assessment</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Blood Pressure (Systolic / Diastolic)</label>
                <div class="input-group">
                    <input type="number" name="systolic" min="50" max="300" placeholder="Sys" value="{{ old('systolic', $visit?->systolic) }}" class="form-control @error('systolic') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">/</span>
                    <input type="number" name="diastolic" min="30" max="200" placeholder="Dia" value="{{ old('diastolic', $visit?->diastolic) }}" class="form-control @error('diastolic') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">mmHg</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Body Temperature</label>
                <div class="input-group">
                    <input type="number" step="0.1" name="temperature" min="30" max="45" placeholder="e.g. 36.8" value="{{ old('temperature', $visit?->temperature) }}" class="form-control @error('temperature') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">°C</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Pulse / Heart Rate</label>
                <div class="input-group">
                    <input type="number" name="pulse_rate" min="20" max="250" placeholder="e.g. 78" value="{{ old('pulse_rate', $visit?->pulse_rate) }}" class="form-control @error('pulse_rate') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">bpm</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Respiratory Rate</label>
                <div class="input-group">
                    <input type="number" name="respiratory_rate" min="5" max="80" placeholder="e.g. 18" value="{{ old('respiratory_rate', $visit?->respiratory_rate) }}" class="form-control @error('respiratory_rate') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">cpm</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Oxygen Saturation (SpO2)</label>
                <div class="input-group">
                    <input type="number" name="oxygen_saturation" min="40" max="100" placeholder="e.g. 98" value="{{ old('oxygen_saturation', $visit?->oxygen_saturation) }}" class="form-control @error('oxygen_saturation') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">%</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Weight</label>
                <div class="input-group">
                    <input type="number" step="0.1" name="weight_kg" min="1" max="400" placeholder="e.g. 60.5" value="{{ old('weight_kg', $visit?->weight_kg) }}" class="form-control @error('weight_kg') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">kg</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Height</label>
                <div class="input-group">
                    <input type="number" step="0.1" name="height_cm" min="30" max="250" placeholder="e.g. 165.0" value="{{ old('height_cm', $visit?->height_cm) }}" class="form-control @error('height_cm') is-invalid @enderror">
                    <span class="input-group-text bg-light text-muted">cm</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Pain Scale (0 - 10)</label>
                <select name="pain_scale" class="form-select @error('pain_scale') is-invalid @enderror">
                    <option value="">-- No Pain (0) --</option>
                    @for($i = 0; $i <= 10; $i++)
                        <option value="{{ $i }}" @selected(old('pain_scale', $visit?->pain_scale) !== null && (int)old('pain_scale', $visit?->pain_scale) === $i)>
                            {{ $i }} - {{ $i === 0 ? 'No Pain' : ($i <= 3 ? 'Mild Pain' : ($i <= 6 ? 'Moderate Pain' : 'Severe Pain')) }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>
    </div>
</div>

