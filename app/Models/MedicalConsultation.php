<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_visit_id',
        'disease_category_id',
        'history_of_present_illness',
        'physical_findings',
        'diagnosis',
        'icd_code',
        'treatment_plan',
        'medications_prescribed',
        'advice',
        'medical_certificate_issued',
        'rest_days',
        'referred_to',
        'referral_reason',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'medical_certificate_issued' => 'boolean',
            'follow_up_date' => 'date',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class, 'clinic_visit_id');
    }

    public function diseaseCategory(): BelongsTo
    {
        return $this->belongsTo(DiseaseCategory::class, 'disease_category_id');
    }
}
