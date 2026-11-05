<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\College;
use App\Services\DataAggregationAlgorithmService;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ClinicReportController extends Controller implements HasMiddleware
{
    public function __construct(
        protected DataAggregationAlgorithmService $aggregationService,
        protected MedicalDocumentPdfService $pdfService,
        protected SettingService $settingService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.reports'),
        ];
    }

    public function index(Request $request): View
    {
        $filters = [
            'date_from' => $request->query('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->query('date_to', now()->format('Y-m-d')),
            'academic_year_id' => $request->query('academic_year_id'),
            'college_id' => $request->query('college_id'),
            'patient_classification' => $request->query('patient_classification'),
        ];

        $reportData = $this->aggregationService->aggregateClinicReport($filters);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.clinic', compact('reportData', 'filters', 'academicYears', 'colleges'));
    }

    public function printPdf(Request $request): Response
    {
        $filters = [
            'date_from' => $request->query('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->query('date_to', now()->format('Y-m-d')),
            'academic_year_id' => $request->query('academic_year_id'),
            'college_id' => $request->query('college_id'),
            'patient_classification' => $request->query('patient_classification'),
        ];

        $reportData = $this->aggregationService->aggregateClinicReport($filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->clinicMorbidityReport($reportData, $settings);
        $filename = 'NVC-Clinic-Morbidity-Report-'.now()->format('Ymd').'.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
