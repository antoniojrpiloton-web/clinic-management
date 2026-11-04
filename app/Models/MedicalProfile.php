<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'past_medical_history',
        'past_medical_history_notes',
        'family_medical_history',
        'family_medical_history_notes',
        'allergies_food',
        'allergies_drugs',
        'allergies_others',
        'existing_conditions',
        'current_medications',
        'hospitalization_history',
        'surgical_history',
        'smoking_status',
        'alcohol_status',
        'immunizations',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'past_medical_history' => 'array',
            'family_medical_history' => 'array',
            'hospitalization_history' => 'array',
            'surgical_history' => 'array',
            'immunizations' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAllergiesSummaryAttribute(): string
    {
        $parts = array_filter([
            $this->allergies_food ? "Food: {$this->allergies_food}" : null,
            $this->allergies_drugs ? "Drugs: {$this->allergies_drugs}" : null,
            $this->allergies_others ? "Others: {$this->allergies_others}" : null,
        ]);

        return $parts ? implode(' | ', $parts) : 'No known allergies reported';
    }
}
