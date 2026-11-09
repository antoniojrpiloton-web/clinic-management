<x-layouts.app title="Receive Stock (Stock-In)">
    @include('partials.plugins.select2')

    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Stock-In Receipt</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Receive Medicine Stock (Stock-In)</h1>
            <p class="dashboard-subheading mb-0">Record incoming medicine deliveries, lot/batch numbers, suppliers, and expiration dates with instant server-side lookup.</p>
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

    <form method="POST" action="{{ route('inventory.stock-in.process') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-in-down text-success fs-5"></i>
                            <span class="fw-bold text-dark">Delivery &amp; Batch Information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label required">Select Medicine (Server-Side Search)</label>
                                <select
                                    name="medicine_id"
                                    id="medicine_select"
                                    class="form-select @error('medicine_id') is-invalid @enderror"
                                    data-select2
                                    data-ajax-url="{{ route('select2.medicines') }}"
                                    data-placeholder="Search medicine by generic name, brand, or code..."
                                    required
                                >
                                    @php
                                        $selectedMed = $preselectedMedicine ?? (old('medicine_id') ? \App\Models\Medicine::find(old('medicine_id')) : null);
                                    @endphp
                                    @if ($selectedMed)
                                        <option value="{{ $selectedMed->id }}" selected data-unit="{{ $selectedMed->unit }}" data-stock="{{ $selectedMed->stock_on_hand }}">
                                            {{ $selectedMed->display_name }} (Current Stock: {{ $selectedMed->stock_on_hand }} {{ $selectedMed->unit }}s)
                                        </option>
                                    @endif
                                </select>
                                <div class="form-text small text-muted">Type at least 1-2 characters to search the university clinic formulary.</div>
                                @error('medicine_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Batch / Lot Number</label>
                                <input type="text" name="batch_number" value="{{ old('batch_number') }}" class="form-control @error('batch_number') is-invalid @enderror" placeholder="e.g. LOT-2026-09A" required>
                                <small class="text-muted">Exact lot number printed on packaging.</small>
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Expiration Date</label>
                                <input type="date" name="expiration_date" id="expiration_date" value="{{ old('expiration_date', now()->addYear()->format('Y-m-d')) }}" min="{{ now()->addDay()->format('Y-m-d') }}" class="form-control @error('expiration_date') is-invalid @enderror" required>
                                <small class="text-muted">Must be a future expiration date.</small>
                                @error('expiration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Quantity Received</label>
                                <div class="input-group">
                                    <input type="number" name="quantity_received" id="quantity_received" min="1" value="{{ old('quantity_received', 100) }}" class="form-control @error('quantity_received') is-invalid @enderror" required>
                                    <span class="input-group-text bg-light text-muted" id="unit_addon">{{ $selectedMed ? $selectedMed->unit.'s' : 'units' }}</span>
                                </div>
                                @error('quantity_received')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unit Cost (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">₱</span>
                                    <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost') }}" class="form-control @error('unit_cost') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('unit_cost')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-truck text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Supplier &amp; Purchase Reference</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier / Distributor</label>
                                <input type="text" name="supplier" value="{{ old('supplier') }}" class="form-control @error('supplier') is-invalid @enderror" placeholder="e.g. Zuellig Pharma, Metro Drug, DOH Depot">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Delivery Receipt / Invoice #</label>
                                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="form-control @error('reference_number') is-invalid @enderror" placeholder="e.g. DR-2026-9876">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Date Received</label>
                                <input type="date" name="received_date" value="{{ old('received_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="form-control @error('received_date') is-invalid @enderror" required>
                                @error('received_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remarks / Storage Location</label>
                                <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control @error('remarks') is-invalid @enderror" placeholder="e.g. Cabinet B Shelf 2, Room Temperature">
                                @error('remarks')
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
                            <i class="bi bi-capsule text-success fs-5"></i>
                            <span class="fw-bold text-dark">Selected Product Profile</span>
                        </div>
                    </div>
                    <div class="card-body" id="med_profile_box">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Current Stock:</span>
                            <span class="fw-bold text-dark" id="preview_stock">{{ $selectedMed ? number_format($selectedMed->stock_on_hand).' '.$selectedMed->unit.'s' : 'Select product' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Batch Shelf-Life:</span>
                            <span class="text-dark fw-medium" id="life_preview">Calculate on date select</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Stock-In By:</span>
                            <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            Server-side inventory updates and automated FEFO lot tracking will be applied upon submission.
                        </div>
                    </div>
                </div>

                <div class="card border bg-light">
                    <div class="card-body d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Submit Stock-In Receipt
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
                const medSelect = $('#medicine_select');
                const unitAddon = document.getElementById('unit_addon');
                const previewStock = document.getElementById('preview_stock');
                const expInput = document.getElementById('expiration_date');
                const lifePreview = document.getElementById('life_preview');

                medSelect.on('select2:select', function (e) {
                    const data = e.params.data;
                    if (data && data.unit) {
                        unitAddon.textContent = data.unit + 's';
                    }
                    if (data && data.stock_on_hand !== undefined) {
                        previewStock.textContent = `${Number(data.stock_on_hand).toLocaleString()} ${(data.unit || 'unit')}s`;
                    }
                });

                medSelect.on('select2:clear', function () {
                    unitAddon.textContent = 'units';
                    previewStock.textContent = 'Select product';
                });

                function updateLife() {
                    if (!expInput.value) return;
                    const exp = new Date(expInput.value);
                    const now = new Date();
                    const diffTime = exp - now;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays > 0) {
                        const months = Math.round(diffDays / 30);
                        lifePreview.textContent = `${diffDays} days (~${months} mos)`;
                    } else {
                        lifePreview.textContent = 'Expired / Invalid';
                    }
                }

                expInput.addEventListener('change', updateLife);
                updateLife();
            });
        </script>
    @endpush
</x-layouts.app>
