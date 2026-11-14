<x-layouts.app title="Add Healthcare Provider">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('clinicians.index') }}">Clinicians</a>
        <span>&gt;</span>
        <span>Add Provider</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Add Healthcare Provider</h1>
            <p class="dashboard-subheading mb-0">Register a doctor, nurse, or dentist to the clinic staff registry.</p>
        </div>

        <a href="{{ route('clinicians.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Registry
        </a>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-person-badge text-primary fs-5"></i>
            <span class="fw-bold text-dark">Provider Professional Details</span>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('clinicians.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Full Name (with Honorifics / Suffixes)</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g. DR. VICENTE S. QUIMPO, MD" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Provider Role</label>
                        <select name="type" class="form-select" required>
                            <option value="Doctor" @selected(old('type') === 'Doctor')>Doctor / Physician</option>
                            <option value="Nurse" @selected(old('type') === 'Nurse')>Registered Nurse</option>
                            <option value="Dentist" @selected(old('type') === 'Dentist')>Dentist</option>
                            <option value="Other" @selected(old('type') === 'Other')>Other Healthcare Staff</option>
                        </select>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Official Title / Designation</label>
                        <input type="text" name="title" value="{{ old('title', 'School Physician') }}" class="form-control" placeholder="e.g. School Physician" required>
                        @error('title')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">PRC License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" class="form-control font-monospace" placeholder="e.g. 0052551" required>
                        @error('license_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">PTR Number (Optional)</label>
                        <input type="text" name="ptr_number" value="{{ old('ptr_number') }}" class="form-control" placeholder="e.g. PTR-AKL-2026-8910">
                        @error('ptr_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Clinical Specialization</label>
                        <input type="text" name="specialization" value="{{ old('specialization') }}" class="form-control" placeholder="e.g. General Medicine, Pediatrics, School Health">
                        @error('specialization')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone / Mobile Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+63 9XX XXX XXXX">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="doctor@nvc.edu.ph">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Link to System User Account (Optional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">None / External Provider</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label fw-medium" for="is_active">Active Healthcare Provider (visible in Select2 examining dropdowns)</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('clinicians.index') }}" class="btn btn-light border px-4 py-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check-lg me-1"></i> Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

