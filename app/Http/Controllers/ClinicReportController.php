<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\College;
use App\Services\DataAggregationAlgorithmService;
use App\Services\Pdf\MedicalDocumentPdfService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
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

    public function index(Request $request): View|JsonResponse
    {
        $filters = $this->extractFilters($request);

        $reportData = $this->aggregationService->aggregateClinicReport($filters);

        if ($request->ajax() || $request->wantsJson()) {
            $cleanQuery = array_filter($filters, fn ($v) => $v !== null && $v !== '');

            return response()->json([
                'html' => view('reports.partials.clinic-content', compact('reportData'))->render(),
                'pdf_url' => route('reports.clinic.print-pdf', $cleanQuery),
                'pdf_medical_dental_url' => route('reports.clinic.print-medical-dental-records', $cleanQuery),
                'pdf_login_url' => route('reports.clinic.print-login-records', $cleanQuery),
                'pdf_communicable_url' => route('reports.clinic.print-communicable-diseases', $cleanQuery),
                'pdf_non_communicable_url' => route('reports.clinic.print-non-communicable-diseases', $cleanQuery),
                'summary' => $reportData['summary'],
            ]);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.clinic', compact('reportData', 'filters', 'academicYears', 'colleges'));
    }

    public function medicalDentalRecords(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $medDental = $this->aggregationService->aggregateMedicalAndDentalRecords($year, $filters);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.medical-dental-records', compact('medDental', 'filters', 'academicYears', 'colleges'));
    }

    public function loginRecords(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $loginRec = $this->aggregationService->aggregateMedicalAndDentalLoginRecords($year, $filters);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.login-records', compact('loginRec', 'filters', 'academicYears', 'colleges'));
    }

    public function communicableDiseases(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $commChart = $this->aggregationService->aggregateCommunicableDiseaseReport($year, $filters);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.communicable-diseases', compact('commChart', 'filters', 'academicYears', 'colleges'));
    }

    public function nonCommunicableDiseases(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $nonCommChart = $this->aggregationService->aggregateNonCommunicableDiseaseReport($year, $filters);
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $colleges = College::orderBy('name')->get();

        return view('reports.non-communicable-diseases', compact('nonCommChart', 'filters', 'academicYears', 'colleges'));
    }

    public function printPdf(Request $request): Response
    {
        $filters = $this->extractFilters($request);

        $reportData = $this->aggregationService->aggregateClinicReport($filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->clinicMorbidityReport($reportData, $settings);
        $filename = 'NVC-Clinic-Morbidity-Report-'.now()->format('Ymd').'.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printMedicalDentalRecords(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $matrix = $this->aggregationService->aggregateMedicalAndDentalRecords($year, $filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->medicalAndDentalRecordsReport($matrix, $settings);
        $filename = "Medical-and-Dental-Records-{$year}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printLoginRecords(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $loginData = $this->aggregationService->aggregateMedicalAndDentalLoginRecords($year, $filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->medicalAndDentalLoginRecordsReport($loginData, $settings);
        $filename = "Medical-and-Dental-Login-Records-{$year}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printCommunicableDiseases(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $chartData = $this->aggregationService->aggregateCommunicableDiseaseReport($year, $filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->communicableDiseasesReport($chartData, $settings);
        $filename = "Communicable-Diseases-{$year}-Data.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function printNonCommunicableDiseases(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $year = (int) ($filters['year'] ?: now()->year);

        $chartData = $this->aggregationService->aggregateNonCommunicableDiseaseReport($year, $filters);
        $settings = $this->settingService->all();

        $pdf = $this->pdfService->nonCommunicableDiseasesReport($chartData, $settings);
        $filename = "Non-Communicable-Diseases-{$year}-Data.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    protected function extractFilters(Request $request): array
    {
        $dateTo = (string) $request->query('date_to', now()->format('Y-m-d'));
        $defaultYear = (int) substr($dateTo, 0, 4) ?: (int) now()->year;

        return [
            'year' => (int) $request->query('year', $defaultYear),
            'date_from' => (string) $request->query('date_from', now()->startOfYear()->format('Y-m-d')),
            'date_to' => $dateTo,
            'academic_year_id' => $request->query('academic_year_id'),
            'college_id' => $request->query('college_id'),
            'patient_classification' => $request->query('patient_classification'),
        ];
    }
}
