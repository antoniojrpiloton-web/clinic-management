<x-layouts.app title="Register New Employee">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('employees.index') }}">Employees</a>
        <span>&gt;</span>
        <span>Register Employee</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Register New Employee</h1>
            <p class="dashboard-subheading mb-0">Record university faculty or administrative personnel clinical health profile.</p>
        </div>

        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Employee Registry
        </a>
    </div>

    <div id="duplicate-warning" class="alert alert-warning border-warning d-none mb-4" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
            <div>
                <strong>Potential Duplicate Record Detected!</strong>
                <div id="duplicate-message" class="small mt-1"></div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" novalidate>
        @csrf

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-briefcase text-primary"></i>
                <span class="fw-bold text-dark">1. University Employment Profile</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Employee ID Number</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="form-control" placeholder="e.g. EMP-2024-0012" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Personnel Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Faculty" @selected(old('category') === 'Faculty')>Faculty (Teaching)</option>
                            <option value="Non-Teaching / Staff" @selected(old('category') === 'Non-Teaching / Staff')>Non-Teaching / Staff (Administrative)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Classification</label>
                        <select name="employment_classification" class="form-select" required>
                            <option value="Permanent" @selected(old('employment_classification') === 'Permanent')>Permanent / Regular</option>
                            <option value="Contractual" @selected(old('employment_classification') === 'Contractual')>Contractual (COS)</option>
                            <option value="Job Order" @selected(old('employment_classification') === 'Job Order')>Job Order (JO)</option>
                            <option value="Part-time" @selected(old('employment_classification') === 'Part-time')>Part-time Lecturer</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Official Position / Plantilla Title</label>
                        <input type="text" name="position" value="{{ old('position') }}" class="form-control" placeholder="e.g. Associate Professor I, Admin Officer II" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Affiliated College (Optional)</label>
                        <select name="college_id" id="college_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.colleges') }}" data-placeholder="Search affiliated college..." data-allow-clear="true">
                            <option value="">Not Assigned to a College</option>
                            @if (old('college_id') && ($oldCollege = \App\Models\College::find(old('college_id'))))
                                <option value="{{ $oldCollege->id }}" selected>{{ $oldCollege->code }} &mdash; {{ $oldCollege->name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Office / Administrative Unit</label>
                        <input type="text" name="office_department" value="{{ old('office_department') }}" class="form-control" placeholder="e.g. Office of the Registrar, Accounting">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Status</label>
                        <select name="employment_status" class="form-select" required>
                            <option value="active" @selected(old('employment_status') === 'active')>Active Duty</option>
                            <option value="on_leave" @selected(old('employment_status') === 'on_leave')>On Official Leave</option>
                            <option value="retired" @selected(old('employment_status') === 'retired')>Retired</option>
                            <option value="resigned" @selected(old('employment_status') === 'resigned')>Resigned</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-person text-primary"></i>
                <span class="fw-bold text-dark">2. Personal Information</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" value="{{ old('suffix') }}" class="form-control" placeholder="Jr., III">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                            <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                            <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Civil Status</label>
                        <select name="civil_status" class="form-select" required>
                            <option value="Single" @selected(old('civil_status') === 'Single')>Single</option>
                            <option value="Married" @selected(old('civil_status') === 'Married')>Married</option>
                            <option value="Widowed" @selected(old('civil_status') === 'Widowed')>Widowed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Blood Type</label>
                        <select name="blood_type" class="form-select">
                            <option value="">Unknown</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-telephone text-primary"></i>
                <span class="fw-bold text-dark">3. Contact &amp; Emergency Contacts</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Official / Personal Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="employee@snsu.edu.ph">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+63 917 000 0000">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Permanent Residence Address</label>
                        <textarea name="address" rows="2" class="form-control" placeholder="Street, Barangay, City / Municipality, Province">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="form-control" placeholder="Spouse / Relative Name">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" class="form-control" placeholder="Spouse, Sibling, Child">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="form-control" placeholder="+63 918 000 0000">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-heart-pulse text-danger"></i>
                <span class="fw-bold text-dark">4. Clinical Notes &amp; Allergies</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Known Drug / Food Allergies</label>
                        <textarea name="allergies" rows="2" class="form-control" placeholder="e.g. Aspirin, Ibuprofen, Peanuts">{{ old('allergies') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chronic Conditions / Medical History</label>
                        <textarea name="medical_history" rows="2" class="form-control" placeholder="e.g. Hypertension, Diabetes Type 2, Maintenance Medication">{{ old('medical_history') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Registering…">
                <i class="bi bi-check-lg me-1"></i> Register Employee
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')

    @pushOnce('scripts', 'duplicate-detector-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const firstNameInput = document.getElementById('first_name');
                const lastNameInput = document.getElementById('last_name');
                const dobInput = document.getElementById('date_of_birth');
                const warningBox = document.getElementById('duplicate-warning');
                const messageBox = document.getElementById('duplicate-message');

                async function checkDuplicate() {
                    const first = firstNameInput?.value.trim();
                    const last = lastNameInput?.value.trim();
                    const dob = dobInput?.value.trim();

                    if (!first || !last || !dob) {
                        warningBox?.classList.add('d-none');
                        return;
                    }

                    try {
                        const url = `{{ route('patients.check-duplicate') }}?first_name=${encodeURIComponent(first)}&last_name=${encodeURIComponent(last)}&date_of_birth=${encodeURIComponent(dob)}`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();

                        if (data.found && data.patient) {
                            messageBox.innerHTML = `Found existing profile for <strong>${data.patient.name}</strong> (${data.patient.type}, ID: ${data.patient.identifier}). <a href="${data.patient.url}" target="_blank" class="fw-bold alert-link">View Patient Record &rarr;</a>`;
                            warningBox?.classList.remove('d-none');
                        } else {
                            warningBox?.classList.add('d-none');
                        }
                    } catch (e) {
                        warningBox?.classList.add('d-none');
                    }
                }

                [firstNameInput, lastNameInput, dobInput].forEach(el => {
                    el?.addEventListener('change', checkDuplicate);
                    el?.addEventListener('blur', checkDuplicate);
                });
            });
        </script>
    @endPushOnce
</x-layouts.app>

