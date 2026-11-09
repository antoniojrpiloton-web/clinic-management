<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'event', 'description', 'subject_type', 'subject_id', 'properties', 'ip_address', 'user_agent', 'created_at'])]
class AuditLog extends Model
{
    use HasFactory, HasObfuscatedRouteKey, MassPrunable;

    public const UPDATED_AT = null;

    public function prunable(): Builder
    {
        $days = max(30, (int) app(SettingService::class)->get('audit_log_retention_days', 365));

        return static::where('created_at', '<', now()->subDays($days));
    }

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventColor(): string
    {
        return match ($this->event) {
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'login_failed' => 'warning',
            default => 'dark',
        };
    }
}
