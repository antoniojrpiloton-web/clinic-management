<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirstAidRecord extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    public const INJURY_TYPES = [
        'Abrasion / Scratch',
        'Laceration / Cut',
        'Puncture Wound',
        'Contusion / Bruise',
        'Sprain / Strain',
        'Minor Burn',
        'Nosebleed (Epistaxis)',
        'Insect Bite / Sting',
        'Animal Bite',
        'Foreign Body (Eye/Ear/Nose)',
        'Dizziness / Fainting',
        'Headache',
        'Dysmenorrhea',
        'Muscle Cramps',
        'Other',
    ];

    /**
     * @var list<string>
     */
    public const FIRST_AID_ACTIONS = [
        'Wound Cleaning',
        'Antiseptic Applied',
        'Dressing / Bandage Applied',
        'Cold Compress',
        'Warm Compress',
        'Elevation',
        'Elastic Bandage / Immobilization',
        'Burn Ointment Applied',
        'Pressure Applied (Bleeding Control)',
        'Eye Irrigation',
        'Rest / Observation',
        'Oral Medication Given',
        'Vital Signs Monitored',
    ];

    protected $fillable = [
        'clinic_visit_id',
        'injury_type',
        'body_part',
        'incident_location',
        'incident_cause',
        'first_aid_given',
        'treatment_notes',
        'supplies_used',
        'was_referred',
        'referred_to',
    ];

    protected function casts(): array
    {
        return [
            'first_aid_given' => 'array',
            'was_referred' => 'boolean',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class, 'clinic_visit_id');
    }
}
