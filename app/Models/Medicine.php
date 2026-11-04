<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'category_id',
        'generic_name',
        'brand_name',
        'dosage_form',
        'strength',
        'unit',
        'reorder_level',
        'description',
        'side_effects',
        'storage_conditions',
        'is_prescription_required',
        'is_active',
    ];

    protected $casts = [
        'is_prescription_required' => 'boolean',
        'is_active' => 'boolean',
        'reorder_level' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class)->orderBy('expiration_date');
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class)
            ->where('quantity_remaining', '>', 0)
            ->where('expiration_date', '>=', now()->startOfDay())
            ->orderBy('expiration_date');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest('transaction_date');
    }

    public function dispensingItems(): HasMany
    {
        return $this->hasMany(MedicineDispensingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")
                ->orWhere('brand_name', 'like', "%{$term}%")
                ->orWhere('dosage_form', 'like', "%{$term}%")
                ->orWhere('strength', 'like', "%{$term}%");
        });
    }

    public function getStockOnHandAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return (int) $this->batches->where('expiration_date', '>=', now()->startOfDay())->sum('quantity_remaining');
        }

        return (int) $this->batches()->where('expiration_date', '>=', now()->startOfDay())->sum('quantity_remaining');
    }

    public function getTotalBatchesCountAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return $this->batches->count();
        }

        return $this->batches()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        $brand = $this->brand_name ? " ({$this->brand_name})" : '';
        $strength = $this->strength ? " {$this->strength}" : '';

        return "{$this->generic_name}{$brand}{$strength} {$this->dosage_form}";
    }

    public function getStockStatusAttribute(): string
    {
        $stock = $this->stock_on_hand;
        if ($stock <= 0) {
            return 'out_of_stock';
        }
        if ($stock <= $this->reorder_level) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = ($last?->id ?? 0) + 1;

        return sprintf('MED-%04d', $next);
    }
}

