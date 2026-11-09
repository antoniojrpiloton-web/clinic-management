<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalExamSchedule extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    public const STATUSES = [
        'scheduled' => ['label' => 'Scheduled', 'badge' => 'badge-blue'],
        'ongoing' => ['label' => 'Ongoing', 'badge' => 'badge-amber'],
        'completed' => ['label' => 'Completed', 'badge' => 'badge-green'],
        'cancelled' => ['label' => 'Cancelled', 'badge' => 'badge-gray'],
    ];

    protected $fillable = [
        'schedule_number',
        'title',
        'purpose',
        'target_classification',
        'academic_year_id',
        'college_id',
        'program_id',
        'year_level',
        'office_department',
        'scheduled_date',
        'start_time',
        'end_time',
        'venue',
        'capacity',
        'clinician_id',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(Clinician::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(PhysicalExamScheduleAttendee::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('scheduled_date', '>=', now()->toDateString())->whereIn('status', ['scheduled', 'ongoing']);
    }

    public static function generateScheduleNumber(): string
    {
        $prefix = 'PES-'.now()->format('Y').'-';
        $last = static::where('schedule_number', 'like', $prefix.'%')->orderByDesc('schedule_number')->value('schedule_number');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status]['badge'] ?? 'badge-gray';
    }

    public function getTimeRangeAttribute(): string
    {
        if (! $this->start_time) {
            return 'N/A';
        }

        $start = date('h:i A', strtotime($this->start_time));
        $end = $this->end_time ? date('h:i A', strtotime($this->end_time)) : null;

        return $end ? "{$start} - {$end}" : $start;
    }

    public function getTargetGroupLabelAttribute(): string
    {
        $parts = [ucfirst($this->target_classification).'s'];

        if ($this->college) {
            $parts[] = $this->college->code ?? $this->college->name;
        }

        if ($this->program) {
            $parts[] = $this->program->code ?? $this->program->name;
        }

        if ($this->year_level) {
            $parts[] = $this->year_level;
        }

        if ($this->office_department) {
            $parts[] = $this->office_department;
        }

        return implode(' • ', $parts);
    }
}
