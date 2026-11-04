<x-layouts.app title="Edit Student — {{ $student->patient->full_name }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('students.index') }}">Students</a>
        <span>&gt;</span>
        <span>Edit Student</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Student: {{ $student->patient->full_name }}</h1>
            <p class="dashboard-subheading mb-0">Update academic enrollment, personal information, and health file.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', $student->patient) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Patient File
            </a>
            <a href="{{ route('students.index') }}" class="btn btn-light btn-sm px-3 py-2 border">
                <i class="bi bi-arrow-left me-1"></i> Back to Registry
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('students.update', $student) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-mortarboard text-primary"></i>
                <span class="fw-bold text-dark">1. Academic Enrollment Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Student ID Number</label>
                        <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">College / Department</label>
                        <select name="college_id" id="college_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.colleges') }}" data-placeholder="Search college..." required>
                            <option value="{{ $student->college_id }}" selected>{{ $student->college->code }} — {{ $student->college->name }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Degree Program</label>
                        <select name="program_id" id="program_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.programs') }}" data-depends-on="#college_id" data-depends-param="college_id" data-placeholder="Search program..." required>
                            <option value="{{ $student->program_id }}" selected>{{ $student->program->code }} — {{ $student->program->name }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Year Level</label>
                        <select name="year_level" class="form-select" required>
                            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $lvl)
                                <option value="{{ $lvl }}" @selected(old('year_level', $student->year_level) === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <input type="text" name="section" value="{{ old('section', $student->section) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Enrollment Status</label>
                        <select name="enrollment_status" class="form-select" required>
                            <option value="enrolled" @selected(old('enrollment_status', $student->enrollment_status) === 'enrolled')>Enrolled</option>
                            <option value="graduated" @selected(old('enrollment_status', $student->enrollment_status) === 'graduated')>Graduated</option>
                            <option value="on_leave" @selected(old('enrollment_status', $student->enrollment_status) === 'on_leave')>On Leave</option>
                            <option value="dropped" @selected(old('enrollment_status', $student->enrollment_status) === 'dropped')>Dropped</option>
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
                        <input type="text" name="first_name" value="{{ old('first_name', $student->patient->first_name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name', $student->patient->middle_name) }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $student->patient->last_name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" value="{{ old('suffix', $student->patient->suffix) }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Female" @selected(old('gender', $student->patient->gender) === 'Female')>Female</option>
                            <option value="Male" @selected(old('gender', $student->patient->gender) === 'Male')>Male</option>
                            <option value="Other" @selected(old('gender', $student->patient->gender) === 'Other')>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->patient->date_of_birth->format('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Civil Status</label>
                        <select name="civil_status" class="form-select" required>
                            <option value="Single" @selected(old('civil_status', $student->patient->civil_status) === 'Single')>Single</option>
                            <option value="Married" @selected(old('civil_status', $student->patient->civil_status) === 'Married')>Married</option>
                            <option value="Widowed" @selected(old('civil_status', $student->patient->civil_status) === 'Widowed')>Widowed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Blood Type</label>
                        <select name="blood_type" class="form-select">
                            <option value="">Unknown</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type', $student->patient->blood_type) === $bt)>{{ $bt }}</option>
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
                        <input type="email" name="email" value="{{ old('email', $student->patient->email) }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->patient->phone) }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Home Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $student->patient->address) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->patient->emergency_contact_name) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $student->patient->emergency_contact_relationship) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->patient->emergency_contact_phone) }}" class="form-control">
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
                        <textarea name="allergies" rows="2" class="form-control">{{ old('allergies', $student->patient->allergies) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Medical History / Chronic Conditions</label>
                        <textarea name="medical_history" rows="2" class="form-control">{{ old('medical_history', $student->patient->medical_history) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('students.index') }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Saving changes…">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>
