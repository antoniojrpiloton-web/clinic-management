<x-layouts.app title="Deduct Stock (Stock-Out)">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Stock-Out / Adjustment</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Deduct Medicine Stock (Stock-Out / Adjustment)</h1>
            <p class="dashboard-subheading mb-0">Record non-dispensing inventory write-offs, damaged stock, expired goods, or count reconciliations.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('inventory.transactions') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-clock-history me-1"></i> Movement Ledger
            </a>
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Medicine Masterlist
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Validation error:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.stock-out.process') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-up text-danger fs-5"></i>
                            <span class="fw-bold text-dark">Medicine &amp; Batch Selection</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label required">Select Medicine</label>
                                <select name="medicine_id" id="medicine_select" class="form-select @error('medicine_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Medicine --</option>
                                    @foreach($medicines as $med)
                                        <option value="{{ $med->id }}" data-unit="{{ $med->unit }}" @selected(old('medicine_id', $preselectedMedicine?->id) == $med->id)>
                                            {{ $med->display_name }} (Total Available: {{ $med->stock_on_hand }} {{ $med->unit }}s)
                                        </option>
                                    @endforeach
                                </select>
                                @error('medicine_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label required">Select Batch / Lot to Deduct From</label>
                                <select name="batch_id" id="batch_select" class="form-select @error('batch_id') is-invalid @enderror" required>
                                    <option value="">-- Select medicine first --</option>
                                </select>
                                <div id="batch_info" class="small text-muted mt-1"></div>
                                @error('batch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Quantity to Deduct</label>
                                <div class="input-group">
                                    <input type="number" name="quantity" id="deduct_qty" min="1" value="{{ old('quantity', 1) }}" class="form-control @error('quantity') is-invalid @enderror" required>
                                    <span class="input-group-text bg-light text-muted" id="unit_addon">units</span>
                                </div>
                                @error('quantity')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Reason Category</label>
                                <select name="reason_type" class="form-select @error('reason_type') is-invalid @enderror" required>
                                    <option value="">-- Select Reason --</option>
                                    <option value="damage" @selected(old('reason_type') === 'damage')>Damaged / Broken Packaging</option>
                                    <option value="disposal" @selected(old('reason_type') === 'disposal')>Expired / Safe Medical Disposal</option>
                                    <option value="return" @selected(old('reason_type') === 'return')>Supplier Return / Recall</option>
                                    <option value="adjustment" @selected(old('reason_type') === 'adjustment')>Inventory Audit Discrepancy</option>
                                    <option value="other" @selected(old('reason_type') === 'other')>Other Justified Write-off</option>
                                </select>
                                @error('reason_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label required">Reason &amp; Clinical Justification</label>
                                <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Describe the reason for deducting this stock (e.g. Broken ampoule during shelf stocking, Batch expired on Oct 30)" required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reference / Incident Report #</label>
                                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="form-control @error('reference_number') is-invalid @enderror" placeholder="e.g. IR-2026-004">
                                @error('reference_number')
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
                            <i class="bi bi-shield-exclamation text-danger fs-5"></i>
                            <span class="fw-bold text-dark">Audit &amp; Stock Impact</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Requested By:</span>
                            <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Batch Available:</span>
                            <span class="fw-bold text-dark" id="preview_available">0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Est. Remaining:</span>
                            <span class="fw-bold text-primary" id="preview_remaining">0</span>
                        </div>
                        <div class="alert alert-warning border-warning small mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Stock-out cannot be reversed directly; a subsequent Stock-In receipt or audit transaction would be required.
                        </div>
                    </div>
                </div>

                <div class="card border bg-light">
                    <div class="card-body d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Confirm Stock-Out
                        </button>
                        <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary w-100 py-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const medSelect = document.getElementById('medicine_select');
                const batchSelect = document.getElementById('batch_select');
                const qtyInput = document.getElementById('deduct_qty');
                const unitAddon = document.getElementById('unit_addon');
                const previewAvail = document.getElementById('preview_available');
                const previewRem = document.getElementById('preview_remaining');
                const preselectedBatchId = "{{ old('batch_id', request()->query('batch_id')) }}";

                function loadBatches() {
                    const medId = medSelect.value;
                    const opt = medSelect.options[medSelect.selectedIndex];
                    if (opt && opt.dataset.unit) {
                        unitAddon.textContent = opt.dataset.unit + 's';
                    }

                    batchSelect.innerHTML = '<option value="">-- Loading batches... --</option>';
                    if (!medId) {
                        batchSelect.innerHTML = '<option value="">-- Select medicine first --</option>';
                        previewAvail.textContent = '0';
                        previewRem.textContent = '0';
                        return;
                    }

                    fetch(`{{ route('select2.medicine-batches') }}?medicine_id=${medId}`)
                        .then(r => r.json())
                        .then(data => {
                            batchSelect.innerHTML = '<option value="">-- Choose Batch / Lot --</option>';
                            if (!data.results || data.results.length === 0) {
                                batchSelect.innerHTML = '<option value="">-- No batches available with remaining stock --</option>';
                                return;
                            }
                            data.results.forEach(b => {
                                const optEl = document.createElement('option');
                                optEl.value = b.id;
                                optEl.textContent = `${b.batch_number} (Exp: ${b.expiration_date} | Stock: ${b.quantity_remaining})`;
                                optEl.dataset.qty = b.quantity_remaining;
                                if (String(b.id) === String(preselectedBatchId)) {
                                    optEl.selected = true;
                                }
                                batchSelect.appendChild(optEl);
                            });
                            calculatePreview();
                        })
                        .catch(() => {
                            batchSelect.innerHTML = '<option value="">-- Error loading batches --</option>';
                        });
                }

                function calculatePreview() {
                    const batchOpt = batchSelect.options[batchSelect.selectedIndex];
                    const avail = batchOpt && batchOpt.dataset.qty ? parseInt(batchOpt.dataset.qty, 10) : 0;
                    const deduct = parseInt(qtyInput.value, 10) || 0;
                    previewAvail.textContent = avail;
                    const rem = avail - deduct;
                    previewRem.textContent = rem >= 0 ? rem : 'Insufficient!';
                    previewRem.className = rem < 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
                }

                medSelect.addEventListener('change', loadBatches);
                batchSelect.addEventListener('change', calculatePreview);
                qtyInput.addEventListener('input', calculatePreview);

                if (medSelect.value) {
                    loadBatches();
                }
            });
        </script>
    @endpush
</x-layouts.app>

