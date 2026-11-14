<x-layouts.app title="Edit Healthcare Provider">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('clinicians.index') }}">Clinicians</a>
        <span>&gt;</span>
        <span>Edit Provider</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Healthcare Provider</h1>
            <p class="dashboard-subheading mb-0">Update professional credentials, role, and contact information.</p>
        </div>

        <a href="{{ route('clinicians.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Registry
        </a>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary fs-5"></i>
                <span class="fw-bold text-dark">{{ $clinician->name }}</span>
            </div>
            <span class="badge {{ $clinician->type === 'Doctor' ? 'badge-blue' : ($clinician->type === 'Nurse' ? 'badge-green' : 'badge-purple') }} px-3 py-1">
                {{ $clinician->type }}
            </span>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('clinicians.update', $clinician) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Full Name (with Honorifics / Suffixes)</label>
                        <input type="text" name="name" value="{{ old('name', $clinician->name) }}" class="form-control" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Provider Role</label>
                        <select name="type" class="form-select" required>
                            <option value="Doctor" @selected(old('type', $clinician->type) === 'Doctor')>Doctor / Physician</option>
                            <option value="Nurse" @selected(old('type', $clinician->type) === 'Nurse')>Registered Nurse</option>
                            <option value="Dentist" @selected(old('type', $clinician->type) === 'Dentist')>Dentist</option>
                            <option value="Other" @selected(old('type', $clinician->type) === 'Other')>Other Healthcare Staff</option>
                        </select>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Official Title / Designation</label>
                        <input type="text" name="title" value="{{ old('title', $clinician->title) }}" class="form-control" required>
                        @error('title')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">PRC License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $clinician->license_number) }}" class="form-control font-monospace" required>
                        @error('license_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">PTR Number (Optional)</label>
                        <input type="text" name="ptr_number" value="{{ old('ptr_number', $clinician->ptr_number) }}" class="form-control">
                        @error('ptr_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Clinical Specialization</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $clinician->specialization) }}" class="form-control">
                        @error('specialization')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone / Mobile Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $clinician->phone) }}" class="form-control">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $clinician->email) }}" class="form-control">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Link to System User Account (Optional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">None / External Provider</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id', $clinician->user_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $clinician->is_active))>
                            <label class="form-check-label fw-medium" for="is_active">Active Healthcare Provider (visible in Select2 examining dropdowns)</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('clinicians.index') }}" class="btn btn-light border px-4 py-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check-lg me-1"></i> Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

