<x-layouts.app title="Register New Student">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('students.index') }}">Students</a>
        <span>&gt;</span>
        <span>Register Student</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Register New Student</h1>
            <p class="dashboard-subheading mb-0">Record academic enrollment, identification, contact, and clinical background.</p>
        </div>

        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Student Registry
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

    <form method="POST" action="{{ route('students.store') }}" novalidate>
        @csrf

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-mortarboard text-primary"></i>
                <span class="fw-bold text-dark">1. Academic Enrollment Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Student ID Number</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" class="form-control" placeholder="e.g. 2024-00123" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">College / Department</label>
                        <select name="college_id" id="college_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.colleges') }}" data-placeholder="Search college..." required>
                            @if (old('college_id') && ($oldCollege = \App\Models\College::find(old('college_id'))))
                                <option value="{{ $oldCollege->id }}" selected>{{ $oldCollege->code }} — {{ $oldCollege->name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Degree Program</label>
                        <select name="program_id" id="program_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.programs') }}" data-depends-on="#college_id" data-depends-param="college_id" data-placeholder="Search program..." required>
                            @if (old('program_id') && ($oldProgram = \App\Models\Program::find(old('program_id'))))
                                <option value="{{ $oldProgram->id }}" selected>{{ $oldProgram->code }} — {{ $oldProgram->name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Year Level</label>
                        <select name="year_level" class="form-select" required>
                            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $lvl)
                                <option value="{{ $lvl }}" @selected(old('year_level') === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <input type="text" name="section" value="{{ old('section') }}" class="form-control" placeholder="e.g. BSIT 1-A">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Enrollment Status</label>
                        <select name="enrollment_status" class="form-select" required>
                            <option value="enrolled" @selected(old('enrollment_status') === 'enrolled')>Enrolled</option>
                            <option value="graduated" @selected(old('enrollment_status') === 'graduated')>Graduated</option>
                            <option value="on_leave" @selected(old('enrollment_status') === 'on_leave')>On Leave</option>
                            <option value="dropped" @selected(old('enrollment_status') === 'dropped')>Dropped</option>
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
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="student@snsu.edu.ph">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+63 912 345 6789">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Home Address</label>
                        <textarea name="address" rows="2" class="form-control" placeholder="Barangay, Municipality / City, Province">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="form-control" placeholder="Parent / Guardian Name">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" class="form-control" placeholder="Mother, Father, Guardian">
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
                        <label class="form-label">Known Allergies (Drug / Food)</label>
                        <textarea name="allergies" rows="2" class="form-control" placeholder="e.g. Penicillin, Shellfish, Ibuprofen">{{ old('allergies') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Medical History / Chronic Conditions</label>
                        <textarea name="medical_history" rows="2" class="form-control" placeholder="e.g. Asthma, Hypertension, Diabetes, Heart Disease">{{ old('medical_history') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('students.index') }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Registering…">
                <i class="bi bi-check-lg me-1"></i> Register Student
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
