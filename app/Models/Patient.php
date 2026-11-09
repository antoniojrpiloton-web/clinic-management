<?php

namespace App\Models;

use App\Models\Concerns\HasObfuscatedRouteKey;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;
    use HasObfuscatedRouteKey;

    protected $fillable = [
        'patient_number',
        'school_id_number',
        'type',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'date_of_birth',
        'civil_status',
        'blood_type',
        'email',
        'phone',
        'address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'allergies',
        'medical_history',
        'status',
        'college_id',
        'program_id',
        'academic_year_id',
        'year_level',
        'section',
        'enrollment_status',
        'office_department',
        'employment_classification',
        'category',
        'position',
        'employment_status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
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

    public function medicalProfile(): HasOne
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function physicalExaminations(): HasMany
    {
        return $this->hasMany(PhysicalExamination::class)->latest('exam_date');
    }

    public function latestPhysicalExamination(): HasOne
    {
        return $this->hasOne(PhysicalExamination::class)->latestOfMany('exam_date');
    }

    public function clinicVisits(): HasMany
    {
        return $this->hasMany(ClinicVisit::class)->latest('visit_date');
    }

    public function getStudentProfileAttribute(): ?self
    {
        return $this->type === 'student' ? $this : null;
    }

    public function getEmployeeProfileAttribute(): ?self
    {
        return $this->type === 'employee' ? $this : null;
    }

    public function getStudentIdAttribute(): ?string
    {
        return $this->school_id_number;
    }

    public function setStudentIdAttribute(?string $value): void
    {
        $this->attributes['school_id_number'] = $value;
    }

    public function getEmployeeIdAttribute(): ?string
    {
        return $this->school_id_number;
    }

    public function setEmployeeIdAttribute(?string $value): void
    {
        $this->attributes['school_id_number'] = $value;
    }

    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? ' '.mb_substr($this->middle_name, 0, 1).'.' : '';
        $suffix = $this->suffix ? ' '.$this->suffix : '';

        return "{$this->first_name}{$middle} {$this->last_name}{$suffix}";
    }

    public function getAgeAttribute(): int
    {
        return (int) $this->date_of_birth->age;
    }

    public function initials(): string
    {
        $first = mb_substr($this->first_name, 0, 1);
        $last = mb_substr($this->last_name, 0, 1);

        return mb_strtoupper($first.$last);
    }

    public function getPrimaryIdentifierAttribute(): string
    {
        return $this->school_id_number ?: $this->patient_number;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('type', 'student');
    }

    public function scopeEmployees(Builder $query): Builder
    {
        return $query->where('type', 'employee');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $tokens = preg_split('/[\s,]+/', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [$term];

        return $query->where(function (Builder $q) use ($term, $tokens) {
            $q->where('patient_number', 'like', "%{$term}%")
                ->orWhere('school_id_number', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('middle_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('office_department', 'like', "%{$term}%")
                ->orWhere('position', 'like', "%{$term}%")
                ->orWhereHas('program', function (Builder $pq) use ($term) {
                    $pq->where('code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                });

            if (count($tokens) > 1) {
                $q->orWhere(function (Builder $multi) use ($tokens) {
                    foreach ($tokens as $token) {
                        $multi->where(function (Builder $part) use ($token) {
                            $part->where('first_name', 'like', "%{$token}%")
                                ->orWhere('middle_name', 'like', "%{$token}%")
                                ->orWhere('last_name', 'like', "%{$token}%")
                                ->orWhere('patient_number', 'like', "%{$token}%")
                                ->orWhere('school_id_number', 'like', "%{$token}%");
                        });
                    }
                });
            }
        });
    }

    public static function generatePatientNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PAT-%d-%05d', $year, $count);
    }

    public static function findDuplicate(string $firstName, string $lastName, string|Carbon $dob, ?int $exceptId = null): ?self
    {
        $date = is_string($dob) ? Carbon::parse($dob)->toDateString() : $dob->toDateString();

        return static::where('first_name', 'like', trim($firstName))
            ->where('last_name', 'like', trim($lastName))
            ->whereDate('date_of_birth', $date)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }
}
