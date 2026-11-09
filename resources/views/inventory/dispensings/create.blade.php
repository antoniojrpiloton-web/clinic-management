<x-layouts.pos title="Pharmacy POS Dispensing Terminal" :slip-number="$nextNumber">
    @include('partials.plugins.select2')

    <style>
        .pos-container {
            display: flex;
            flex-direction: row;
            height: calc(100vh - 52px);
            background: #eef2ef;
            overflow: hidden;
        }
        @media (max-width: 991.98px) {
            .pos-container {
                flex-direction: column;
                height: auto;
                overflow: visible;
            }
        }
        .pos-catalog-pane {
            flex: 1 1 58%;
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #d4ded7;
            background: #f4f7f5;
        }
        .pos-ticket-pane {
            flex: 1 1 42%;
            min-width: 360px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .pos-pane-header {
            padding: 0.85rem 1.15rem;
            background: #ffffff;
            border-bottom: 1px solid #dce4df;
            flex-shrink: 0;
        }
        .pos-catalog-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem;
        }
        .pos-ticket-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem 1.15rem;
        }
        .pos-ticket-footer {
            padding: 0.85rem 1.15rem;
            background: #fbfdfc;
            border-top: 2px solid #e1e9e4;
            flex-shrink: 0;
        }
        .pos-search-input {
            border-radius: 8px;
            padding: 0.6rem 0.85rem 0.6rem 2.4rem;
            font-size: 0.92rem;
            border: 1px solid #c7d5cd;
            background: #ffffff;
        }
        .pos-search-input:focus {
            border-color: #1b5e3a;
            box-shadow: 0 0 0 3px rgba(27, 94, 58, 0.12);
        }
        .pos-cat-pill {
            display: inline-block;
            padding: 0.3rem 0.75rem;
            font-size: 0.76rem;
            font-weight: 600;
            border-radius: 20px;
            border: 1px solid #cdd8d2;
            background: #ffffff;
            color: #384d42;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .pos-cat-pill:hover,
        .pos-cat-pill.active {
            background: #1b5e3a;
            color: #ffffff;
            border-color: #1b5e3a;
        }
        .pos-med-card {
            background: #ffffff;
            border: 1px solid #dbe4de;
            border-radius: 8px;
            padding: 0.8rem 0.9rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .pos-med-card:hover {
            transform: translateY(-2px);
            border-color: #1b5e3a;
            box-shadow: 0 4px 12px rgba(27, 94, 58, 0.08);
        }
        .pos-med-card.is-out-of-stock {
            opacity: 0.65;
            cursor: not-allowed;
            background: #fafafa;
        }
        .pos-med-card.is-out-of-stock:hover {
            transform: none;
            border-color: #dbe4de;
            box-shadow: none;
        }
        .pos-ticket-item {
            background: #fbfdfc;
            border: 1px solid #dce5e0;
            border-radius: 8px;
            padding: 0.75rem 0.85rem;
            margin-bottom: 0.65rem;
            transition: border-color 0.15s ease;
        }
        .pos-ticket-item:hover {
            border-color: #1b5e3a;
        }
        .pos-qty-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .pos-sig-chip {
            display: inline-block;
            font-size: 0.68rem;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            background: #eef3f0;
            color: #214332;
            border: 1px solid #c9d8d0;
            cursor: pointer;
            margin-right: 0.25rem;
            margin-top: 0.25rem;
        }
        .pos-sig-chip:hover {
            background: #1b5e3a;
            color: #ffffff;
            border-color: #1b5e3a;
        }
    </style>

    <form method="POST" action="{{ route('inventory.dispensings.store') }}" id="pos-dispense-form" class="h-100">
        @csrf
        <input type="hidden" name="dispensing_number" value="{{ old('dispensing_number', $nextNumber) }}">
        <input type="hidden" name="dispensed_date" value="{{ old('dispensed_date', now()->format('Y-m-d')) }}">

        <div class="pos-container">
            <div class="pos-catalog-pane">
                <div class="pos-pane-header">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-capsule-pill me-1"></i>Pharmacy POS Dispensing Terminal
                            </span>
                            <span class="text-muted small">Catalog Quick Launch</span>
                        </div>
                        <span class="small text-muted font-monospace"><kbd>F2</kbd> Focus Search &bull; <kbd>F9</kbd> Issue</span>
                    </div>

                    <div class="position-relative mb-2">
                        <i class="bi bi-search position-absolute text-muted" style="left: 0.85rem; top: 0.75rem; font-size: 0.95rem;"></i>
                        <input type="text" id="pos-med-search" class="form-control pos-search-input" placeholder="Search medicines by generic name, brand, dosage form, or code... (F2)" autocomplete="off">
                        <button type="button" id="pos-clear-search" class="btn btn-sm btn-link position-absolute text-muted d-none" style="right: 0.5rem; top: 0.45rem; text-decoration: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <div class="d-flex gap-1 overflow-x-auto pb-1" id="pos-category-pills" style="scrollbar-width: thin;">
                        <span class="pos-cat-pill active" data-cat="all">All Medicines</span>
                        @foreach ($categories as $cat)
                            <span class="pos-cat-pill" data-cat="{{ $cat->id }}">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="pos-catalog-scroll">
                    <div class="row g-2" id="pos-med-grid">
                        @forelse ($medicines as $med)
                            @php
                                $stock = (int) $med->stock_on_hand;
                                $isOut = $stock <= 0;
                                $firstBatch = $med->activeBatches->first();
                                $batchesJson = $med->activeBatches->map(fn ($b) => [
                                    'id' => $b->id,
                                    'batch_number' => $b->batch_number,
                                    'expiration_date' => $b->expiration_date?->format('Y-m-d'),
                                    'quantity_remaining' => (int) $b->quantity_remaining,
                                ])->values();
                            @endphp
                            <div class="col-12 col-sm-6 col-xl-4 pos-card-wrapper"
                                 data-id="{{ $med->id }}"
                                 data-name="{{ $med->generic_name }} {{ $med->brand_name }} {{ $med->strength }}"
                                 data-cat="{{ $med->category_id }}"
                                 data-unit="{{ $med->unit ?? 'unit' }}"
                                 data-stock="{{ $stock }}"
                                 data-batches="{{ $batchesJson->toJson() }}">
                                <div class="pos-med-card @if($isOut) is-out-of-stock @endif" @if(!$isOut) onclick="posAddToCart({{ $med->id }})" @endif>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                                            <span class="fw-bold text-dark lh-sm text-truncate" style="font-size: 0.92rem;" title="{{ $med->generic_name }}">
                                                {{ $med->generic_name }}
                                            </span>
                                            @if ($stock > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle flex-shrink-0" style="font-size: 0.7rem;">
                                                    {{ number_format($stock) }} {{ $med->unit ?? 'unit' }}s
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle flex-shrink-0" style="font-size: 0.7rem;">
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </div>
                                        <div class="small text-muted text-truncate mb-2" style="font-size: 0.78rem;">
                                            {{ $med->brand_name ? "({$med->brand_name})" : '' }} {{ $med->strength }} &bull; {{ $med->dosage_form }}
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                                        <small class="text-muted text-truncate" style="font-size: 0.72rem;">
                                            @if ($firstBatch)
                                                FEFO: {{ $firstBatch->expiration_date?->format('M Y') }}
                                            @else
                                                No active lot
                                            @endif
                                        </small>
                                        @if (! $isOut)
                                            <button type="button" class="btn btn-sm btn-outline-forest py-0 px-2" style="font-size: 0.75rem;" onclick="event.stopPropagation(); posAddToCart({{ $med->id }});">
                                                <i class="bi bi-plus"></i> Add
                                            </button>
                                        @else
                                            <span class="text-muted small" style="font-size: 0.72rem;">Unavailable</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-5">
                                <i class="bi bi-capsule fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                <div>No active medicines available in the inventory.</div>
                            </div>
                        @endforelse
                    </div>

                    <div id="pos-no-match" class="text-center text-muted py-5 d-none">
                        <i class="bi bi-search fs-3 d-block mb-2 text-secondary opacity-50"></i>
                        <div class="fw-semibold text-dark">No matching medicines found</div>
                        <small class="text-muted">Try adjusting your search query or switching categories.</small>
                    </div>
                </div>
            </div>

            <div class="pos-ticket-pane">
                <div class="pos-pane-header">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-receipt text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Dispensing Ticket</span>
                            <span class="font-monospace small badge bg-light text-dark border">#{{ $nextNumber }}</span>
                        </div>
                        <span class="small text-muted font-monospace"><kbd>F4</kbd> Patient</span>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1 required">Patient Recipient (F4)</label>
                            <select name="patient_id" id="pos-patient-select" class="form-select form-select-sm" data-ajax-url="{{ route('select2.patients') }}" data-placeholder="Search patient by ID, First or Last Name..." required>
                                <option value=""></option>
                                @if (old('patient_id') || $preselectedPatient)
                                    @php
                                        $p = $preselectedPatient ?? \App\Models\Patient::find(old('patient_id'));
                                    @endphp
                                    @if ($p)
                                        <option value="{{ $p->id }}" selected>
                                            {{ $p->full_name }} ({{ $p->primary_identifier }} &bull; {{ ucfirst($p->type) }})
                                        </option>
                                    @endif
                                @endif
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1">Prescribing Clinician</label>
                            <select name="clinician_id" class="form-select form-select-sm">
                                <option value="">Direct / OTC Dispensing</option>
                                @foreach ($clinicians as $doc)
                                    <option value="{{ $doc->id }}" @selected(old('clinician_id', $defaultClinician?->id) == $doc->id)>
                                        {{ $doc->name }} ({{ $doc->title ?? $doc->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1">Diagnosis / Reason</label>
                            <input type="text" name="diagnosis" class="form-control form-control-sm" placeholder="e.g. Headache, Cough, Fever" value="{{ old('diagnosis') }}">
                        </div>
                    </div>
                </div>

                <div class="pos-ticket-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark text-uppercase" style="letter-spacing: 0.05em; font-size: 0.74rem;">Prescribed Items (<span id="pos-cart-count">0</span>)</span>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" id="btn-clear-cart" style="font-size: 0.76rem;">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    </div>

                    <div id="pos-ticket-items">
                    </div>

                    <div id="pos-empty-cart" class="text-center text-muted py-5">
                        <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-25"></i>
                        <div class="fw-semibold text-dark small mb-1">Ticket is currently empty</div>
                        <div class="small">Click any medicine from the catalog on the left to add it to this dispensing slip.</div>
                    </div>
                </div>

                <div class="pos-ticket-footer">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Total Dispensing Quantity:</span>
                        <span class="fw-bold fs-6 text-dark" id="pos-total-units">0 units</span>
                    </div>

                    <div class="mb-2">
                        <input type="text" name="instructions" class="form-control form-control-sm" placeholder="Special doctor instructions or warnings (optional)..." value="{{ old('instructions') }}">
                    </div>

                    <button type="submit" id="btn-submit-dispense" class="btn btn-success btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2 py-2" style="font-size: 0.98rem;">
                        <i class="bi bi-capsule"></i>
                        <span>Issue Medicine Dispensing</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            const posMedicinesData = @json($medicines->keyBy('id'));
            let posCart = {};

            function posAddToCart(medId) {
                const med = posMedicinesData[medId];
                if (!med || !med.active_batches || med.active_batches.length === 0) {
                    if (window.toastr) {
                        window.toastr.warning('This medicine has no active inventory batches available.');
                    }
                    return;
                }

                if (posCart[medId]) {
                    const currentBatch = med.active_batches.find(b => b.id == posCart[medId].batch_id) || med.active_batches[0];
                    if (posCart[medId].quantity < currentBatch.quantity_remaining) {
                        posCart[medId].quantity += 1;
                    } else {
                        if (window.toastr) {
                            window.toastr.warning(`Cannot exceed available batch stock (${currentBatch.quantity_remaining} ${med.unit || 'units'}).`);
                        }
                    }
                } else {
                    const firstBatch = med.active_batches[0];
                    posCart[medId] = {
                        medicine_id: med.id,
                        name: med.generic_name + (med.brand_name ? ' (' + med.brand_name + ')' : '') + ' ' + (med.strength || ''),
                        unit: med.unit || 'tablet',
                        batch_id: firstBatch.id,
                        quantity: 1,
                        dosage_instruction: '1 ' + (med.unit || 'tab') + ' TID pc',
                        batches: med.active_batches
                    };
                }

                renderPosCart();
            }

            function posRemoveFromCart(medId) {
                delete posCart[medId];
                renderPosCart();
            }

            function posUpdateQty(medId, delta) {
                if (!posCart[medId]) return;
                const item = posCart[medId];
                const batch = item.batches.find(b => b.id == item.batch_id) || item.batches[0];
                const max = batch ? batch.quantity_remaining : 999;
                const newQty = item.quantity + delta;

                if (newQty <= 0) {
                    posRemoveFromCart(medId);
                } else if (newQty > max) {
                    if (window.toastr) {
                        window.toastr.warning(`Max available in batch is ${max}.`);
                    }
                } else {
                    item.quantity = newQty;
                    renderPosCart();
                }
            }

            function posSetSig(medId, sigText) {
                if (!posCart[medId]) return;
                posCart[medId].dosage_instruction = sigText;
                const input = document.querySelector(`input[name="items[${medId}][dosage_instruction]"]`);
                if (input) {
                    input.value = sigText;
                }
            }

            function renderPosCart() {
                const container = document.getElementById('pos-ticket-items');
                const emptyNotice = document.getElementById('pos-empty-cart');
                const countBadge = document.getElementById('pos-cart-count');
                const totalUnits = document.getElementById('pos-total-units');
                const submitBtn = document.getElementById('btn-submit-dispense');

                const keys = Object.keys(posCart);
                countBadge.textContent = keys.length;

                if (keys.length === 0) {
                    container.innerHTML = '';
                    emptyNotice.classList.remove('d-none');
                    totalUnits.textContent = '0 units';
                    submitBtn.disabled = true;
                    return;
                }

                emptyNotice.classList.add('d-none');
                submitBtn.disabled = false;

                let html = '';
                let totalQty = 0;

                keys.forEach((medId, idx) => {
                    const item = posCart[medId];
                    totalQty += item.quantity;
                    const batch = item.batches.find(b => b.id == item.batch_id) || item.batches[0];
                    const maxQty = batch ? batch.quantity_remaining : 999;

                    let batchOptions = '';
                    item.batches.forEach(b => {
                        const sel = b.id == item.batch_id ? 'selected' : '';
                        const exp = b.expiration_date ? 'Exp: ' + b.expiration_date : '';
                        batchOptions += `<option value="${b.id}" ${sel}>Lot #${b.batch_number} (${exp} &bull; ${b.quantity_remaining} left)</option>`;
                    });

                    html += `
                        <div class="pos-ticket-item" data-med-id="${medId}">
                            <input type="hidden" name="items[${idx}][medicine_id]" value="${item.medicine_id}">

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-dark lh-sm" style="font-size: 0.88rem;">${item.name}</div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="posRemoveFromCart(${medId})" title="Remove item">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-7">
                                    <label class="small text-muted mb-0" style="font-size: 0.72rem;">Batch / Lot (FEFO)</label>
                                    <select name="items[${idx}][batch_id]" class="form-select form-select-sm" onchange="posChangeBatch(${medId}, this.value)">
                                        ${batchOptions}
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="small text-muted mb-0" style="font-size: 0.72rem;">Quantity (${item.unit}s)</label>
                                    <div class="input-group input-group-sm">
                                        <button type="button" class="btn btn-outline-secondary pos-qty-btn" onclick="posUpdateQty(${medId}, -1)">-</button>
                                        <input type="number" name="items[${idx}][quantity]" class="form-control text-center fw-bold px-1" min="1" max="${maxQty}" value="${item.quantity}" onchange="posInputQty(${medId}, this.value, ${maxQty})">
                                        <button type="button" class="btn btn-outline-secondary pos-qty-btn" onclick="posUpdateQty(${medId}, 1)">+</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="small text-muted mb-1" style="font-size: 0.72rem;">Dosage &amp; Sig Instructions</label>
                                <input type="text" name="items[${idx}][dosage_instruction]" class="form-control form-control-sm mb-1" value="${item.dosage_instruction}" placeholder="e.g. 1 tablet every 8 hrs pc">
                                <div class="d-flex flex-wrap">
                                    <span class="pos-sig-chip" onclick="posSetSig(${medId}, '1 ${item.unit} TID pc')">1 TID pc</span>
                                    <span class="pos-sig-chip" onclick="posSetSig(${medId}, '1 ${item.unit} BID')">1 BID</span>
                                    <span class="pos-sig-chip" onclick="posSetSig(${medId}, '1 ${item.unit} OD hs')">1 OD hs</span>
                                    <span class="pos-sig-chip" onclick="posSetSig(${medId}, '1 ${item.unit} q4h PRN fever')">q4h PRN</span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
                totalUnits.textContent = totalQty.toLocaleString() + ' units';
            }

            function posChangeBatch(medId, batchId) {
                if (!posCart[medId]) return;
                posCart[medId].batch_id = batchId;
                const batch = posCart[medId].batches.find(b => b.id == batchId);
                if (batch && posCart[medId].quantity > batch.quantity_remaining) {
                    posCart[medId].quantity = Math.max(1, batch.quantity_remaining);
                }
                renderPosCart();
            }

            function posInputQty(medId, val, max) {
                if (!posCart[medId]) return;
                let parsed = parseInt(val, 10);
                if (isNaN(parsed) || parsed < 1) parsed = 1;
                if (parsed > max) {
                    parsed = max;
                    if (window.toastr) {
                        window.toastr.warning(`Max available in batch is ${max}.`);
                    }
                }
                posCart[medId].quantity = parsed;
                renderPosCart();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const searchInput = document.getElementById('pos-med-search');
                const clearBtn = document.getElementById('pos-clear-search');
                const cards = document.querySelectorAll('.pos-card-wrapper');
                const noMatch = document.getElementById('pos-no-match');
                const pills = document.querySelectorAll('.pos-cat-pill');
                let activeCat = 'all';

                function filterCatalog() {
                    const q = (searchInput.value || '').trim().toLowerCase();
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const name = (card.dataset.name || '').toLowerCase();
                        const cat = card.dataset.cat;
                        const matchText = !q || name.includes(q);
                        const matchCat = activeCat === 'all' || cat === activeCat;

                        if (matchText && matchCat) {
                            card.classList.remove('d-none');
                            visibleCount++;
                        } else {
                            card.classList.add('d-none');
                        }
                    });

                    clearBtn.classList.toggle('d-none', !q);
                    noMatch.classList.toggle('d-none', visibleCount > 0);
                }

                searchInput.addEventListener('input', filterCatalog);
                clearBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    filterCatalog();
                    searchInput.focus();
                });

                pills.forEach(pill => {
                    pill.addEventListener('click', () => {
                        pills.forEach(p => p.classList.remove('active'));
                        pill.classList.add('active');
                        activeCat = pill.dataset.cat;
                        filterCatalog();
                    });
                });

                document.getElementById('btn-clear-cart').addEventListener('click', () => {
                    if (Object.keys(posCart).length === 0) return;
                    if (confirm('Are you sure you want to clear all items in this ticket?')) {
                        posCart = {};
                        renderPosCart();
                    }
                });

                const patientSelect = $('#pos-patient-select');
                if (patientSelect.length) {
                    patientSelect.select2({
                        theme: 'bootstrap-5',
                        placeholder: patientSelect.data('placeholder') || 'Search patient...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('.pos-ticket-pane'),
                        ajax: {
                            url: patientSelect.data('ajax-url'),
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                q: params.term || '',
                                page: params.page || 1
                            }),
                            processResults: (data, params) => {
                                params.page = params.page || 1;
                                return {
                                    results: data.results || [],
                                    pagination: { more: data.pagination ? data.pagination.more : false }
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 1
                    });
                }

                document.addEventListener('keydown', e => {
                    if (e.key === 'F2') {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    } else if (e.key === 'F4') {
                        e.preventDefault();
                        $('#pos-patient-select').select2('open');
                    } else if (e.key === 'F9') {
                        e.preventDefault();
                        document.getElementById('btn-submit-dispense')?.click();
                    }
                });

                renderPosCart();
            });
        </script>
    @endpush
</x-layouts.pos>
