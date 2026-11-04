<x-layouts.app title="Settings">
    <x-page-header title="Application Settings" :breadcrumbs="['Settings' => route('settings.edit')]" />

    <form method="POST" action="{{ route('settings.update') }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">Clinic Information</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="clinic_name" class="form-label required">Clinic Name</label>
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

                        <div class="mb-3">
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

                        <div class="mb-3">
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

                        <div class="mb-0">
                            <label for="clinic_address" class="form-label">Address</label>
                            <textarea
                                id="clinic_address"
                                name="clinic_address"
                                rows="3"
                                @class(['form-control', 'is-invalid' => $errors->has('clinic_address')])
                            >{{ old('clinic_address', $settings['clinic_address']) }}</textarea>
                            @error('clinic_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">Display Preferences</div>
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
                                            {{ $rows }} rows
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Backups &amp; Retention</div>
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
                            <label class="form-check-label" for="backup_enabled">Enable automated daily database backups (01:00)</label>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="backup_retention_days" class="form-label required">Keep Backups (Days)</label>
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
                                <label for="audit_log_retention_days" class="form-label required">Keep Audit Logs (Days)</label>
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
            <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
                <i class="bi bi-check-lg me-1"></i>Save Settings
            </button>
        </div>
    </form>
</x-layouts.app>

