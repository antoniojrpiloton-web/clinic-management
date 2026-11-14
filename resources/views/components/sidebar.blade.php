@props(['clinicName' => 'SNSU Clinic'])
@php
    $user = auth()->user();

    $patientActive = request()->routeIs(['patients.*', 'students.*', 'employees.*', 'medical-profiles.*']);
    $operationsActive = request()->routeIs(['appointments.*', 'clinic-logbooks.*', 'clinic-visits.*']);
    $consultationsActive = request()->routeIs(['medical-consultations.*', 'dental-consultations.*', 'emergency-records.*', 'first-aid-records.*']);
    $peActive = request()->routeIs(['physical-examinations.*', 'physical-exam-schedules.*', 'clinicians.*']);
    $dispensingActive = request()->routeIs(['inventory.dispensings.*', 'medicines.*', 'medicine-categories.*']);
    $stockActive = request()->routeIs(['inventory.stock-in*', 'inventory.stock-out*', 'inventory.expirations', 'inventory.transactions', 'inventory.batches.*']);
    $morbidityReportsActive = request()->routeIs(['reports.clinic.index', 'reports.clinic.communicable', 'reports.clinic.non-communicable']);
    $serviceReportsActive = request()->routeIs(['reports.clinic.medical-dental', 'reports.clinic.login-records', 'inventory.reports']);
    $adminActive = request()->routeIs(['users.*', 'roles.*', 'permissions.*', 'audit-logs.*']);
    $configActive = request()->routeIs(['settings.*', 'academic-years.*', 'colleges.*', 'programs.*', 'disease-categories.*']);
    $dbActive = request()->routeIs(['backups.*']);
    $accountActive = request()->routeIs(['profile.*']);

    $faviconUrl = ! empty($appSettings['logo_compact'])
        ? asset('storage/'.$appSettings['logo_compact'])
        : (! empty($appSettings['logo_primary'])
            ? asset('storage/'.$appSettings['logo_primary'])
            : asset('assets/images/logo.svg'));
