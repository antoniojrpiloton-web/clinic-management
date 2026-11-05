<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'supplier',
        'expiration_date',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
        'received_date',
        'remarks',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'received_date' => 'date',
        'quantity_received' => 'integer',
        'quantity_remaining' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'batch_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('quantity_remaining', '>', 0);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiration_date', '<', now()->startOfDay());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 60): Builder
    {
        return $query->where('expiration_date', '>=', now()->startOfDay())
            ->where('expiration_date', '<=', now()->addDays($days)->endOfDay());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date->isPast() && ! $this->expiration_date->isToday();
    }

    public function getDaysUntilExpirationAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiration_date, false);
    }

    public function getExpirationStatusAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        }

        $days = $this->days_until_expiration;
        if ($days <= 30) {
            return 'critical';
        }
        if ($days <= 90) {
            return 'warning';
        }

        return 'good';
    }

    public function getExpirationBadgeAttribute(): string
    {
        return match ($this->expiration_status) {
            'expired' => '<span class="badge badge-soft-red"><i class="bi bi-x-circle me-1"></i>Expired</span>',
            'critical' => '<span class="badge badge-soft-amber"><i class="bi bi-exclamation-triangle me-1"></i>Expiring in '.$this->days_until_expiration.'d</span>',
            'warning' => '<span class="badge badge-soft-cyan"><i class="bi bi-clock-history me-1"></i>Expiring in '.$this->days_until_expiration.'d</span>',
            default => '<span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Good</span>',
        };
    }
}
