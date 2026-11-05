<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalExamination extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    protected $fillable = [
        'pe_number',
        'patient_id',
        'academic_year_id',
        'examiner_id',
        'clinician_id',
        'examiner_name',
        'examiner_license_number',
        'exam_date',
        'purpose',
        'systolic',
        'diastolic',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'height_cm',
        'weight_kg',
        'bmi',
        'bmi_category',
        'visual_acuity_right',
        'visual_acuity_left',
        'visual_acuity_is_corrected',
        'color_vision',
        'hearing',
        'general_appearance',
        'skin',
        'heent',
        'neck',
        'chest_lungs',
        'heart',
        'abdomen',
        'musculoskeletal',
        'neurological',
        'dental',
        'chest_xray_findings',
        'chest_xray_date',
        'laboratory_notes',
        'clinical_findings',
        'medical_recommendations',
        'fitness_assessment',
        'fitness_activities',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'chest_xray_date' => 'date',
            'visual_acuity_is_corrected' => 'boolean',
            'temperature' => 'decimal:1',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'bmi' => 'decimal:2',
            'skin' => 'array',
            'heent' => 'array',
            'neck' => 'array',
            'chest_lungs' => 'array',
            'heart' => 'array',
            'abdomen' => 'array',
            'musculoskeletal' => 'array',
            'neurological' => 'array',
            'dental' => 'array',
            'fitness_activities' => 'array',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (PhysicalExamination $exam) {
            if (empty($exam->pe_number)) {
                $exam->pe_number = static::generatePeNumber();
            }

            if ($exam->height_cm && $exam->weight_kg) {
                $exam->bmi = static::calculateBmi((float) $exam->weight_kg, (float) $exam->height_cm);
                $exam->bmi_category = static::determineBmiCategory($exam->bmi);
            }
        });

        static::updating(function (PhysicalExamination $exam) {
            if ($exam->isDirty(['height_cm', 'weight_kg']) && $exam->height_cm && $exam->weight_kg) {
                $exam->bmi = static::calculateBmi((float) $exam->weight_kg, (float) $exam->height_cm);
                $exam->bmi_category = static::determineBmiCategory($exam->bmi);
            }
        });
    }

    public static function generatePeNumber(): string
    {
        $year = date('Y');
        $prefix = "PE-{$year}-";

        $last = static::where('pe_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('pe_number');

        $nextSeq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public static function calculateBmi(float $weightKg, float $heightCm): ?float
    {
        if ($heightCm <= 0 || $weightKg <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;

        return round($weightKg / ($heightM * $heightM), 2);
    }

    public static function determineBmiCategory(?float $bmi): ?string
    {
        if ($bmi === null || $bmi <= 0) {
            return null;
        }

        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            $bmi < 35.0 => 'Obese I',
            default => 'Obese II',
        };
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function getBloodPressureAttribute(): ?string
    {
        if ($this->systolic && $this->diastolic) {
            return "{$this->systolic}/{$this->diastolic} mmHg";
        }

        return null;
    }

    public function getBmiBadgeClassAttribute(): string
    {
        return match ($this->bmi_category) {
            'Normal' => 'badge-green',
            'Underweight' => 'badge-amber',
            'Overweight' => 'badge-amber',
            'Obese I', 'Obese II' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function getFitnessBadgeClassAttribute(): string
    {
        return match ($this->fitness_assessment) {
            'Class A' => 'badge-green',
            'Class B' => 'badge-cyan',
            'Class C' => 'badge-amber',
            'Class D' => 'bg-orange',
            'Class E' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? null;
        if (is_array($search)) {
            $search = $search['value'] ?? null;
        }
        $search = is_string($search) ? trim($search) : null;

        return $query
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('pe_number', 'like', "%{$term}%")
                        ->orWhereHas('patient', function ($p) use ($term) {
                            $p->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%")
                                ->orWhere('patient_number', 'like', "%{$term}%");
                        });
                });
            })
            ->when($filters['purpose'] ?? null, fn ($q, $val) => $q->where('purpose', $val))
            ->when($filters['fitness_assessment'] ?? null, fn ($q, $val) => $q->where('fitness_assessment', $val))
            ->when($filters['academic_year_id'] ?? null, fn ($q, $val) => $q->where('academic_year_id', $val))
            ->when($filters['date_from'] ?? null, fn ($q, $val) => $q->whereDate('exam_date', '>=', $val))
            ->when($filters['date_to'] ?? null, fn ($q, $val) => $q->whereDate('exam_date', '<=', $val));
    }
}
