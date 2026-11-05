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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = MedicineDispensing::query()
            ->with(['patient', 'clinician', 'dispenser', 'items.medicine'])
            ->when($search, function ($q, $term) {
                $q->where('dispensing_number', 'like', "%{$term}%")
                    ->orWhere('diagnosis', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($p) use ($term) {
                        $p->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('school_id_number', 'like', "%{$term}%");
                    });
            })
            ->latest('dispensed_date');

        $dispensings = $query->paginate(15)->withQueryString();

        $stats = [
            'total_dispensed' => MedicineDispensing::count(),
            'today_dispensed' => MedicineDispensing::whereDate('dispensed_date', now()->today())->count(),
            'total_items_dispensed' => MedicineDispensingItem::sum('quantity'),
        ];

        return view('inventory.dispensings.index', compact('dispensings', 'stats', 'search'));
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
