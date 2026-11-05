<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyRecord extends Model
{
    use HasFactory;

    /**
     * @var array<string, array{label: string, badge: string}>
     */
    public const TRIAGE_LEVELS = [
        'red' => ['label' => 'Red - Immediate / Life-threatening', 'badge' => 'badge-red'],
        'yellow' => ['label' => 'Yellow - Urgent', 'badge' => 'badge-amber'],
        'green' => ['label' => 'Green - Non-urgent / Minor', 'badge' => 'badge-green'],
        'black' => ['label' => 'Black - Deceased / Expectant', 'badge' => 'badge-gray'],
    ];

    /**
     * @var list<string>
     */
    public const NATURES = [
        'Syncope / Loss of Consciousness',
        'Seizure',
        'Severe Asthma / Difficulty of Breathing',
        'Chest Pain / Cardiac',
        'Severe Allergic Reaction / Anaphylaxis',
        'Trauma / Fall / Vehicular Accident',
        'Fracture / Dislocation',
        'Severe Bleeding / Laceration',
        'Burns',
        'Poisoning / Overdose',
        'Hypertensive Crisis',
        'Hypoglycemia / Diabetic Emergency',
        'Heat Stroke / Heat Exhaustion',
        'Panic Attack / Psychiatric Emergency',
        'Other',
    ];

    /**
     * @var array<string, string>
     */
    public const CONSCIOUSNESS_LEVELS = [
        'alert' => 'A - Alert',
        'verbal' => 'V - Responds to Voice',
        'pain' => 'P - Responds to Pain',
        'unresponsive' => 'U - Unresponsive',
    ];

    /**
     * @var list<string>
     */
    public const INTERVENTIONS = [
        'CPR',
        'AED Applied',
        'Oxygen Therapy',
        'Nebulization',
        'Airway Management',
        'Bleeding Control / Pressure Dressing',
        'Splinting / Immobilization',
        'Spinal Precaution',
        'IV Line Inserted',
        'Emergency Medication Given',
        'Recovery Position',
        'Cold / Heat Compress',
        'Wound Care',
        'Monitoring of Vital Signs',
    ];

    /**
     * @var list<string>
     */
    public const OUTCOMES = [
        'Stabilized and Discharged',
        'Stabilized and Sent Home',
        'Transferred to Hospital',
        'Admitted for Observation',
        'Expired',
    ];

    protected $fillable = [
        'clinic_visit_id',
        'incident_datetime',
        'incident_location',
        'emergency_nature',
        'triage_level',
        'level_of_consciousness',
        'gcs_score',
        'incident_description',
        'interventions',
        'intervention_notes',
        'ambulance_called',
        'transferred_to_hospital',
        'hospital_name',
        'transport_mode',
        'transferred_at',
        'guardian_notified',
        'notified_person',
        'notified_at',
        'incident_report_number',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'incident_datetime' => 'datetime',
            'transferred_at' => 'datetime',
            'notified_at' => 'datetime',
            'interventions' => 'array',
            'ambulance_called' => 'boolean',
            'transferred_to_hospital' => 'boolean',
            'guardian_notified' => 'boolean',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ClinicVisit::class, 'clinic_visit_id');
    }

    public function getTriageLabelAttribute(): string
    {
        return self::TRIAGE_LEVELS[$this->triage_level]['label'] ?? 'N/A';
    }

    public function getTriageBadgeAttribute(): string
    {
        return self::TRIAGE_LEVELS[$this->triage_level]['badge'] ?? 'badge-gray';
    }

    public function getConsciousnessLabelAttribute(): string
    {
        return self::CONSCIOUSNESS_LEVELS[$this->level_of_consciousness] ?? 'N/A';
    }
}
