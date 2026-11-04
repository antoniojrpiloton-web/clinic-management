<x-layouts.app title="Issue Medicine Dispensing">
    @pushOnce('styles', 'select2-styles')
        <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css') }}">
    @endpushOnce

    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <a href="{{ route('inventory.dispensings.index') }}">Dispensing</a>
        <span>&gt;</span>
        <span>Issue Medicines</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Issue Medicine Dispensing</h1>
            <p class="dashboard-subheading mb-0">Record medicines dispensed to patient with batch tracking, dosage instructions, and inventory deduction.</p>
        </div>

        <a href="{{ route('inventory.dispensings.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Dispensing Log
        </a>
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

    <form method="POST" action="{{ route('inventory.dispensings.store') }}" id="dispense-form" novalidate>
        @csrf

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Patient &amp; Prescriber Information</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Patient</label>
                        <select name="patient_id" id="patient_id" class="form-select" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search patient by ID, First or Last Name..." required>
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
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Prescribing Clinician / Doctor</label>
                        <select name="clinician_id" class="form-select @error('clinician_id') is-invalid @enderror">
                            <option value="">-- Direct Dispensing / OTC (No Prescriber) --</option>
                            @foreach($clinicians as $doc)
                                <option value="{{ $doc->id }}" @selected(old('clinician_id', $defaultClinician?->id) == $doc->id)>
                                    {{ $doc->name }} ({{ $doc->type }}{{ $doc->license_number ? ' - Lic: '.$doc->license_number : '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('clinician_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Dispensing Slip Number</label>
                        <input type="text" name="dispensing_number" value="{{ old('dispensing_number', $nextNumber) }}" class="form-control font-monospace" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Dispensed Date</label>
                        <input type="date" name="dispensed_date" value="{{ old('dispensed_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Diagnosis / Chief Complaint</label>
                        <input type="text" name="diagnosis" value="{{ old('diagnosis') }}" class="form-control" placeholder="e.g. Headache, Fever, Dyspepsia">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dispensing Notes / Special Instructions</label>
                        <textarea name="instructions" rows="2" class="form-control" placeholder="General precautions, dietary advice, or clinic return instructions">{{ old('instructions') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-capsule-pill text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Medicines to Dispense</span>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-item">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Medicine
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="dispensing-items-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 32%;">Medicine Item</th>
                            <th style="width: 28%;">Batch / Lot (Remaining Stock)</th>
                            <th style="width: 14%;">Quantity</th>
                            <th style="width: 22%;">Dosage Instructions (Sig)</th>
                            <th style="width: 4%;" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="dispensing-items-body">
                        <tr class="item-row" data-index="0">
                            <td>
                                <select name="items[0][medicine_id]" class="form-select medicine-select" required>
                                    <option value="">-- Choose Medicine --</option>
                                    @foreach($medicines as $m)
                                        <option value="{{ $m->id }}" data-unit="{{ $m->unit }}">
                                            {{ $m->display_name }} (Stock: {{ $m->stock_on_hand }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="items[0][batch_id]" class="form-select batch-select" required>
                                    <option value="">-- Select medicine first --</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="items[0][quantity]" class="form-control qty-input" min="1" value="1" required>
                                    <span class="input-group-text bg-light text-muted unit-label">pcs</span>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="items[0][dosage_instruction]" class="form-control" placeholder="e.g. 1 tab TID after meals for 3d">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light border text-danger btn-remove-row" title="Remove Item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('inventory.dispensings.index') }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Confirm &amp; Dispense Medicines
            </button>
        </div>
    </form>

    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <select name="items[__INDEX__][medicine_id]" class="form-select medicine-select" required>
                    <option value="">-- Choose Medicine --</option>
                    @foreach($medicines as $m)
                        <option value="{{ $m->id }}" data-unit="{{ $m->unit }}">
                            {{ $m->display_name }} (Stock: {{ $m->stock_on_hand }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[__INDEX__][batch_id]" class="form-select batch-select" required>
                    <option value="">-- Select medicine first --</option>
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="items[__INDEX__][quantity]" class="form-control qty-input" min="1" value="1" required>
                    <span class="input-group-text bg-light text-muted unit-label">pcs</span>
                </div>
            </td>
            <td>
                <input type="text" name="items[__INDEX__][dosage_instruction]" class="form-control" placeholder="e.g. 1 tab TID after meals for 3d">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light border text-danger btn-remove-row" title="Remove Item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    @pushOnce('scripts', 'select2-scripts')
        <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    @endpushOnce

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const patientSelect = $('#patient_id');
                if (patientSelect.length && typeof patientSelect.select2 === 'function') {
                    patientSelect.select2({
                        theme: 'bootstrap-5',
                        placeholder: patientSelect.data('placeholder') || 'Search patient...',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: patientSelect.data('ajax-url'),
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term || '',
                                    page: params.page || 1
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.results || [],
                                    pagination: {
                                        more: data.pagination ? data.pagination.more : false
                                    }
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 1
                    });
                }

                let rowIndex = 1;
                const tbody = document.getElementById('dispensing-items-body');
                const btnAdd = document.getElementById('btn-add-item');
                const template = document.getElementById('item-row-template');

                function initRowEvents(row) {
                    const medSelect = row.querySelector('.medicine-select');
                    const batchSelect = row.querySelector('.batch-select');
                    const unitLabel = row.querySelector('.unit-label');
                    const removeBtn = row.querySelector('.btn-remove-row');

                    medSelect.addEventListener('change', function () {
                        const medId = this.value;
                        const opt = this.options[this.selectedIndex];
                        if (opt && opt.dataset.unit) {
                            unitLabel.textContent = opt.dataset.unit + 's';
                        }

                        batchSelect.innerHTML = '<option value="">-- Loading... --</option>';
                        if (!medId) {
                            batchSelect.innerHTML = '<option value="">-- Select medicine first --</option>';
                            return;
                        }

                        fetch(`{{ route('select2.medicine-batches') }}?medicine_id=${medId}`)
                            .then(r => r.json())
                            .then(data => {
                                batchSelect.innerHTML = '<option value="">-- Choose Batch --</option>';
                                if (!data.results || data.results.length === 0) {
                                    batchSelect.innerHTML = '<option value="">-- No stock available --</option>';
                                    return;
                                }
                                data.results.forEach(b => {
                                    const bOpt = document.createElement('option');
                                    bOpt.value = b.id;
                                    bOpt.textContent = `${b.batch_number} (Exp: ${b.expiration_date} | Avail: ${b.quantity_remaining})`;
                                    batchSelect.appendChild(bOpt);
                                });
                            })
                            .catch(() => {
                                batchSelect.innerHTML = '<option value="">-- Error loading batches --</option>';
                            });
                    });

                    removeBtn.addEventListener('click', function () {
                        const rows = tbody.querySelectorAll('.item-row');
                        if (rows.length > 1) {
                            row.remove();
                        } else {
                            alert('A dispensing record must include at least one medicine item.');
                        }
                    });
                }

                initRowEvents(tbody.querySelector('.item-row'));

                btnAdd.addEventListener('click', function () {
                    const html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
                    const tempWrap = document.createElement('tbody');
                    tempWrap.innerHTML = html;
                    const newRow = tempWrap.firstElementChild;
                    tbody.appendChild(newRow);
                    initRowEvents(newRow);
                });
            });
        </script>
    @endpush
</x-layouts.app>

