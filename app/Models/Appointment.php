<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    public const SERVICE_TYPES = [
        'medical' => 'Medical Consultation',
        'dental' => 'Dental Consultation',
        'general' => 'General / Routine Checkup',
        'physical_exam' => 'Physical Examination',
        'consultation' => 'General Health Consultation',
        'clearance' => 'Medical / Dental Clearance',
        'follow_up' => 'Follow-up Evaluation',
    ];

    public const PRIORITIES = [
        'urgent' => 'Urgent Priority',
        'high' => 'High Priority',
        'normal' => 'Normal Priority',
    ];

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'checked_in' => 'Checked In / Waiting',
        'in_consultation' => 'In Consultation',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    public const TIME_SLOTS = [
        '08:00 AM',
        '08:30 AM',
        '09:00 AM',
        '09:30 AM',
        '10:00 AM',
        '10:30 AM',
        '11:00 AM',
        '11:30 AM',
        '01:00 PM',
        '01:30 PM',
        '02:00 PM',
        '02:30 PM',
        '03:00 PM',
        '03:30 PM',
        '04:00 PM',
        '04:30 PM',
    ];

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'clinician_id',
        'booked_by_user_id',
        'clinic_visit_id',
        'appointment_date',
        'appointment_time',
        'service_type',
        'priority_level',
        'reason',
        'notes',
        'status',
        'cancellation_reason',
        'checked_in_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Appointment $appointment) {
            if (empty($appointment->appointment_number)) {
                $appointment->appointment_number = static::generateAppointmentNumber();
            }
        });
    }

    public static function generateAppointmentNumber(): string
    {
        $year = date('Y');
        $prefix = "APP-{$year}-";

        $last = static::where('appointment_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('appointment_number');

        $nextSeq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    public function clinicVisit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', Carbon::today());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', Carbon::today());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['scheduled', 'checked_in', 'in_consultation']);
    }

    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->where('status', 'checked_in');
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority_level) {
            'urgent' => 'bg-danger text-white',
            'high' => 'bg-warning text-dark',
            default => 'bg-primary text-white',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'checked_in' => 'bg-info text-dark',
            'in_consultation' => 'bg-primary text-white',
            'completed' => 'bg-success text-white',
            'cancelled' => 'bg-secondary text-white',
            'no_show' => 'bg-danger text-white',
            default => 'bg-warning text-dark',
        };
    }
}
