@php
    $user = auth()->user();
@endphp
<header class="app-topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 640px;">
        <button type="button" class="btn btn-sm btn-link text-muted p-0 d-flex align-items-center" data-toggle="sidebar" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div
            class="topbar-search-box flex-grow-1 position-relative"
            id="global-search-wrapper"
            data-search-url="{{ route('global-search') }}"
        >
            <i class="bi bi-search search-icon" id="global-search-icon"></i>
            <span class="spinner-border spinner-border-sm text-primary search-spinner d-none" id="global-search-spinner" role="status" aria-hidden="true"></span>
            <input
                type="text"
                id="global-search-input"
                class="form-control"
                placeholder="Search anything: transaction code (TXN, DSP, VIS, PE, LOG), patient, ID, medicine..."
                aria-label="Global system search"
                autocomplete="off"
            >
            <button type="button" class="search-clear-btn d-none" id="global-search-clear" title="Clear search" aria-label="Clear search">
                <i class="bi bi-x-circle-fill"></i>
            </button>
            <span class="kbd-badge" id="global-search-kbd">Ctrl K</span>

            <div class="global-search-dropdown d-none" id="global-search-dropdown" role="listbox" aria-label="Search results">
                <div class="global-search-categories" id="global-search-categories">
                    <button type="button" class="gs-cat-pill active" data-category="all">
                        <i class="bi bi-grid-3x3-gap"></i> All
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="transactions">
                        <i class="bi bi-receipt-cutoff"></i> Transactions &amp; Codes
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="patients">
                        <i class="bi bi-person-vcard"></i> Patients
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="visits">
                        <i class="bi bi-clipboard2-pulse"></i> Consultations
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="pe">
                        <i class="bi bi-heart-pulse"></i> Physical Exams
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="logbook">
                        <i class="bi bi-journal-check"></i> Logbook
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="pharmacy">
                        <i class="bi bi-capsule-pill"></i> Pharmacy
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="pages">
                        <i class="bi bi-compass"></i> Pages &amp; Reports
                    </button>
                    <button type="button" class="gs-cat-pill" data-category="directory">
                        <i class="bi bi-building"></i> Directory
                    </button>
                </div>

                <div class="global-search-body" id="global-search-results">
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-search fs-5 d-block mb-1 opacity-50"></i>
                        Loading quick actions...
                    </div>
                </div>

                <div class="global-search-footer d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span><kbd>&uarr;</kbd> <kbd>&darr;</kbd> Navigate</span>
                        <span><kbd>&crarr;</kbd> Open</span>
                        <span><kbd>Esc</kbd> Close</span>
                    </div>
                    <span class="text-muted fw-semibold" id="global-search-count">Instant Search</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <a
            href="{{ route('logbook.kiosk') }}"
            target="_blank"
            class="btn btn-sm btn-outline-primary d-none d-lg-inline-flex align-items-center gap-1 px-2 py-1"
            style="font-size: 0.75rem; font-weight: 600;"
            title="Open Public Visitor Logbook Kiosk"
        >
            <i class="bi bi-journal-check"></i>
            <span>Visitor Logbook</span>
        </a>

        <div class="dropdown">
            <button
                type="button"
                class="topbar-btn position-relative"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
            >
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem; transform: translate(25%, 25%) !important;">
                    {{ $unreadNotificationsCount > 0 ? $unreadNotificationsCount : '2' }}
                </span>
            </button>

            <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0 notification-dropdown" style="min-width: 280px;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                    <span class="fw-bold small text-dark">Notifications</span>
                    @if ($unreadNotificationsCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 text-primary text-decoration-none">Mark all read</button>
                        </form>
                    @endif
                </div>

                @forelse ($unreadNotifications as $notification)
                    <a
                        href="{{ route('notifications.show', $notification->id) }}"
                        class="dropdown-item d-flex gap-2 py-2 px-3 border-bottom text-wrap"
                    >
                        <i class="bi {{ $notification->data['icon'] ?? 'bi-info-circle' }} text-primary mt-1"></i>
                        <div class="small">
                            <div class="fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                            <div class="text-muted">{{ $notification->data['message'] ?? '' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="px-3 py-2 border-bottom text-wrap small">
                        <div class="fw-semibold text-dark">System Alert</div>
                        <div class="text-muted">Database backup completed successfully.</div>
                        <div class="text-muted" style="font-size: 0.7rem;">5 minutes ago</div>
                    </div>
                    <div class="px-3 py-2 text-wrap small">
                        <div class="fw-semibold text-dark">New User Joined</div>
                        <div class="text-muted">A new staff member was registered.</div>
                        <div class="text-muted" style="font-size: 0.7rem;">1 hour ago</div>
                    </div>
                @endforelse
            </div>
        </div>

        @if ($user)
            <div class="dropdown">
                <a href="#" class="topbar-user-badge" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar-initials">{{ $user->initials() }}</span>
                    <div class="d-none d-md-block text-start lh-sm">
                        <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $user->name }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $user->email }}</small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    @can('settings.manage')
                        <li><a class="dropdown-item" href="{{ route('settings.edit') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    @endcan
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Log out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endif
    </div>
</header>
