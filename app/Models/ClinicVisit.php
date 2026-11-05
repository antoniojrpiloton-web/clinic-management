<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClinicVisit extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    public const TYPE_MEDICAL = 'medical';

    public const TYPE_DENTAL = 'dental';

    public const TYPE_EMERGENCY = 'emergency';

    public const TYPE_FIRST_AID = 'first_aid';

    public const TYPE_PHYSICAL_EXAM = 'physical_exam';

    public const TYPE_GENERAL = 'general';

    /**
     * @var array<string, array{label: string, icon: string, badge: string, route: string|null}>
     */
    public const TYPES = [
        self::TYPE_MEDICAL => ['label' => 'Medical Consultation', 'icon' => 'bi-clipboard2-pulse', 'badge' => 'badge-blue', 'route' => 'medical-consultations'],
        self::TYPE_DENTAL => ['label' => 'Dental Consultation', 'icon' => 'bi-emoji-smile', 'badge' => 'badge-cyan', 'route' => 'dental-consultations'],
        self::TYPE_EMERGENCY => ['label' => 'Emergency', 'icon' => 'bi-truck-front', 'badge' => 'badge-red', 'route' => 'emergency-records'],
        self::TYPE_FIRST_AID => ['label' => 'First Aid', 'icon' => 'bi-bandaid', 'badge' => 'badge-amber', 'route' => 'first-aid-records'],
        self::TYPE_PHYSICAL_EXAM => ['label' => 'Physical Examination', 'icon' => 'bi-person-check', 'badge' => 'badge-purple', 'route' => null],
        self::TYPE_GENERAL => ['label' => 'General Visit', 'icon' => 'bi-journal-text', 'badge' => 'badge-gray', 'route' => null],
    ];

    /**
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'walk_in' => 'Walk-in',
        'scheduled' => 'Scheduled',
        'follow_up' => 'Follow-up',
        'referral' => 'Referral (from Faculty/Office)',
        'emergency_call' => 'Emergency Call / Response',
    ];

    /**
     * @var array<string, string>
     */
    public const DISPOSITIONS = [
        'back_to_class' => 'Sent Back to Class / Work',
        'sent_home' => 'Sent Home',
        'observation' => 'Under Clinic Observation',
        'referred' => 'Referred to Specialist / Facility',
        'hospital_transfer' => 'Transferred to Hospital',
        'follow_up' => 'Advised Follow-up',
        'refused' => 'Refused Treatment',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'waiting' => 'Waiting',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @var list<string>
     */
    public const GENERAL_PURPOSES = [
        'Blood Pressure Monitoring',
        'Medical Certificate Request',
        'Medicine Request',
        'Health Counseling',
        'Follow-up Check',
        'Clearance Signing',
        'Vaccination',
        'Other',
    ];

    protected $fillable = [
        'visit_number',
        'patient_id',
        'visit_type',
        'visit_category',
        'patient_classification',
        'classification_label',
        'college_id',
        'program_id',
        'academic_year_id',
        'year_level',
        'office_department',
        'visit_date',
        'time_out',
        'chief_complaint',
        'systolic',
        'diastolic',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'weight_kg',
        'height_cm',
        'pain_scale',
        'clinician_id',
        'recorded_by',
        'disposition',
        'status',
        'remarks',
        'physical_examination_id',
        'medicine_dispensing_id',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'datetime',
            'time_out' => 'datetime',
            'temperature' => 'decimal:1',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function physicalExamination(): BelongsTo
    {
        return $this->belongsTo(PhysicalExamination::class);
    }

    public function medicineDispensing(): BelongsTo
    {
        return $this->belongsTo(MedicineDispensing::class);
    }

    public function medicalConsultation(): HasOne
    {
        return $this->hasOne(MedicalConsultation::class);
    }

    public function dentalConsultation(): HasOne
    {
        return $this->hasOne(DentalConsultation::class);
    }

    public function emergencyRecord(): HasOne
    {
        return $this->hasOne(EmergencyRecord::class);
    }

    public function firstAidRecord(): HasOne
    {
        return $this->hasOne(FirstAidRecord::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('visit_type', $type);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('visit_date', now()->toDateString());
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['visit_type'] ?? null, fn (Builder $q, string $type) => $q->where('visit_type', $type))
            ->when($filters['patient_classification'] ?? null, fn (Builder $q, string $classification) => $q->where('patient_classification', $classification))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['disposition'] ?? null, fn (Builder $q, string $disposition) => $q->where('disposition', $disposition))
            ->when($filters['college_id'] ?? null, fn (Builder $q, string $collegeId) => $q->where('college_id', $collegeId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('visit_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('visit_date', '<=', $to))
            ->when($filters['patient_id'] ?? null, fn (Builder $q, string $patientId) => $q->where('patient_id', $patientId));
    }

    public static function generateVisitNumber(): string
    {
        $prefix = 'VIS-'.now()->format('Ymd').'-';
        $last = static::where('visit_number', 'like', $prefix.'%')->orderByDesc('visit_number')->value('visit_number');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->visit_type]['label'] ?? ucfirst(str_replace('_', ' ', $this->visit_type));
    }

    public function getTypeBadgeAttribute(): string
    {
        return self::TYPES[$this->visit_type]['badge'] ?? 'badge-gray';
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPES[$this->visit_type]['icon'] ?? 'bi-journal-text';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->visit_category] ?? 'N/A';
    }

    public function getDispositionLabelAttribute(): string
    {
        return $this->disposition ? (self::DISPOSITIONS[$this->disposition] ?? $this->disposition) : 'N/A';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'badge-amber',
            'in_progress' => 'badge-blue',
            'completed' => 'badge-green',
            'cancelled' => 'badge-gray',
            default => 'badge-gray',
        };
    }

    public function getClassificationBadgeAttribute(): string
    {
        return $this->patient_classification === 'student' ? 'badge-blue' : 'badge-green';
    }

    public function getBloodPressureAttribute(): ?string
    {
        return $this->systolic && $this->diastolic ? "{$this->systolic}/{$this->diastolic}" : null;
    }

    public function getDetailUrlAttribute(): string
    {
        $route = self::TYPES[$this->visit_type]['route'] ?? null;

        if ($route) {
            return route("{$route}.show", $this);
        }

        if ($this->visit_type === self::TYPE_PHYSICAL_EXAM && $this->physical_examination_id) {
            return route('physical-examinations.show', $this->physical_examination_id);
        }

        return route('clinic-visits.show', $this);
    }

    public function getSummaryAttribute(): string
    {
        return match ($this->visit_type) {
            self::TYPE_MEDICAL => $this->medicalConsultation?->diagnosis ?? '',
            self::TYPE_DENTAL => $this->dentalConsultation?->diagnosis ?? '',
            self::TYPE_EMERGENCY => $this->emergencyRecord?->emergency_nature ?? '',
            self::TYPE_FIRST_AID => trim(($this->firstAidRecord?->injury_type ?? '').' '.($this->firstAidRecord?->body_part ? '('.$this->firstAidRecord->body_part.')' : '')),
            self::TYPE_PHYSICAL_EXAM => $this->physicalExamination?->fitness_assessment ? 'Fitness: '.$this->physicalExamination->fitness_assessment : '',
            default => '',
        } ?: ($this->chief_complaint ?? '');
    }

    public function getDurationLabelAttribute(): string
    {
        if (! $this->time_out) {
            return 'N/A';
        }

        $minutes = (int) $this->visit_date->diffInMinutes($this->time_out);

        return $minutes >= 60 ? intdiv($minutes, 60).'h '.($minutes % 60).'m' : "{$minutes} min";
    }
}
