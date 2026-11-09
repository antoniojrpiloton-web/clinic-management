@props(['visit' => null])

<div class="card border mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-right text-primary fs-5"></i>
            <span class="fw-bold text-dark">Disposition &amp; Remarks</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Patient Disposition</label>
                <select name="disposition" class="form-select @error('disposition') is-invalid @enderror">
                    <option value="">-- Select Disposition --</option>
                    @foreach(\App\Models\ClinicVisit::DISPOSITIONS as $dKey => $dLbl)
                        <option value="{{ $dKey }}" @selected(old('disposition', $visit?->disposition) === $dKey)>
                            {{ $dLbl }}
                        </option>
                    @endforeach
                </select>
                @error('disposition')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required">Visit Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach(\App\Models\ClinicVisit::STATUSES as $sKey => $sLbl)
                        <option value="{{ $sKey }}" @selected(old('status', $visit?->status ?? 'completed') === $sKey)>
                            {{ $sLbl }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label">General Remarks / Care Instructions</label>
                <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror" placeholder="Clinical notes, patient counseling, or observation notes...">{{ old('remarks', $visit?->remarks) }}</textarea>
                @error('remarks')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

