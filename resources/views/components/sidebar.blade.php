@props(['clinicName' => 'SNSU Clinic'])
@php
    $user = auth()->user();
    $patientActive = request()->routeIs(['patients.*']);
    $clinicalActive = request()->routeIs(['physical-examinations.*', 'medical-profiles.*', 'clinicians.*']);
    $pharmacyActive = request()->routeIs(['medicines.*', 'medicine-categories.*', 'inventory.*']);
    $adminActive = request()->routeIs(['users.*', 'roles.*', 'permissions.*', 'audit-logs.*']);
    $configActive = request()->routeIs(['settings.*', 'academic-years.*', 'colleges.*', 'programs.*']);
    $dbActive = request()->routeIs(['backups.*']);
    $accountActive = request()->routeIs(['profile.*']);
@endphp
<aside class="app-sidebar" aria-label="Main navigation">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-badge">T</div>
        <span class="brand-text">{{ $clinicName }}</span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-heading">MAIN MENU</div>

        @can('dashboard.view')
            <a href="{{ route('dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard')])>
                <div class="nav-left">
                    <i class="bi bi-grid-fill menu-icon"></i>
                    <span>Dashboard</span>
                </div>
            </a>
        @endcan

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
                        <a href="{{ route('patients.index') }}" @class(['submenu-link', 'active' => request()->routeIs('patients.index') || request()->routeIs('patients.show')])>
                            <span>Masterlist Registry</span>
                        </a>
                    </li>
                @endcan
                @can('patients.create')
                    <li>
                        <a href="{{ route('patients.create') }}" @class(['submenu-link', 'active' => request()->routeIs('patients.create')])>
                            <span>Add New Patient</span>
                        </a>
                    </li>
                @endcan
                @can('patients.import')
                    <li>
                        <a href="{{ route('patients.import') }}" @class(['submenu-link', 'active' => request()->routeIs('patients.import')])>
                            <span>Batch Import</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $clinicalActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-clinical"
            aria-expanded="{{ $clinicalActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-heart-pulse menu-icon"></i>
                <span>Clinical &amp; Physical Exam</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $clinicalActive]) id="submenu-clinical">
            <ul class="sidebar-submenu">
                @can('physical-examinations.view')
                    <li>
                        <a href="{{ route('physical-examinations.index') }}" @class(['submenu-link', 'active' => request()->routeIs('physical-examinations.index') || request()->routeIs('physical-examinations.show')])>
                            <span>Physical Exam Registry</span>
                        </a>
                    </li>
                @endcan
                @can('physical-examinations.create')
                    <li>
                        <a href="{{ route('physical-examinations.create') }}" @class(['submenu-link', 'active' => request()->routeIs('physical-examinations.create')])>
                            <span>New Physical Exam</span>
                        </a>
                    </li>
                @endcan
                @can('clinicians.view')
                    <li>
                        <a href="{{ route('clinicians.index') }}" @class(['submenu-link', 'active' => request()->routeIs('clinicians.*')])>
                            <span>Healthcare Providers (Doctors &amp; Nurses)</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $pharmacyActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-pharmacy"
            aria-expanded="{{ $pharmacyActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-capsule-pill menu-icon"></i>
                <span>Pharmacy &amp; Inventory</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $pharmacyActive]) id="submenu-pharmacy">
            <ul class="sidebar-submenu">
                @can('medicines.view')
                    <li>
                        <a href="{{ route('medicines.index') }}" @class(['submenu-link', 'active' => request()->routeIs('medicines.*')])>
                            <span>Medicine Masterlist</span>
                        </a>
                    </li>
                @endcan
                @can('inventory.dispense')
                    <li>
                        <a href="{{ route('inventory.dispensings.index') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.dispensings.*')])>
                            <span>Medicine Dispensing</span>
                        </a>
                    </li>
                @endcan
                @can('inventory.stock-in')
                    <li>
                        <a href="{{ route('inventory.stock-in') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.stock-in')])>
                            <span>Receive Stock (Stock-In)</span>
                        </a>
                    </li>
                @endcan
                @can('inventory.stock-out')
                    <li>
                        <a href="{{ route('inventory.stock-out') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.stock-out')])>
                            <span>Deduct Stock (Stock-Out)</span>
                        </a>
                    </li>
                @endcan
                @can('inventory.view')
                    <li>
                        <a href="{{ route('inventory.expirations') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.expirations')])>
                            <span>Expiration Monitoring</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.transactions') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.transactions')])>
                            <span>Transaction Ledger</span>
                        </a>
                    </li>
                @endcan
                @can('inventory.reports')
                    <li>
                        <a href="{{ route('inventory.reports') }}" @class(['submenu-link', 'active' => request()->routeIs('inventory.reports')])>
                            <span>Inventory Reports</span>
                        </a>
                    </li>
                @endcan
                @can('medicine-categories.view')
                    <li>
                        <a href="{{ route('medicine-categories.index') }}" @class(['submenu-link', 'active' => request()->routeIs('medicine-categories.*')])>
                            <span>Medicine Categories</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $adminActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-admin"
            aria-expanded="{{ $adminActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-shield-lock menu-icon"></i>
                <span>System Administration</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $adminActive]) id="submenu-admin">
            <ul class="sidebar-submenu">
                @can('users.view')
                    <li>
                        <a href="{{ route('users.index') }}" @class(['submenu-link', 'active' => request()->routeIs('users.*')])>
                            <span>User Management</span>
                        </a>
                    </li>
                @endcan
                @can('roles.view')
                    <li>
                        <a href="{{ route('roles.index') }}" @class(['submenu-link', 'active' => request()->routeIs('roles.*')])>
                            <span>Roles</span>
                        </a>
                    </li>
                @endcan
                @can('permissions.view')
                    <li>
                        <a href="{{ route('permissions.index') }}" @class(['submenu-link', 'active' => request()->routeIs('permissions.*')])>
                            <span>Permissions</span>
                        </a>
                    </li>
                @endcan
                @can('audit-logs.view')
                    <li>
                        <a href="{{ route('audit-logs.index') }}" @class(['submenu-link', 'active' => request()->routeIs('audit-logs.*')])>
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
                <span>System Configuration</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $configActive]) id="submenu-config">
            <ul class="sidebar-submenu">
                @can('settings.manage')
                    <li>
                        <a href="{{ route('settings.edit') }}#clinic-info" class="submenu-link">
                            <span>Clinic Information</span>
                        </a>
                    </li>
                @endcan
                @can('academic-years.view')
                    <li>
                        <a href="{{ route('academic-years.index') }}" @class(['submenu-link', 'active' => request()->routeIs('academic-years.*')])>
                            <span>Academic Year</span>
                        </a>
                    </li>
                @endcan
                @can('colleges.view')
                    <li>
                        <a href="{{ route('colleges.index') }}" @class(['submenu-link', 'active' => request()->routeIs('colleges.*')])>
                            <span>Colleges and Departments</span>
                        </a>
                    </li>
                @endcan
                @can('programs.view')
                    <li>
                        <a href="{{ route('programs.index') }}" @class(['submenu-link', 'active' => request()->routeIs('programs.*')])>
                            <span>Courses and Programs</span>
                        </a>
                    </li>
                @endcan
                @can('settings.manage')
                    <li>
                        <a href="{{ route('settings.edit') }}" @class(['submenu-link', 'active' => request()->routeIs('settings.*')])>
                            <span>System Settings</span>
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
                <span>Database Management</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $dbActive]) id="submenu-db">
            <ul class="sidebar-submenu">
                @can('backups.view')
                    <li>
                        <a href="{{ route('backups.index') }}#database-info" class="submenu-link">
                            <span>Database Information</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backups.index') }}" @class(['submenu-link', 'active' => request()->routeIs('backups.*')])>
                            <span>Backup Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backups.index') }}" class="submenu-link">
                            <span>Backup History</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <button
            type="button"
            @class(['nav-dropdown-toggle', 'active' => $accountActive])
            data-bs-toggle="collapse"
            data-bs-target="#submenu-account"
            aria-expanded="{{ $accountActive ? 'true' : 'false' }}"
        >
            <div class="nav-left">
                <i class="bi bi-person-circle menu-icon"></i>
                <span>User Account</span>
            </div>
            <i class="bi bi-chevron-right menu-chevron"></i>
        </button>
        <div @class(['collapse', 'show' => $accountActive]) id="submenu-account">
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('profile.edit') }}" @class(['submenu-link', 'active' => request()->routeIs('profile.*')])>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}#password" class="submenu-link">
                        <span>Change Password</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="submenu-link w-100 border-0 bg-transparent text-start text-danger">
                            <span>Logout</span>
                        </button>
                    </form>
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