@endphp
<aside class="app-sidebar" aria-label="Main navigation">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        @if ($faviconUrl)
            <img src="{{ $faviconUrl }}" alt="Logo" class="rounded" style="width: 34px; height: 34px; object-fit: contain;">
        @else
            <div class="brand-badge">{{ substr($clinicName, 0, 1) ?: 'C' }}</div>
        @endif
        <span class="brand-text">{{ $clinicName }}</span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-heading">MAIN</div>

        @can('dashboard.view')
            <a href="{{ route('dashboard') }}" @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('dashboard')])>
                <div class="nav-left">
                    <i class="bi bi-grid-fill menu-icon"></i>
                    <span>Dashboard</span>
                </div>
            </a>
        @endcan

        <div class="nav-heading">PATIENT CARE &amp; CLINICAL</div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $patientActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-patients"
            aria-expanded="{{ $patientActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-person-vcard menu-icon"></i>
                <span>Patient Masterlist</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $patientActive]) id="submenu-patients">
            <ul class="sidebar-submenu">
                @can('patients.view')
                    <li>
                        <a href="{{ route('patients.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.index') || request()->routeIs('patients.show')])>
                            <span>Masterlist Registry</span>
                        </a>
                    </li>
                @endcan
                @can('patients.create')
                    <li>
                        <a href="{{ route('patients.create') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.create')])>
                            <span>Add New Patient</span>
                        </a>
                    </li>
                @endcan
                @can('patients.import')
                    <li>
                        <a href="{{ route('patients.import') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.import')])>
                            <span>Batch CSV Import</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $operationsActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-operations"
            aria-expanded="{{ $operationsActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-hospital menu-icon"></i>
                <span>Clinic Operations</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $operationsActive]) id="submenu-operations">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('clinic-logbooks.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinic-logbooks.*')])>
                        <span>Daily Clinic Logbook</span>
                    </a>
                </li>
                @can('clinic-visits.view')
                    <li>
                        <a href="{{ route('clinic-visits.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinic-visits.*')])>
                            <span>Patient Visit Logs</span>
                        </a>
                    </li>
                @endcan
                <li>
                    <a href="{{ route('appointments.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('appointments.index')])>
                        <span>Doctor Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('appointments.calendar') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('appointments.calendar')])>
                        <span>Appointment Calendar</span>
                    </a>
                </li>
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $consultationsActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-consultations"
            aria-expanded="{{ $consultationsActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-heart-pulse menu-icon"></i>
                <span>Clinical Consultations</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $consultationsActive]) id="submenu-consultations">
            <ul class="sidebar-submenu">
                @can('medical-consultations.view')
                    <li>
                        <a href="{{ route('medical-consultations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medical-consultations.*')])>
                            <span>Medical Consultation</span>
                        </a>
                    </li>
                @endcan
                @can('dental-consultations.view')
                    <li>
                        <a href="{{ route('dental-consultations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('dental-consultations.*')])>
                            <span>Dental Consultation</span>
                        </a>
                    </li>
                @endcan
                @can('emergency-records.view')
                    <li>
                        <a href="{{ route('emergency-records.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('emergency-records.*')])>
                            <span>Emergency Records</span>
                        </a>
                    </li>
                @endcan
                @can('first-aid-records.view')
                    <li>
                        <a href="{{ route('first-aid-records.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('first-aid-records.*')])>
                            <span>First Aid Records</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        {{-- Phase 3 Modules --}}
        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $peActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-pe"
            aria-expanded="{{ $peActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-lungs menu-icon"></i>
                <span>Physical Examination</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 3</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $peActive]) id="submenu-pe">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('physical-exam-schedules.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('physical-exam-schedules.*')])>
                        <span>PE Schedules &amp; Batches</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('physical-examinations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('physical-examinations.*')])>
                        <span>Physical Exam Registry</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('clinicians.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinicians.*')])>
                        <span>Healthcare Providers</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-heading">PHARMACY &amp; INVENTORY</div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $dispensingActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-pharmacy-dispensing"
            aria-expanded="{{ $dispensingActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-capsule-pill menu-icon"></i>
                <span>Pharmacy &amp; Dispensing</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 3</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $dispensingActive]) id="submenu-pharmacy-dispensing">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('inventory.dispensings.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.dispensings.*')])>
                        <span>Medicine Dispensing</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('medicines.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medicines.*')])>
                        <span>Medicine Masterlist</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('medicine-categories.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medicine-categories.*')])>
                        <span>Medicine Categories</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $stockActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-pharmacy-stock"
            aria-expanded="{{ $stockActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-boxes menu-icon"></i>
                <span>Stock &amp; Inventory</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 3</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $stockActive]) id="submenu-pharmacy-stock">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('inventory.stock-in') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.stock-in')])>
                        <span>Receive Stock (Stock-In)</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.stock-out') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.stock-out')])>
                        <span>Deduct Stock (Stock-Out)</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.expirations') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.expirations')])>
                        <span>Expiration Monitoring</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.transactions') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.transactions')])>
                        <span>Transaction Ledger</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 3</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Phase 4 Modules --}}
        <div class="nav-heading">REPORTS &amp; ANALYTICS</div>
        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $morbidityReportsActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-reports-morbidity"
            aria-expanded="{{ $morbidityReportsActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-activity menu-icon"></i>
                <span>Morbidity &amp; Surveillance</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 4</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $morbidityReportsActive]) id="submenu-reports-morbidity">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('reports.clinic.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.index')])>
                        <span>Morbidity &amp; Utilization</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.clinic.communicable') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.communicable')])>
                        <span>Communicable Diseases</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.clinic.non-communicable') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.non-communicable')])>
                        <span>Non-Communicable Diseases</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $serviceReportsActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-reports-service"
            aria-expanded="{{ $serviceReportsActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-file-earmark-bar-graph menu-icon"></i>
                <span>Service &amp; Inventory Logs</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 4</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $serviceReportsActive]) id="submenu-reports-service">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('reports.clinic.medical-dental') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.medical-dental')])>
                        <span>Medical &amp; Dental Records</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.clinic.login-records') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.login-records')])>
                        <span>Daily Log-In Records</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.reports') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.reports')])>
                        <span>Inventory &amp; Stock Reports</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-heading">SETTINGS &amp; ADMINISTRATION</div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $adminActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-admin"
            aria-expanded="{{ $adminActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-shield-lock menu-icon"></i>
                <span>Administration</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $adminActive]) id="submenu-admin">
            <ul class="sidebar-submenu">
                @can('users.view')
                    <li>
                        <a href="{{ route('users.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('users.*')])>
                            <span>User Management</span>
                        </a>
                    </li>
                @endcan
                @can('roles.view')
                    <li>
                        <a href="{{ route('roles.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('roles.*')])>
                            <span>Roles</span>
                        </a>
                    </li>
                @endcan
                @can('permissions.view')
                    <li>
                        <a href="{{ route('permissions.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('permissions.*')])>
                            <span>Permissions</span>
                        </a>
                    </li>
                @endcan
                @can('audit-logs.view')
                    <li>
                        <a href="{{ route('audit-logs.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('audit-logs.*')])>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $configActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-config"
            aria-expanded="{{ $configActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-sliders2 menu-icon"></i>
                <span>Configuration</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $configActive]) id="submenu-config">
            <ul class="sidebar-submenu">
                @can('settings.manage')
                    <li>
                        <a href="{{ route('settings.edit') }}#clinic-info" class="submenu-link d-flex justify-content-between align-items-center">
                            <span>Clinic Information</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.edit') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('settings.*') && ! str_contains(request()->fullUrl(), '#clinic-info')])>
                            <span>System Settings</span>
                        </a>
                    </li>
                @endcan
                @can('academic-years.view')
                    <li>
                        <a href="{{ route('academic-years.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('academic-years.*')])>
                            <span>Academic Year</span>
                        </a>
                    </li>
                @endcan
                @can('colleges.view')
                    <li>
                        <a href="{{ route('colleges.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('colleges.*')])>
                            <span>Colleges &amp; Depts</span>
                        </a>
                    </li>
                @endcan
                @can('programs.view')
                    <li>
                        <a href="{{ route('programs.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('programs.*')])>
                            <span>Programs</span>
                        </a>
                    </li>
                @endcan
                @can('medical-consultations.view')
                    <li>
                        <a href="{{ route('disease-categories.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('disease-categories.*')])>
                            <span>Disease Categories</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $dbActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-db"
            aria-expanded="{{ $dbActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-database menu-icon"></i>
                <span>Database</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Phase 4</span>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </div>
        </button>
        <div @class(['collapse', 'show' => $dbActive]) id="submenu-db">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('backups.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('backups.*')])>
                        <span>Backup Management</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">Sprint 4</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    @if ($user)
        <div class="sidebar-user-footer">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="avatar-initials">{{ $user->initials() }}</span>
                <div class="lh-sm overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.78rem;">{{ $user->name }}</div>
                    <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">{{ $user->email }}</small>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-muted p-1" title="Logout">
                    <i class="bi bi-box-arrow-right fs-6"></i>
                </button>
            </form>
        </div>
    @endif
</aside>
