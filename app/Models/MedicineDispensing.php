<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineDispensing extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    protected $fillable = [
        'dispensing_number',
        'patient_id',
        'clinician_id',
        'dispensed_by',
        'dispensed_date',
        'diagnosis',
        'instructions',
        'notes',
        'total_items',
    ];

    protected $casts = [
        'dispensed_date' => 'datetime',
        'total_items' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicineDispensingItem::class, 'dispensing_id');
    }

    public static function generateDispensingNumber(): string
    {
        $year = date('Y');
        $prefix = "DSP-{$year}-";

        $last = static::where('dispensing_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/-(\d+)$/', $last->dispensing_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s%05d', $prefix, $next);
    }
}
