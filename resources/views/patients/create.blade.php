<x-layouts.app title="Register New Patient">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('patients.index') }}">Patients</a>
        <span>&gt;</span>
        <span>Register Patient</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Register New Patient</h1>
            <p class="dashboard-subheading mb-0">Unified medical and demographic registration for university students and employees.</p>
        </div>

        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Masterlist
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

    <form method="POST" action="{{ route('patients.store') }}" novalidate>
        @csrf

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-vcard text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Patient Classification</span>
                </div>
                <div class="btn-group" role="group" aria-label="Patient Type">
                    <input type="radio" class="btn-check" name="type" id="type_student" value="student" @checked(old('type', $defaultType) === 'student') autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm px-3 py-2" for="type_student">
                        <i class="bi bi-mortarboard me-1"></i> Student
                    </label>

                    <input type="radio" class="btn-check" name="type" id="type_employee" value="employee" @checked(old('type', $defaultType) === 'employee') autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm px-3 py-2" for="type_employee">
                        <i class="bi bi-briefcase me-1"></i> Employee / Staff
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" id="label-school-id">
                            {{ old('type', $defaultType) === 'employee' ? 'Employee ID Number' : 'Student ID Number' }}
                        </label>
                        <input type="text" name="school_id_number" id="school_id_number" value="{{ old('school_id_number') }}" class="form-control" placeholder="e.g. 2024-00123" required>
                        @error('school_id_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Record Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div id="section-student" class="card mb-4 border {{ old('type', $defaultType) === 'employee' ? 'd-none' : '' }}">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                <i class="bi bi-mortarboard text-primary"></i>
                <span class="fw-bold text-dark">Academic Enrollment Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">College / Department</label>
                        <select name="college_id" id="college_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.colleges') }}" data-placeholder="Search college...">
                            @if (old('college_id') && ($oldCollege = \App\Models\College::find(old('college_id'))))
                                <option value="{{ $oldCollege->id }}" selected>{{ $oldCollege->code }} — {{ $oldCollege->name }}</option>
                            @endif
                        </select>
                        @error('college_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Degree Program</label>
                        <select name="program_id" id="program_id" class="form-select" data-select2 data-ajax-url="{{ route('select2.programs') }}" data-depends-on="#college_id" data-depends-param="college_id" data-placeholder="Search program...">
                            @if (old('program_id') && ($oldProgram = \App\Models\Program::find(old('program_id'))))
                                <option value="{{ $oldProgram->id }}" selected>{{ $oldProgram->code }} — {{ $oldProgram->name }}</option>
                            @endif
                        </select>
                        @error('program_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year / Term</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Current Active Term</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" @selected(old('academic_year_id') == $ay->id)>{{ $ay->code }} — {{ $ay->semester }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Year Level</label>
                        <select name="year_level" class="form-select">
                            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate Studies'] as $lvl)
                                <option value="{{ $lvl }}" @selected(old('year_level', '1st Year') === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <input type="text" name="section" value="{{ old('section') }}" class="form-control" placeholder="e.g. BSIT 1-A">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Enrollment Status</label>
                        <select name="enrollment_status" class="form-select">
                            <option value="enrolled" @selected(old('enrollment_status', 'enrolled') === 'enrolled')>Enrolled</option>
                            <option value="graduated" @selected(old('enrollment_status') === 'graduated')>Graduated</option>
                            <option value="on_leave" @selected(old('enrollment_status') === 'on_leave')>On Leave</option>
                            <option value="dropped" @selected(old('enrollment_status') === 'dropped')>Dropped</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-employee" class="card mb-4 border {{ old('type', $defaultType) === 'employee' ? '' : 'd-none' }}">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                <i class="bi bi-briefcase text-primary"></i>
                <span class="fw-bold text-dark">Employment &amp; Department Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Office / Department</label>
                        <input type="text" name="office_department" value="{{ old('office_department') }}" class="form-control" placeholder="e.g. Office of the Registrar, IT Services">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">College / Academic Unit (If Faculty)</label>
                        <select name="employee_college_id" class="form-select">
                            <option value="">None / Non-Academic Department</option>
                            @foreach ($colleges as $c)
                                <option value="{{ $c->id }}" @selected(old('college_id') == $c->id)>{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Personnel Category</label>
                        <select name="category" class="form-select">
                            <option value="Faculty" @selected(old('category') === 'Faculty')>Teaching Faculty</option>
                            <option value="Non-Teaching / Staff" @selected(old('category', 'Non-Teaching / Staff') === 'Non-Teaching / Staff')>Non-Teaching / Staff</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Position / Job Title</label>
                        <input type="text" name="position" value="{{ old('position') }}" class="form-control" placeholder="e.g. Assistant Professor, Admin Officer">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Classification</label>
                        <select name="employment_classification" class="form-select">
                            <option value="Permanent" @selected(old('employment_classification', 'Permanent') === 'Permanent')>Permanent / Tenured</option>
                            <option value="Contractual" @selected(old('employment_classification') === 'Contractual')>Contractual / COS</option>
                            <option value="Job Order" @selected(old('employment_classification') === 'Job Order')>Job Order</option>
                            <option value="Part-Time" @selected(old('employment_classification') === 'Part-Time')>Part-Time</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Employment Status</label>
                        <select name="employment_status" class="form-select">
                            <option value="active" @selected(old('employment_status', 'active') === 'active')>Active Duty</option>
                            <option value="on_leave" @selected(old('employment_status') === 'on_leave')>On Leave</option>
                            <option value="resigned" @selected(old('employment_status') === 'resigned')>Resigned</option>
                            <option value="retired" @selected(old('employment_status') === 'retired')>Retired</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                <i class="bi bi-person text-primary"></i>
                <span class="fw-bold text-dark">Personal Information</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="form-control" placeholder="e.g. Maria" required>
                        @error('first_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-control" placeholder="e.g. Santos">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" class="form-control" placeholder="e.g. Dela Cruz" required>
                        @error('last_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" value="{{ old('suffix') }}" class="form-control" placeholder="Jr., III">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Sex / Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control" required>
                        @error('date_of_birth')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Civil Status</label>
                        <select name="civil_status" class="form-select" required>
                            <option value="Single" @selected(old('civil_status', 'Single') === 'Single')>Single</option>
                            <option value="Married" @selected(old('civil_status') === 'Married')>Married</option>
                            <option value="Widowed" @selected(old('civil_status') === 'Widowed')>Widowed</option>
                            <option value="Separated" @selected(old('civil_status') === 'Separated')>Separated</option>
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

                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="e.g. maria.delacruz@snsu.edu.ph">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone / Mobile</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+63 9XX XXX XXXX">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Residential Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="House/Bldg No., Street, Barangay, City/Municipality, Province">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                <i class="bi bi-telephone text-primary"></i>
                <span class="fw-bold text-dark">Emergency Contact</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contact Person Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="form-control" placeholder="Parent / Guardian / Spouse">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" class="form-control" placeholder="Mother, Father, Spouse">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="form-control" placeholder="+63 9XX XXX XXXX">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                <i class="bi bi-heart-pulse text-primary"></i>
                <span class="fw-bold text-dark">Initial Health Background</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Known Allergies</label>
                        <textarea name="allergies" rows="2" class="form-control" placeholder="Drugs, food, environmental allergens...">{{ old('allergies') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Past &amp; Existing Medical Conditions</label>
                        <textarea name="medical_history" rows="2" class="form-control" placeholder="Asthma, hypertension, surgeries, maintenance meds...">{{ old('medical_history') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('patients.index') }}" class="btn btn-light px-4 py-2 border">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="bi bi-check-lg me-1"></i> Save Patient Record
            </button>
        </div>
    </form>

    @include('partials.plugins.select2')

    @pushOnce('scripts', 'patient-form-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const radioStudent = document.getElementById('type_student');
                const radioEmployee = document.getElementById('type_employee');
                const secStudent = document.getElementById('section-student');
                const secEmployee = document.getElementById('section-employee');
                const labelId = document.getElementById('label-school-id');
                const inputId = document.getElementById('school_id_number');

                function togglePatientType() {
                    const isEmployee = radioEmployee.checked;
                    if (isEmployee) {
                        secStudent.classList.add('d-none');
                        secEmployee.classList.remove('d-none');
                        labelId.innerText = 'Employee ID Number';
                        inputId.placeholder = 'e.g. EMP-2024-0012';
                    } else {
                        secStudent.classList.remove('d-none');
                        secEmployee.classList.add('d-none');
                        labelId.innerText = 'Student ID Number';
                        inputId.placeholder = 'e.g. 2024-00123';
                    }
                }

                radioStudent?.addEventListener('change', togglePatientType);
                radioEmployee?.addEventListener('change', togglePatientType);

                const fnInput = document.getElementById('first_name');
                const lnInput = document.getElementById('last_name');
                const dobInput = document.getElementById('date_of_birth');
                const warnBox = document.getElementById('duplicate-warning');
                const warnMsg = document.getElementById('duplicate-message');

                function checkDuplicate() {
                    const fn = fnInput?.value.trim();
                    const ln = lnInput?.value.trim();
                    const dob = dobInput?.value;

                    if (!fn || !ln || !dob) {
                        warnBox.classList.add('d-none');
                        return;
                    }

                    fetch(`{{ route('patients.check-duplicate') }}?first_name=${encodeURIComponent(fn)}&last_name=${encodeURIComponent(ln)}&date_of_birth=${encodeURIComponent(dob)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.found) {
                                warnMsg.innerHTML = `Found matching record: <a href="${data.patient.url}" target="_blank" class="fw-bold">${data.patient.name} (${data.patient.type} - ${data.patient.identifier})</a>. Please check before submitting.`;
                                warnBox.classList.remove('d-none');
                            } else {
                                warnBox.classList.add('d-none');
                            }
                        })
                        .catch(() => warnBox.classList.add('d-none'));
                }

                fnInput?.addEventListener('blur', checkDuplicate);
                lnInput?.addEventListener('blur', checkDuplicate);
                dobInput?.addEventListener('change', checkDuplicate);
            });
        </script>
    @endPushOnce
</x-layouts.app>

