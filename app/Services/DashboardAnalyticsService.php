<?php

namespace App\Services;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\MedicalConsultation;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
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
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $totalVisits = ClinicVisit::count();
        $todayVisits = ClinicVisit::whereDate('visit_date', $today)->count();
        $thisMonthVisits = ClinicVisit::where('visit_date', '>=', $thisMonth)->count();
        $lastMonthVisits = ClinicVisit::whereBetween('visit_date', [$lastMonthStart, $lastMonthEnd])->count();

        $studentVisits = ClinicVisit::where('patient_classification', 'student')->count();
        $employeeVisits = ClinicVisit::whereIn('patient_classification', ['faculty', 'staff', 'employee'])->count();

        $medicalVisits = ClinicVisit::where('visit_type', ClinicVisit::TYPE_MEDICAL)->count();
        $dentalVisits = ClinicVisit::where('visit_type', ClinicVisit::TYPE_DENTAL)->count();
        $emergencyVisits = ClinicVisit::where('visit_type', ClinicVisit::TYPE_EMERGENCY)->count();
        $firstAidVisits = ClinicVisit::where('visit_type', ClinicVisit::TYPE_FIRST_AID)->count();

        $totalPatients = Patient::count();
        $activePatients = Patient::where('status', 'active')->count();
        $studentsCount = Patient::where('type', 'student')->count();
        $employeesCount = Patient::where('type', 'employee')->count();

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
        ];
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array{months: list<string>, total: list<int>, medical: list<int>, dental: list<int>, emergency: list<int>}
     */
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

            $visits = ClinicVisit::whereBetween('visit_date', [$start, $end])->get(['visit_type']);
            $totals[] = $visits->count();
            $medical[] = $visits->where('visit_type', ClinicVisit::TYPE_MEDICAL)->count();
            $dental[] = $visits->where('visit_type', ClinicVisit::TYPE_DENTAL)->count();
            $emergency[] = $visits->whereIn('visit_type', [ClinicVisit::TYPE_EMERGENCY, ClinicVisit::TYPE_FIRST_AID])->count();
        }

        return [
            'months' => $months,
            'total' => $totals,
            'medical' => $medical,
            'dental' => $dental,
            'emergency' => $emergency,
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>, colors: list<string>}
     */
    public function studentVsEmployeeBreakdown(): array
    {
        $students = ClinicVisit::where('patient_classification', 'student')->count();
        $faculty = ClinicVisit::where('patient_classification', 'faculty')->count();
        $staff = ClinicVisit::whereIn('patient_classification', ['staff', 'employee'])->count();
        $others = ClinicVisit::whereNotIn('patient_classification', ['student', 'faculty', 'staff', 'employee'])->count();

        return [
            'labels' => ['Students', 'Faculty', 'Staff', 'Others'],
            'data' => [$students, $faculty, $staff, $others],
            'colors' => ['#2563eb', '#10b981', '#f59e0b', '#64748b'],
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>, colors: list<string>}
     */
    public function servicesBreakdown(): array
    {
        $medical = ClinicVisit::where('visit_type', ClinicVisit::TYPE_MEDICAL)->count();
        $dental = ClinicVisit::where('visit_type', ClinicVisit::TYPE_DENTAL)->count();
        $emergency = ClinicVisit::where('visit_type', ClinicVisit::TYPE_EMERGENCY)->count();
        $firstAid = ClinicVisit::where('visit_type', ClinicVisit::TYPE_FIRST_AID)->count();
        $general = ClinicVisit::where('visit_type', ClinicVisit::TYPE_GENERAL)->count();

        return [
            'labels' => ['Medical', 'Dental', 'Emergency', 'First Aid', 'General Checkup'],
            'data' => [$medical, $dental, $emergency, $firstAid, $general],
            'colors' => ['#3b82f6', '#06b6d4', '#ef4444', '#f59e0b', '#8b5cf6'],
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    public function topComplaints(int $limit = 7): array
    {
        $complaints = ClinicVisit::query()
            ->whereNotNull('chief_complaint')
            ->where('chief_complaint', '!=', '')
            ->pluck('chief_complaint');

        $tally = [];
        foreach ($complaints as $text) {
            $normalized = $this->categorizeComplaint((string) $text);
            $tally[$normalized] = ($tally[$normalized] ?? 0) + 1;
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

    /**
     * @return array{
     *   communicable_count: int,
     *   non_communicable_count: int,
     *   other_count: int,
     *   top_communicable: list<array{name: string, count: int}>,
     *   top_non_communicable: list<array{name: string, count: int}>
     * }
     */
    public function diseaseClassification(): array
    {
        $diagnoses = MedicalConsultation::pluck('diagnosis');

        $communicable = 0;
        $nonCommunicable = 0;
        $other = 0;

        $communicableTally = [];
        $nonCommunicableTally = [];

        foreach ($diagnoses as $diag) {
            $clean = trim((string) $diag);
            if ($clean === '') {
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
                $communicable++;
                $label = $this->labelDisease($clean, self::COMMUNICABLE_KEYWORDS);
                $communicableTally[$label] = ($communicableTally[$label] ?? 0) + 1;
            } elseif ($isNonCommunicable) {
                $nonCommunicable++;
                $label = $this->labelDisease($clean, self::NON_COMMUNICABLE_KEYWORDS);
                $nonCommunicableTally[$label] = ($nonCommunicableTally[$label] ?? 0) + 1;
            } else {
                $other++;
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

    /**
     * @return array{labels: list<string>, quantities: list<int>, total_dispensed: int}
     */
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

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
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

    /**
     * @return array{labels: list<string>, data: list<int>, colors: list<string>}
     */
    public function peFitnessBreakdown(): array
    {
        $classes = ['Class A', 'Class B', 'Class C', 'Class D', 'Class E'];
        $counts = [];

        foreach ($classes as $cls) {
            $counts[] = PhysicalExamination::where('fitness_assessment', $cls)->count();
        }

        return [
            'labels' => ['Class A (Fit all)', 'Class B (Correctable defect)', 'Class C (Restricted work)', 'Class D (Defective/Unfit)', 'Class E (Pending/Exempt)'],
            'data' => $counts,
            'colors' => ['#10b981', '#06b6d4', '#f59e0b', '#f97316', '#ef4444'],
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
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

    /**
     * @param  list<string>  $keywords
     */
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
