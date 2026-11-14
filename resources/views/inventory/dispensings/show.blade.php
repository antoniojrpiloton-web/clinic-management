<x-layouts.app title="Dispensing Slip #{{ $dispensing->dispensing_number }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <a href="{{ route('inventory.dispensings.index') }}">Dispensing</a>
        <span>&gt;</span>
        <span>Slip #{{ $dispensing->dispensing_number }}</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="dashboard-heading mb-0">Dispensing Slip: {{ $dispensing->dispensing_number }}</h1>
                <span class="badge badge-soft-blue">Completed</span>
            </div>
            <p class="dashboard-subheading mb-0 mt-1">
                Issued on {{ $dispensing->dispensed_date ? $dispensing->dispensed_date->format('F d, Y') : 'N/A' }}
                by <span class="fw-semibold text-dark">{{ $dispensing->dispenser?->name ?? 'System' }}</span>
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('inventory.dispensings.print', $dispensing) }}" target="_blank" class="btn btn-primary btn-sm px-3 py-2">
                <i class="bi bi-printer me-1"></i> Print Dispensing Slip
            </a>
            <a href="{{ route('inventory.dispensings.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Log
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Patient Demographics</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 pb-3 border-bottom mb-3">
                        <div class="patient-avatar-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
                            {{ substr($dispensing->patient->first_name, 0, 1) }}{{ substr($dispensing->patient->last_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">
                                <a href="{{ route('patients.show', $dispensing->patient) }}" class="text-decoration-none text-dark">
                                    {{ $dispensing->patient->full_name }}
                                </a>
                            </div>
                            <span class="badge badge-soft-blue me-1">{{ $dispensing->patient->primary_identifier }}</span>
                            <span class="badge badge-soft-gray">{{ ucfirst($dispensing->patient->type) }}</span>
                        </div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Sex / Age:</span>
                            <span class="fw-medium text-dark ms-1">{{ $dispensing->patient->sex ?? 'N/A' }} / {{ $dispensing->patient->age ?? 'N/A' }} yrs</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Blood Type:</span>
                            <span class="badge badge-soft-red ms-1">{{ $dispensing->patient->blood_type ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-muted">College / Dept:</span>
                            <span class="fw-medium text-dark ms-1">{{ $dispensing->patient->college?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted">Program / Position:</span>
                            <span class="fw-medium text-dark ms-1">{{ $dispensing->patient->program?->name ?? ($dispensing->patient->position ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-medical text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Prescription &amp; Clinical Context</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small">Prescribing Clinician:</span>
                        @if($dispensing->clinician)
                            <div class="fw-bold text-dark mt-1">{{ $dispensing->clinician->name }}</div>
                            <small class="text-muted">{{ $dispensing->clinician->title }} • Lic: {{ $dispensing->clinician->license_number ?? 'N/A' }}</small>
                        @else
                            <div class="text-muted mt-1">Direct OTC / Non-Prescription Consultation</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small">Diagnosis / Chief Complaint:</span>
                        <div class="fw-semibold text-dark mt-1">{{ $dispensing->diagnosis ?: 'N/A' }}</div>
                    </div>

                    <div>
                        <span class="text-muted small">Special Instructions:</span>
                        <div class="text-dark small mt-1">{{ $dispensing->instructions ?: 'Standard clinic dosage instructions provided.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-capsule text-primary fs-5"></i>
                <h2 class="h6 mb-0 fw-bold text-dark">Dispensed Medicine Items ({{ $dispensing->items->count() }})</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Medicine</th>
                        <th>Batch / Lot Number</th>
                        <th>Expiration Date</th>
                        <th class="text-center">Quantity Dispensed</th>
                        <th class="pe-3">Dosage Instructions (Sig)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dispensing->items as $idx => $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <a href="{{ route('medicines.show', $item->medicine) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $item->medicine->generic_name }}
                                    @if($item->medicine->brand_name)
                                        <span class="text-primary fw-medium">({{ $item->medicine->brand_name }})</span>
                                    @endif
                                </a>
                                <small class="text-muted d-block">{{ $item->medicine->code }} • {{ $item->medicine->dosage_form }}</small>
                            </td>
                            <td class="font-monospace fw-semibold">{{ $item->batch?->batch_number ?? 'N/A' }}</td>
                            <td>
                                {{ $item->batch?->expiration_date ? $item->batch->expiration_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark fs-6">{{ $item->quantity }}</span>
                                <small class="text-muted">{{ $item->medicine->unit }}s</small>
                            </td>
                            <td class="pe-3 fw-medium text-primary">
                                {{ $item->dosage_instruction ?: 'As directed by physician' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

