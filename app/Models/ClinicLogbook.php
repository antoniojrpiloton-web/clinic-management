<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicLogbook extends Model
{
    use HasFactory;

    public const PURPOSES = [
        'Medical Consultation',
        'Dental Consultation',
        'BP / Vital Signs Check',
        'Medicine Request',
        'Physical Exam / Clearance',
        'First Aid / Rest',
        'Inquiry / Document Submission',
        'Others',
    ];

    protected $fillable = [
        'log_number',
        'log_date',
        'time_in',
        'time_out',
        'patient_id',
        'clinic_visit_id',
        'academic_year_id',
        'college_id',
        'program_id',
        'visitor_type',
        'id_number',
        'full_name',
        'course_or_department',
        'year_or_position',
        'contact_number',
        'purpose',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (ClinicLogbook $log) {
            if (empty($log->log_number)) {
                $log->log_number = static::generateLogNumber();
            }
        });
    }

    public static function generateLogNumber(): string
    {
        $year = date('Y');
        $prefix = "LOG-{$year}-";

        $last = static::where('log_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('log_number');

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

    public function clinicVisit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class);
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

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($term) {
            $sub->where('full_name', 'like', "%{$term}%")
                ->orWhere('id_number', 'like', "%{$term}%")
                ->orWhere('log_number', 'like', "%{$term}%")
                ->orWhere('course_or_department', 'like', "%{$term}%")
                ->orWhere('purpose', 'like', "%{$term}%")
                ->orWhere('notes', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('log_number', 'like', "%{$search}%")
                        ->orWhere('course_or_department', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%");
                });
            })
            ->when($filters['visitor_type'] ?? null, function (Builder $q, string $type) {
                if ($type === 'employee') {
                    $q->whereIn('visitor_type', ['employee', 'faculty', 'staff']);
                } else {
                    $q->where('visitor_type', $type);
                }
            })
            ->when($filters['program_id'] ?? null, fn (Builder $q, $progId) => $q->where('program_id', $progId))
            ->when($filters['purpose'] ?? null, fn (Builder $q, string $purpose) => $q->where('purpose', $purpose))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('log_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('log_date', '<=', $to));
    }
}
