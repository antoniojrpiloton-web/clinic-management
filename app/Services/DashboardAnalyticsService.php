<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\DentalConsultation;
use App\Models\EmergencyRecord;
use App\Models\FirstAidRecord;
use App\Models\InventoryTransaction;
use App\Models\MedicalConsultation;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
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

    public function stats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $visitsByType = ClinicVisit::query()
            ->selectRaw('visit_type, COUNT(*) as aggregate')
            ->groupBy('visit_type')
            ->pluck('aggregate', 'visit_type');

        $visitsByClass = ClinicVisit::query()
            ->selectRaw('patient_classification, COUNT(*) as aggregate')
            ->groupBy('patient_classification')
            ->pluck('aggregate', 'patient_classification');

        $totalVisits = (int) $visitsByType->sum();
        $todayVisits = ClinicVisit::whereDate('visit_date', $today)->count();
        $thisMonthVisits = ClinicVisit::where('visit_date', '>=', $thisMonth)->count();
        $lastMonthVisits = ClinicVisit::whereBetween('visit_date', [$lastMonthStart, $lastMonthEnd])->count();

        $studentVisits = (int) ($visitsByClass['student'] ?? 0);
        $employeeVisits = (int) ($visitsByClass['faculty'] ?? 0)
            + (int) ($visitsByClass['staff'] ?? 0)
            + (int) ($visitsByClass['employee'] ?? 0);

        $medicalVisits = (int) ($visitsByType[ClinicVisit::TYPE_MEDICAL] ?? 0);
        $dentalVisits = (int) ($visitsByType[ClinicVisit::TYPE_DENTAL] ?? 0);
        $emergencyVisits = (int) ($visitsByType[ClinicVisit::TYPE_EMERGENCY] ?? 0);
        $firstAidVisits = (int) ($visitsByType[ClinicVisit::TYPE_FIRST_AID] ?? 0);

        $patientsByType = Patient::query()
            ->selectRaw('type, COUNT(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $totalPatients = (int) $patientsByType->sum();
        $activePatients = Patient::where('status', 'active')->count();
        $studentsCount = (int) ($patientsByType['student'] ?? 0);
        $employeesCount = (int) ($patientsByType['employee'] ?? 0);

        $totalPE = PhysicalExamination::count();
        $completedPE = PhysicalExamination::where('status', 'completed')->count();

        $totalMedicines = Medicine::where('is_active', true)->count();
        $totalDispensedUnits = (int) MedicineDispensingItem::sum('quantity');
        $totalDispensingOrders = MedicineDispensing::count();

        $lowStockCount = Medicine::where('is_active', true)
            ->with(['batches' => fn ($q) => $q->where('expiration_date', '>', now())])
            ->get()
            ->filter(fn (Medicine $med): bool => $med->stock_on_hand <= $med->reorder_level)
            ->count();

        $expiringIn30Days = MedicineBatch::where('quantity_remaining', '>', 0)
            ->whereBetween('expiration_date', [now(), now()->addDays(30)])
            ->count();

        $expiredBatches = MedicineBatch::where('quantity_remaining', '>', 0)
            ->where('expiration_date', '<', now())
            ->count();

        $totalClinicians = Clinician::where('is_active', true)->count();

        $growth = $lastMonthVisits > 0
            ? round((($thisMonthVisits - $lastMonthVisits) / $lastMonthVisits) * 100, 1)
            : ($thisMonthVisits > 0 ? 100.0 : 0.0);

        return [
            'total_visits' => $totalVisits,
            'today_visits' => $todayVisits,
            'this_month_visits' => $thisMonthVisits,
            'last_month_visits' => $lastMonthVisits,
            'visits_growth' => ($growth >= 0 ? "+{$growth}%" : "{$growth}%"),
            'student_visits' => $studentVisits,
            'employee_visits' => $employeeVisits,
            'medical_visits' => $medicalVisits,
            'dental_visits' => $dentalVisits,
            'emergency_visits' => $emergencyVisits,
            'first_aid_visits' => $firstAidVisits,
            'total_patients' => $totalPatients,
            'active_patients' => $activePatients,
            'students_count' => $studentsCount,
            'employees_count' => $employeesCount,
            'total_pe' => $totalPE,
            'completed_pe' => $completedPE,
            'total_medicines' => $totalMedicines,
            'medicines_dispensed' => $totalDispensedUnits,
            'dispensing_orders' => $totalDispensingOrders,
            'low_stock_count' => $lowStockCount,
            'expiring_count' => $expiringIn30Days,
            'expired_count' => $expiredBatches,
            'total_clinicians' => $totalClinicians,
            'medical_consultations_count' => MedicalConsultation::count(),
            'dental_consultations_count' => DentalConsultation::count(),
            'emergency_records_count' => EmergencyRecord::count(),
            'first_aid_records_count' => FirstAidRecord::count(),
            'pe_schedules_count' => PhysicalExamSchedule::count(),
            'inventory_transactions_count' => InventoryTransaction::count(),
            'colleges_count' => College::count(),
            'programs_count' => Program::count(),
            'users_count' => User::count(),
            'audit_logs_count' => AuditLog::count(),
        ];
    }

    public function charts(): array
    {
        return [
            'monthly_utilization' => $this->monthlyClinicUtilization(),
            'classification_breakdown' => $this->studentVsEmployeeBreakdown(),
            'services_breakdown' => $this->servicesBreakdown(),
            'top_complaints' => $this->topComplaints(7),
            'disease_classification' => $this->diseaseClassification(),
            'top_medicines' => $this->topMedicineUtilization(7),
            'recent_visits_trend' => $this->dailyTrend(14),
            'pe_fitness_breakdown' => $this->peFitnessBreakdown(),
            'college_utilization' => $this->collegeUtilizationBreakdown(),
        ];
    }

    public function monthlyClinicUtilization(): array
    {
        $months = [];
        $totals = [];
        $medical = [];
        $dental = [];
        $emergency = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            $months[] = $start->format('M Y');

            $byType = ClinicVisit::query()
                ->whereBetween('visit_date', [$start, $end])
                ->selectRaw('visit_type, COUNT(*) as aggregate')
                ->groupBy('visit_type')
                ->pluck('aggregate', 'visit_type');

            $medCount = (int) ($byType[ClinicVisit::TYPE_MEDICAL] ?? 0);
            $denCount = (int) ($byType[ClinicVisit::TYPE_DENTAL] ?? 0);
            $emgCount = (int) ($byType[ClinicVisit::TYPE_EMERGENCY] ?? 0)
                + (int) ($byType[ClinicVisit::TYPE_FIRST_AID] ?? 0);

            $totals[] = (int) $byType->sum();
            $medical[] = $medCount;
            $dental[] = $denCount;
            $emergency[] = $emgCount;
        }

        return [
            'months' => $months,
            'total' => $totals,
            'medical' => $medical,
            'dental' => $dental,
            'emergency' => $emergency,
        ];
    }

    public function studentVsEmployeeBreakdown(): array
    {
        $byClass = ClinicVisit::query()
            ->selectRaw('patient_classification, COUNT(*) as aggregate')
            ->groupBy('patient_classification')
            ->pluck('aggregate', 'patient_classification');

        $students = (int) ($byClass['student'] ?? 0);
        $faculty = (int) ($byClass['faculty'] ?? 0);
        $staff = (int) ($byClass['staff'] ?? 0) + (int) ($byClass['employee'] ?? 0);
        $others = max(0, (int) $byClass->sum() - ($students + $faculty + $staff));

        return [
            'labels' => ['Students', 'Faculty', 'Staff', 'Others'],
            'data' => [$students, $faculty, $staff, $others],
            'colors' => ['#1b5e3a', '#28666e', '#c27829', '#6e8078'],
        ];
    }

    public function servicesBreakdown(): array
    {
        $byType = ClinicVisit::query()
            ->selectRaw('visit_type, COUNT(*) as aggregate')
            ->groupBy('visit_type')
            ->pluck('aggregate', 'visit_type');

        $medical = (int) ($byType[ClinicVisit::TYPE_MEDICAL] ?? 0);
        $dental = (int) ($byType[ClinicVisit::TYPE_DENTAL] ?? 0);
        $emergency = (int) ($byType[ClinicVisit::TYPE_EMERGENCY] ?? 0);
        $firstAid = (int) ($byType[ClinicVisit::TYPE_FIRST_AID] ?? 0);
        $general = (int) ($byType[ClinicVisit::TYPE_GENERAL] ?? 0);

        return [
            'labels' => ['Medical', 'Dental', 'Emergency', 'First Aid', 'General Checkup'],
            'data' => [$medical, $dental, $emergency, $firstAid, $general],
            'colors' => ['#1b5e3a', '#28666e', '#b42318', '#c27829', '#52796f'],
        ];
    }

    public function topComplaints(int $limit = 7): array
    {
        $complaints = ClinicVisit::query()
            ->whereNotNull('chief_complaint')
            ->where('chief_complaint', '!=', '')
            ->select('chief_complaint', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('chief_complaint')
            ->get();

        $tally = [];
        foreach ($complaints as $row) {
            $normalized = $this->categorizeComplaint((string) $row->chief_complaint);
            $tally[$normalized] = ($tally[$normalized] ?? 0) + (int) $row->aggregate;
        }

        arsort($tally);
        $top = array_slice($tally, 0, $limit, true);

        if (empty($top)) {
            $top = ['No recorded complaints' => 0];
        }

        return [
            'labels' => array_keys($top),
            'data' => array_values($top),
        ];
    }

    public function diseaseClassification(): array
    {
        $diagnoses = MedicalConsultation::query()
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->select('diagnosis', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('diagnosis')
            ->get();

        $communicable = 0;
        $nonCommunicable = 0;
        $other = 0;

        $communicableTally = [];
        $nonCommunicableTally = [];

        foreach ($diagnoses as $row) {
            $clean = trim((string) $row->diagnosis);
            $qty = (int) $row->aggregate;
            if ($clean === '' || $qty <= 0) {
                continue;
            }
            $lower = strtolower($clean);

            $isCommunicable = false;
            foreach (self::COMMUNICABLE_KEYWORDS as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $isCommunicable = true;
                    break;
                }
            }

            $isNonCommunicable = false;
            if (! $isCommunicable) {
                foreach (self::NON_COMMUNICABLE_KEYWORDS as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        $isNonCommunicable = true;
                        break;
                    }
                }
            }

            if ($isCommunicable) {
                $communicable += $qty;
                $label = $this->labelDisease($clean, self::COMMUNICABLE_KEYWORDS);
                $communicableTally[$label] = ($communicableTally[$label] ?? 0) + $qty;
            } elseif ($isNonCommunicable) {
                $nonCommunicable += $qty;
                $label = $this->labelDisease($clean, self::NON_COMMUNICABLE_KEYWORDS);
                $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + $qty;
            } else {
                $other += $qty;
            }
        }

        arsort($communicableTally);
        arsort($nonCommunicableTally);

        $topComm = [];
        foreach (array_slice($communicableTally, 0, 5, true) as $k => $v) {
            $topComm[] = ['name' => $k, 'count' => $v];
        }

        $topNonComm = [];
        foreach (array_slice($nonCommunicableTally, 0, 5, true) as $k => $v) {
            $topNonComm[] = ['name' => $k, 'count' => $v];
        }

        return [
            'communicable_count' => $communicable,
            'non_communicable_count' => $nonCommunicable,
            'other_count' => $other,
            'top_communicable' => $topComm,
            'top_non_communicable' => $topNonComm,
        ];
    }

    public function topMedicineUtilization(int $limit = 7): array
    {
        $items = MedicineDispensingItem::query()
            ->select('medicine_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('medicine:id,generic_name,brand_name,dosage_form')
            ->get();

        $labels = [];
        $quantities = [];

        foreach ($items as $item) {
            $name = $item->medicine?->generic_name ?? 'Unknown Medicine';
            if ($item->medicine?->brand_name) {
                $name .= ' ('.$item->medicine->brand_name.')';
            }
            $labels[] = $name;
            $quantities[] = (int) $item->total_qty;
        }

        if (empty($labels)) {
            $topStocked = Medicine::orderByDesc('reorder_level')->limit($limit)->get(['generic_name', 'brand_name']);
            foreach ($topStocked as $med) {
                $labels[] = $med->brand_name ? "{$med->generic_name} ({$med->brand_name})" : $med->generic_name;
                $quantities[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'total_dispensed' => (int) MedicineDispensingItem::sum('quantity'),
        ];
    }

    public function dailyTrend(int $days = 14): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $counts = ClinicVisit::query()
            ->where('visit_date', '>=', $start)
            ->selectRaw('DATE(visit_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date->lte(Carbon::now()); $date->addDay()) {
            $labels[] = $date->format('M d');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function peFitnessBreakdown(): array
    {
        $byClass = PhysicalExamination::query()
            ->selectRaw('fitness_assessment, COUNT(*) as aggregate')
            ->groupBy('fitness_assessment')
            ->pluck('aggregate', 'fitness_assessment');

        $classA = (int) ($byClass['Class A'] ?? 0);
        $classB = (int) ($byClass['Class B'] ?? 0);
        $classC = (int) ($byClass['Class C'] ?? 0);
        $classD = (int) ($byClass['Class D'] ?? 0);
        $classE = (int) ($byClass['Class E'] ?? 0) + (int) ($byClass['Pending / Incomplete'] ?? 0);

        return [
            'labels' => ['Class A (Fit all)', 'Class B (Correctable defect)', 'Class C (Restricted work)', 'Class D (Defective/Unfit)', 'Class E (Pending/Exempt)'],
            'data' => [$classA, $classB, $classC, $classD, $classE],
            'colors' => ['#1b5e3a', '#28666e', '#c27829', '#c25e14', '#b42318'],
        ];
    }

    public function collegeUtilizationBreakdown(): array
    {
        $colleges = College::withCount('clinicVisits')->orderByDesc('clinic_visits_count')->limit(6)->get();

        $labels = [];
        $data = [];

        foreach ($colleges as $col) {
            $labels[] = $col->code ?: mb_strimwidth($col->name, 0, 18, '...');
            $data[] = (int) $col->clinic_visits_count;
        }

        if (empty($labels)) {
            $labels = ['No Visits Recorded'];
            $data = [0];
        }

        return [
            'labels' => $labels,
            'data' => $data,
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
            return 'Dental Pain / Checkup';
        }
        if (str_contains($lower, 'stomach') || str_contains($lower, 'abdominal') || str_contains($lower, 'sakit ng tiyan') || str_contains($lower, 'gastric')) {
            return 'Abdominal / Stomach Pain';
        }
        if (str_contains($lower, 'wound') || str_contains($lower, 'abrasion') || str_contains($lower, 'cut') || str_contains($lower, 'laceration') || str_contains($lower, 'fall')) {
            return 'Trauma / Wound / Fall';
        }
        if (str_contains($lower, 'dizziness') || str_contains($lower, 'syncope') || str_contains($lower, 'hilo') || str_contains($lower, 'faint')) {
            return 'Dizziness / Syncope';
        }
        if (str_contains($lower, 'dysmenorrhea') || str_contains($lower, 'cramps') || str_contains($lower, 'menstrual')) {
            return 'Dysmenorrhea';
        }
        if (str_contains($lower, 'bp') || str_contains($lower, 'blood pressure') || str_contains($lower, 'hypertension')) {
            return 'BP Check / Elevation';
        }
        if (str_contains($lower, 'allergy') || str_contains($lower, 'rashes') || str_contains($lower, 'itch')) {
            return 'Allergy / Skin Rash';
        }

        return mb_strimwidth(trim($text), 0, 28, '...');
    }

    protected function labelDisease(string $original, array $keywords): string
    {
        $lower = strtolower($original);
        foreach ($keywords as $k) {
            if (str_contains($lower, $k)) {
                return ucwords($k);
            }
        }

        return mb_strimwidth(trim($original), 0, 32, '...');
    }
}
