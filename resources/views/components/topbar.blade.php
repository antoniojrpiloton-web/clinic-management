@php
    $user = auth()->user();
@endphp
<header class="app-topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-sm btn-light d-lg-none" data-toggle="sidebar" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>
        <h1 class="topbar-title">Executive Dashboard</h1>
    </div>

    <div class="d-flex align-items-center gap-2 gap-sm-3">
        {{-- Time Period Selector Pill (SNSU Style) --}}
        <div class="dropdown d-none d-md-block">
            <button class="btn btn-sm btn-outline-forest rounded-pill dropdown-toggle px-3 py-1 text-muted" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-calendar3 me-1"></i> Time period: <strong class="text-dark">FY {{ now()->year }}</strong>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                <li><a class="dropdown-item active" href="#">FY {{ now()->year }}</a></li>
                <li><a class="dropdown-item" href="#">FY {{ now()->subYear()->year }}</a></li>
                <li><a class="dropdown-item" href="#">All-Time History</a></li>
            </ul>
        </div>

        {{-- Notifications Bell with Counter Pill --}}
        <div class="dropdown">
            <button
                type="button"
                class="btn btn-sm btn-outline-forest rounded-circle position-relative p-0"
                style="width: 36px; height: 36px; display: grid; place-items: center;"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
            >
                <i class="bi bi-bell"></i>
                @if ($unreadNotificationsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.65rem;">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </button>

            <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0 notification-dropdown">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                    <span class="fw-bold small text-dark">Notifications</span>
                    @if ($unreadNotificationsCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 text-success text-decoration-none">Mark all read</button>
                        </form>
                    @endif
                </div>

                @forelse ($unreadNotifications as $notification)
                    <a
                        href="{{ route('notifications.show', $notification->id) }}"
                        class="dropdown-item d-flex gap-2 py-2 px-3 border-bottom text-wrap"
                    >
                        <i class="bi {{ $notification->data['icon'] ?? 'bi-info-circle' }} text-success mt-1"></i>
                        <div class="small">
                            <div class="fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                            <div class="text-muted">{{ $notification->data['message'] ?? '' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted small py-4">
                        <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
                        No unread notifications
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick User Avatar / Profile link --}}
        @if ($user)
            <a href="{{ route('profile.edit') }}" class="avatar text-decoration-none" title="{{ $user->name }}">
                {{ $user->initials() }}
            </a>
        @endif
    </div>
</header>
