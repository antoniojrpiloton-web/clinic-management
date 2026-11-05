<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalConsultation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    public const PROCEDURES = [
        'Oral Examination / Consultation',
        'Oral Prophylaxis (Cleaning)',
        'Tooth Extraction',
        'Temporary Filling',
        'Permanent Filling (Restoration)',
        'Fluoride Application',
        'Pit and Fissure Sealant',
        'Gum Treatment',
        'Prescription Only',
        'Referral to Dentist / Specialist',
    ];

    /**
     * @var array<string, string>
     */
    public const TOOTH_CONDITIONS = [
        'C' => 'Caries',
        'M' => 'Missing',
        'X' => 'For Extraction',
        'F' => 'Filled',
        'RF' => 'Root Fragment',
        'IMP' => 'Impacted',
        'J' => 'Jacket Crown',
    ];

    /**
     * @var list<string>
     */
    public const HYGIENE_STATUSES = ['Good', 'Fair', 'Poor'];

    /**
     * @var array<string, string>
     */
    public const TREATMENT_FREQUENCIES = [
        'regular' => 'Regular (every 6 months)',
        'occasional' => 'Occasional',
        'never' => 'Never',
    ];

    /**
     * @var array<string, string>
     */
    public const PERIODONTAL_STATUSES = [
        'healthy' => 'Healthy',
        'gingivitis' => 'Gingivitis',
        'mild_periodontitis' => 'Mild Periodontitis',
        'moderate_periodontitis' => 'Moderate Periodontitis',
        'severe_periodontitis' => 'Severe Periodontitis',
    ];

    /**
     * @var array<string, string>
     */
    public const CALCULUS_INDICES = [
        'none' => 'None',
        'light' => 'Light',
        'moderate' => 'Moderate',
        'heavy' => 'Heavy',
    ];

    /**
     * @var array<string, string>
     */
    public const GINGIVAL_INDICES = [
        'healthy' => 'Healthy',
        'mild' => 'Mild',
        'moderate' => 'Moderate',
        'severe' => 'Severe',
    ];

    /**
     * @var array<string, string>
     */
    public const PLAQUE_INDICES = [
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    /**
     * @var array<string, string>
     */
    public const PRIORITY_LEVELS = [
        'urgent' => 'Urgent',
        'soon' => 'Soon (within 2 weeks)',
        'routine' => 'Routine',
        'elective' => 'Elective',
    ];

    /**
     * @var array<string, string>
     */
    public const SOFT_TISSUE_STATUSES = [
        'normal' => 'Normal',
        'inflamed' => 'Inflamed',
        'ulcerated' => 'Ulcerated',
        'swollen' => 'Swollen',
        'other' => 'Other',
    ];

    protected $fillable = [
        'clinic_visit_id',
        'oral_examination_findings',
        'tooth_chart',
        'procedures',
        'teeth_involved',
        'anesthesia_used',
        'diagnosis',
        'treatment_rendered',
        'recommendations',
        'oral_hygiene_status',
        'follow_up_date',
        'previous_dental_treatment',
        'dental_treatment_frequency',
        'last_dental_visit',
        'dental_fears',
        'dental_concerns',
        'has_toothache',
        'has_bleeding_gums',
        'has_bad_breath',
        'has_tooth_sensitivity',
        'has_jaw_pain',
        'has_loose_teeth',
        'has_swelling',
        'systemic_diabetes',
        'systemic_hypertension',
        'systemic_heart_disease',
        'systemic_bleeding_disorder',
        'systemic_allergies',
        'systemic_allergy_details',
        'soft_tissue_lips',
        'soft_tissue_cheeks',
        'soft_tissue_tongue',
        'soft_tissue_palate',
        'soft_tissue_gingiva',
        'soft_tissue_floor_of_mouth',
        'periodontal_status',
        'calculus_index',
        'gingival_index',
        'plaque_index',
        'dmft_decayed',
        'dmft_missing',
        'dmft_filled',
        'dmfs_decayed',
        'dmfs_missing',
        'dmfs_filled',
        'treatment_plan',
        'priority_level',
        'primary_tooth_chart',
    ];

    protected function casts(): array
    {
        return [
            'tooth_chart' => 'array',
            'primary_tooth_chart' => 'array',
            'procedures' => 'array',
            'follow_up_date' => 'date',
            'last_dental_visit' => 'date',
            'dental_fears' => 'boolean',
            'has_toothache' => 'boolean',
            'has_bleeding_gums' => 'boolean',
            'has_bad_breath' => 'boolean',
            'has_tooth_sensitivity' => 'boolean',
            'has_jaw_pain' => 'boolean',
            'has_loose_teeth' => 'boolean',
            'has_swelling' => 'boolean',
            'systemic_diabetes' => 'boolean',
            'systemic_hypertension' => 'boolean',
            'systemic_heart_disease' => 'boolean',
            'systemic_bleeding_disorder' => 'boolean',
            'systemic_allergies' => 'boolean',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class, 'clinic_visit_id');
    }

    public function getDmftTotalAttribute(): int
    {
        return (int) $this->dmft_decayed + (int) $this->dmft_missing + (int) $this->dmft_filled;
    }

    public function getDmfsTotalAttribute(): int
    {
        return (int) $this->dmfs_decayed + (int) $this->dmfs_missing + (int) $this->dmfs_filled;
    }

    /**
     * @return list<string>
     */
    public function getOralConditionListAttribute(): array
    {
        $conditions = [
            'has_toothache' => 'Toothache',
            'has_bleeding_gums' => 'Bleeding Gums',
            'has_bad_breath' => 'Bad Breath',
            'has_tooth_sensitivity' => 'Tooth Sensitivity',
            'has_jaw_pain' => 'Jaw Pain',
            'has_loose_teeth' => 'Loose Teeth',
            'has_swelling' => 'Swelling',
        ];

        return array_values(array_filter($conditions, fn (string $key): bool => (bool) $this->{$key}, ARRAY_FILTER_USE_KEY));
    }

    /**
     * @return list<string>
     */
    public function getSystemicConditionListAttribute(): array
    {
        $conditions = [
            'systemic_diabetes' => 'Diabetes',
            'systemic_hypertension' => 'Hypertension',
            'systemic_heart_disease' => 'Heart Disease',
            'systemic_bleeding_disorder' => 'Bleeding Disorder',
            'systemic_allergies' => 'Allergies',
        ];

        return array_values(array_filter($conditions, fn (string $key): bool => (bool) $this->{$key}, ARRAY_FILTER_USE_KEY));
    }

    /**
     * @return array{upper: list<int>, lower: list<int>}
     */
    public static function permanentTeeth(): array
    {
        return [
            'upper' => [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28],
            'lower' => [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38],
        ];
    }

    /**
     * @return array{upper: list<int>, lower: list<int>}
     */
    public static function deciduousTeeth(): array
    {
        return [
            'upper' => [55, 54, 53, 52, 51, 61, 62, 63, 64, 65],
            'lower' => [85, 84, 83, 82, 81, 71, 72, 73, 74, 75],
        ];
    }
}
