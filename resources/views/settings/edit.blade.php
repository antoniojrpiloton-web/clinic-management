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

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" novalidate>
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

        <div class="row g-4 mb-4" id="branding-info">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-image text-primary"></i>
                            <span class="fw-semibold">School &amp; Clinic Logo &amp; Auto-Generated Variants</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border">Single Upload &bull; Auto Variants</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center mb-4">
                            <div class="col-lg-6">
                                <label for="logo" class="form-label fw-bold">Upload Master School / Clinic Logo</label>
                                <p class="text-muted small mb-3">
                                    Mag-upload lamang ng <strong>isang (1) high-resolution logo</strong> (PNG, JPG, o WebP). Awtomatikong bubuo ang system ng lahat ng kinakailangang variants: standard sizes (Large, Medium, Small, Favicon), transparent background, official circular seal, grayscale, at watermark.
                                </p>
                                <input
                                    type="file"
                                    id="logo"
                                    name="logo"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="form-control @error('logo') is-invalid @enderror"
                                    onchange="previewMasterLogo(this)"
                                >
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if (! empty($settings['logo_original']) || ! empty($settings['logo_primary']))
                                    <div class="form-check mt-3 pt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                        <label class="form-check-label text-danger small" for="remove_logo">
                                            <i class="bi bi-trash me-1"></i> Remove currently active logo and purge all its variants
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-6">
                                <div class="p-3 border rounded-3 bg-light text-center">
                                    <span class="text-muted small d-block mb-2 fw-semibold">Uploaded Logo Preview</span>
                                    <div id="master-logo-box" class="d-flex align-items-center justify-content-center bg-white rounded border p-2" style="min-height: 120px;">
                                        @if (! empty($settings['logo_original']) || ! empty($settings['logo_primary']))
                                            <img
                                                src="{{ asset('storage/' . ($settings['logo_large'] ?? ($settings['logo_primary'] ?? $settings['logo_original']))) }}"
                                                id="preview-master-logo"
                                                alt="Active Master Logo"
                                                class="img-fluid"
                                                style="max-height: 100px; object-fit: contain;"
                                            >
                                        @else
                                            <div id="master-placeholder" class="text-muted small">
                                                <i class="bi bi-cloud-arrow-up fs-2 opacity-50 d-block mb-1"></i>
                                                <span>No logo uploaded yet. Choose an image file on the left.</span>
                                            </div>
                                            <img src="" id="preview-master-logo" alt="New Preview" class="img-fluid d-none" style="max-height: 100px; object-fit: contain;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-4 mb-4">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-grid-3x3-gap text-primary"></i>
                                <span>Auto-Generated Variants</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-1">Generated Automatically</span>
                            </h6>

                            @if (! empty($settings['logo_original']) || ! empty($settings['logo_primary']))
                                <div class="row g-3">
                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Original Master</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-light rounded" style="height: 90px;">
                                                <img src="{{ asset('storage/' . ($settings['logo_original'] ?? $settings['logo_primary'])) }}" alt="Original" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                            </div>
                                            <span class="badge bg-light text-muted border">High-Res</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Official Emblem</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-light rounded" style="height: 90px;">
                                                @if (! empty($settings['logo_seal']))
                                                    <img src="{{ asset('storage/' . $settings['logo_seal']) }}" alt="Official Emblem" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted small">Auto on save</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger border">Medical Forms</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Transparent</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 rounded" style="height: 90px; background: repeating-conic-gradient(#e6e6e6 0% 25%, #ffffff 0% 50%) 50% / 12px 12px;">
                                                @if (! empty($settings['logo_transparent']))
                                                    <img src="{{ asset('storage/' . $settings['logo_transparent']) }}" alt="Transparent" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted small">Auto on save</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-info-subtle text-info border">Alpha Mask</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Grayscale</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-light rounded" style="height: 90px;">
                                                @if (! empty($settings['logo_grayscale']))
                                                    <img src="{{ asset('storage/' . $settings['logo_grayscale']) }}" alt="Grayscale" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted small">Auto on save</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-secondary-subtle text-secondary border">B&amp;W Print</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Watermark</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-white rounded border" style="height: 90px;">
                                                @if (! empty($settings['logo_watermark']))
                                                    <img src="{{ asset('storage/' . $settings['logo_watermark']) }}" alt="Watermark" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted small">Auto on save</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-light text-muted border">Background</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="border rounded p-2 text-center h-100 bg-white shadow-sm">
                                            <div class="small fw-bold text-dark mb-1">Favicon &amp; App Icon</div>
                                            <div class="d-flex align-items-center justify-content-center p-2 mb-2 bg-light rounded" style="height: 90px;">
                                                @if (! empty($settings['logo_favicon']) || ! empty($settings['logo_small']))
                                                    <img src="{{ asset('storage/' . ($settings['logo_favicon'] ?? $settings['logo_small'])) }}" alt="Favicon" class="p-1 border rounded bg-white" style="width: 44px; height: 44px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted small">Auto on save</span>
                                                @endif
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border">32px &bull; 64px</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border d-flex align-items-center gap-3 mb-0">
                                    <i class="bi bi-info-circle text-primary fs-3"></i>
                                    <div>
                                        <div class="fw-semibold">No variants generated yet</div>
                                        <div class="text-muted small">Mag-upload ng logo sa itaas at i-click ang "Save Configuration". Awtomatikong lilikhain ang Circular Official Seal, Transparent, Monochrome, Watermark, Large (512px), Medium (256px), Small (64px), at Favicon (32px).</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="p-3 border rounded bg-white">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-printer text-primary"></i>
                                <span>PDF Document Branding &amp; Sizing Configuration</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-switch pt-2">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="logo_print_enabled"
                                            name="logo_print_enabled"
                                            value="1"
                                            @checked((bool) old('logo_print_enabled', $settings['logo_print_enabled'] ?? true))
                                        >
                                        <label class="form-check-label fw-semibold" for="logo_print_enabled">Include Logo on PDF Documents</label>
                                    </div>
                                    <div class="form-text small">Display the emblem in the generated PDF header.</div>
                                </div>

                                <div class="col-md-3">
                                    <label for="logo_print_variant" class="form-label required">Header Logo Variant</label>
                                    <select id="logo_print_variant" name="logo_print_variant" class="form-select form-select-sm">
                                        <option value="seal" @selected(old('logo_print_variant', $settings['logo_print_variant'] ?? 'seal') === 'seal')>Official Circular Seal</option>
                                        <option value="primary" @selected(old('logo_print_variant', $settings['logo_print_variant'] ?? 'seal') === 'primary')>Primary Institutional Logo</option>
                                        <option value="transparent" @selected(old('logo_print_variant', $settings['logo_print_variant'] ?? 'seal') === 'transparent')>Transparent Variant</option>
                                        <option value="grayscale" @selected(old('logo_print_variant', $settings['logo_print_variant'] ?? 'seal') === 'grayscale')>Grayscale / B&amp;W Variant</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="logo_print_size" class="form-label required">Header Logo Sizing</label>
                                    <select id="logo_print_size" name="logo_print_size" class="form-select form-select-sm">
                                        <option value="small" @selected(old('logo_print_size', $settings['logo_print_size'] ?? 'medium') === 'small')>Small (16mm width)</option>
                                        <option value="medium" @selected(old('logo_print_size', $settings['logo_print_size'] ?? 'medium') === 'medium')>Medium (20mm width) — Standard</option>
                                        <option value="large" @selected(old('logo_print_size', $settings['logo_print_size'] ?? 'medium') === 'large')>Large (25mm width)</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="logo_print_position" class="form-label required">Header Placement</label>
                                    <select id="logo_print_position" name="logo_print_position" class="form-select form-select-sm">
                                        <option value="left" @selected(old('logo_print_position', $settings['logo_print_position'] ?? 'left') === 'left')>Left Seal (Standard HSD)</option>
                                        <option value="dual" @selected(old('logo_print_position', $settings['logo_print_position'] ?? 'left') === 'dual')>Dual (University Left + Clinic Seal Right)</option>
                                    </select>
                                </div>
                            </div>
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

    @push('scripts')
        <script>
            function previewMasterLogo(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB. Please upload a smaller image.');
                        input.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('preview-master-logo');
                        const placeholder = document.getElementById('master-placeholder');
                        if (preview) {
                            preview.src = e.target.result;
                            preview.classList.remove('d-none');
                        }
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush

    @push('scripts')
        <script>
            function previewMasterLogo(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB. Please upload a smaller image.');
                        input.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('preview-master-logo');
                        const placeholder = document.getElementById('master-placeholder');
                        if (preview) {
                            preview.src = e.target.result;
                            preview.classList.remove('d-none');
                        }
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush
</x-layouts.app>
