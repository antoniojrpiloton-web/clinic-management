<?php

namespace App\Services;

use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DataAggregationAlgorithmService
{
    /**
     * @var list<string>
     */
    public const COMMUNICABLE_KEYWORDS = [
        'urti',
        'upper respiratory',
        'respiratory tract infection',
        'influenza',
        'flu',
        'cough',
        'cold',
        'rhinitis',
        'pharyngitis',
        'tonsillitis',
        'bronchitis',
        'pneumonia',
        'tuberculosis',
        'tb',
        'gastroenteritis',
        'diarrhea',
        'conjunctivitis',
        'pink eye',
        'varicella',
        'chickenpox',
        'measles',
        'dengue',
        'covid',
        'scabies',
        'tinea',
        'fungal',
        'viral',
        'bacterial',
        'herpes',
    ];

    /**
     * @var list<string>
     */
    public const NON_COMMUNICABLE_KEYWORDS = [
        'hypertension',
        'elevated bp',
        'high blood',
        'diabetes',
        'hyperglycemia',
        'asthma',
        'cardiovascular',
        'migraine',
        'headache',
        'tension headache',
        'gastritis',
        'gerd',
        'acid reflux',
        'dyspepsia',
        'hyperuricemia',
        'gout',
        'anemia',
        'allergy',
        'allergic',
        'urticaria',
        'dermatitis',
        'eczema',
        'dysmenorrhea',
        'musculoskeletal',
        'myalgia',
        'back pain',
        'muscle spasm',
        'caries',
        'gingivitis',
        'periodontitis',
    ];

    /**
     * @param  array{
     *   date_from?: string|null,
     *   date_to?: string|null,
     *   academic_year_id?: int|string|null,
     *   college_id?: int|string|null,
     *   patient_classification?: string|null
     * }  $filters
     * @return array<string, mixed>
     */
    public function aggregateClinicReport(array $filters = []): array
    {
        $visitQuery = ClinicVisit::query()->with(['patient.college', 'patient.program', 'clinician', 'medicalConsultation', 'dentalConsultation']);

        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : Carbon::now()->startOfYear();
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : Carbon::now()->endOfDay();

        $visitQuery->whereBetween('visit_date', [$dateFrom, $dateTo]);

        if (! empty($filters['academic_year_id'])) {
            $visitQuery->where('academic_year_id', $filters['academic_year_id']);
        }

        if (! empty($filters['college_id'])) {
            $visitQuery->where('college_id', $filters['college_id']);
        }

        if (! empty($filters['patient_classification'])) {
            $visitQuery->where('patient_classification', $filters['patient_classification']);
        }

        $visits = $visitQuery->get();

        $totalVisits = $visits->count();
        $uniquePatients = $visits->pluck('patient_id')->unique()->count();

        $serviceDistribution = [
            'Medical Consultations' => $visits->where('visit_type', ClinicVisit::TYPE_MEDICAL)->count(),
            'Dental Consultations' => $visits->where('visit_type', ClinicVisit::TYPE_DENTAL)->count(),
            'Emergency Cases' => $visits->where('visit_type', ClinicVisit::TYPE_EMERGENCY)->count(),
            'First Aid Cases' => $visits->where('visit_type', ClinicVisit::TYPE_FIRST_AID)->count(),
            'General / Other Visits' => $visits->whereNotIn('visit_type', [ClinicVisit::TYPE_MEDICAL, ClinicVisit::TYPE_DENTAL, ClinicVisit::TYPE_EMERGENCY, ClinicVisit::TYPE_FIRST_AID])->count(),
        ];

        $classificationRollup = [
            'Students' => $visits->where('patient_classification', 'student')->count(),
            'Faculty' => $visits->where('patient_classification', 'faculty')->count(),
            'Staff' => $visits->whereIn('patient_classification', ['staff', 'employee'])->count(),
            'Others / Dependents' => $visits->whereNotIn('patient_classification', ['student', 'faculty', 'staff', 'employee'])->count(),
        ];

        $collegeRollup = [];
        $colleges = College::orderBy('name')->get();
        foreach ($colleges as $col) {
            $count = $visits->where('college_id', $col->id)->count();
            if ($count > 0) {
                $collegeRollup[$col->code ?: $col->name] = $count;
            }
        }
        $nonCollegeCount = $visits->whereNull('college_id')->count();
        if ($nonCollegeCount > 0) {
            $collegeRollup['Non-Academic / Employees'] = $nonCollegeCount;
        }

        $morbidityData = $this->aggregateMorbidityData($visits);

        $complaintRollup = $this->aggregateChiefComplaints($visits);

        $dispensingData = $this->aggregateMedicineDispensing($dateFrom, $dateTo, $filters);

        return [
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'label' => $dateFrom->format('M d, Y').' to '.$dateTo->format('M d, Y'),
            ],
            'summary' => [
                'total_visits' => $totalVisits,
                'unique_patients' => $uniquePatients,
                'total_medicines_dispensed' => $dispensingData['total_units'],
            ],
            'service_distribution' => $serviceDistribution,
            'classification_rollup' => $classificationRollup,
            'college_rollup' => $collegeRollup,
            'morbidity' => $morbidityData,
            'complaints' => $complaintRollup,
            'dispensing' => $dispensingData,
        ];
    }

    /**
     * @param  Collection<int, ClinicVisit>  $visits
     * @return array{
     *   communicable: list<array{name: string, count: int, percentage: float}>,
     *   non_communicable: list<array{name: string, count: int, percentage: float}>,
     *   dental: list<array{name: string, count: int, percentage: float}>,
     *   total_communicable: int,
     *   total_non_communicable: int,
     *   total_dental: int
     * }
     */
    protected function aggregateMorbidityData($visits): array
    {
        $communicableTally = [];
        $nonCommunicableTally = [];
        $dentalTally = [];

        foreach ($visits as $visit) {
            if ($visit->medicalConsultation && ! empty($visit->medicalConsultation->diagnosis)) {
                $diag = trim($visit->medicalConsultation->diagnosis);
                $lower = strtolower($diag);

                $isComm = false;
                foreach (self::COMMUNICABLE_KEYWORDS as $k) {
                    if (str_contains($lower, $k)) {
                        $isComm = true;
                        $label = ucwords($k);
                        $communicableTally[$label] = ($communicableTally[$label] ?? 0) + 1;
                        break;
                    }
                }

                if (! $isComm) {
                    $isNonComm = false;
                    foreach (self::NON_COMMUNICABLE_KEYWORDS as $k) {
                        if (str_contains($lower, $k)) {
                            $isNonComm = true;
                            $label = ucwords($k);
                            $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + 1;
                            break;
                        }
                    }

                    if (! $isNonComm) {
                        $label = mb_strimwidth($diag, 0, 30, '...');
                        $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + 1;
                    }
                }
            }

            if ($visit->dentalConsultation && ! empty($visit->dentalConsultation->diagnosis)) {
                $diag = trim($visit->dentalConsultation->diagnosis);
                $label = mb_strimwidth($diag, 0, 35, '...');
                $dentalTally[$label] = ($dentalTally[$label] ?? 0) + 1;
            }
        }

        arsort($communicableTally);
        arsort($nonCommunicableTally);
        arsort($dentalTally);

        $totComm = array_sum($communicableTally);
        $totNonComm = array_sum($nonCommunicableTally);
        $totDental = array_sum($dentalTally);

        $formatList = function (array $tally, int $total): array {
            $res = [];
            foreach ($tally as $k => $v) {
                $res[] = [
                    'name' => (string) $k,
                    'count' => (int) $v,
                    'percentage' => $total > 0 ? round(($v / $total) * 100, 1) : 0.0,
                ];
            }

            return $res;
        };

        return [
            'communicable' => $formatList($communicableTally, $totComm),
            'non_communicable' => $formatList($nonCommunicableTally, $totNonComm),
            'dental' => $formatList($dentalTally, $totDental),
            'total_communicable' => $totComm,
            'total_non_communicable' => $totNonComm,
            'total_dental' => $totDental,
        ];
    }

    /**
     * @param  Collection<int, ClinicVisit>  $visits
     * @return list<array{name: string, count: int, percentage: float}>
     */
    protected function aggregateChiefComplaints($visits): array
    {
        $tally = [];
        foreach ($visits as $v) {
            if (! empty($v->chief_complaint)) {
                $cat = $this->categorizeComplaint((string) $v->chief_complaint);
                $tally[$cat] = ($tally[$cat] ?? 0) + 1;
            }
        }

        arsort($tally);
        $total = array_sum($tally);

        $res = [];
        foreach ($tally as $k => $v) {
            $res[] = [
                'name' => (string) $k,
                'count' => (int) $v,
                'percentage' => $total > 0 ? round(($v / $total) * 100, 1) : 0.0,
            ];
        }

        return $res;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *   total_units: int,
     *   total_dispensings: int,
     *   top_medicines: list<array{name: string, quantity: int}>
     * }
     */
    protected function aggregateMedicineDispensing(Carbon $dateFrom, Carbon $dateTo, array $filters): array
    {
        $dispQuery = MedicineDispensing::query()->whereBetween('dispensed_date', [$dateFrom, $dateTo]);

        if (! empty($filters['patient_classification'])) {
            $dispQuery->whereHas('patient', function ($q) use ($filters) {
                $q->where('type', $filters['patient_classification']);
            });
        }

        $dispensingIds = $dispQuery->pluck('id');
        $totalDispensings = $dispensingIds->count();

        $items = MedicineDispensingItem::whereIn('dispensing_id', $dispensingIds)
            ->select('medicine_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_qty')
            ->with('medicine:id,generic_name,brand_name')
            ->limit(10)
            ->get();

        $topMeds = [];
        foreach ($items as $it) {
            $name = $it->medicine?->generic_name ?? 'Unknown';
            if ($it->medicine?->brand_name) {
                $name .= ' ('.$it->medicine->brand_name.')';
            }
            $topMeds[] = [
                'name' => $name,
                'quantity' => (int) $it->total_qty,
            ];
        }

        $totalUnits = (int) MedicineDispensingItem::whereIn('dispensing_id', $dispensingIds)->sum('quantity');

        return [
            'total_units' => $totalUnits,
            'total_dispensings' => $totalDispensings,
            'top_medicines' => $topMeds,
        ];
    }

    protected function categorizeComplaint(string $text): string
    {
        $lower = strtolower($text);

        if (str_contains($lower, 'fever') || str_contains($lower, 'lagnat')) {
            return 'Fever / Pyrexia';
        }
        if (str_contains($lower, 'cough') || str_contains($lower, 'ubo')) {
            return 'Cough / Colds';
        }
        if (str_contains($lower, 'headache') || str_contains($lower, 'sakit ang ulo') || str_contains($lower, 'migraine')) {
            return 'Headache / Migraine';
        }
        if (str_contains($lower, 'tooth') || str_contains($lower, 'ngipin') || str_contains($lower, 'caries') || str_contains($lower, 'molar')) {
            return 'Dental Concern / Toothache';
        }
        if (str_contains($lower, 'stomach') || str_contains($lower, 'abdominal') || str_contains($lower, 'sakit ng tiyan') || str_contains($lower, 'gastric')) {
            return 'Abdominal / Stomach Pain';
        }
        if (str_contains($lower, 'wound') || str_contains($lower, 'abrasion') || str_contains($lower, 'cut') || str_contains($lower, 'laceration') || str_contains($lower, 'fall')) {
            return 'Trauma / Fall / Wound';
        }
        if (str_contains($lower, 'dizziness') || str_contains($lower, 'syncope') || str_contains($lower, 'hilo') || str_contains($lower, 'faint')) {
            return 'Dizziness / Syncope';
        }
        if (str_contains($lower, 'dysmenorrhea') || str_contains($lower, 'cramps') || str_contains($lower, 'menstrual')) {
            return 'Dysmenorrhea';
        }
        if (str_contains($lower, 'bp') || str_contains($lower, 'blood pressure') || str_contains($lower, 'hypertension')) {
            return 'Hypertension / BP Check';
        }
        if (str_contains($lower, 'allergy') || str_contains($lower, 'rashes') || str_contains($lower, 'itch')) {
            return 'Allergy / Skin Rash';
        }

        return mb_strimwidth(trim($text), 0, 28, '...');
    }
}
