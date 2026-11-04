<x-layouts.app title="Edit Employee — {{ $employee->patient->full_name }}">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('employees.index') }}">Employees</a>
        <span>&gt;</span>
        <span>Edit Employee</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Edit Employee: {{ $employee->patient->full_name }}</h1>
            <p class="dashboard-subheading mb-0">Update academic faculty or administrative employment record.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', $employee->patient) }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-eye me-1"></i> View Patient File
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-light btn-sm px-3 py-2 border">
                <i class="bi bi-arrow-left me-1"></i> Back to Registry
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('employees.update', $employee) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-briefcase text-primary"></i>
                <span class="fw-bold text-dark">1. University Employment Profile</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Employee ID Number</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Personnel Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Faculty" @selected(old('category', $employee->category) === 'Faculty')>Faculty (Teaching)</option>
                            <option value="Non-Teaching / Staff" @selected(old('category', $employee->category) === 'Non-Teaching / Staff')>Non-Teaching / Staff (Administrative)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Classification</label>
                        <select name="employment_classification" class="form-select" required>
                            <option value="Permanent" @selected(old('employment_classification', $employee->employment_classification) === 'Permanent')>Permanent / Regular</option>
                            <option value="Contractual" @selected(old('employment_classification', $employee->employment_classification) === 'Contractual')>Contractual (COS)</option>
                            <option value="Job Order" @selected(old('employment_classification', $employee->employment_classification) === 'Job Order')>Job Order (JO)</option>
                            <option value="Part-time" @selected(old('employment_classification', $employee->employment_classification) === 'Part-time')>Part-time Lecturer</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Official Position / Plantilla Title</label>
                        <input type="text" name="position" value="{{ old('position', $employee->position) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Affiliated College (Optional)</label>
                        <select name="college_id" id="college_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.colleges') }}" data-placeholder="Search affiliated college..." data-allow-clear="true">
                            <option value="">Not Assigned to a College</option>
                            @php
                                $selectedCollegeId = old('college_id', $employee->college_id);
                                $selectedCollege = $selectedCollegeId ? \App\Models\College::find($selectedCollegeId) : null;
                            @endphp
                            @if ($selectedCollege)
                                <option value="{{ $selectedCollege->id }}" selected>{{ $selectedCollege->code }} &mdash; {{ $selectedCollege->name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Office / Administrative Unit</label>
                        <input type="text" name="office_department" value="{{ old('office_department', $employee->office_department) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Status</label>
                        <select name="employment_status" class="form-select" required>
                            <option value="active" @selected(old('employment_status', $employee->employment_status) === 'active')>Active Duty</option>
                            <option value="on_leave" @selected(old('employment_status', $employee->employment_status) === 'on_leave')>On Official Leave</option>
                            <option value="retired" @selected(old('employment_status', $employee->employment_status) === 'retired')>Retired</option>
                            <option value="resigned" @selected(old('employment_status', $employee->employment_status) === 'resigned')>Resigned</option>
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
                        <input type="text" name="first_name" value="{{ old('first_name', $employee->patient->first_name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name', $employee->patient->middle_name) }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $employee->patient->last_name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" value="{{ old('suffix', $employee->patient->suffix) }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Female" @selected(old('gender', $employee->patient->gender) === 'Female')>Female</option>
                            <option value="Male" @selected(old('gender', $employee->patient->gender) === 'Male')>Male</option>
                            <option value="Other" @selected(old('gender', $employee->patient->gender) === 'Other')>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->patient->date_of_birth->format('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Civil Status</label>
                        <select name="civil_status" class="form-select" required>
                            <option value="Single" @selected(old('civil_status', $employee->patient->civil_status) === 'Single')>Single</option>
                            <option value="Married" @selected(old('civil_status', $employee->patient->civil_status) === 'Married')>Married</option>
                            <option value="Widowed" @selected(old('civil_status', $employee->patient->civil_status) === 'Widowed')>Widowed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Blood Type</label>
                        <select name="blood_type" class="form-select">
                            <option value="">Unknown</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type', $employee->patient->blood_type) === $bt)>{{ $bt }}</option>
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
                        <input type="email" name="email" value="{{ old('email', $employee->patient->email) }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->patient->phone) }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Permanent Residence Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $employee->patient->address) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->patient->emergency_contact_name) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $employee->patient->emergency_contact_relationship) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->patient->emergency_contact_phone) }}" class="form-control">
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
                        <textarea name="allergies" rows="2" class="form-control">{{ old('allergies', $employee->patient->allergies) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chronic Conditions / Medical History</label>
                        <textarea name="medical_history" rows="2" class="form-control">{{ old('medical_history', $employee->patient->medical_history) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-light px-3 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" data-loading-text="Saving changes…">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')
</x-layouts.app>

