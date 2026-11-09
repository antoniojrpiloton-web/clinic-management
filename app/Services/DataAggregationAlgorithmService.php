<?php

namespace App\Services;

use App\Models\ClinicLogbook;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\DentalConsultation;
use App\Models\DiseaseCategory;
use App\Models\MedicalConsultation;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataAggregationAlgorithmService
{
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

    public function aggregateClinicReport(array $filters = []): array
    {
        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : Carbon::now()->startOfYear();
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : Carbon::now()->endOfDay();

        $visitQuery = $this->buildFilteredVisitQuery($dateFrom, $dateTo, $filters);

        $totalVisits = (clone $visitQuery)->count();
        $uniquePatients = (clone $visitQuery)->distinct()->count('clinic_visits.patient_id');

        $visitTypeCounts = (clone $visitQuery)
            ->select('clinic_visits.visit_type', DB::raw('COUNT(*) as total'))
            ->groupBy('clinic_visits.visit_type')
            ->pluck('total', 'visit_type');

        $medicalVisits = (int) ($visitTypeCounts[ClinicVisit::TYPE_MEDICAL] ?? 0);
        $dentalVisits = (int) ($visitTypeCounts[ClinicVisit::TYPE_DENTAL] ?? 0);
        $emergencyVisits = (int) ($visitTypeCounts[ClinicVisit::TYPE_EMERGENCY] ?? 0);
        $firstAidVisits = (int) ($visitTypeCounts[ClinicVisit::TYPE_FIRST_AID] ?? 0);
        $otherVisits = max(0, $totalVisits - ($medicalVisits + $dentalVisits + $emergencyVisits + $firstAidVisits));

        $serviceDistribution = [
            'Medical Consultations' => $medicalVisits,
            'Dental Consultations' => $dentalVisits,
            'Emergency Cases' => $emergencyVisits,
            'First Aid Cases' => $firstAidVisits,
            'General / Other Visits' => $otherVisits,
        ];

        $classificationCounts = (clone $visitQuery)
            ->select('clinic_visits.patient_classification', DB::raw('COUNT(*) as total'))
            ->groupBy('clinic_visits.patient_classification')
            ->pluck('total', 'patient_classification');

        $studentVisits = (int) ($classificationCounts['student'] ?? 0);
        $facultyVisits = (int) ($classificationCounts['faculty'] ?? 0);
        $staffVisits = (int) ($classificationCounts['staff'] ?? 0) + (int) ($classificationCounts['employee'] ?? 0);
        $otherClassificationVisits = max(0, $totalVisits - ($studentVisits + $facultyVisits + $staffVisits));

        $classificationRollup = [
            'Students' => $studentVisits,
            'Faculty' => $facultyVisits,
            'Staff' => $staffVisits,
            'Others / Dependents' => $otherClassificationVisits,
        ];

        $collegeCounts = (clone $visitQuery)
            ->select('clinic_visits.college_id', DB::raw('COUNT(*) as total'))
            ->groupBy('clinic_visits.college_id')
            ->get()
            ->keyBy('college_id');

        $collegeRollup = [];
        $colleges = College::orderBy('name')->get(['id', 'code', 'name']);
        foreach ($colleges as $col) {
            $count = (int) ($collegeCounts->get($col->id)?->total ?? 0);
            if ($count > 0) {
                $collegeRollup[$col->code ?: $col->name] = $count;
            }
        }
        $nonCollegeCount = (int) ($collegeCounts->get(null)?->total ?? $collegeCounts->get('')?->total ?? 0);
        if ($nonCollegeCount > 0) {
            $collegeRollup['Non-Academic / Employees'] = $nonCollegeCount;
        }

        $morbidityData = $this->aggregateMorbidityData($dateFrom, $dateTo, $filters);

        $complaintRollup = $this->aggregateChiefComplaints($visitQuery);

        $dispensingData = $this->aggregateMedicineDispensing($dateFrom, $dateTo, $filters);

        $reportYear = ! empty($filters['year']) ? (int) $filters['year'] : (int) $dateTo->format('Y');
        $morbidityData['communicable_categories'] = $this->aggregateCommunicableDiseaseReport($reportYear, $filters);
        $morbidityData['non_communicable_categories'] = $this->aggregateNonCommunicableDiseaseReport($reportYear, $filters);
        $medicalDentalRecords = $this->aggregateMedicalAndDentalRecords($reportYear, $filters);
        $loginRecords = $this->aggregateMedicalAndDentalLoginRecords($reportYear, $filters);
        $servicePatientMatrix = $this->aggregateServiceByPatientTypeReport($dateFrom, $dateTo, $filters);
        $patientClassificationReport = $this->aggregatePatientClassificationReport($dateFrom, $dateTo, $filters);

        return [
            'year' => $reportYear,
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
            'service_patient_matrix' => $servicePatientMatrix,
            'patient_classification_report' => $patientClassificationReport,
            'college_rollup' => $collegeRollup,
            'morbidity' => $morbidityData,
            'complaints' => $complaintRollup,
            'dispensing' => $dispensingData,
            'medical_dental_records' => $medicalDentalRecords,
            'login_records' => $loginRecords,
        ];
    }

    public function aggregateServiceByPatientTypeReport(Carbon $dateFrom, Carbon $dateTo, array $filters = []): array
    {
        $emptyBucket = [
            'students' => 0,
            'faculty' => 0,
            'non_teaching' => 0,
            'other_personnel' => 0,
            'total' => 0,
        ];

        $matrix = [
            ClinicVisit::TYPE_MEDICAL => array_merge(['service' => 'Medical Consultation'], $emptyBucket),
            ClinicVisit::TYPE_DENTAL => array_merge(['service' => 'Dental Consultation'], $emptyBucket),
            ClinicVisit::TYPE_EMERGENCY => array_merge(['service' => 'Emergency Records'], $emptyBucket),
            ClinicVisit::TYPE_FIRST_AID => array_merge(['service' => 'First Aid Records'], $emptyBucket),
            'scheduled_pe' => array_merge(['service' => 'Scheduled Physical Examination'], $emptyBucket),
        ];

        $visitTotals = $emptyBucket;

        $visitQuery = ClinicVisit::query()
            ->leftJoin('patients', 'patients.id', '=', 'clinic_visits.patient_id')
            ->whereBetween('clinic_visits.visit_date', [$dateFrom, $dateTo]);

        if (! empty($filters['academic_year_id'])) {
            $visitQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $visitQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $visitQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $visitRows = $visitQuery
            ->selectRaw("
                clinic_visits.visit_type,
                CASE
                    WHEN clinic_visits.patient_classification = 'student' OR patients.type = 'student' THEN 'students'
                    WHEN clinic_visits.patient_classification = 'faculty'
                         OR (patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%faculty%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%faculty%'))
                         THEN 'faculty'
                    WHEN clinic_visits.patient_classification = 'staff'
                         OR (patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.category, '')) LIKE '%staff%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%staff%'))
                         THEN 'non_teaching'
                    ELSE 'other_personnel'
                END as resolved_patient_type,
                COUNT(*) as total
            ")
            ->groupBy('clinic_visits.visit_type', 'resolved_patient_type')
            ->get();

        foreach ($visitRows as $r) {
            $vType = (string) $r->visit_type;
            $pBucket = (string) $r->resolved_patient_type;
            $cnt = (int) $r->total;

            if (! isset($visitTotals[$pBucket])) {
                $pBucket = 'other_personnel';
            }

            $visitTotals[$pBucket] += $cnt;
            $visitTotals['total'] += $cnt;

            if (isset($matrix[$vType])) {
                $matrix[$vType][$pBucket] += $cnt;
                $matrix[$vType]['total'] += $cnt;
            }
        }

        $peQuery = PhysicalExamination::query()
            ->join('patients', 'patients.id', '=', 'physical_examinations.patient_id')
            ->whereBetween('physical_examinations.exam_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        if (! empty($filters['academic_year_id'])) {
            $peQuery->where('physical_examinations.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $peQuery->where('patients.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            if ($filters['patient_classification'] === 'student') {
                $peQuery->where('patients.type', 'student');
            } elseif ($filters['patient_classification'] === 'faculty') {
                $peQuery->where('patients.type', 'employee')
                    ->where(function ($q) {
                        $q->where('patients.category', 'like', '%Faculty%')
                            ->orWhere('patients.employment_classification', 'like', '%Faculty%');
                    });
            } elseif ($filters['patient_classification'] === 'staff') {
                $peQuery->where('patients.type', 'employee')
                    ->where(function ($q) {
                        $q->where('patients.category', 'like', '%Non-Teaching%')
                            ->orWhere('patients.category', 'like', '%Staff%')
                            ->orWhere('patients.employment_classification', 'like', '%Non-Teaching%');
                    });
            }
        }

        $peRows = $peQuery
            ->selectRaw("
                CASE
                    WHEN patients.type = 'student' THEN 'students'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%faculty%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%faculty%') THEN 'faculty'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.category, '')) LIKE '%staff%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%staff%') THEN 'non_teaching'
                    ELSE 'other_personnel'
                END as resolved_patient_type,
                COUNT(*) as total
            ")
            ->groupBy('resolved_patient_type')
            ->get();

        foreach ($peRows as $pr) {
            $pBucket = (string) $pr->resolved_patient_type;
            $cnt = (int) $pr->total;
            if (! isset($matrix['scheduled_pe'][$pBucket])) {
                $pBucket = 'other_personnel';
            }
            $matrix['scheduled_pe'][$pBucket] += $cnt;
            $matrix['scheduled_pe']['total'] += $cnt;
        }

        $grandTotals = $emptyBucket;
        foreach ($matrix as $row) {
            $grandTotals['students'] += $row['students'];
            $grandTotals['faculty'] += $row['faculty'];
            $grandTotals['non_teaching'] += $row['non_teaching'];
            $grandTotals['other_personnel'] += $row['other_personnel'];
            $grandTotals['total'] += $row['total'];
        }

        return [
            'rows' => array_values($matrix),
            'visit_totals' => $visitTotals,
            'grand_totals' => $grandTotals,
        ];
    }

    public function aggregatePatientClassificationReport(Carbon $dateFrom, Carbon $dateTo, array $filters = []): array
    {
        $baseQuery = Patient::query();

        if (! empty($filters['college_id'])) {
            $baseQuery->where('patients.college_id', $filters['college_id']);
        }
        if (! empty($filters['academic_year_id'])) {
            $baseQuery->where('patients.academic_year_id', $filters['academic_year_id']);
        }

        $rowsRaw = (clone $baseQuery)
            ->selectRaw("
                CASE
                    WHEN patients.type = 'student' THEN 'students'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%faculty%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%faculty%') THEN 'faculty'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.category, '')) LIKE '%staff%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%staff%') THEN 'non_teaching'
                    ELSE 'other_personnel'
                END as resolved_group,
                COUNT(*) as total_count,
                SUM(CASE WHEN LOWER(COALESCE(patients.status, 'active')) = 'active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN LOWER(COALESCE(patients.status, 'active')) != 'active' THEN 1 ELSE 0 END) as inactive_count
            ")
            ->groupBy('resolved_group')
            ->get()
            ->keyBy('resolved_group');

        $newRaw = (clone $baseQuery)
            ->whereBetween('patients.created_at', [$dateFrom, $dateTo])
            ->selectRaw("
                CASE
                    WHEN patients.type = 'student' THEN 'students'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%faculty%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%faculty%') THEN 'faculty'
                    WHEN patients.type = 'employee' AND (LOWER(COALESCE(patients.category, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.category, '')) LIKE '%staff%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%non-teaching%' OR LOWER(COALESCE(patients.employment_classification, '')) LIKE '%staff%') THEN 'non_teaching'
                    ELSE 'other_personnel'
                END as resolved_group,
                COUNT(*) as new_count
            ")
            ->groupBy('resolved_group')
            ->pluck('new_count', 'resolved_group');

        $groupsMeta = [
            'students' => 'Students',
            'faculty' => 'Faculty (Teaching Personnel)',
            'non_teaching' => 'Non-Teaching Personnel / Staff',
            'other_personnel' => 'Other Personnel',
        ];

        $totalRegistered = 0;
        $activePatients = 0;
        $inactivePatients = 0;
        $newRegistrations = 0;

        $countsByGroup = [];
        foreach ($groupsMeta as $key => $label) {
            $r = $rowsRaw->get($key);
            $tot = (int) ($r?->total_count ?? 0);
            $act = (int) ($r?->active_count ?? 0);
            $inact = (int) ($r?->inactive_count ?? 0);
            $newCnt = (int) ($newRaw[$key] ?? 0);

            $totalRegistered += $tot;
            $activePatients += $act;
            $inactivePatients += $inact;
            $newRegistrations += $newCnt;

            $countsByGroup[$key] = [
                'classification' => $label,
                'total' => $tot,
                'active' => $act,
                'inactive' => $inact,
                'new_registrations' => $newCnt,
            ];
        }

        $denom = max(1, $totalRegistered);
        $formattedRows = [];
        foreach ($countsByGroup as $item) {
            $item['percentage'] = $totalRegistered > 0 ? round(($item['total'] / $denom) * 100, 1) : 0.0;
            $formattedRows[] = $item;
        }

        return [
            'total_registered' => $totalRegistered,
            'total_students' => $countsByGroup['students']['total'],
            'total_faculty' => $countsByGroup['faculty']['total'],
            'total_non_teaching' => $countsByGroup['non_teaching']['total'],
            'total_other_personnel' => $countsByGroup['other_personnel']['total'],
            'active_patients' => $activePatients,
            'inactive_patients' => $inactivePatients,
            'new_registrations' => $newRegistrations,
            'rows' => $formattedRows,
        ];
    }

    public function aggregateMedicalAndDentalRecords(int $year, array $filters = []): array
    {
        $months = $this->monthLabels();
        $emptyMonths = array_fill(1, 12, 0);

        $serviceDefinitions = [
            ClinicVisit::TYPE_MEDICAL => ['label' => 'MEDICAL CONSULTATION', 'color' => 'blue'],
            ClinicVisit::TYPE_DENTAL => ['label' => 'DENTAL CONSULTATION', 'color' => 'peach'],
            ClinicVisit::TYPE_EMERGENCY => ['label' => 'EMERGENCY', 'color' => 'slate'],
            ClinicVisit::TYPE_FIRST_AID => ['label' => 'FIRST AIDE', 'color' => 'rose'],
            ClinicVisit::TYPE_GENERAL => ['label' => 'GENERAL CONSULTATION', 'color' => 'blue'],
        ];

        $visitQuery = ClinicVisit::query()
            ->leftJoin('patients', 'patients.id', '=', 'clinic_visits.patient_id')
            ->whereYear('clinic_visits.visit_date', $year);

        if (! empty($filters['academic_year_id'])) {
            $visitQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $visitQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $visitQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $visitMonthSql = $this->monthSqlExpression('clinic_visits.visit_date');
        $visitRows = $visitQuery
            ->selectRaw("
                clinic_visits.visit_type,
                CASE
                    WHEN clinic_visits.patient_classification IN ('faculty', 'staff', 'employee') OR patients.type = 'employee' THEN 'Employee'
                    WHEN clinic_visits.patient_classification = 'student' OR patients.type = 'student' THEN 'Student'
                    ELSE COALESCE(NULLIF(clinic_visits.patient_classification, ''), 'Student')
                END as client_label,
                {$visitMonthSql} as month_num,
                COUNT(*) as total
            ")
            ->groupBy('clinic_visits.visit_type', 'client_label', 'month_num')
            ->get();

        $visitMatrix = [];
        foreach ([ClinicVisit::TYPE_MEDICAL, ClinicVisit::TYPE_DENTAL, ClinicVisit::TYPE_EMERGENCY, ClinicVisit::TYPE_FIRST_AID] as $coreType) {
            $visitMatrix[$coreType] = [
                'Employee' => $emptyMonths,
                'Student' => $emptyMonths,
            ];
        }

        foreach ($visitRows as $r) {
            $vType = (string) $r->visit_type;
            $clientLabel = ucfirst(trim((string) $r->client_label));
            $m = (int) $r->month_num;
            if ($m < 1 || $m > 12) {
                continue;
            }
            if (! isset($visitMatrix[$vType])) {
                $visitMatrix[$vType] = [
                    'Employee' => $emptyMonths,
                    'Student' => $emptyMonths,
                ];
            }
            if (! isset($visitMatrix[$vType][$clientLabel])) {
                $visitMatrix[$vType][$clientLabel] = $emptyMonths;
            }
            $visitMatrix[$vType][$clientLabel][$m] += (int) $r->total;
        }

        $dbPrograms = Program::query()
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'degree_level', 'is_active']);

        $peCategories = ['Employee' => $emptyMonths];
        $programCodeToPeLabel = [];

        foreach ($dbPrograms as $prog) {
            $code = strtoupper(trim((string) $prog->code));
            $label = $this->formatPeProgramClientLabel($code, (string) $prog->degree_level);
            $programCodeToPeLabel[$code] = $label;
            if ($prog->is_active && ! isset($peCategories[$label])) {
                $peCategories[$label] = $emptyMonths;
            }
        }
        $peCategories['Athletes'] = $emptyMonths;

        $peQuery = PhysicalExamination::query()
            ->join('patients', 'patients.id', '=', 'physical_examinations.patient_id')
            ->leftJoin('programs', 'programs.id', '=', 'patients.program_id')
            ->whereYear('physical_examinations.exam_date', $year);

        if (! empty($filters['academic_year_id'])) {
            $peQuery->where('physical_examinations.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $peQuery->where('patients.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            if ($filters['patient_classification'] === 'student') {
                $peQuery->where('patients.type', 'student');
            } else {
                $peQuery->where('patients.type', 'employee');
            }
        }

        $peMonthSql = $this->monthSqlExpression('physical_examinations.exam_date');
        $peRows = $peQuery
            ->selectRaw("
                patients.type as patient_type,
                UPPER(COALESCE(programs.code, '')) as resolved_program_code,
                COALESCE(programs.degree_level, '') as resolved_degree_level,
                CASE WHEN LOWER(COALESCE(physical_examinations.purpose, '')) LIKE '%athlet%'
                       OR LOWER(COALESCE(physical_examinations.purpose, '')) LIKE '%sport%'
                       OR LOWER(COALESCE(physical_examinations.purpose, '')) LIKE '%varsity%'
                     THEN 1 ELSE 0 END as is_athlete,
                {$peMonthSql} as month_num,
                COUNT(*) as total
            ")
            ->groupBy('patient_type', 'resolved_program_code', 'resolved_degree_level', 'is_athlete', 'month_num')
            ->get();

        foreach ($peRows as $r) {
            $m = (int) $r->month_num;
            if ($m < 1 || $m > 12) {
                continue;
            }
            $pType = (string) $r->patient_type;
            $pCode = strtoupper(trim((string) $r->resolved_program_code));
            $isAthlete = (int) $r->is_athlete === 1;

            if ($pType === 'employee') {
                $cat = 'Employee';
            } elseif ($isAthlete) {
                $cat = 'Athletes';
            } elseif ($pCode !== '') {
                $cat = $programCodeToPeLabel[$pCode] ?? $this->formatPeProgramClientLabel($pCode, (string) $r->resolved_degree_level);
            } else {
                $cat = 'Unassigned Students';
            }

            if (! isset($peCategories[$cat])) {
                $peCategories[$cat] = $emptyMonths;
            }
            $peCategories[$cat][$m] += (int) $r->total;
        }

        if (isset($peCategories['Athletes'])) {
            $athletesMonths = $peCategories['Athletes'];
            unset($peCategories['Athletes']);
            $peCategories['Athletes'] = $athletesMonths;
        }

        $formatRows = function (array $catMap): array {
            $out = [];
            foreach ($catMap as $label => $mCounts) {
                $out[] = [
                    'label' => $label,
                    'months' => $mCounts,
                    'total' => array_sum($mCounts),
                ];
            }

            return $out;
        };

        $groups = [
            [
                'group' => $serviceDefinitions[ClinicVisit::TYPE_MEDICAL]['label'],
                'color' => $serviceDefinitions[ClinicVisit::TYPE_MEDICAL]['color'],
                'rows' => $formatRows($visitMatrix[ClinicVisit::TYPE_MEDICAL]),
            ],
            [
                'group' => 'SCHEDULED PHYSICAL EXAMINATION',
                'color' => 'pink',
                'rows' => $formatRows($peCategories),
            ],
            [
                'group' => $serviceDefinitions[ClinicVisit::TYPE_DENTAL]['label'],
                'color' => $serviceDefinitions[ClinicVisit::TYPE_DENTAL]['color'],
                'rows' => $formatRows($visitMatrix[ClinicVisit::TYPE_DENTAL]),
            ],
            [
                'group' => $serviceDefinitions[ClinicVisit::TYPE_EMERGENCY]['label'],
                'color' => $serviceDefinitions[ClinicVisit::TYPE_EMERGENCY]['color'],
                'rows' => $formatRows($visitMatrix[ClinicVisit::TYPE_EMERGENCY]),
            ],
            [
                'group' => $serviceDefinitions[ClinicVisit::TYPE_FIRST_AID]['label'],
                'color' => $serviceDefinitions[ClinicVisit::TYPE_FIRST_AID]['color'],
                'rows' => $formatRows($visitMatrix[ClinicVisit::TYPE_FIRST_AID]),
            ],
        ];

        foreach ($visitMatrix as $vType => $clientMap) {
            if (in_array($vType, [ClinicVisit::TYPE_MEDICAL, ClinicVisit::TYPE_DENTAL, ClinicVisit::TYPE_EMERGENCY, ClinicVisit::TYPE_FIRST_AID], true)) {
                continue;
            }
            $def = $serviceDefinitions[$vType] ?? [
                'label' => strtoupper(str_replace('_', ' ', $vType)),
                'color' => 'blue',
            ];
            $rows = $formatRows($clientMap);
            $sum = array_sum(array_column($rows, 'total'));
            if ($sum > 0) {
                $groups[] = [
                    'group' => $def['label'],
                    'color' => $def['color'],
                    'rows' => $rows,
                ];
            }
        }

        $monthlyTotals = $emptyMonths;
        $monthlyStudentTotals = $emptyMonths;
        $monthlyEmployeeTotals = $emptyMonths;
        $serviceSummaries = [];
        $totalStudentAll = 0;
        $totalEmployeeAll = 0;

        foreach ($groups as $g) {
            $grpMonths = $emptyMonths;
            $grpStudent = 0;
            $grpEmployee = 0;

            foreach ($g['rows'] as $row) {
                $isEmpRow = strcasecmp(trim((string) $row['label']), 'Employee') === 0;
                $rowSum = (int) $row['total'];
                if ($isEmpRow) {
                    $grpEmployee += $rowSum;
                    $totalEmployeeAll += $rowSum;
                } else {
                    $grpStudent += $rowSum;
                    $totalStudentAll += $rowSum;
                }

                for ($m = 1; $m <= 12; $m++) {
                    $val = (int) ($row['months'][$m] ?? 0);
                    $grpMonths[$m] += $val;
                    $monthlyTotals[$m] += $val;
                    if ($isEmpRow) {
                        $monthlyEmployeeTotals[$m] += $val;
                    } else {
                        $monthlyStudentTotals[$m] += $val;
                    }
                }
            }

            $grpTotal = $grpStudent + $grpEmployee;
            $serviceSummaries[] = [
                'service' => $g['group'],
                'color' => $g['color'],
                'months' => $grpMonths,
                'student_total' => $grpStudent,
                'employee_total' => $grpEmployee,
                'annual_total' => $grpTotal,
                'student_percentage' => $grpTotal > 0 ? round(($grpStudent / $grpTotal) * 100, 1) : 0.0,
                'employee_percentage' => $grpTotal > 0 ? round(($grpEmployee / $grpTotal) * 100, 1) : 0.0,
            ];
        }

        $grandTotal = array_sum($monthlyTotals);
        $denomGrand = max(1, $grandTotal);

        foreach ($serviceSummaries as &$sItem) {
            $sItem['share_percentage'] = $grandTotal > 0 ? round(($sItem['annual_total'] / $denomGrand) * 100, 1) : 0.0;
        }
        unset($sItem);

        $summaryByGroup = collect($serviceSummaries)->keyBy('service');
        $monthlyComparison = [];
        $peakMonthName = 'N/A';
        $peakMonthTotal = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mTot = $monthlyTotals[$m];
            if ($mTot > $peakMonthTotal) {
                $peakMonthTotal = $mTot;
                $peakMonthName = $months[$m];
            }

            $monthlyComparison[$m] = [
                'month_num' => $m,
                'month_name' => $months[$m],
                'medical' => (int) ($summaryByGroup->get('MEDICAL CONSULTATION')['months'][$m] ?? 0),
                'dental' => (int) ($summaryByGroup->get('DENTAL CONSULTATION')['months'][$m] ?? 0),
                'emergency' => (int) ($summaryByGroup->get('EMERGENCY')['months'][$m] ?? 0),
                'first_aid' => (int) ($summaryByGroup->get('FIRST AIDE')['months'][$m] ?? 0),
                'scheduled_pe' => (int) ($summaryByGroup->get('SCHEDULED PHYSICAL EXAMINATION')['months'][$m] ?? 0),
                'student_total' => $monthlyStudentTotals[$m],
                'employee_total' => $monthlyEmployeeTotals[$m],
                'total' => $mTot,
                'percentage' => $grandTotal > 0 ? round(($mTot / $denomGrand) * 100, 1) : 0.0,
            ];
        }

        return [
            'year' => $year,
            'months' => $months,
            'groups' => $groups,
            'monthly_totals' => $monthlyTotals,
            'grand_total' => $grandTotal,
            'service_summaries' => $serviceSummaries,
            'monthly_comparison' => $monthlyComparison,
            'peak_month' => [
                'name' => $peakMonthName,
                'total' => $peakMonthTotal,
            ],
            'client_distribution' => [
                'student_total' => $totalStudentAll,
                'employee_total' => $totalEmployeeAll,
                'student_percentage' => $grandTotal > 0 ? round(($totalStudentAll / $denomGrand) * 100, 1) : 0.0,
                'employee_percentage' => $grandTotal > 0 ? round(($totalEmployeeAll / $denomGrand) * 100, 1) : 0.0,
            ],
        ];
    }

    public function aggregateMedicalAndDentalLoginRecords(int $year, array $filters = []): array
    {
        $months = $this->monthLabels();
        $emptyMonths = array_fill(1, 12, 0);

        $dbPrograms = Program::query()
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'degree_level', 'is_active']);

        $categories = ['Employee' => $emptyMonths];
        $programCodeToLabel = [];

        foreach ($dbPrograms as $prog) {
            $code = strtoupper(trim((string) $prog->code));
            $label = $this->formatLoginProgramClientLabel($code, (string) $prog->degree_level);
            $programCodeToLabel[$code] = $label;
            if ($prog->is_active && ! isset($categories[$label])) {
                $categories[$label] = $emptyMonths;
            }
        }

        $loginQuery = ClinicVisit::query()
            ->leftJoin('patients', 'patients.id', '=', 'clinic_visits.patient_id')
            ->leftJoin('programs', 'programs.id', '=', 'clinic_visits.program_id')
            ->leftJoin('programs as patient_programs', 'patient_programs.id', '=', 'patients.program_id')
            ->whereYear('clinic_visits.visit_date', $year);

        if (! empty($filters['academic_year_id'])) {
            $loginQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $loginQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $loginQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $loginMonthSql = $this->monthSqlExpression('clinic_visits.visit_date');
        $rows = $loginQuery
            ->selectRaw("
                clinic_visits.patient_classification,
                COALESCE(patients.type, 'student') as patient_type,
                UPPER(COALESCE(programs.code, patient_programs.code, '')) as resolved_program_code,
                COALESCE(programs.degree_level, patient_programs.degree_level, '') as resolved_degree_level,
                {$loginMonthSql} as month_num,
                COUNT(*) as total
            ")
            ->groupBy('clinic_visits.patient_classification', 'patient_type', 'resolved_program_code', 'resolved_degree_level', 'month_num')
            ->get();

        foreach ($rows as $r) {
            $m = (int) $r->month_num;
            if ($m < 1 || $m > 12) {
                continue;
            }
            $isEmployee = in_array((string) $r->patient_classification, ['faculty', 'staff', 'employee'], true)
                || (string) $r->patient_type === 'employee';
            $pCode = strtoupper(trim((string) $r->resolved_program_code));

            if ($isEmployee) {
                $cat = 'Employee';
            } elseif ($pCode !== '') {
                $cat = $programCodeToLabel[$pCode] ?? $this->formatLoginProgramClientLabel($pCode, (string) $r->resolved_degree_level);
            } else {
                $cat = 'Unassigned Students';
            }

            if (! isset($categories[$cat])) {
                $categories[$cat] = $emptyMonths;
            }
            $categories[$cat][$m] += (int) $r->total;
        }

        if (Schema::hasTable('clinic_logbooks')) {
            $logbookQuery = ClinicLogbook::query()
                ->leftJoin('patients', 'patients.id', '=', 'clinic_logbooks.patient_id')
                ->leftJoin('programs', 'programs.id', '=', 'clinic_logbooks.program_id')
                ->leftJoin('programs as patient_programs', 'patient_programs.id', '=', 'patients.program_id')
                ->whereNull('clinic_logbooks.clinic_visit_id')
                ->whereYear('clinic_logbooks.log_date', $year);

            if (! empty($filters['academic_year_id'])) {
                $logbookQuery->where('clinic_logbooks.academic_year_id', $filters['academic_year_id']);
            }
            if (! empty($filters['college_id'])) {
                $logbookQuery->where('clinic_logbooks.college_id', $filters['college_id']);
            }
            if (! empty($filters['patient_classification'])) {
                $logbookQuery->where('clinic_logbooks.visitor_type', $filters['patient_classification']);
            }

            $logbookMonthSql = $this->monthSqlExpression('clinic_logbooks.log_date');
            $logbookRows = $logbookQuery
                ->selectRaw("
                    clinic_logbooks.visitor_type,
                    COALESCE(patients.type, 'student') as patient_type,
                    UPPER(COALESCE(programs.code, patient_programs.code, clinic_logbooks.course_or_department, '')) as resolved_program_code,
                    COALESCE(programs.degree_level, patient_programs.degree_level, '') as resolved_degree_level,
                    {$logbookMonthSql} as month_num,
                    COUNT(*) as total
                ")
                ->groupBy('clinic_logbooks.visitor_type', 'patient_type', 'resolved_program_code', 'resolved_degree_level', 'month_num')
                ->get();

            foreach ($logbookRows as $lr) {
                $m = (int) $lr->month_num;
                if ($m < 1 || $m > 12) {
                    continue;
                }
                $isEmployee = in_array((string) $lr->visitor_type, ['faculty', 'staff', 'employee'], true)
                    || (string) $lr->patient_type === 'employee';
                $pCode = strtoupper(trim((string) $lr->resolved_program_code));

                if ($isEmployee) {
                    $cat = 'Employee';
                } elseif (isset($programCodeToLabel[$pCode])) {
                    $cat = $programCodeToLabel[$pCode];
                } elseif ($pCode !== '') {
                    $cat = $this->formatLoginProgramClientLabel($pCode, (string) $lr->resolved_degree_level);
                } else {
                    $cat = 'Unassigned Students';
                }

                if (! isset($categories[$cat])) {
                    $categories[$cat] = $emptyMonths;
                }
                $categories[$cat][$m] += (int) $lr->total;
            }
        }

        $formattedRows = [];
        $monthlyTotals = $emptyMonths;
        foreach ($categories as $label => $mCounts) {
            $rowTotal = array_sum($mCounts);
            $formattedRows[] = [
                'label' => $label,
                'months' => $mCounts,
                'total' => $rowTotal,
            ];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyTotals[$m] += $mCounts[$m];
            }
        }

        return [
            'year' => $year,
            'months' => $months,
            'rows' => $formattedRows,
            'monthly_totals' => $monthlyTotals,
            'grand_total' => array_sum($monthlyTotals),
        ];
    }

    protected function monthSqlExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    public function monthLabels(): array
    {
        return [
            1 => 'JANUARY',
            2 => 'FEBRUARY',
            3 => 'MARCH',
            4 => 'APRIL',
            5 => 'MAY',
            6 => 'JUNE',
            7 => 'JULY',
            8 => 'AUGUST',
            9 => 'SEPTEMBER',
            10 => 'OCTOBER',
            11 => 'NOVEMBER',
            12 => 'DECEMBER',
        ];
    }

    protected function formatPeProgramClientLabel(string $programCode, string $degreeLevel = ''): string
    {
        $code = strtoupper(trim($programCode));

        return match (true) {
            $code === 'BSCRIM' => 'CRIM Students',
            $code === 'BACOMM' => 'BACOMM',
            in_array($code, ['BSHM', 'BSTM'], true) => 'BSHM/ TM Students',
            str_starts_with($code, 'BSED') || $code === 'BEED' => 'Education Students',
            $code === 'BSFS' => 'Forensic Students',
            $code === 'ELEM' => 'ELEMENTARY',
            in_array($code, ['SHS', 'JHS'], true) || strtolower($degreeLevel) === 'basic education' => $code,
            default => "{$code} Students",
        };
    }

    protected function formatLoginProgramClientLabel(string $programCode, string $degreeLevel = ''): string
    {
        $code = strtoupper(trim($programCode));

        return match (true) {
            $code === 'BSCRIM' => 'CRIM Students',
            $code === 'BACOMM' => 'BACOMM',
            str_starts_with($code, 'BSED') || $code === 'BEED' => 'Education Students',
            $code === 'BSFS' => 'Forensic Students',
            $code === 'ELEM' => 'ELEMENTARY',
            in_array($code, ['SHS', 'JHS'], true) || strtolower($degreeLevel) === 'basic education' => $code,
            default => "{$code} Students",
        };
    }

    protected function buildFilteredVisitQuery(Carbon $dateFrom, Carbon $dateTo, array $filters): Builder
    {
        $visitQuery = ClinicVisit::query()->whereBetween('clinic_visits.visit_date', [$dateFrom, $dateTo]);

        if (! empty($filters['academic_year_id'])) {
            $visitQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }

        if (! empty($filters['college_id'])) {
            $visitQuery->where('clinic_visits.college_id', $filters['college_id']);
        }

        if (! empty($filters['patient_classification'])) {
            $visitQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        return $visitQuery;
    }

    protected function aggregateMorbidityData(Carbon $dateFrom, Carbon $dateTo, array $filters): array
    {
        $communicableTally = [];
        $nonCommunicableTally = [];
        $dentalTally = [];

        $medicalQuery = MedicalConsultation::query()
            ->join('clinic_visits', 'clinic_visits.id', '=', 'medical_consultations.clinic_visit_id')
            ->whereBetween('clinic_visits.visit_date', [$dateFrom, $dateTo])
            ->whereNotNull('medical_consultations.diagnosis')
            ->where('medical_consultations.diagnosis', '!=', '');

        if (! empty($filters['academic_year_id'])) {
            $medicalQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $medicalQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $medicalQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $medicalGroups = $medicalQuery
            ->select('medical_consultations.diagnosis', DB::raw('COUNT(*) as total'))
            ->groupBy('medical_consultations.diagnosis')
            ->get();

        foreach ($medicalGroups as $row) {
            $diag = trim((string) $row->diagnosis);
            if ($diag === '') {
                continue;
            }
            $count = (int) $row->total;
            $lower = strtolower($diag);

            $isComm = false;
            foreach (self::COMMUNICABLE_KEYWORDS as $k) {
                if (str_contains($lower, $k)) {
                    $isComm = true;
                    $label = ucwords($k);
                    $communicableTally[$label] = ($communicableTally[$label] ?? 0) + $count;
                    break;
                }
            }

            if (! $isComm) {
                $isNonComm = false;
                foreach (self::NON_COMMUNICABLE_KEYWORDS as $k) {
                    if (str_contains($lower, $k)) {
                        $isNonComm = true;
                        $label = ucwords($k);
                        $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + $count;
                        break;
                    }
                }

                if (! $isNonComm) {
                    $label = mb_strimwidth($diag, 0, 30, '...');
                    $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + $count;
                }
            }
        }

        $dentalQuery = DentalConsultation::query()
            ->join('clinic_visits', 'clinic_visits.id', '=', 'dental_consultations.clinic_visit_id')
            ->whereBetween('clinic_visits.visit_date', [$dateFrom, $dateTo])
            ->whereNotNull('dental_consultations.diagnosis')
            ->where('dental_consultations.diagnosis', '!=', '');

        if (! empty($filters['academic_year_id'])) {
            $dentalQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $dentalQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $dentalQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $dentalGroups = $dentalQuery
            ->select('dental_consultations.diagnosis', DB::raw('COUNT(*) as total'))
            ->groupBy('dental_consultations.diagnosis')
            ->get();

        foreach ($dentalGroups as $row) {
            $diag = trim((string) $row->diagnosis);
            if ($diag === '') {
                continue;
            }
            $label = mb_strimwidth($diag, 0, 35, '...');
            $dentalTally[$label] = ($dentalTally[$label] ?? 0) + (int) $row->total;
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

    public function communicableCategoryDefinitions(): array
    {
        $rows = DiseaseCategory::query()
            ->active()
            ->communicable()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $defs = [];
        foreach ($rows as $row) {
            $defs[] = [
                'id' => $row->id,
                'key' => $row->code,
                'label' => $row->name,
                'keywords' => $row->keywordList(),
            ];
        }

        return $defs;
    }

    public function nonCommunicableCategoryDefinitions(): array
    {
        $rows = DiseaseCategory::query()
            ->active()
            ->nonCommunicable()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $defs = [];
        foreach ($rows as $row) {
            $defs[] = [
                'id' => $row->id,
                'key' => $row->code,
                'label' => $row->name,
                'keywords' => $row->keywordList(),
            ];
        }

        return $defs;
    }

    public function aggregateCommunicableDiseaseReport(int $year, array $filters = []): array
    {
        $defs = $this->communicableCategoryDefinitions();
        $counts = [];
        $idToKey = [];
        foreach ($defs as $d) {
            $counts[$d['key']] = 0;
            $idToKey[$d['id']] = $d['key'];
        }

        $records = $this->fetchClinicalTextRecordsForYear($year, $filters);

        foreach ($records as $rec) {
            $catId = $rec['disease_category_id'];
            if ($catId !== null && isset($idToKey[$catId])) {
                $counts[$idToKey[$catId]] += $rec['total'];

                continue;
            }

            if ($catId !== null) {
                continue;
            }

            $text = strtolower($rec['diagnosis'].' '.$rec['complaint']);
            foreach ($defs as $d) {
                foreach ($d['keywords'] as $kw) {
                    if ($kw !== '' && str_contains($text, $kw)) {
                        $counts[$d['key']] += $rec['total'];
                        break 2;
                    }
                }
            }
        }

        $items = [];
        $maxVal = 0;
        foreach ($defs as $d) {
            $c = $counts[$d['key']];
            if ($c > $maxVal) {
                $maxVal = $c;
            }
            $items[] = [
                'key' => $d['key'],
                'label' => $d['label'],
                'count' => $c,
            ];
        }

        $maxScale = max(10, (int) ceil(($maxVal + 2) / 5) * 5);

        return [
            'year' => $year,
            'title' => "COMMUNICABLE DISEASES {$year} DATA",
            'items' => $items,
            'total' => array_sum($counts),
            'max_scale' => $maxScale,
        ];
    }

    public function aggregateNonCommunicableDiseaseReport(int $year, array $filters = []): array
    {
        $defs = $this->nonCommunicableCategoryDefinitions();
        $commDefs = $this->communicableCategoryDefinitions();
        $counts = [];
        $idToKey = [];
        foreach ($defs as $d) {
            $counts[$d['key']] = 0;
            $idToKey[$d['id']] = $d['key'];
        }

        $records = $this->fetchClinicalTextRecordsForYear($year, $filters);

        foreach ($records as $rec) {
            $catId = $rec['disease_category_id'];
            if ($catId !== null && isset($idToKey[$catId])) {
                $counts[$idToKey[$catId]] += $rec['total'];

                continue;
            }

            if ($catId !== null) {
                continue;
            }

            $text = strtolower($rec['diagnosis'].' '.$rec['complaint']);

            $matchedNonComm = false;
            foreach ($defs as $d) {
                foreach ($d['keywords'] as $kw) {
                    if ($kw !== '' && str_contains($text, $kw)) {
                        $counts[$d['key']] += $rec['total'];
                        $matchedNonComm = true;
                        break 2;
                    }
                }
            }

            if ($matchedNonComm) {
                continue;
            }

            $isComm = false;
            foreach ($commDefs as $cd) {
                foreach ($cd['keywords'] as $kw) {
                    if ($kw !== '' && str_contains($text, $kw)) {
                        $isComm = true;
                        break 2;
                    }
                }
            }

            if (! $isComm && trim($text) !== '') {
                $unclassifiedLabel = ucwords(mb_strimwidth(trim($rec['diagnosis'] ?: $rec['complaint']), 0, 42, '...'));
                $unclassifiedKey = 'DYNAMIC-'.md5($unclassifiedLabel);
                if (! isset($counts[$unclassifiedKey])) {
                    $counts[$unclassifiedKey] = 0;
                    $defs[] = [
                        'id' => 0,
                        'key' => $unclassifiedKey,
                        'label' => $unclassifiedLabel,
                        'keywords' => [],
                    ];
                }
                $counts[$unclassifiedKey] += $rec['total'];
            }
        }

        $items = [];
        $maxVal = 0;
        foreach ($defs as $d) {
            $c = $counts[$d['key']];
            if ($c > $maxVal) {
                $maxVal = $c;
            }
            $items[] = [
                'key' => $d['key'],
                'label' => $d['label'],
                'count' => $c,
            ];
        }

        $maxScale = max(10, (int) ceil(($maxVal + 1) / 2) * 2);

        return [
            'year' => $year,
            'title' => "NON- COMMUNICABLE DISEASES {$year} DATA",
            'items' => $items,
            'total' => array_sum($counts),
            'max_scale' => $maxScale,
        ];
    }

    protected function fetchClinicalTextRecordsForYear(int $year, array $filters = []): array
    {
        $visitQuery = ClinicVisit::query()
            ->leftJoin('medical_consultations', 'medical_consultations.clinic_visit_id', '=', 'clinic_visits.id')
            ->leftJoin('emergency_records', 'emergency_records.clinic_visit_id', '=', 'clinic_visits.id')
            ->leftJoin('first_aid_records', 'first_aid_records.clinic_visit_id', '=', 'clinic_visits.id')
            ->whereYear('clinic_visits.visit_date', $year)
            ->whereIn('clinic_visits.visit_type', [
                ClinicVisit::TYPE_MEDICAL,
                ClinicVisit::TYPE_EMERGENCY,
                ClinicVisit::TYPE_FIRST_AID,
                ClinicVisit::TYPE_GENERAL,
            ]);

        if (! empty($filters['academic_year_id'])) {
            $visitQuery->where('clinic_visits.academic_year_id', $filters['academic_year_id']);
        }
        if (! empty($filters['college_id'])) {
            $visitQuery->where('clinic_visits.college_id', $filters['college_id']);
        }
        if (! empty($filters['patient_classification'])) {
            $visitQuery->where('clinic_visits.patient_classification', $filters['patient_classification']);
        }

        $rows = $visitQuery
            ->selectRaw("
                medical_consultations.disease_category_id as resolved_category_id,
                COALESCE(medical_consultations.diagnosis, emergency_records.emergency_nature, first_aid_records.injury_type, '') as diag_text,
                COALESCE(clinic_visits.chief_complaint, '') as comp_text,
                COUNT(*) as total
            ")
            ->groupBy('resolved_category_id', 'diag_text', 'comp_text')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'disease_category_id' => $r->resolved_category_id !== null ? (int) $r->resolved_category_id : null,
                'diagnosis' => (string) $r->diag_text,
                'complaint' => (string) $r->comp_text,
                'total' => (int) $r->total,
            ];
        }

        return $out;
    }

    protected function aggregateChiefComplaints(Builder $visitQuery): array
    {
        $complaintGroups = (clone $visitQuery)
            ->whereNotNull('clinic_visits.chief_complaint')
            ->where('clinic_visits.chief_complaint', '!=', '')
            ->select('clinic_visits.chief_complaint', DB::raw('COUNT(*) as total'))
            ->groupBy('clinic_visits.chief_complaint')
            ->get();

        $tally = [];
        foreach ($complaintGroups as $row) {
            $complaint = trim((string) $row->chief_complaint);
            if ($complaint !== '') {
                $cat = $this->categorizeComplaint($complaint);
                $tally[$cat] = ($tally[$cat] ?? 0) + (int) $row->total;
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

    protected function aggregateMedicineDispensing(Carbon $dateFrom, Carbon $dateTo, array $filters): array
    {
        $dispQuery = MedicineDispensing::query()->whereBetween('medicine_dispensings.dispensed_date', [$dateFrom, $dateTo]);

        if (! empty($filters['patient_classification'])) {
            $dispQuery->whereHas('patient', function ($q) use ($filters) {
                $q->where('type', $filters['patient_classification']);
            });
        }

        $totalDispensings = (clone $dispQuery)->count();

        $itemsQuery = MedicineDispensingItem::query()
            ->join('medicine_dispensings', 'medicine_dispensings.id', '=', 'medicine_dispensing_items.dispensing_id')
            ->whereBetween('medicine_dispensings.dispensed_date', [$dateFrom, $dateTo]);

        if (! empty($filters['patient_classification'])) {
            $itemsQuery->join('patients', 'patients.id', '=', 'medicine_dispensings.patient_id')
                ->where('patients.type', $filters['patient_classification']);
        }

        $totalUnits = (int) (clone $itemsQuery)->sum('medicine_dispensing_items.quantity');

        $items = (clone $itemsQuery)
            ->select('medicine_dispensing_items.medicine_id', DB::raw('SUM(medicine_dispensing_items.quantity) as total_qty'))
            ->groupBy('medicine_dispensing_items.medicine_id')
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
