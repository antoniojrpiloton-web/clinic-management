<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalExamScheduleAttendee extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    /**
     * @var array<string, array{label: string, badge: string}>
     */
    public const STATUSES = [
        'pending' => ['label' => 'Pending', 'badge' => 'badge-amber'],
        'attended' => ['label' => 'Examined', 'badge' => 'badge-green'],
        'no_show' => ['label' => 'No Show', 'badge' => 'badge-red'],
    ];

    protected $fillable = [
        'physical_exam_schedule_id',
        'patient_id',
        'status',
        'physical_examination_id',
        'clinic_visit_id',
        'attended_at',
    ];

    protected function casts(): array
    {
        return [
            'attended_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PhysicalExamSchedule::class, 'physical_exam_schedule_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physicalExamination(): BelongsTo
    {
        return $this->belongsTo(PhysicalExamination::class);
    }

    public function clinicVisit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status]['badge'] ?? 'badge-gray';
    }
}
