<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'medicine_id',
        'batch_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_number',
        'reason',
        'patient_id',
        'clinician_id',
        'user_id',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilterType(Builder $query, ?string $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public static function generateTransactionNumber(): string
    {
        $year = date('Y');
        $prefix = "TXN-{$year}-";

        $last = static::where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/-(\d+)$/', $last->transaction_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s%05d', $prefix, $next);
    }
}

