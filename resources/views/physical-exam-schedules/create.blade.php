<x-layouts.app title="Create Physical Examination Schedule">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('physical-exam-schedules.index') }}">Scheduled Physical Exams</a>
        <span>&gt;</span>
        <span>Create Schedule</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Create Physical Examination Schedule</h1>
            <p class="dashboard-subheading mb-0">Schedule university cohort health screening, college PE campaigns, and auto-populate attendee rosters.</p>
        </div>

        <a href="{{ route('physical-exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Schedules
        </a>
    </div>

    <form method="POST" action="{{ route('physical-exam-schedules.store') }}" novalidate>
        @csrf

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar3 text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Schedule Identification &amp; Purpose</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label required">Campaign / Schedule Title</label>
                        <input type="text" name="title" value="{{ old('title', $schedule->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. CEIT 1st Year Annual Physical Examination Screening 2026" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Examination Purpose</label>
                        <select name="purpose" class="form-select @error('purpose') is-invalid @enderror" required>
                            @foreach($purposes as $purp)
                                <option value="{{ $purp }}" @selected(old('purpose', $schedule->purpose) === $purp)>{{ $purp }}</option>
                            @endforeach
                        </select>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Target Cohort Classification</label>
                        <select name="target_classification" id="target_classification" class="form-select @error('target_classification') is-invalid @enderror" required>
                            <option value="student" @selected(old('target_classification', $schedule->target_classification) === 'student')>Students</option>
                            <option value="employee" @selected(old('target_classification', $schedule->target_classification) === 'employee')>Employees / University Staff</option>
                        </select>
                        @error('target_classification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror">
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" @selected(old('academic_year_id', $schedule->academic_year_id) == $ay->id)>
                                    {{ $ay->code }} ({{ $ay->semester }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Schedule Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(\App\Models\PhysicalExamSchedule::STATUSES as $stKey => $stData)
                                <option value="{{ $stKey }}" @selected(old('status', $schedule->status) === $stKey)>{{ $stData['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-mortarboard text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Target Group &amp; Department Filters</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Target College (Optional)</label>
                        <select name="college_id" id="college_id" class="form-select @error('college_id') is-invalid @enderror">
                            <option value="">-- All Colleges --</option>
                            @foreach($colleges as $col)
                                <option value="{{ $col->id }}" @selected(old('college_id', $schedule->college_id) == $col->id)>
                                    {{ $col->code }} - {{ $col->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Target Degree Program (Optional)</label>
                        <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror">
                            <option value="">-- All Programs --</option>
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}" @selected(old('program_id', $schedule->program_id) == $prog->id)>
                                    {{ $prog->code }} - {{ $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Year Level (Students)</label>
                        <select name="year_level" class="form-select @error('year_level') is-invalid @enderror">
                            <option value="">-- All Year Levels --</option>
                            @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Postgraduate'] as $yr)
                                <option value="{{ $yr }}" @selected(old('year_level', $schedule->year_level) === $yr)>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Office / Department (Employees)</label>
                        <input type="text" name="office_department" value="{{ old('office_department', $schedule->office_department) }}" class="form-control" placeholder="e.g. College of Education, Registrar's Office, HRMD">
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch pt-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="auto_pop" name="auto_populate_attendees" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="auto_pop">
                                Auto-populate attendee roster from active records
                            </label>
                            <small class="text-muted d-block">Automatically enrols active patients matching the target filters above into this schedule's roster.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Schedule Timing, Venue &amp; Examiner</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Examination Date</label>
                        <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $schedule->scheduled_date ? $schedule->scheduled_date->format('Y-m-d') : '') }}" class="form-control @error('scheduled_date') is-invalid @enderror" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $schedule->start_time) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $schedule->end_time) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Examination Venue / Room</label>
                        <input type="text" name="venue" value="{{ old('venue', $schedule->venue ?? 'University Clinic Main Room') }}" class="form-control" placeholder="e.g. University Health Services Examination Room 1">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cohort Capacity (Max Patients)</label>
                        <input type="number" min="1" max="1000" name="capacity" value="{{ old('capacity', $schedule->capacity) }}" class="form-control" placeholder="50">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lead Examining Physician / Clinician</label>
                        <select name="clinician_id" class="form-select @error('clinician_id') is-invalid @enderror">
                            <option value="">-- Select Clinician --</option>
                            @foreach($clinicians as $doc)
                                <option value="{{ $doc->id }}" @selected(old('clinician_id', $schedule->clinician_id) == $doc->id)>
                                    {{ $doc->name }} ({{ $doc->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Schedule Instructions &amp; Fasting Guidelines</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Special patient instructions (e.g. Fasting 8 hours for blood tests, bring valid school ID)...">{{ old('notes', $schedule->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('physical-exam-schedules.index') }}" class="btn btn-outline-secondary px-4 py-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-calendar-check me-1"></i> Save Examination Schedule
            </button>
        </div>
    </form>
</x-layouts.app>

