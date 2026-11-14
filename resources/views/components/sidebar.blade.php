@props(['clinicName' => 'SNSU Clinic'])
@php
    $user = auth()->user();
    $isController = $user?->isFeatureController() ?? false;
    $fc = $featureControl ?? app(\App\Services\FeatureControlService::class);
    $progress = $fc->progressSummary();
    $lockMode = (string) ($appSettings['feature_lock_display_mode'] ?? 'badge');

    $canSeeFeature = static function (string $key) use ($fc, $user, $lockMode): bool {
        if ($fc->userCanAccess($key, $user)) {
            return true;
        }

        return $lockMode === 'badge';
    };

    $isFeatureLocked = static function (string $key) use ($fc, $user): bool {
        return ! $fc->userCanAccess($key, $user);
    };

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
        @if ($isController)
            <div class="nav-heading text-warning">DEVELOPER TURN-OVER</div>
            <a href="{{ route('feature-control.index') }}" @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('feature-control.*')])>
                <div class="nav-left">
                    <i class="bi bi-toggles2 menu-icon text-warning"></i>
                    <span>Feature Release</span>
                </div>
                <span class="badge rounded-pill bg-warning text-dark" style="font-size: 0.65rem;">{{ $progress['percentage'] }}%</span>
            </a>
        @endif

        <div class="nav-heading">MAIN</div>

        @can('dashboard.view')
            @if ($canSeeFeature('feature_dashboard'))
                <a href="{{ route('dashboard') }}" @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('dashboard')])>
                    <div class="nav-left">
                        <i class="bi bi-grid-fill menu-icon"></i>
                        <span>Dashboard</span>
                    </div>
                    @if ($isFeatureLocked('feature_dashboard'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                </a>
            @endif
        @endcan

        <div class="nav-heading">PATIENT CARE &amp; CLINICAL</div>

        @if ($canSeeFeature('feature_patients'))
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
                <div class="d-flex align-items-center gap-1">
                    @if ($isFeatureLocked('feature_patients'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $patientActive]) id="submenu-patients">
                <ul class="sidebar-submenu">
                    @can('patients.view')
                        <li>
                            <a href="{{ route('patients.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.index') || request()->routeIs('patients.show')])>
                                <span>Masterlist Registry</span>
                                @if ($isFeatureLocked('feature_patients'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('patients.create')
                        <li>
                            <a href="{{ route('patients.create') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.create')])>
                                <span>Add New Patient</span>
                                @if ($isFeatureLocked('feature_patients'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('patients.import')
                        <li>
                            <a href="{{ route('patients.import') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('patients.import')])>
                                <span>Batch CSV Import</span>
                                @if ($isFeatureLocked('feature_patients'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endif

        @php
            $showAnyOperations = $canSeeFeature('feature_logbook')
                || $canSeeFeature('feature_medical_consultations');
        @endphp
        @if ($showAnyOperations)
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
                    @if ($canSeeFeature('feature_logbook'))
                        <li>
                            <a href="{{ route('clinic-logbooks.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinic-logbooks.*')])>
                                <span>Daily Clinic Logbook</span>
                                @if ($isFeatureLocked('feature_logbook'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endif
                    @can('clinic-visits.view')
                        @if ($canSeeFeature('feature_medical_consultations'))
                            <li>
                                <a href="{{ route('clinic-visits.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinic-visits.*')])>
                                    <span>Patient Visit Logs</span>
                                    @if ($isFeatureLocked('feature_medical_consultations'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
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
        @endif

        @php
            $showAnyConsultations = $canSeeFeature('feature_medical_consultations')
                || $canSeeFeature('feature_dental_consultations')
                || $canSeeFeature('feature_emergency_first_aid');
        @endphp
        @if ($showAnyConsultations)
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
                <div class="d-flex align-items-center gap-1">
                    @if ($isFeatureLocked('feature_medical_consultations') && $isFeatureLocked('feature_dental_consultations') && $isFeatureLocked('feature_emergency_first_aid'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $consultationsActive]) id="submenu-consultations">
                <ul class="sidebar-submenu">
                    @can('medical-consultations.view')
                        @if ($canSeeFeature('feature_medical_consultations'))
                            <li>
                                <a href="{{ route('medical-consultations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medical-consultations.*')])>
                                    <span>Medical Consultation</span>
                                    @if ($isFeatureLocked('feature_medical_consultations'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('dental-consultations.view')
                        @if ($canSeeFeature('feature_dental_consultations'))
                            <li>
                                <a href="{{ route('dental-consultations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('dental-consultations.*')])>
                                    <span>Dental Consultation</span>
                                    @if ($isFeatureLocked('feature_dental_consultations'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('emergency-records.view')
                        @if ($canSeeFeature('feature_emergency_first_aid'))
                            <li>
                                <a href="{{ route('emergency-records.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('emergency-records.*')])>
                                    <span>Emergency Records</span>
                                    @if ($isFeatureLocked('feature_emergency_first_aid'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('first-aid-records.view')
                        @if ($canSeeFeature('feature_emergency_first_aid'))
                            <li>
                                <a href="{{ route('first-aid-records.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('first-aid-records.*')])>
                                    <span>First Aid Records</span>
                                    @if ($isFeatureLocked('feature_emergency_first_aid'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                </ul>
            </div>
        @endif

        @if ($canSeeFeature('feature_physical_examinations'))
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
                    @if ($isFeatureLocked('feature_physical_examinations'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $peActive]) id="submenu-pe">
                <ul class="sidebar-submenu">
                    @can('physical-examinations.view')
                        <li>
                            <a href="{{ route('physical-exam-schedules.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('physical-exam-schedules.*')])>
                                <span>PE Schedules &amp; Batches</span>
                                @if ($isFeatureLocked('feature_physical_examinations'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('physical-examinations.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('physical-examinations.index') || request()->routeIs('physical-examinations.show')])>
                                <span>Physical Exam Registry</span>
                                @if ($isFeatureLocked('feature_physical_examinations'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('physical-examinations.create')
                        <li>
                            <a href="{{ route('physical-examinations.create') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('physical-examinations.create')])>
                                <span>New Physical Exam</span>
                                @if ($isFeatureLocked('feature_physical_examinations'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('clinicians.view')
                        <li>
                            <a href="{{ route('clinicians.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('clinicians.*')])>
                                <span>Healthcare Providers</span>
                                @if ($isFeatureLocked('feature_physical_examinations'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endif

        @if ($canSeeFeature('feature_pharmacy_dispensing') || $canSeeFeature('feature_pharmacy_inventory'))
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
                    @if ($isFeatureLocked('feature_pharmacy_dispensing') && $isFeatureLocked('feature_pharmacy_inventory'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $dispensingActive]) id="submenu-pharmacy-dispensing">
                <ul class="sidebar-submenu">
                    @can('inventory.dispense')
                        @if ($canSeeFeature('feature_pharmacy_dispensing'))
                            <li>
                                <a href="{{ route('inventory.dispensings.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.dispensings.*')])>
                                    <span>Medicine Dispensing</span>
                                    @if ($isFeatureLocked('feature_pharmacy_dispensing'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('medicines.view')
                        @if ($canSeeFeature('feature_pharmacy_inventory'))
                            <li>
                                <a href="{{ route('medicines.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medicines.*')])>
                                    <span>Medicine Masterlist</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('medicine-categories.view')
                        @if ($canSeeFeature('feature_pharmacy_inventory'))
                            <li>
                                <a href="{{ route('medicine-categories.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('medicine-categories.*')])>
                                    <span>Medicine Categories</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endcan
                </ul>
            </div>

            @if ($canSeeFeature('feature_pharmacy_inventory'))
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
                        @if ($isFeatureLocked('feature_pharmacy_inventory'))
                            <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                        @endif
                        <i class="bi bi-chevron-right menu-chevron"></i>
                    </div>
                </button>
                <div @class(['collapse', 'show' => $stockActive]) id="submenu-pharmacy-stock">
                    <ul class="sidebar-submenu">
                        @can('inventory.stock-in')
                            <li>
                                <a href="{{ route('inventory.stock-in') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.stock-in')])>
                                    <span>Receive Stock (Stock-In)</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endcan
                        @can('inventory.stock-out')
                            <li>
                                <a href="{{ route('inventory.stock-out') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.stock-out')])>
                                    <span>Deduct Stock (Stock-Out)</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endcan
                        @can('inventory.view')
                            <li>
                                <a href="{{ route('inventory.expirations') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.expirations')])>
                                    <span>Expiration Monitoring</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventory.transactions') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.transactions')])>
                                    <span>Transaction Ledger</span>
                                    @if ($isFeatureLocked('feature_pharmacy_inventory'))
                                        <i class="bi bi-lock-fill text-warning small"></i>
                                    @endif
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            @endif
        @endif

        @can('inventory.reports')
            @if ($canSeeFeature('feature_reports_analytics'))
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
                        @if ($isFeatureLocked('feature_reports_analytics'))
                            <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                        @endif
                        <i class="bi bi-chevron-right menu-chevron"></i>
                    </div>
                </button>
                <div @class(['collapse', 'show' => $morbidityReportsActive]) id="submenu-reports-morbidity">
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('reports.clinic.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.index')])>
                                <span>Morbidity &amp; Utilization</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.clinic.communicable') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.communicable')])>
                                <span>Communicable Diseases</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.clinic.non-communicable') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.non-communicable')])>
                                <span>Non-Communicable Diseases</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
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
                        @if ($isFeatureLocked('feature_reports_analytics'))
                            <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                        @endif
                        <i class="bi bi-chevron-right menu-chevron"></i>
                    </div>
                </button>
                <div @class(['collapse', 'show' => $serviceReportsActive]) id="submenu-reports-service">
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('reports.clinic.medical-dental') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.medical-dental')])>
                                <span>Medical &amp; Dental Records</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.clinic.login-records') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('reports.clinic.login-records')])>
                                <span>Daily Log-In Records</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('inventory.reports') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('inventory.reports')])>
                                <span>Inventory &amp; Stock Reports</span>
                                @if ($isFeatureLocked('feature_reports_analytics'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        @endcan

        @if ($canSeeFeature('feature_administration') || $canSeeFeature('feature_configuration_backups'))
            <div class="nav-heading">SETTINGS &amp; ADMINISTRATION</div>
        @endif

        @if ($canSeeFeature('feature_administration'))
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
                <div class="d-flex align-items-center gap-1">
                    @if ($isFeatureLocked('feature_administration'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $adminActive]) id="submenu-admin">
                <ul class="sidebar-submenu">
                    @can('users.view')
                        <li>
                            <a href="{{ route('users.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('users.*')])>
                                <span>User Management</span>
                                @if ($isFeatureLocked('feature_administration'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('roles.view')
                        <li>
                            <a href="{{ route('roles.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('roles.*')])>
                                <span>Roles</span>
                                @if ($isFeatureLocked('feature_administration'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('permissions.view')
                        <li>
                            <a href="{{ route('permissions.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('permissions.*')])>
                                <span>Permissions</span>
                                @if ($isFeatureLocked('feature_administration'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('audit-logs.view')
                        <li>
                            <a href="{{ route('audit-logs.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('audit-logs.*')])>
                                <span>Activity Logs</span>
                                @if ($isFeatureLocked('feature_administration'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endif

        @if ($canSeeFeature('feature_configuration_backups'))
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
                <div class="d-flex align-items-center gap-1">
                    @if ($isFeatureLocked('feature_configuration_backups'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $configActive]) id="submenu-config">
                <ul class="sidebar-submenu">
                    @can('settings.manage')
                        <li>
                            <a href="{{ route('settings.edit') }}#clinic-info" class="submenu-link d-flex justify-content-between align-items-center">
                                <span>Clinic Information</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.edit') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('settings.*') && ! str_contains(request()->fullUrl(), '#clinic-info')])>
                                <span>System Settings</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('academic-years.view')
                        <li>
                            <a href="{{ route('academic-years.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('academic-years.*')])>
                                <span>Academic Year</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('colleges.view')
                        <li>
                            <a href="{{ route('colleges.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('colleges.*')])>
                                <span>Colleges &amp; Depts</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('programs.view')
                        <li>
                            <a href="{{ route('programs.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('programs.*')])>
                                <span>Programs</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('medical-consultations.view')
                        <li>
                            <a href="{{ route('disease-categories.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('disease-categories.*')])>
                                <span>Disease Categories</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
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
                    @if ($isFeatureLocked('feature_configuration_backups'))
                        <i class="bi bi-lock-fill text-warning small" title="Locked"></i>
                    @endif
                    <i class="bi bi-chevron-right menu-chevron"></i>
                </div>
            </button>
            <div @class(['collapse', 'show' => $dbActive]) id="submenu-db">
                <ul class="sidebar-submenu">
                    @can('backups.view')
                        <li>
                            <a href="{{ route('backups.index') }}#database-info" class="submenu-link d-flex justify-content-between align-items-center">
                                <span>Database Information</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('backups.index') }}" @class(['submenu-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('backups.*')])>
                                <span>Backup Management</span>
                                @if ($isFeatureLocked('feature_configuration_backups'))
                                    <i class="bi bi-lock-fill text-warning small"></i>
                                @endif
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endif
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
