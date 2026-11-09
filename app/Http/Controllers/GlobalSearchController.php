<?php

namespace App\Http\Controllers;

use App\Models\Clinician;
use App\Models\ClinicLogbook;
use App\Models\ClinicVisit;
use App\Models\College;
use App\Models\DiseaseCategory;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', 'all'));

        $navigationItems = $this->getNavigationCatalog();

        if ($query === '') {
            return response()->json([
                'query' => '',
                'category' => $category,
                'total' => 0,
                'groups' => $this->getQuickStartGroups($navigationItems),
            ]);
        }

        $smartPatterns = $this->expandSmartCodePatterns($query);
        $groups = [];
        $seenUrls = [];

        $appendGroup = function (string $key, string $label, string $icon, array $items) use (&$groups, &$seenUrls): void {
            $unique = [];
            foreach ($items as $item) {
                $url = (string) ($item['url'] ?? '');
                if ($url !== '' && isset($seenUrls[$url])) {
                    continue;
                }
                if ($url !== '') {
                    $seenUrls[$url] = true;
                }
                $unique[] = $item;
            }
            if (! empty($unique)) {
                $groups[] = [
                    'key' => $key,
                    'label' => $label,
                    'icon' => $icon,
                    'items' => $unique,
                ];
            }
        };

        if ($category === 'all' && $this->looksLikeCodeOrTransaction($query, $smartPatterns)) {
            $smartMatches = $this->searchSmartCodeMatches($query, $smartPatterns);
            $appendGroup('smart', 'Smart Code & Transaction Match', 'bi-lightning-charge-fill', $smartMatches);
        }

        if (in_array($category, ['all', 'transactions'], true)) {
            $transactions = $this->searchTransactionsAndBatches($query, $smartPatterns, $category === 'transactions' ? 14 : 6);
            $appendGroup('transactions', 'Transactions, Dispensing & Batch Codes', 'bi-receipt-cutoff', $transactions);
        }

        if (in_array($category, ['all', 'patients'], true)) {
            $patients = $this->searchPatients($query, $smartPatterns, $category === 'patients' ? 12 : 6);
            $appendGroup('patients', 'Patients (Students & Employees)', 'bi-person-vcard', $patients);
        }

        if (in_array($category, ['all', 'visits'], true)) {
            $visits = $this->searchClinicVisits($query, $smartPatterns, $category === 'visits' ? 12 : 6);
            $appendGroup('visits', 'Consultations & Clinic Visits', 'bi-clipboard2-pulse', $visits);
        }

        if (in_array($category, ['all', 'pe'], true)) {
            $peItems = $this->searchPhysicalExams($query, $smartPatterns, $category === 'pe' ? 12 : 5);
            $appendGroup('pe', 'Physical Examinations & Schedules', 'bi-heart-pulse', $peItems);
        }

        if (in_array($category, ['all', 'logbook'], true) && Schema::hasTable('clinic_logbooks')) {
            $logs = $this->searchLogbooks($query, $smartPatterns, $category === 'logbook' ? 12 : 5);
            $appendGroup('logbook', 'Clinic Visitor Logbook', 'bi-journal-check', $logs);
        }

        if (in_array($category, ['all', 'pharmacy'], true)) {
            $pharmacy = $this->searchPharmacy($query, $smartPatterns, $category === 'pharmacy' ? 12 : 5);
            $appendGroup('pharmacy', 'Medicines & Pharmacy Inventory', 'bi-capsule-pill', $pharmacy);
        }

        if (in_array($category, ['all', 'pages'], true)) {
            $matchedPages = $this->searchPages($navigationItems, $query);
            $appendGroup('pages', 'Pages, Reports & Quick Actions', 'bi-compass', $matchedPages);
        }

        if (in_array($category, ['all', 'directory'], true)) {
            $directory = $this->searchDirectory($query, $category === 'directory' ? 12 : 5);
            $appendGroup('directory', 'Clinicians, Programs & Directory', 'bi-building', $directory);
        }

        $total = 0;
        foreach ($groups as $g) {
            $total += count($g['items']);
        }

        return response()->json([
            'query' => $query,
            'category' => $category,
            'total' => $total,
            'groups' => $groups,
        ]);
    }

    protected function expandSmartCodePatterns(string $query): array
    {
        $patterns = [];
        $clean = strtoupper(trim($query));

        $prefixMap = [
            'TXN' => ['prefix' => 'TXN-', 'pad' => [5, 6]],
            'TRX' => ['prefix' => 'TXN-', 'pad' => [5, 6]],
            'DSP' => ['prefix' => 'DSP-', 'pad' => [5, 6]],
            'RX' => ['prefix' => 'DSP-', 'pad' => [5, 6]],
            'VIS' => ['prefix' => 'VIS-', 'pad' => [4, 5, 6]],
            'VISIT' => ['prefix' => 'VIS-', 'pad' => [4, 5, 6]],
            'MC' => ['prefix' => 'VIS-MC-', 'pad' => [4, 5, 6]],
            'DC' => ['prefix' => 'VIS-DC-', 'pad' => [4, 5, 6]],
            'ER' => ['prefix' => 'VIS-ER-', 'pad' => [4, 5, 6]],
            'FA' => ['prefix' => 'VIS-FA-', 'pad' => [4, 5, 6]],
            'GN' => ['prefix' => 'VIS-GN-', 'pad' => [4, 5, 6]],
            'PE' => ['prefix' => 'PE-', 'pad' => [4, 5, 6]],
            'PES' => ['prefix' => 'PES-', 'pad' => [4, 5, 6]],
            'SCH' => ['prefix' => 'PES-', 'pad' => [4, 5, 6]],
            'LOG' => ['prefix' => 'LOG-', 'pad' => [5, 6]],
            'PAT' => ['prefix' => 'PAT-', 'pad' => [5, 6]],
            'MED' => ['prefix' => 'MED-', 'pad' => [4]],
        ];

        if (preg_match('/^([A-Z]+)[\s\-_#]*(?:(\d{4})[\s\-_#]+)?0*(\d+)$/', $clean, $m)) {
            $key = $m[1];
            $yearPart = $m[2] !== '' ? $m[2].'-' : '%';
            $num = $m[3];

            if (isset($prefixMap[$key])) {
                $cfg = $prefixMap[$key];
                foreach ($cfg['pad'] as $padLen) {
                    $padded = str_pad($num, $padLen, '0', STR_PAD_LEFT);
                    $patterns[] = $cfg['prefix'].$yearPart.$padded;
                }
                $patterns[] = $cfg['prefix'].'%'.$num;
            }
        }

        if (str_contains($clean, ' ')) {
            $dashed = preg_replace('/\s+/', '-', $clean);
            if ($dashed) {
                $patterns[] = "%{$dashed}%";
            }
        }

        return array_values(array_unique($patterns));
    }

    protected function looksLikeCodeOrTransaction(string $query, array $smartPatterns): bool
    {
        if (! empty($smartPatterns)) {
            return true;
        }

        return (bool) preg_match('/^(TXN|DSP|VIS|PE|PES|LOG|PAT|MED|BATCH|LOT|EMP|STU|REF)[\-\s#0-9]/i', $query)
            || (bool) preg_match('/\d{2,}/', $query);
    }

    protected function searchSmartCodeMatches(string $term, array $smartPatterns): array
    {
        $matches = [];

        $txnQuery = InventoryTransaction::with(['medicine', 'batch', 'patient'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('transaction_number', 'like', "%{$term}%")
                    ->orWhere('reference_number', 'like', "%{$term}%");
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('transaction_number', 'like', $pat);
                }
            })
            ->latest('transaction_date')
            ->limit(3)
            ->get();

        foreach ($txnQuery as $txn) {
            $matches[] = $this->formatInventoryTransactionItem($txn);
        }

        $dispQuery = MedicineDispensing::with('patient')
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('dispensing_number', 'like', "%{$term}%");
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('dispensing_number', 'like', $pat);
                }
            })
            ->latest('dispensed_date')
            ->limit(3)
            ->get();

        foreach ($dispQuery as $disp) {
            $matches[] = $this->formatDispensingItem($disp);
        }

        $visitQuery = ClinicVisit::with(['patient', 'medicalConsultation', 'dentalConsultation'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('visit_number', 'like', "%{$term}%");
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('visit_number', 'like', $pat);
                }
            })
            ->latest('visit_date')
            ->limit(3)
            ->get();

        foreach ($visitQuery as $visit) {
            $matches[] = $this->formatClinicVisitItem($visit);
        }

        $peQuery = PhysicalExamination::with('patient')
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('pe_number', 'like', "%{$term}%");
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('pe_number', 'like', $pat);
                }
            })
            ->latest('exam_date')
            ->limit(3)
            ->get();

        foreach ($peQuery as $exam) {
            $matches[] = $this->formatPhysicalExamItem($exam);
        }

        if (Schema::hasTable('clinic_logbooks')) {
            $logQuery = ClinicLogbook::with('program')
                ->where(function (Builder $q) use ($term, $smartPatterns) {
                    $q->where('log_number', 'like', "{$term}%")
                        ->orWhere('id_number', $term);
                    foreach ($smartPatterns as $pat) {
                        $q->orWhere('log_number', 'like', $pat);
                    }
                })
                ->latest('log_date')
                ->limit(3)
                ->get();

            foreach ($logQuery as $log) {
                $matches[] = $this->formatLogbookItem($log);
            }
        }

        $patQuery = Patient::with(['program', 'college'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('patient_number', 'like', "{$term}%")
                    ->orWhere('school_id_number', 'like', "{$term}%");
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('patient_number', 'like', $pat);
                }
            })
            ->limit(3)
            ->get();

        foreach ($patQuery as $pat) {
            $matches[] = $this->formatPatientItem($pat);
        }

        return array_slice($matches, 0, 6);
    }

    protected function searchTransactionsAndBatches(string $term, array $smartPatterns, int $limit): array
    {
        $items = [];

        $transactions = InventoryTransaction::with(['medicine', 'batch', 'patient', 'user'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('transaction_number', 'like', "%{$term}%")
                    ->orWhere('reference_number', 'like', "%{$term}%")
                    ->orWhere('reason', 'like', "%{$term}%")
                    ->orWhere('type', 'like', "%{$term}%")
                    ->orWhereHas('medicine', function (Builder $mq) use ($term) {
                        $mq->search($term);
                    })
                    ->orWhereHas('batch', function (Builder $bq) use ($term) {
                        $bq->where('batch_number', 'like', "%{$term}%");
                    })
                    ->orWhereHas('patient', function (Builder $pq) use ($term) {
                        $pq->search($term);
                    });

                foreach ($smartPatterns as $pat) {
                    $q->orWhere('transaction_number', 'like', $pat)
                        ->orWhere('reference_number', 'like', $pat);
                }
            })
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        foreach ($transactions as $txn) {
            $items[] = $this->formatInventoryTransactionItem($txn);
        }

        $dispensings = MedicineDispensing::with('patient')
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('dispensing_number', 'like', "%{$term}%")
                    ->orWhere('diagnosis', 'like', "%{$term}%")
                    ->orWhere('instructions', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('patient', function (Builder $pq) use ($term) {
                        $pq->search($term);
                    });

                foreach ($smartPatterns as $pat) {
                    $q->orWhere('dispensing_number', 'like', $pat);
                }
            })
            ->latest('dispensed_date')
            ->limit(5)
            ->get();

        foreach ($dispensings as $disp) {
            $items[] = $this->formatDispensingItem($disp);
        }

        $batches = MedicineBatch::with('medicine')
            ->where(function (Builder $q) use ($term) {
                $q->where('batch_number', 'like', "%{$term}%")
                    ->orWhere('supplier', 'like', "%{$term}%")
                    ->orWhere('remarks', 'like', "%{$term}%");
            })
            ->latest('expiration_date')
            ->limit(4)
            ->get();

        foreach ($batches as $batch) {
            $medName = $batch->medicine?->generic_name ?? 'Medicine';
            $expStr = $batch->expiration_date?->format('M d, Y') ?? 'N/A';

            $items[] = [
                'title' => "Batch {$batch->batch_number} — {$medName}",
                'subtitle' => "Remaining: {$batch->quantity_remaining}/{$batch->quantity_received} • Exp: {$expStr}".($batch->supplier ? " • Supplier: {$batch->supplier}" : ''),
                'badge' => $batch->is_expired ? 'Expired Batch' : 'Active Batch',
                'badge_class' => $batch->is_expired ? 'badge-soft-red' : 'badge-soft-cyan',
                'icon' => 'bi-box-seam',
                'url' => $batch->medicine ? route('medicines.show', $batch->medicine) : route('inventory.expirations'),
                'actions' => [
                    [
                        'label' => 'Transactions',
                        'url' => route('inventory.transactions', ['search' => $batch->batch_number]),
                        'icon' => 'bi-clock-history',
                    ],
                ],
            ];
        }

        return array_slice($items, 0, $limit);
    }

    protected function formatInventoryTransactionItem(InventoryTransaction $txn): array
    {
        $medName = $txn->medicine?->generic_name ?? 'Medicine';
        $batchNo = $txn->batch?->batch_number ? " • Batch: {$txn->batch->batch_number}" : '';
        $refNo = $txn->reference_number ? " • Ref: {$txn->reference_number}" : '';
        $dateStr = $txn->transaction_date?->format('M d, Y h:i A') ?? '';
        $typeLabel = strtoupper(str_replace('_', ' ', (string) $txn->type));
        $badgeClass = match ($txn->type) {
            'stock_in' => 'badge-soft-green',
            'dispense', 'stock_out' => 'badge-soft-blue',
            'disposal', 'expired' => 'badge-soft-red',
            default => 'badge-soft-amber',
        };

        return [
            'title' => "{$txn->transaction_number} — {$medName} (Qty: {$txn->quantity})",
            'subtitle' => "{$dateStr}{$batchNo}{$refNo}".($txn->reason ? " • {$txn->reason}" : ''),
            'badge' => $typeLabel ?: 'Transaction',
            'badge_class' => $badgeClass,
            'icon' => 'bi-arrow-left-right',
            'url' => route('inventory.transactions', ['search' => $txn->transaction_number]),
            'actions' => array_values(array_filter([
                [
                    'label' => 'Ledger',
                    'url' => route('inventory.transactions', ['search' => $txn->transaction_number]),
                    'icon' => 'bi-journal-text',
                ],
                $txn->medicine ? [
                    'label' => 'Medicine',
                    'url' => route('medicines.show', $txn->medicine),
                    'icon' => 'bi-capsule',
                ] : null,
            ])),
        ];
    }

    protected function formatDispensingItem(MedicineDispensing $disp): array
    {
        $patientName = $disp->patient?->full_name ?? 'Patient';
        $dateStr = $disp->dispensed_date?->format('M d, Y') ?? '';
        $diag = $disp->diagnosis ? " • {$disp->diagnosis}" : '';

        return [
            'title' => "{$disp->dispensing_number} — {$patientName}",
            'subtitle' => "Medicine Dispensing Slip • {$dateStr}{$diag}",
            'badge' => 'Dispensing Slip',
            'badge_class' => 'badge-soft-blue',
            'icon' => 'bi-receipt',
            'url' => route('inventory.dispensings.show', $disp),
            'actions' => [
                [
                    'label' => 'View Slip',
                    'url' => route('inventory.dispensings.show', $disp),
                    'icon' => 'bi-eye',
                ],
                [
                    'label' => 'Print Rx',
                    'url' => route('inventory.dispensings.print', $disp),
                    'icon' => 'bi-printer',
                ],
            ],
        ];
    }

    protected function getQuickStartGroups(array $navigationItems): array
    {
        $quickActions = array_slice($navigationItems, 0, 8);

        $recentPatients = Patient::with(['program', 'college'])
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Patient $patient): array => $this->formatPatientItem($patient))
            ->values()
            ->all();

        $groups = [
            [
                'key' => 'pages',
                'label' => 'Quick Navigation & Actions',
                'icon' => 'bi-lightning-charge',
                'items' => $quickActions,
            ],
        ];

        if (! empty($recentPatients)) {
            $groups[] = [
                'key' => 'patients',
                'label' => 'Recent Patients',
                'icon' => 'bi-clock-history',
                'items' => $recentPatients,
            ];
        }

        return $groups;
    }

    protected function searchPatients(string $term, array $smartPatterns, int $limit): array
    {
        return Patient::with(['program', 'college'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->search($term);
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('patient_number', 'like', $pat);
                }
            })
            ->orderBy('last_name')
            ->limit($limit)
            ->get()
            ->map(fn (Patient $patient): array => $this->formatPatientItem($patient))
            ->values()
            ->all();
    }

    protected function formatPatientItem(Patient $patient): array
    {
        $isEmployee = $patient->type === 'employee';
        $idCode = $patient->school_id_number
            ? "{$patient->school_id_number} ({$patient->patient_number})"
            : $patient->patient_number;
        $programOrDept = $patient->program?->code
            ?: ($patient->office_department ?: ($patient->college?->code ?: 'Unassigned'));
        $yearOrPos = $isEmployee
            ? ($patient->position ?: 'Employee')
            : ($patient->year_level ?: 'Student');

        return [
            'title' => $patient->full_name,
            'subtitle' => "{$idCode} • {$programOrDept} ({$yearOrPos})",
            'badge' => $isEmployee ? 'Employee' : 'Student',
            'badge_class' => $isEmployee ? 'badge-soft-purple' : 'badge-soft-blue',
            'icon' => $isEmployee ? 'bi-person-workspace' : 'bi-mortarboard',
            'url' => route('patients.show', $patient),
            'actions' => [
                [
                    'label' => 'Profile',
                    'url' => route('patients.show', $patient),
                    'icon' => 'bi-person-lines-fill',
                ],
                [
                    'label' => '+ Visit',
                    'url' => route('clinic-visits.create', ['patient_id' => $patient->id]),
                    'icon' => 'bi-plus-circle',
                ],
                [
                    'label' => '+ PE',
                    'url' => route('physical-examinations.create', ['patient_id' => $patient->id]),
                    'icon' => 'bi-heart-pulse',
                ],
            ],
        ];
    }

    protected function searchClinicVisits(string $term, array $smartPatterns, int $limit): array
    {
        return ClinicVisit::with(['patient', 'medicalConsultation', 'dentalConsultation'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('visit_number', 'like', "%{$term}%")
                    ->orWhere('chief_complaint', 'like', "%{$term}%")
                    ->orWhere('remarks', 'like', "%{$term}%")
                    ->orWhere('visit_type', 'like', "%{$term}%")
                    ->orWhereHas('patient', function (Builder $pq) use ($term) {
                        $pq->search($term);
                    })
                    ->orWhereHas('medicalConsultation', function (Builder $mq) use ($term) {
                        $mq->where('diagnosis', 'like', "%{$term}%")
                            ->orWhere('icd_code', 'like', "%{$term}%")
                            ->orWhere('history_of_present_illness', 'like', "%{$term}%")
                            ->orWhere('physical_findings', 'like', "%{$term}%")
                            ->orWhere('treatment_plan', 'like', "%{$term}%")
                            ->orWhere('medications_prescribed', 'like', "%{$term}%");
                    })
                    ->orWhereHas('dentalConsultation', function (Builder $dq) use ($term) {
                        $dq->where('diagnosis', 'like', "%{$term}%")
                            ->orWhere('oral_examination_findings', 'like', "%{$term}%")
                            ->orWhere('treatment_rendered', 'like', "%{$term}%")
                            ->orWhere('teeth_involved', 'like', "%{$term}%");
                    });

                foreach ($smartPatterns as $pat) {
                    $q->orWhere('visit_number', 'like', $pat);
                }
            })
            ->latest('visit_date')
            ->limit($limit)
            ->get()
            ->map(fn (ClinicVisit $visit): array => $this->formatClinicVisitItem($visit))
            ->values()
            ->all();
    }

    protected function formatClinicVisitItem(ClinicVisit $visit): array
    {
        $typeMeta = ClinicVisit::TYPES[$visit->visit_type] ?? ClinicVisit::TYPES[ClinicVisit::TYPE_GENERAL];
        $routePrefix = $typeMeta['route'] ?? 'clinic-visits';
        $patientName = $visit->patient?->full_name ?? 'Unknown Patient';
        $dateStr = $visit->visit_date?->format('M d, Y') ?? '';
        $complaint = $visit->medicalConsultation?->diagnosis
            ?: ($visit->dentalConsultation?->diagnosis ?: ($visit->chief_complaint ?: 'Clinic Visit'));

        return [
            'title' => "{$visit->visit_number} — {$patientName}",
            'subtitle' => "{$dateStr} • {$complaint}",
            'badge' => $typeMeta['label'],
            'badge_class' => $typeMeta['badge'],
            'icon' => $typeMeta['icon'],
            'url' => route("{$routePrefix}.show", $visit),
            'actions' => [
                [
                    'label' => 'View Record',
                    'url' => route("{$routePrefix}.show", $visit),
                    'icon' => 'bi-eye',
                ],
                [
                    'label' => 'Print Slip',
                    'url' => route('clinic-visits.print', $visit),
                    'icon' => 'bi-printer',
                ],
            ],
        ];
    }

    protected function searchPhysicalExams(string $term, array $smartPatterns, int $limit): array
    {
        $exams = PhysicalExamination::with('patient')
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('pe_number', 'like', "%{$term}%")
                    ->orWhere('examiner_name', 'like', "%{$term}%")
                    ->orWhere('purpose', 'like', "%{$term}%")
                    ->orWhere('clinical_findings', 'like', "%{$term}%")
                    ->orWhere('fitness_assessment', 'like', "%{$term}%")
                    ->orWhereHas('patient', function (Builder $pq) use ($term) {
                        $pq->search($term);
                    });

                foreach ($smartPatterns as $pat) {
                    $q->orWhere('pe_number', 'like', $pat);
                }
            })
            ->latest('exam_date')
            ->limit($limit)
            ->get()
            ->map(fn (PhysicalExamination $exam): array => $this->formatPhysicalExamItem($exam))
            ->values()
            ->all();

        $schedules = PhysicalExamSchedule::with(['college', 'program'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->where('schedule_number', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('purpose', 'like', "%{$term}%")
                    ->orWhere('venue', 'like', "%{$term}%");

                foreach ($smartPatterns as $pat) {
                    $q->orWhere('schedule_number', 'like', $pat);
                }
            })
            ->latest('scheduled_date')
            ->limit(3)
            ->get()
            ->map(function (PhysicalExamSchedule $sched): array {
                $dateStr = $sched->scheduled_date?->format('M d, Y') ?? '';

                return [
                    'title' => "{$sched->schedule_number} — {$sched->title}",
                    'subtitle' => "Scheduled PE: {$dateStr} • ".($sched->venue ?: 'Clinic'),
                    'badge' => 'PE Schedule',
                    'badge_class' => 'badge-soft-cyan',
                    'icon' => 'bi-calendar2-check',
                    'url' => route('physical-exam-schedules.show', $sched),
                    'actions' => [],
                ];
            })
            ->values()
            ->all();

        return array_slice(array_merge($exams, $schedules), 0, $limit);
    }

    protected function formatPhysicalExamItem(PhysicalExamination $exam): array
    {
        $patientName = $exam->patient?->full_name ?? 'Unknown Patient';
        $dateStr = $exam->exam_date?->format('M d, Y') ?? '';

        return [
            'title' => "{$exam->pe_number} — {$patientName}",
            'subtitle' => "{$dateStr} • {$exam->purpose} • Examiner: ".($exam->examiner_name ?: 'N/A'),
            'badge' => strtoupper((string) ($exam->fitness_assessment ?: 'PE Record')),
            'badge_class' => $exam->fitness_assessment === 'unfit' ? 'badge-soft-red' : 'badge-soft-green',
            'icon' => 'bi-heart-pulse',
            'url' => route('physical-examinations.show', $exam),
            'actions' => [
                [
                    'label' => 'View PE',
                    'url' => route('physical-examinations.show', $exam),
                    'icon' => 'bi-eye',
                ],
                [
                    'label' => 'Print 2-Page PDF',
                    'url' => route('physical-examinations.print-pe', $exam),
                    'icon' => 'bi-file-earmark-pdf',
                ],
            ],
        ];
    }

    protected function searchLogbooks(string $term, array $smartPatterns, int $limit): array
    {
        return ClinicLogbook::with('program')
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->search($term);
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('log_number', 'like', $pat);
                }
            })
            ->latest('log_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (ClinicLogbook $log): array => $this->formatLogbookItem($log))
            ->values()
            ->all();
    }

    protected function formatLogbookItem(ClinicLogbook $log): array
    {
        $dateStr = $log->log_date?->format('M d, Y') ?? '';
        $course = $log->program?->code ?: ($log->course_or_department ?: ucfirst($log->visitor_type));

        return [
            'title' => "{$log->log_number} — {$log->full_name}",
            'subtitle' => "{$dateStr} • {$course} • Purpose: {$log->purpose}",
            'badge' => $log->status === 'inside' ? 'Inside Clinic' : 'Logged Out',
            'badge_class' => $log->status === 'inside' ? 'badge-soft-amber' : 'badge-soft-green',
            'icon' => 'bi-journal-check',
            'url' => route('clinic-logbooks.index', ['search' => $log->log_number]),
            'actions' => [],
        ];
    }

    protected function searchPharmacy(string $term, array $smartPatterns, int $limit): array
    {
        $medicines = Medicine::with(['category', 'batches'])
            ->where(function (Builder $q) use ($term, $smartPatterns) {
                $q->search($term);
                foreach ($smartPatterns as $pat) {
                    $q->orWhere('code', 'like', $pat);
                }
            })
            ->orderBy('generic_name')
            ->limit($limit)
            ->get()
            ->map(function (Medicine $med): array {
                $stock = $med->stock_on_hand;
                $brand = $med->brand_name ? " ({$med->brand_name})" : '';
                $catName = $med->category?->name ?? 'Medicine';

                return [
                    'title' => "{$med->code} — {$med->generic_name}{$brand} ({$med->strength})",
                    'subtitle' => "{$catName} • {$med->dosage_form}",
                    'badge' => "{$stock} {$med->unit} in stock",
                    'badge_class' => $stock <= $med->reorder_level ? 'badge-soft-red' : 'badge-soft-green',
                    'icon' => 'bi-capsule',
                    'url' => route('medicines.show', $med),
                    'actions' => [
                        [
                            'label' => 'View Stock',
                            'url' => route('medicines.show', $med),
                            'icon' => 'bi-box-seam',
                        ],
                        [
                            'label' => 'Dispense',
                            'url' => route('inventory.dispensings.create'),
                            'icon' => 'bi-bag-plus',
                        ],
                    ],
                ];
            })
            ->values()
            ->all();

        return array_slice($medicines, 0, $limit);
    }

    protected function searchDirectory(string $term, int $limit): array
    {
        $items = [];

        $clinicians = Clinician::search($term)->limit(4)->get();
        foreach ($clinicians as $clinician) {
            $items[] = [
                'title' => $clinician->formatted_title,
                'subtitle' => ($clinician->specialization ?: ucfirst($clinician->type)).($clinician->license_number ? " • Lic: {$clinician->license_number}" : ''),
                'badge' => ucfirst($clinician->type),
                'badge_class' => 'badge-soft-cyan',
                'icon' => 'bi-person-badge',
                'url' => route('clinicians.show', $clinician),
                'actions' => [],
            ];
        }

        $programs = Program::with('college')
            ->where('code', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('degree_level', 'like', "%{$term}%")
            ->limit(4)
            ->get();

        foreach ($programs as $prog) {
            $items[] = [
                'title' => "{$prog->code} — {$prog->name}",
                'subtitle' => 'Academic Program • '.($prog->college?->name ?? ucfirst((string) $prog->degree_level)),
                'badge' => 'Program',
                'badge_class' => 'badge-soft-purple',
                'icon' => 'bi-mortarboard-fill',
                'url' => route('programs.index', ['search' => $prog->code]),
                'actions' => [],
            ];
        }

        $colleges = College::where('code', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->limit(3)
            ->get();

        foreach ($colleges as $col) {
            $items[] = [
                'title' => "{$col->code} — {$col->name}",
                'subtitle' => 'College / Department',
                'badge' => 'College',
                'badge_class' => 'badge-soft-gray',
                'icon' => 'bi-building',
                'url' => route('colleges.index', ['search' => $col->code]),
                'actions' => [],
            ];
        }

        $diseases = DiseaseCategory::where('code', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('keywords', 'like', "%{$term}%")
            ->limit(3)
            ->get();

        foreach ($diseases as $dis) {
            $items[] = [
                'title' => $dis->name,
                'subtitle' => ($dis->keywords ?: $dis->code),
                'badge' => $dis->classification === DiseaseCategory::TYPE_COMMUNICABLE ? 'Communicable' : 'Non-Communicable',
                'badge_class' => $dis->classification === DiseaseCategory::TYPE_COMMUNICABLE ? 'badge-soft-green' : 'badge-soft-blue',
                'icon' => 'bi-virus',
                'url' => route('disease-categories.index', ['search' => $dis->name]),
                'actions' => [],
            ];
        }

        return array_slice($items, 0, $limit);
    }

    protected function searchPages(array $navigationItems, string $term): array
    {
        $needle = strtolower($term);
        $matched = [];

        foreach ($navigationItems as $item) {
            $haystack = strtolower($item['title'].' '.$item['subtitle'].' '.($item['keywords'] ?? ''));
            if (str_contains($haystack, $needle)) {
                $matched[] = $item;
            }
        }

        return array_slice($matched, 0, 6);
    }

    protected function getNavigationCatalog(): array
    {
        return [
            [
                'title' => 'Daily Clinic Logbook (Staff Management)',
                'subtitle' => 'View today\'s clinic visitor logbook, time-out visitors, or add manual entries',
                'keywords' => 'logbook attendance tracing time in time out visitor',
                'badge' => 'Logbook',
                'badge_class' => 'badge-soft-blue',
                'icon' => 'bi-journal-check',
                'url' => route('clinic-logbooks.index'),
                'actions' => [],
            ],
            [
                'title' => 'Public Clinic Visitor Logbook Kiosk',
                'subtitle' => 'Public walk-in logbook page for students, employees, and visitors entering the clinic',
                'keywords' => 'public kiosk walk-in logbook student employee visitor tracing',
                'badge' => 'Public Kiosk',
                'badge_class' => 'badge-soft-green',
                'icon' => 'bi-display',
                'url' => route('logbook.kiosk'),
                'actions' => [],
            ],
            [
                'title' => 'New Medical Consultation',
                'subtitle' => 'Record a new medical consultation, vital signs, diagnosis, and disposition',
                'keywords' => 'create add medical consultation doctor checkup illness complaint',
                'badge' => 'Action',
                'badge_class' => 'badge-soft-blue',
                'icon' => 'bi-clipboard2-pulse',
                'url' => route('medical-consultations.create'),
                'actions' => [],
            ],
            [
                'title' => 'New Physical Examination (HSD Form 1)',
                'subtitle' => 'Create a 2-page back-to-back Physical Examination & Medical Clearance record',
                'keywords' => 'create add physical exam pe form 1 medical clearance ojt athlete',
                'badge' => 'Action',
                'badge_class' => 'badge-soft-purple',
                'icon' => 'bi-heart-pulse',
                'url' => route('physical-examinations.create'),
                'actions' => [],
            ],
            [
                'title' => 'New Dental Consultation',
                'subtitle' => 'Record tooth charting, extraction, prophylaxis, and dental treatment',
                'keywords' => 'create add dental consultation dentist tooth odontogram',
                'badge' => 'Action',
                'badge_class' => 'badge-soft-cyan',
                'icon' => 'bi-emoji-smile',
                'url' => route('dental-consultations.create'),
                'actions' => [],
            ],
            [
                'title' => 'Add New Patient (Student / Employee)',
                'subtitle' => 'Register a new student or employee into the Patient Masterlist',
                'keywords' => 'create add patient student employee masterlist register',
                'badge' => 'Action',
                'badge_class' => 'badge-soft-green',
                'icon' => 'bi-person-plus',
                'url' => route('patients.create'),
                'actions' => [],
            ],
            [
                'title' => 'Dispense Medicine (Pharmacy)',
                'subtitle' => 'Release medicine to a patient using FEFO batch deduction',
                'keywords' => 'dispense medicine pharmacy prescription rx stock out',
                'badge' => 'Pharmacy',
                'badge_class' => 'badge-soft-amber',
                'icon' => 'bi-capsule-pill',
                'url' => route('inventory.dispensings.create'),
                'actions' => [],
            ],
            [
                'title' => 'Inventory Transactions & Stock Ledger',
                'subtitle' => 'Search stock-in, stock-out, dispensing, and batch disposal transactions (TXN-...)',
                'keywords' => 'inventory transactions stock ledger txn reference batch',
                'badge' => 'Pharmacy',
                'badge_class' => 'badge-soft-cyan',
                'icon' => 'bi-arrow-left-right',
                'url' => route('inventory.transactions'),
                'actions' => [],
            ],
            [
                'title' => '1. Medical & Dental Records Report',
                'subtitle' => '12-Month matrix of services rendered by client classification + PDF export',
                'keywords' => 'report analytics medical dental records monthly matrix pdf',
                'badge' => 'Report 1',
                'badge_class' => 'badge-soft-blue',
                'icon' => 'bi-table',
                'url' => route('reports.clinic.medical-dental'),
                'actions' => [],
            ],
            [
                'title' => '2. Medical & Dental Log-In Records Report',
                'subtitle' => '12-Month clinic attendance & log-in tally across programs and employees',
                'keywords' => 'report analytics login log-in attendance records pdf',
                'badge' => 'Report 2',
                'badge_class' => 'badge-soft-cyan',
                'icon' => 'bi-journal-check',
                'url' => route('reports.clinic.login-records'),
                'actions' => [],
            ],
            [
                'title' => '3. Communicable Diseases Report',
                'subtitle' => 'Horizontal bar chart & breakdown of communicable disease cases from DB',
                'keywords' => 'report analytics communicable diseases urti svi tuberculosis chart pdf',
                'badge' => 'Report 3',
                'badge_class' => 'badge-soft-green',
                'icon' => 'bi-bar-chart-steps',
                'url' => route('reports.clinic.communicable'),
                'actions' => [],
            ],
            [
                'title' => '4. Non-Communicable Diseases Report',
                'subtitle' => 'Horizontal bar chart & breakdown of non-communicable disease cases from DB',
                'keywords' => 'report analytics non-communicable diseases cardiovascular gastrointestinal chart pdf',
                'badge' => 'Report 4',
                'badge_class' => 'badge-soft-purple',
                'icon' => 'bi-bar-chart-line',
                'url' => route('reports.clinic.non-communicable'),
                'actions' => [],
            ],
            [
                'title' => '5. Analytics Overview & Pharmacy Reports',
                'subtitle' => 'Executive KPIs, top diagnoses, demographic utilization, and inventory reports',
                'keywords' => 'report analytics overview pharmacy inventory stock expiration',
                'badge' => 'Analytics',
                'badge_class' => 'badge-soft-amber',
                'icon' => 'bi-graph-up-arrow',
                'url' => route('reports.clinic.index'),
                'actions' => [],
            ],
            [
                'title' => 'Patient Masterlist Registry',
                'subtitle' => 'Browse, filter, and manage all registered students and employees',
                'keywords' => 'patients masterlist students employees directory',
                'badge' => 'Module',
                'badge_class' => 'badge-soft-gray',
                'icon' => 'bi-people',
                'url' => route('patients.index'),
                'actions' => [],
            ],
            [
                'title' => 'Emergency Records & First Aid Logs',
                'subtitle' => 'Manage urgent emergency cases and walk-in first aid treatments',
                'keywords' => 'emergency first aid wound ambulance hospital transfer',
                'badge' => 'Module',
                'badge_class' => 'badge-soft-red',
                'icon' => 'bi-truck-front',
                'url' => route('emergency-records.index'),
                'actions' => [],
            ],
            [
                'title' => 'Scheduled Physical Examinations',
                'subtitle' => 'Manage batch PE schedules, rosters, and student attendance checklists',
                'keywords' => 'scheduled pe roster batch schedule physical exam',
                'badge' => 'Module',
                'badge_class' => 'badge-soft-cyan',
                'icon' => 'bi-calendar2-week',
                'url' => route('physical-exam-schedules.index'),
                'actions' => [],
            ],
            [
                'title' => 'Medicine Catalog & Stock In / Out',
                'subtitle' => 'Manage pharmacy inventory, batches, expirations, and stock movements',
                'keywords' => 'medicine catalog inventory stock in stock out expiration batch',
                'badge' => 'Pharmacy',
                'badge_class' => 'badge-soft-green',
                'icon' => 'bi-boxes',
                'url' => route('medicines.index'),
                'actions' => [],
            ],
            [
                'title' => 'Disease Categories (Communicable & Non-Communicable)',
                'subtitle' => 'Configure database disease categories used in clinic consultations and charts',
                'keywords' => 'disease categories communicable non-communicable settings config',
                'badge' => 'Config',
                'badge_class' => 'badge-soft-purple',
                'icon' => 'bi-virus',
                'url' => route('disease-categories.index'),
                'actions' => [],
            ],
            [
                'title' => 'Academic Years, Colleges & Programs',
                'subtitle' => 'Configure school years, colleges, and academic programs (including TESDA/SHS)',
                'keywords' => 'academic years colleges programs tesda shs configuration',
                'badge' => 'Config',
                'badge_class' => 'badge-soft-gray',
                'icon' => 'bi-sliders',
                'url' => route('programs.index'),
                'actions' => [],
            ],
            [
                'title' => 'System Settings & Branding',
                'subtitle' => 'Update university name, clinic address, logos, and system preferences',
                'keywords' => 'settings branding logo university clinic configuration',
                'badge' => 'Settings',
                'badge_class' => 'badge-soft-gray',
                'icon' => 'bi-gear',
                'url' => route('settings.edit'),
                'actions' => [],
            ],
        ];
    }
}
