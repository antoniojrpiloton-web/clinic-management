<x-layouts.app title="System Configuration & Settings">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>System Configuration</span>
        <span>&gt;</span>
        <span>Settings</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">System Configuration</h1>
            <p class="dashboard-subheading mb-0">Manage university identity, clinic profile, system preferences, and security retention.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4 mb-4">
            <div class="col-lg-6" id="university-info">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-building text-primary"></i>
                        <span>University Information</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="university_name" class="form-label required">University Name</label>
                            <input
                                type="text"
                                id="university_name"
                                name="university_name"
                                value="{{ old('university_name', $settings['university_name']) }}"
                                @class(['form-control', 'is-invalid' => $errors->has('university_name')])
                                required
                            >
                            @error('university_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="university_short_name" class="form-label required">Acronym / Abbreviation</label>
                                <input
                                    type="text"
                                    id="university_short_name"
                                    name="university_short_name"
                                    value="{{ old('university_short_name', $settings['university_short_name']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('university_short_name')])
                                    required
                                >
                                @error('university_short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="university_campus" class="form-label">Campus</label>
                                <input
                                    type="text"
                                    id="university_campus"
                                    name="university_campus"
                                    value="{{ old('university_campus', $settings['university_campus']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('university_campus')])
                                >
                                @error('university_campus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="university_president" class="form-label">University President</label>
                                <input
                                    type="text"
                                    id="university_president"
                                    name="university_president"
                                    value="{{ old('university_president', $settings['university_president']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('university_president')])
                                >
                                @error('university_president')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="university_website" class="form-label">Official Website</label>
                                <input
                                    type="url"
                                    id="university_website"
                                    name="university_website"
                                    value="{{ old('university_website', $settings['university_website']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('university_website')])
                                >
                                @error('university_website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="university_address" class="form-label">University Main Address</label>
                            <textarea
                                id="university_address"
                                name="university_address"
                                rows="2"
                                @class(['form-control', 'is-invalid' => $errors->has('university_address')])
                            >{{ old('university_address', $settings['university_address']) }}</textarea>
                            @error('university_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" id="clinic-info">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-hospital text-primary"></i>
                        <span>Clinic Information</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="clinic_name" class="form-label required">Clinic Unit Name</label>
                            <input
                                type="text"
                                id="clinic_name"
                                name="clinic_name"
                                value="{{ old('clinic_name', $settings['clinic_name']) }}"
                                @class(['form-control', 'is-invalid' => $errors->has('clinic_name')])
                                required
                            >
                            @error('clinic_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="clinic_head" class="form-label">Chief Medical Officer / Head</label>
                                <input
                                    type="text"
                                    id="clinic_head"
                                    name="clinic_head"
                                    value="{{ old('clinic_head', $settings['clinic_head'] ?? '') }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('clinic_head')])
                                >
                                @error('clinic_head')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="clinic_license_no" class="form-label">Accreditation / License No</label>
                                <input
                                    type="text"
                                    id="clinic_license_no"
                                    name="clinic_license_no"
                                    value="{{ old('clinic_license_no', $settings['clinic_license_no'] ?? '') }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('clinic_license_no')])
                                >
                                @error('clinic_license_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="clinic_email" class="form-label">Contact Email</label>
                                <input
                                    type="email"
                                    id="clinic_email"
                                    name="clinic_email"
                                    value="{{ old('clinic_email', $settings['clinic_email']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('clinic_email')])
                                >
                                @error('clinic_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="clinic_phone" class="form-label">Contact Phone</label>
                                <input
                                    type="text"
                                    id="clinic_phone"
                                    name="clinic_phone"
                                    value="{{ old('clinic_phone', $settings['clinic_phone']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('clinic_phone')])
                                >
                                @error('clinic_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="clinic_address" class="form-label">Clinic Facility Location</label>
                            <textarea
                                id="clinic_address"
                                name="clinic_address"
                                rows="2"
                                @class(['form-control', 'is-invalid' => $errors->has('clinic_address')])
                            >{{ old('clinic_address', $settings['clinic_address']) }}</textarea>
                            @error('clinic_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-display text-primary"></i>
                        <span>Display &amp; Table Preferences</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="date_format" class="form-label required">Date Format</label>
                                <select id="date_format" name="date_format" class="form-select">
                                    @foreach ($dateFormats as $format => $example)
                                        <option value="{{ $format }}" @selected(old('date_format', $settings['date_format']) === $format)>
                                            {{ $example }} ({{ $format }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label for="table_page_length" class="form-label required">Default Table Rows</label>
                                <select id="table_page_length" name="table_page_length" class="form-select">
                                    @foreach ([10, 25, 50, 100] as $rows)
                                        <option value="{{ $rows }}" @selected((int) old('table_page_length', $settings['table_page_length']) === $rows)>
                                            {{ $rows }} rows per page
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary"></i>
                        <span>Automated Backups &amp; Security Retention</span>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="backup_enabled"
                                name="backup_enabled"
                                value="1"
                                @checked((bool) old('backup_enabled', $settings['backup_enabled']))
                            >
                            <label class="form-check-label" for="backup_enabled">Enable automated daily database backup job (01:00 AM)</label>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="backup_retention_days" class="form-label required">Backup Retention (Days)</label>
                                <input
                                    type="number"
                                    id="backup_retention_days"
                                    name="backup_retention_days"
                                    min="1"
                                    max="3650"
                                    value="{{ old('backup_retention_days', $settings['backup_retention_days']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('backup_retention_days')])
                                    required
                                >
                                @error('backup_retention_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="audit_log_retention_days" class="form-label required">Audit Trail Retention (Days)</label>
                                <input
                                    type="number"
                                    id="audit_log_retention_days"
                                    name="audit_log_retention_days"
                                    min="30"
                                    max="3650"
                                    value="{{ old('audit_log_retention_days', $settings['audit_log_retention_days']) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('audit_log_retention_days')])
                                    required
                                >
                                @error('audit_log_retention_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Saving...">
                <i class="bi bi-check-lg me-1"></i> Save Configuration
            </button>
        </div>
    </form>
</x-layouts.app>
