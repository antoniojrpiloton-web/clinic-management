<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClinicLogbook;
use App\Models\Patient;
use App\Models\Program;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicLogbookController extends Controller
{
    public function kiosk(SettingService $settingService): View
    {
        $programs = Program::where('is_active', true)->orderBy('id')->get();
        $todayLogs = ClinicLogbook::with('program')
            ->whereDate('log_date', Carbon::today())
            ->orderByDesc('time_in')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $settings = $settingService->all();

        return view('clinic-logbooks.kiosk', [
            'programs' => $programs,
            'todayLogs' => $todayLogs,
            'purposes' => ClinicLogbook::PURPOSES,
            'settings' => $settings,
        ]);
    }

    public function lookupPatient(Request $request): JsonResponse
    {
        $idNumber = trim((string) $request->query('id_number', $request->query('q', '')));
        if ($idNumber === '') {
            return response()->json(['found' => false]);
        }

        $patient = Patient::with(['program', 'college'])
            ->where('school_id_number', $idNumber)
            ->orWhere('patient_number', $idNumber)
            ->first();

        if (! $patient) {
            return response()->json(['found' => false]);
        }

        $visitorType = 'student';
        if ($patient->type === 'employee') {
            $isFaculty = stripos((string) $patient->category, 'faculty') !== false
                || stripos((string) $patient->employment_classification, 'faculty') !== false;
            $visitorType = $isFaculty ? 'faculty' : 'staff';
        }

        return response()->json([
            'found' => true,
            'patient_id' => $patient->id,
            'id_number' => $patient->school_id_number ?: $patient->patient_number,
            'full_name' => $patient->full_name,
            'visitor_type' => $visitorType,
            'program_id' => $patient->program_id,
            'course_or_department' => $patient->program?->code ?: ($patient->office_department ?: ($patient->college?->code ?: '')),
            'year_or_position' => $patient->type === 'employee' ? ($patient->position ?: ($isFaculty ? 'Faculty' : 'Staff')) : ($patient->year_level ?: ''),
            'contact_number' => $patient->phone ?: '',
        ]);
    }

    public function kioskStore(Request $request): RedirectResponse
    {
        $log = $this->createLogEntry($request);

        return redirect()->route('logbook.kiosk')
            ->with('logbook_success', "Welcome, {$log->full_name}! Your clinic log-in ({$log->log_number}) at ".Carbon::parse($log->time_in)->format('h:i A').' has been recorded.');
    }

    public function kioskTimeOut(ClinicLogbook $clinicLogbook): RedirectResponse
    {
        if ($clinicLogbook->status !== 'completed') {
            $clinicLogbook->update([
                'time_out' => now()->format('H:i:s'),
                'status' => 'completed',
            ]);
        }

        return redirect()->route('logbook.kiosk')
            ->with('logbook_success', "Time-out recorded for {$clinicLogbook->full_name} at ".now()->format('h:i A').'.');
    }

    public function index(Request $request): View|JsonResponse
    {
        return app(ClinicVisitController::class)->index($request->merge([
            'tab' => 'logbook',
            'log_search' => $request->query('search', $request->query('log_search')),
            'log_status' => $request->query('status', $request->query('log_status')),
            'log_date_from' => $request->query('date_from', $request->query('log_date_from')),
            'log_date_to' => $request->query('date_to', $request->query('log_date_to')),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $log = $this->createLogEntry($request);

        return redirect()->route('clinic-logbooks.index')
            ->with('success', "Logbook entry {$log->log_number} for {$log->full_name} recorded.");
    }

    public function timeOut(ClinicLogbook $clinicLogbook): RedirectResponse
    {
        $clinicLogbook->update([
            'time_out' => now()->format('H:i:s'),
            'status' => 'completed',
        ]);

        return redirect()->back()
            ->with('success', "Time-out recorded for {$clinicLogbook->full_name}.");
    }

    public function destroy(ClinicLogbook $clinicLogbook): RedirectResponse
    {
        $num = $clinicLogbook->log_number;
        $clinicLogbook->delete();

        return redirect()->route('clinic-logbooks.index')
            ->with('success', "Logbook entry {$num} deleted.");
    }

    protected function createLogEntry(Request $request): ClinicLogbook
    {
        $validated = $request->validate([
            'visitor_type' => 'required|string|in:student,faculty,staff,employee,visitor',
            'id_number' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:160',
            'program_id' => 'nullable|exists:programs,id',
            'course_or_department' => 'nullable|string|max:120',
            'year_or_position' => 'nullable|string|max:80',
            'contact_number' => 'nullable|string|max:40',
            'purpose' => 'required|string|max:190',
            'notes' => 'nullable|string|max:500',
            'log_date' => 'nullable|date',
            'time_in' => 'nullable|string|max:15',
        ]);

        $idNum = trim((string) ($validated['id_number'] ?? ''));
        $patient = null;
        if ($idNum !== '') {
            $patient = Patient::where('school_id_number', $idNum)
                ->orWhere('patient_number', $idNum)
                ->first();
        }

        $program = ! empty($validated['program_id'])
            ? Program::find($validated['program_id'])
            : ($patient?->program);

        $collegeId = $program?->college_id ?? $patient?->college_id;
        $currentAyId = AcademicYear::current()->value('id') ?? $patient?->academic_year_id;

        $courseOrDept = trim((string) ($validated['course_or_department'] ?? ''));
        if ($courseOrDept === '' && $program) {
            $courseOrDept = $program->code;
        } elseif ($courseOrDept === '' && $validated['visitor_type'] === 'employee') {
            $courseOrDept = $patient?->office_department ?: 'Employee';
        }

        return ClinicLogbook::create([
            'log_date' => $validated['log_date'] ?? now()->toDateString(),
            'time_in' => ! empty($validated['time_in']) ? $validated['time_in'] : now()->format('H:i:s'),
            'patient_id' => $patient?->id,
            'academic_year_id' => $currentAyId,
            'college_id' => $collegeId,
            'program_id' => $program?->id,
            'visitor_type' => $validated['visitor_type'],
            'id_number' => $idNum !== '' ? $idNum : null,
            'full_name' => trim($validated['full_name']),
            'course_or_department' => $courseOrDept !== '' ? $courseOrDept : null,
            'year_or_position' => $validated['year_or_position'] ?? ($patient?->year_level ?: ($patient?->position ?: null)),
            'contact_number' => $validated['contact_number'] ?? $patient?->phone,
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'inside',
        ]);
    }
}
