<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthenticationEvent
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Record authentication activity in the audit trail.
     */
    public function handle(Login|Logout|Failed|PasswordReset $event): void
    {
        match (true) {
            $event instanceof Login => $this->handleLogin($event),
            $event instanceof Logout && $event->user !== null => $this->auditLogger->log(
                'logout', "{$event->user->name} logged out", $event->user, userId: $event->user->getAuthIdentifier(),
            ),
            $event instanceof Failed => $this->auditLogger->log(
                'login_failed', 'Failed login attempt for '.($event->credentials['email'] ?? 'unknown'), $event->user, userId: $event->user?->getAuthIdentifier(),
            ),
            $event instanceof PasswordReset => $this->auditLogger->log(
                'password_reset', "{$event->user->name} reset their password", $event->user, userId: $event->user->getAuthIdentifier(),
            ),
            default => null,
        };
    }

    protected function handleLogin(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();

        $this->auditLogger->log('login', "{$event->user->name} logged in", $event->user, userId: $event->user->getAuthIdentifier());
    }
}
