<x-layouts.app title="Edit Medicine: {{ $medicine->generic_name }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Medicines</a>
        <span>&gt;</span>
        <span>Edit Medicine</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Medicine: {{ $medicine->generic_name }}</h1>
            <p class="dashboard-subheading mb-0">Update formulary details, specifications, and reorder levels.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Batches
            </a>
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Masterlist
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('medicines.update', $medicine) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-capsule text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Primary Medicine Identification</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Medicine Code / SKU</label>
                                <input type="text" name="code" value="{{ old('code', $medicine->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label required">Category</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id', $medicine->category_id) == $cat->id)>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-7">
                                <label class="form-label required">Generic Name</label>
                                <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}" class="form-control @error('generic_name') is-invalid @enderror" required>
                                @error('generic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Brand Name (Optional)</label>
                                <input type="text" name="brand_name" value="{{ old('brand_name', $medicine->brand_name) }}" class="form-control @error('brand_name') is-invalid @enderror">
                                @error('brand_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">Dosage Form</label>
                                <select name="dosage_form" class="form-select @error('dosage_form') is-invalid @enderror" required>
                                    <option value="">-- Select Form --</option>
                                    @foreach(['Tablet', 'Capsule', 'Syrup', 'Suspension', 'Drops', 'Ointment', 'Cream', 'Gel', 'Injection', 'Inhaler', 'Solution', 'Patch', 'Powder'] as $form)
                                        <option value="{{ $form }}" @selected(old('dosage_form', $medicine->dosage_form) === $form)>{{ $form }}</option>
                                    @endforeach
                                </select>
                                @error('dosage_form')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Strength / Concentration</label>
                                <input type="text" name="strength" value="{{ old('strength', $medicine->strength) }}" class="form-control @error('strength') is-invalid @enderror">
                                @error('strength')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">Unit of Measure</label>
                                <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">-- Select Unit --</option>
                                    @foreach(['Piece', 'Tablet', 'Capsule', 'Bottle', 'Strip', 'Box', 'Vial', 'Ampoule', 'Tube', 'Sachet', 'Blister Pack'] as $unit)
                                        <option value="{{ $unit }}" @selected(old('unit', $medicine->unit) === $unit)>{{ $unit }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-medical text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Clinical &amp; Storage Details</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Indications / Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $medicine->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Possible Side Effects / Precautions</label>
                                <textarea name="side_effects" rows="2" class="form-control @error('side_effects') is-invalid @enderror">{{ old('side_effects', $medicine->side_effects) }}</textarea>
                                @error('side_effects')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Storage Conditions</label>
                                <input type="text" name="storage_conditions" value="{{ old('storage_conditions', $medicine->storage_conditions) }}" class="form-control @error('storage_conditions') is-invalid @enderror">
                                @error('storage_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Inventory Settings</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Reorder Level Threshold</label>
                            <div class="input-group">
                                <input type="number" name="reorder_level" min="0" value="{{ old('reorder_level', $medicine->reorder_level) }}" class="form-control @error('reorder_level') is-invalid @enderror" required>
                                <span class="input-group-text bg-light text-muted">units</span>
                            </div>
                            <small class="text-muted d-block mt-1">Alert triggers when total unexpired stock drops to or below this level.</small>
                            @error('reorder_level')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-3">

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_prescription_required" name="is_prescription_required" value="1" @checked(old('is_prescription_required', $medicine->is_prescription_required))>
                            <label class="form-check-label fw-medium text-dark" for="is_prescription_required">
                                Prescription Required (Rx)
                            </label>
                            <small class="text-muted d-block">Requires licensed clinician approval to dispense.</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $medicine->is_active))>
                            <label class="form-check-label fw-medium text-dark" for="is_active">
                                Active in Formulary
                            </label>
                            <small class="text-muted d-block">Inactive medicines cannot be selected for new stock receipts or dispensing.</small>
                        </div>
                    </div>
                </div>

                <div class="card border bg-light">
                    <div class="card-body d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Update Medicine
                        </button>
                        <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary w-100 py-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>

