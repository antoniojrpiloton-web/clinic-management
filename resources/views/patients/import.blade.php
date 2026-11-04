<x-layouts.app title="Import Patient Masterlist">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('patients.index') }}">Patients</a>
        <span>&gt;</span>
        <span>Batch Import</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Batch Import Masterlist</h1>
            <p class="dashboard-subheading mb-0">Upload university students or employee records via CSV or Excel-compatible spreadsheet.</p>
        </div>

        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Masterlist
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white">
                    <span class="fw-bold text-dark">Upload Spreadsheet File</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('patients.import.process') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label required">Target Population Classification</label>
                            <select name="type" class="form-select" required>
                                <option value="student">Students (Enrolled Cohort)</option>
                                <option value="employee">Employees (Faculty &amp; Administrative Personnel)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required">CSV / Spreadsheet File</label>
                            <input type="file" name="file" accept=".csv,text/csv,text/plain" class="form-control" required>
                            <small class="text-muted d-block mt-1">Accepted formats: <code>.csv</code> (UTF-8 encoded). Maximum file size: 5 MB.</small>
                        </div>

                        <div class="alert alert-info py-2 px-3 small mb-4">
                            <i class="bi bi-shield-check me-1"></i>
                            <strong>Duplicate Detection Active:</strong> Any row matching an existing First Name, Last Name, and Date of Birth will automatically be detected and safely skipped.
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Processing batch…">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Start Batch Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border h-100">
                <div class="card-header bg-white">
                    <span class="fw-bold text-dark">Header Column Guidelines</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Ensure your CSV first row contains the following headers (case-insensitive):
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small">
                            <thead class="table-light">
                                <tr>
                                    <th>Column Name</th>
                                    <th>Required</th>
                                    <th>Sample Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>first_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>Krystal</td>
                                </tr>
                                <tr>
                                    <td><code>last_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>Bernardino</td>
                                </tr>
                                <tr>
                                    <td><code>date_of_birth</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>2004-05-14</td>
                                </tr>
                                <tr>
                                    <td><code>gender</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>Female / Male</td>
                                </tr>
                                <tr>
                                    <td><code>id_number</code> / <code>student_id</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>2023-01045</td>
                                </tr>
                                <tr>
                                    <td><code>college</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>CCIS</td>
                                </tr>
                                <tr>
                                    <td><code>program</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>BSIT</td>
                                </tr>
                                <tr>
                                    <td><code>email</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>krystal@snsu.edu.ph</td>
                                </tr>
                                <tr>
                                    <td><code>phone</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>09123456789</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

