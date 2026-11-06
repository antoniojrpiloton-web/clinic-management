<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiseaseCategory extends Model
{
    use HasFactory;

    public const TYPE_COMMUNICABLE = 'communicable';

    public const TYPE_NON_COMMUNICABLE = 'non_communicable';

    protected $fillable = [
        'code',
        'name',
        'classification',
        'keywords',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function medicalConsultations(): HasMany
    {
        return $this->hasMany(MedicalConsultation::class, 'disease_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCommunicable(Builder $query): Builder
    {
        return $query->where('classification', self::TYPE_COMMUNICABLE);
    }

    public function scopeNonCommunicable(Builder $query): Builder
    {
        return $query->where('classification', self::TYPE_NON_COMMUNICABLE);
    }

    /**
     * @return list<string>
     */
    public function keywordList(): array
    {
        if (! is_string($this->keywords) || trim($this->keywords) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $kw): string => strtolower(trim($kw)),
            explode(',', $this->keywords)
        )));
    }
}
