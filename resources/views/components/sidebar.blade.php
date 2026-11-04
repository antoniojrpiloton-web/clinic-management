@props(['clinicName' => 'SNSU Clinic'])
@php
    $user = auth()->user();
@endphp
<aside class="app-sidebar" aria-label="Main navigation">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('assets/images/logo.svg') }}" alt="SNSU Logo" class="brand-logo">
        <span class="brand-text">SNSU</span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-heading">Operations &amp; Programs</div>

        @can('dashboard.view')
            <a href="{{ route('dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard')])>
                <i class="bi bi-grid-fill menu-icon"></i>
                <span>Executive Dashboard</span>
            </a>
        @endcan

        <div class="nav-heading">System Governance</div>

        @can('users.view')
            <a href="{{ route('users.index') }}" @class(['nav-link', 'active' => request()->routeIs('users.*')])>
                <i class="bi bi-people-fill menu-icon"></i>
                <span>Personnel &amp; Users</span>
            </a>
        @endcan

        @can('roles.view')
            <a href="{{ route('roles.index') }}" @class(['nav-link', 'active' => request()->routeIs('roles.*')])>
                <i class="bi bi-shield-lock-fill menu-icon"></i>
                <span>Role Matrix</span>
            </a>
        @endcan

        @can('permissions.view')
            <a href="{{ route('permissions.index') }}" @class(['nav-link', 'active' => request()->routeIs('permissions.*')])>
                <i class="bi bi-key-fill menu-icon"></i>
                <span>Permissions</span>
            </a>
        @endcan

        <div class="nav-heading">Records &amp; Intelligence</div>

        @can('audit-logs.view')
            <a href="{{ route('audit-logs.index') }}" @class(['nav-link', 'active' => request()->routeIs('audit-logs.*')])>
                <i class="bi bi-journal-text menu-icon"></i>
                <span>Audit Trail Registry</span>
            </a>
        @endcan

        @can('backups.view')
            <a href="{{ route('backups.index') }}" @class(['nav-link', 'active' => request()->routeIs('backups.*')])>
                <i class="bi bi-database-check menu-icon"></i>
                <span>Database Backups</span>
            </a>
        @endcan

        @can('settings.manage')
            <a href="{{ route('settings.edit') }}" @class(['nav-link', 'active' => request()->routeIs('settings.*')])>
                <i class="bi bi-sliders2 menu-icon"></i>
                <span>System Settings</span>
            </a>
        @endcan
    </nav>

    @if ($user)
        <div class="sidebar-user-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="avatar">{{ $user->initials() }}</span>
                <div class="lh-sm overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;">{{ $user->name }}</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $user->roles->first()?->name ?? 'Staff' }}</small>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0 text-muted text-decoration-none d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                    <i class="bi bi-box-arrow-left"></i> Log out
                </button>
            </form>
        @endcan
    </nav>

    {{-- Bottom User / Signout Panel as shown in screenshot --}}
    @if ($user)
        <div class="sidebar-user-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="avatar">{{ $user->initials() }}</span>
                <div class="lh-sm overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;">{{ $user->name }}</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $user->roles->first()?->name ?? 'Staff' }}</small>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0 text-muted text-decoration-none d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                    <i class="bi bi-box-arrow-left"></i> Log out
                </button>
            </form>
        </div>
    @endif
</aside>
