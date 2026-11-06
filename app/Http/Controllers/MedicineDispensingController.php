<?php

namespace App\Http\Controllers;

use App\Models\Clinician;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineDispensing;
use App\Models\MedicineDispensingItem;
use App\Models\Patient;
use App\Services\AuditLogger;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use App\Support\IdObfuscator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MedicineDispensingController extends Controller implements HasMiddleware
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.dispense', only: ['create', 'store']),
            new Middleware('permission:inventory.view', only: ['index', 'show', 'printSlip']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if ($request->ajax()) {
            $query = MedicineDispensing::query()
                ->with(['patient:id,first_name,middle_name,last_name,suffix,type,school_id_number', 'clinician:id,name,title', 'dispenser:id,name'])
                ->when($dateFrom, fn (Builder $q) => $q->whereDate('dispensed_date', '>=', $dateFrom))
                ->when($dateTo, fn (Builder $q) => $q->whereDate('dispensed_date', '<=', $dateTo))
                ->latest('dispensed_date');

            return DataTables::eloquent($query)
                ->filter(function (Builder $q) use ($request): void {
                    $term = $request->input('custom_search') ?: $request->input('search.value');
                    if (is_string($term) && trim($term) !== '') {
                        $term = trim($term);
                        $q->where(function (Builder $inner) use ($term): void {
                            $inner->where('dispensing_number', 'like', "%{$term}%")
                                ->orWhere('diagnosis', 'like', "%{$term}%")
                                ->orWhereHas('patient', fn (Builder $p) => $p->search($term));
                        });
                    }
                })
                ->addColumn('slip_col', function (MedicineDispensing $d): string {
                    $url = route('inventory.dispensings.show', $d);
                    $num = e($d->dispensing_number);

                    return "<a href=\"{$url}\" class=\"font-monospace fw-bold text-dark text-decoration-none\">{$num}</a>";
                })
                ->addColumn('date_col', fn (MedicineDispensing $d): string => '<span class="small text-muted">'.($d->dispensed_date ? e($d->dispensed_date->format('M d, Y')) : 'N/A').'</span>')
                ->addColumn('patient_col', function (MedicineDispensing $d): string {
                    $p = $d->patient;
                    if (! $p) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    $url = route('patients.show', $p);
                    $name = e($p->full_name);
                    $sub = e($p->primary_identifier.' • '.ucfirst($p->type));

                    return "<div><a href=\"{$url}\" class=\"fw-bold text-dark text-decoration-none\">{$name}</a></div><small class=\"text-muted\">{$sub}</small>";
                })
                ->addColumn('clinician_col', function (MedicineDispensing $d): string {
                    if ($d->clinician) {
                        return '<span class="fw-medium text-dark">'.e($d->clinician->name).'</span><small class="text-muted d-block">'.e($d->clinician->title).'</small>';
                    }

                    return '<span class="text-muted">Direct / OTC</span>';
                })
                ->addColumn('items_col', fn (MedicineDispensing $d): string => '<span class="badge badge-soft-blue">'.(int) $d->total_items.' items</span>')
                ->addColumn('diagnosis_col', function (MedicineDispensing $d): string {
                    $diag = e($d->diagnosis ?: 'N/A');

                    return "<span class=\"small text-truncate d-inline-block\" style=\"max-width: 220px;\" title=\"{$diag}\">{$diag}</span>";
                })
                ->addColumn('dispenser_col', fn (MedicineDispensing $d): string => '<span class="small text-muted">'.e($d->dispenser?->name ?? 'System').'</span>')
                ->addColumn('actions', function (MedicineDispensing $d): string {
                    $showUrl = route('inventory.dispensings.show', $d);
                    $printUrl = route('inventory.dispensings.print', $d);

                    return "<div class=\"d-flex align-items-center justify-content-end gap-1\">
                        <a href=\"{$showUrl}\" class=\"btn btn-sm btn-light border p-1 px-2\" title=\"View Details\">
                            <i class=\"bi bi-eye\"></i>
                        </a>
                        <a href=\"{$printUrl}\" target=\"_blank\" class=\"btn btn-sm btn-light border p-1 px-2 text-primary\" title=\"Print Dispensing Slip\">
                            <i class=\"bi bi-printer\"></i>
                        </a>
                    </div>";
                })
                ->orderColumn('slip_col', 'dispensing_number $1')
                ->orderColumn('date_col', 'dispensed_date $1')
                ->orderColumn('items_col', 'total_items $1')
                ->rawColumns(['slip_col', 'date_col', 'patient_col', 'clinician_col', 'items_col', 'diagnosis_col', 'dispenser_col', 'actions'])
                ->make(true);
        }

        $todayDate = now()->toDateString();
        $agg = MedicineDispensing::query()
            ->selectRaw('COUNT(*) as total_dispensed')
            ->selectRaw('SUM(CASE WHEN dispensed_date = ? THEN 1 ELSE 0 END) as today_dispensed', [$todayDate])
            ->first();

        $stats = [
            'total_dispensed' => (int) ($agg?->total_dispensed ?? 0),
            'today_dispensed' => (int) ($agg?->today_dispensed ?? 0),
            'total_items_dispensed' => (int) MedicineDispensingItem::sum('quantity'),
        ];

        return view('inventory.dispensings.index', compact('stats', 'search', 'dateFrom', 'dateTo'));
    }

    public function create(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $resolvedPatientId = $patientId ? IdObfuscator::decodeOrRaw($patientId, Patient::class) : null;
        $preselectedPatient = $resolvedPatientId ? Patient::find($resolvedPatientId) : null;

        $clinicians = Clinician::active()->orderBy('name')->get();
        $defaultClinician = Clinician::where('user_id', auth()->id())->first()
            ?? Clinician::where('type', 'Doctor')->first()
            ?? Clinician::first();

        $medicines = Medicine::active()
            ->with(['activeBatches' => fn ($q) => $q->orderBy('expiration_date')])
            ->orderBy('generic_name')
            ->get();

        $nextNumber = MedicineDispensing::generateDispensingNumber();

        return view('inventory.dispensings.create', compact(
            'medicines',
            'clinicians',
            'defaultClinician',
            'preselectedPatient',
            'nextNumber'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'clinician_id' => ['nullable', 'exists:clinicians,id'],
            'dispensing_number' => ['nullable', 'string', 'max:60', 'unique:medicine_dispensings,dispensing_number'],
            'dispensed_date' => ['required', 'date'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.batch_id' => ['required', 'exists:medicine_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.dosage_instruction' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['items'] as $index => $itemData) {
            $batch = MedicineBatch::find($itemData['batch_id']);
            $qty = (int) $itemData['quantity'];
            if (! $batch || $batch->quantity_remaining < $qty) {
                $medName = $batch?->medicine?->display_name ?? 'Medicine';
                $avail = $batch?->quantity_remaining ?? 0;

                return back()->withInput()->withErrors([
                    "items.{$index}.quantity" => "Insufficient stock for {$medName}. Only {$avail} remaining in batch {$batch?->batch_number}.",
                ]);
            }
        }

        $dispensing = DB::transaction(function () use ($validated) {
            $patient = Patient::findOrFail($validated['patient_id']);
            $clinicianId = $validated['clinician_id'] ?? null;
            $dispensingNumber = $validated['dispensing_number'] ?: MedicineDispensing::generateDispensingNumber();

            $record = MedicineDispensing::create([
                'dispensing_number' => $dispensingNumber,
                'patient_id' => $patient->id,
                'clinician_id' => $clinicianId,
                'dispensed_by' => auth()->id(),
                'dispensed_date' => $validated['dispensed_date'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_items' => count($validated['items']),
            ]);

            foreach ($validated['items'] as $item) {
                $batch = MedicineBatch::findOrFail($item['batch_id']);
                $medicine = $batch->medicine;
                $qty = (int) $item['quantity'];

                MedicineDispensingItem::create([
                    'dispensing_id' => $record->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'dosage_instruction' => $item['dosage_instruction'] ?? null,
                ]);

                $beforeStock = $medicine->stock_on_hand;
                $batch->quantity_remaining -= $qty;
                $batch->save();
                $afterStock = $beforeStock - $qty;

                InventoryTransaction::create([
                    'transaction_number' => InventoryTransaction::generateTransactionNumber(),
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'type' => 'dispensed',
                    'quantity' => -$qty,
                    'quantity_before' => $beforeStock,
                    'quantity_after' => $afterStock,
                    'reference_number' => $dispensingNumber,
                    'reason' => "Dispensed to patient {$patient->full_name} ({$patient->primary_identifier})",
                    'patient_id' => $patient->id,
                    'clinician_id' => $clinicianId,
                    'user_id' => auth()->id(),
                    'transaction_date' => now(),
                ]);
            }

            $this->auditLogger->log('dispensed', "Dispensed medicines ({$dispensingNumber}) to patient {$patient->full_name}", $record);

            return $record;
        });

        return redirect()->route('inventory.dispensings.show', $dispensing)
            ->with('success', "Medicine dispensing {$dispensing->dispensing_number} recorded successfully.");
    }

    public function show(MedicineDispensing $dispensing): View
    {
        $dispensing->load([
            'patient.college',
            'patient.program',
            'clinician',
            'dispenser',
            'items.medicine',
            'items.batch',
        ]);

        return view('inventory.dispensings.show', compact('dispensing'));
    }

    public function printSlip(Request $request, MedicineDispensing $dispensing, SettingService $settingService, MedicalDocumentPdfService $pdfService): View|Response
    {
        $dispensing->load([
            'patient.college',
            'patient.program',
            'clinician',
            'dispenser',
            'items.medicine',
            'items.batch',
        ]);

        if ($request->query('format') === 'html') {
            return view('inventory.dispensings.print', [
                'dispensing' => $dispensing,
                'patient' => $dispensing->patient,
                'settings' => $settingService->all(),
            ]);
        }

        $pdf = $pdfService->medicineDispensingSlip($dispensing, $settingService->all(), $dispensing->clinician);
        $filename = "DispensingSlip-{$dispensing->dispensing_number}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
