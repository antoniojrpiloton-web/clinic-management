<?php

namespace App\Services\Pdf;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\MedicineDispensing;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;

class MedicalDocumentPdfService
{
    protected function defaultAddress(array $settings): string
    {
        return (string) ($settings['university_campus'] ?? ($settings['university_address'] ?? 'Surigao City'));
    }

    protected function resolvePhysicianName(array $settings, ?Clinician $physician = null, ?string $examinerName = null): string
    {
        if (! empty($settings['clinic_head'])) {
            return (string) $settings['clinic_head'];
        }

        return (string) ($physician?->name ?: ($examinerName ?: 'Dr. Maria Elena Santos, MD'));
    }

    protected function resolvePhysicianLicense(array $settings, ?Clinician $physician = null, ?string $examinerLicense = null): string
    {
        if (! empty($settings['clinic_license_no'])) {
            return (string) $settings['clinic_license_no'];
        }

        return (string) ($physician?->license_number ?: ($examinerLicense ?: 'DOH-R13-CLN-2026-088'));
    }

    public function medicalCertificate(PhysicalExamination $exam, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $exam->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 4- Personal Copy', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(46);
        $doc->SetX(128);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(11, 5.5, 'Date:', 0, 0, 'L');
        $doc->drawInlineBlank($exam->exam_date->format('F d, Y'), 47, 'C', true);
        $doc->Ln(12);

        $doc->SetFont('Arial', 'B', 13);
        $doc->Cell(0, 6, 'MEDICAL CERTIFICATE', 0, 1, 'C');
        $doc->Ln(8);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'To Whom It May Concern:', 0, 1, 'L');
        $doc->Ln(6);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(35, 5.5, 'This is to certify that ', 0, 0, 'L');
        $doc->drawInlineBlank($patient->full_name, 98, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(3, 5.5, ', ', 0, 0, 'L');
        $doc->drawInlineBlank((string) ($patient->age ?? ''), 14, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(16, 5.5, ' years', 0, 1, 'L');
        $doc->Ln(3.5);

        $genderLabel = 'old, Male/Female and a resident of ';
        $doc->SetFont('Arial', '', 10);
        $xStart = $doc->GetX();
        $yStart = $doc->GetY();
        $doc->Cell(57, 5.5, $genderLabel, 0, 0, 'L');

        $gender = strtolower((string) ($patient->gender ?? ''));
        if ($gender === 'male') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 7.2, $yStart + 5.1, $xStart + 15.2, $yStart + 5.1);
        } elseif ($gender === 'female') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 16.5, $yStart + 5.1, $xStart + 28.5, $yStart + 5.1);
        }

        $doc->drawInlineBlank($patient->address ?: $this->defaultAddress($settings), 109, 'C', true);
        $doc->Ln(9);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'has been seen and examined by the undersigned .', 0, 1, 'L');
        $doc->Ln(4);

        $findings = $exam->clinical_findings ?: ($exam->medical_recommendations ?: '');
        $afterRuledY = $doc->drawRuledLines(22, $doc->GetY(), 166, 2, 9.5, $findings);
        $doc->SetY($afterRuledY + 10);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'He/ She is', 0, 1, 'L');
        $doc->Ln(2);

        $acts = (array) ($exam->fitness_activities ?? []);
        $items = [
            'physical_activities' => 'to undergo Physical Activities/ Trainings',
            'on_the_job_training' => 'On-the-Job Training',
            'work_immersion' => 'Work Immersion',
            'to_work' => 'to Work',
            'to_travel' => 'to Travel',
            'other_requirements' => 'other Requirements',
        ];

        foreach ($items as $key => $label) {
            $rowY = $doc->GetY();
            $val = $acts[$key] ?? 'fit';
            $isFit = $val === 'fit';
            $isUnfit = $val === 'unfit';

            $doc->drawOvalCheckItem(22, $rowY, $isFit, 'Fit');
            $doc->drawOvalCheckItem(40, $rowY, $isUnfit, 'Unfit');

            $doc->SetXY(59, $rowY);
            $doc->SetFont('Arial', 'B', 10);
            $doc->Cell(0, 5, $doc->conv($label), 0, 1, 'L');
            $doc->Ln(3);
        }

        $doc->Ln(6);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'This certificate is being issued upon the request of the above person for whatever purpose it may', 0, 1, 'L');
        $doc->Ln(1);
        $doc->Cell(0, 5.5, 'serve. This certificate is not intended for use in legal matters.', 0, 1, 'L');

        $physicianName = $this->resolvePhysicianName($settings, $physician, $exam->examiner_name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $exam->examiner_license_number);

        $doc->drawHsdSignatureBlock($physicianName, 'Attending Physician', $license, 122, 236);

        return $doc;
    }

    public function medicalExemption(PhysicalExamination $exam, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $exam->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 6- Medical Exemption Certificate', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(48);
        $doc->SetFont('Arial', 'B', 13);
        $doc->Cell(0, 6, 'MEDICAL EXEMPTION CERTIFICATE', 0, 1, 'C');
        $doc->Ln(6);

        $doc->SetX(128);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(11, 5.5, 'Date:', 0, 0, 'L');
        $doc->drawInlineBlank($exam->exam_date->format('F d, Y'), 47, 'C', true);
        $doc->Ln(7);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'To whom it may concern:', 0, 1, 'L');
        $doc->Ln(5);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(57, 5.5, 'I hereby certify that I have examined ', 0, 0, 'L');
        $doc->drawInlineBlank($patient->full_name, 81, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(3, 5.5, ', ', 0, 0, 'L');
        $doc->drawInlineBlank((string) ($patient->age ?? ''), 12, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(13, 5.5, ' years', 0, 1, 'L');
        $doc->Ln(3.5);

        $xStart = $doc->GetX();
        $yStart = $doc->GetY();
        $doc->Cell(51, 5.5, 'old, Male/Female, a resident of ', 0, 0, 'L');

        $gender = strtolower((string) ($patient->gender ?? ''));
        if ($gender === 'male') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 7.2, $yStart + 5.1, $xStart + 15.2, $yStart + 5.1);
        } elseif ($gender === 'female') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 16.5, $yStart + 5.1, $xStart + 28.5, $yStart + 5.1);
        }

        $doc->drawInlineBlank($patient->address ?: $this->defaultAddress($settings), 112, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(3, 5.5, ',', 0, 1, 'L');
        $doc->Ln(3.5);

        $course = $patient->program?->code ?? ($patient->college?->code ?? 'College');
        $doc->drawInlineBlank($course, 64, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, ' student and found them to have a medical condition that requires', 0, 1, 'L');
        $doc->Ln(3.5);

        $doc->Cell(0, 5.5, 'them to be temporarily excused from certain school activities/can join activities but with limitation.', 0, 1, 'L');
        $doc->Ln(7);

        $doc->SetFont('Arial', 'B', 10);
        $doc->Cell(0, 5.5, 'Medical Condition:', 0, 1, 'L');
        $condition = $exam->clinical_findings ?: ($patient->medicalProfile?->existing_conditions ?: '');
        $afterCondY = $doc->drawRuledLines(22, $doc->GetY(), 166, 3, 8.5, $condition);
        $doc->SetY($afterCondY + 7);

        $doc->SetFont('Arial', 'B', 10);
        $doc->Cell(0, 5.5, 'Exempted Activities:', 0, 1, 'L');
        $exempted = $exam->medical_recommendations ?: '';
        $afterExY = $doc->drawRuledLines(22, $doc->GetY(), 166, 3, 8.5, $exempted);
        $doc->SetY($afterExY + 9);

        $startDate = $exam->exam_date->format('M d, Y');
        $endDate = $exam->exam_date->copy()->addMonths(1)->format('M d, Y');

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(48, 5.5, 'The exemption is effective from ', 0, 0, 'L');
        $doc->drawInlineBlank($startDate, 58, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(10, 5.5, ' until ', 0, 0, 'L');
        $doc->drawInlineBlank($endDate, 48, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(2, 5.5, '.', 0, 1, 'L');
        $doc->Ln(6);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, "I certify that the information provided is accurate and medically advised for the student's health and safety.", 0, 1, 'L');

        $physicianName = $this->resolvePhysicianName($settings, $physician, $exam->examiner_name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $exam->examiner_license_number);

        $doc->drawHsdSignatureBlock($physicianName, 'School Physician', $license, 122, 238);

        return $doc;
    }

    public function medicalReferral(PhysicalExamination $exam, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $exam->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 3- Referral Form', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $physicianName = $this->resolvePhysicianName($settings, $physician, $exam->examiner_name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $exam->examiner_license_number);

        $findings = $exam->clinical_findings ?: '';
        if ($exam->blood_pressure || $exam->pulse_rate || $exam->temperature) {
            $vitals = 'Vitals: BP '.($exam->blood_pressure ?: '—').' | PR '.($exam->pulse_rate ?: '—').' bpm | Temp '.($exam->temperature ?: '—').' C';
            $findings = trim($findings."\n".$vitals);
        }

        $this->renderHsdForm3Layout(
            $doc,
            $exam->exam_date->format('F d, Y'),
            'Attending Medical Specialist / Provincial Hospital',
            $patient->full_name,
            $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : '',
            (string) ($patient->age ?? ''),
            (string) ($patient->gender ?? ''),
            (string) ($patient->address ?: $this->defaultAddress($settings)),
            (string) ($patient->phone ?: ''),
            (string) ($patient->emergency_contact_name ?: ''),
            (string) ($patient->emergency_contact_phone ?: ''),
            $findings,
            (string) ($exam->medical_recommendations ?: ''),
            'For comprehensive clinical work-up, diagnostic evaluation, and medical clearance.',
            $physicianName,
            $license
        );

        return $doc;
    }

    public function medicalCertificateFromVisit(ClinicVisit $visit, array $settings): ClinicFpdf
    {
        $patient = $visit->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 4- Personal Copy', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(46);
        $doc->SetX(128);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(11, 5.5, 'Date:', 0, 0, 'L');
        $doc->drawInlineBlank($visit->visit_date->format('F d, Y'), 47, 'C', true);
        $doc->Ln(12);

        $doc->SetFont('Arial', 'B', 13);
        $doc->Cell(0, 6, 'MEDICAL CERTIFICATE', 0, 1, 'C');
        $doc->Ln(8);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'To Whom It May Concern:', 0, 1, 'L');
        $doc->Ln(6);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(35, 5.5, 'This is to certify that ', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->full_name ?? '', 98, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(3, 5.5, ', ', 0, 0, 'L');
        $doc->drawInlineBlank((string) ($patient?->age ?? ''), 14, 'C', true);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(16, 5.5, ' years', 0, 1, 'L');
        $doc->Ln(3.5);

        $xStart = $doc->GetX();
        $yStart = $doc->GetY();
        $doc->Cell(57, 5.5, 'old, Male/Female and a resident of ', 0, 0, 'L');

        $gender = strtolower((string) ($patient?->gender ?? ''));
        if ($gender === 'male') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 7.2, $yStart + 5.1, $xStart + 15.2, $yStart + 5.1);
        } elseif ($gender === 'female') {
            $doc->SetLineWidth(0.35);
            $doc->Line($xStart + 16.5, $yStart + 5.1, $xStart + 28.5, $yStart + 5.1);
        }

        $doc->drawInlineBlank($patient?->address ?: $this->defaultAddress($settings), 109, 'C', true);
        $doc->Ln(9);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'has been seen and examined by the undersigned .', 0, 1, 'L');
        $doc->Ln(4);

        $dx = $visit->medicalConsultation?->diagnosis ?: ($visit->chief_complaint ?: '');
        $rest = $visit->medicalConsultation?->rest_days;
        $findings = $rest ? "{$dx}. Advised {$rest} day(s) medical rest." : $dx;

        $afterRuledY = $doc->drawRuledLines(22, $doc->GetY(), 166, 2, 9.5, $findings);
        $doc->SetY($afterRuledY + 10);

        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'He/ She is', 0, 1, 'L');
        $doc->Ln(2);

        $items = [
            'to undergo Physical Activities/ Trainings',
            'On-the-Job Training',
            'Work Immersion',
            'to Work',
            'to Travel',
            'other Requirements',
        ];

        $isUnfitForRest = (int) $rest > 0;

        foreach ($items as $label) {
            $rowY = $doc->GetY();
            $doc->drawOvalCheckItem(22, $rowY, ! $isUnfitForRest, 'Fit');
            $doc->drawOvalCheckItem(40, $rowY, $isUnfitForRest, 'Unfit');

            $doc->SetXY(59, $rowY);
            $doc->SetFont('Arial', 'B', 10);
            $doc->Cell(0, 5, $doc->conv($label), 0, 1, 'L');
            $doc->Ln(3);
        }

        $doc->Ln(6);
        $doc->SetFont('Arial', '', 10);
        $doc->Cell(0, 5.5, 'This certificate is being issued upon the request of the above person for whatever purpose it may', 0, 1, 'L');
        $doc->Ln(1);
        $doc->Cell(0, 5.5, 'serve. This certificate is not intended for use in legal matters.', 0, 1, 'L');

        $clinician = $visit->clinician;
        $physicianName = $this->resolvePhysicianName($settings, $clinician);
        $license = $this->resolvePhysicianLicense($settings, $clinician);

        $doc->drawHsdSignatureBlock($physicianName, 'Attending Physician', $license, 122, 236);

        return $doc;
    }

    public function medicalReferralFromVisit(ClinicVisit $visit, array $settings): ClinicFpdf
    {
        $patient = $visit->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 3- Referral Form', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $referredTo = $visit->medicalConsultation?->referred_to
            ?? ($visit->emergencyRecord?->transferred_to_hospital ?? '');

        $complaints = trim(($visit->chief_complaint ?: '').($visit->summary ? ' - '.$visit->summary : ''));
        $treatment = $visit->medicalConsultation?->treatment_plan
            ?? ($visit->firstAidRecord?->treatment_notes ?? '');
        $reason = $visit->medicalConsultation?->referral_reason
            ?? ($visit->emergencyRecord?->transfer_notes ?? '');

        $clinician = $visit->clinician;
        $physicianName = $this->resolvePhysicianName($settings, $clinician);
        $license = $this->resolvePhysicianLicense($settings, $clinician);

        $this->renderHsdForm3Layout(
            $doc,
            $visit->visit_date->format('F d, Y'),
            $referredTo,
            $patient?->full_name ?? '',
            $patient?->date_of_birth ? $patient->date_of_birth->format('M d, Y') : '',
            (string) ($patient?->age ?? ''),
            (string) ($patient?->gender ?? ''),
            (string) ($patient?->address ?: $this->defaultAddress($settings)),
            (string) ($patient?->phone ?: ''),
            (string) ($patient?->emergency_contact_name ?: ''),
            (string) ($patient?->emergency_contact_phone ?: ''),
            $complaints,
            $treatment,
            $reason,
            $physicianName,
            $license
        );

        return $doc;
    }

    protected function renderHsdForm3Layout(
        ClinicFpdf $doc,
        string $date,
        string $referTo,
        string $patientName,
        string $dob,
        string $age,
        string $gender,
        string $address,
        string $phone,
        string $emergencyName,
        string $emergencyPhone,
        string $complaints,
        string $treatment,
        string $reasons,
        string $physicianName,
        string $license
    ): void {
        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12.5);
        $doc->Cell(0, 6, 'MEDICAL REFERRAL FORM', 0, 1, 'C');
        $doc->Ln(2);

        $x = 20;
        $w = 170;
        $y = $doc->GetY();
        $doc->SetLineWidth(0.25);

        $doc->Rect($x, $y, $w, 6.5);
        $doc->SetXY($x + 1.5, $y + 1);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(12, 4.5, 'Date:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($date), 0, 1, 'L');
        $y += 6.5;

        $doc->Rect($x, $y, $w, 20);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(18, 4.5, 'Refer to:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->SetXY($x + 20, $y + 1.2);
        $doc->MultiCell($w - 22, 4.5, $doc->conv($referTo), 0, 'L');
        $y += 20;

        $doc->Rect($x, $y, $w, 6);
        $doc->SetXY($x + 1.5, $y + 0.8);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(0, 4.5, 'Patient Information:', 0, 1, 'L');
        $y += 6;

        $doc->Rect($x, $y, $w, 9);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(14, 4.5, 'Name:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($patientName), 0, 1, 'L');
        $y += 9;

        $doc->Rect($x, $y, $w, 8.5);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(25, 4.5, 'Date of Birth:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(65, 4.5, $doc->conv($dob), 0, 0, 'L');
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(10, 4.5, 'Age:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(25, 4.5, $doc->conv($age), 0, 0, 'L');
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(15, 4.5, 'Gender:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($gender), 0, 1, 'L');
        $y += 8.5;

        $doc->Rect($x, $y, $w, 10);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(17, 4.5, 'Address:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(73, 4.5, $doc->conv($address), 0, 0, 'L');
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(30, 4.5, 'Contact Number:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($phone), 0, 1, 'L');
        $y += 10;

        $doc->Rect($x, $y, $w, 6);
        $doc->SetXY($x + 1.5, $y + 0.8);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(0, 4.5, 'In case of Emergency:', 0, 1, 'L');
        $y += 6;

        $doc->Rect($x, $y, $w, 15);
        $doc->SetXY($x + 6, $y + 1.5);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(28, 4.5, 'Contact Name:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($emergencyName), 0, 1, 'L');

        $doc->SetXY($x + 6, $y + 8.5);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(30, 4.5, 'Contact Number:', 0, 0, 'L');
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(0, 4.5, $doc->conv($emergencyPhone), 0, 1, 'L');
        $y += 15;

        $doc->Rect($x, $y, $w, 30);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(0, 4.5, 'Complaints/ Findings:', 0, 1, 'L');
        if ($complaints !== '') {
            $doc->SetXY($x + 2, $y + 6.5);
            $doc->SetFont('Arial', '', 9);
            $doc->MultiCell($w - 4, 4.2, $doc->conv($complaints), 0, 'L');
        }
        $y += 30;

        $doc->Rect($x, $y, $w, 24);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(0, 4.5, 'Treatment:', 0, 1, 'L');
        if ($treatment !== '') {
            $doc->SetXY($x + 2, $y + 6.5);
            $doc->SetFont('Arial', '', 9);
            $doc->MultiCell($w - 4, 4.2, $doc->conv($treatment), 0, 'L');
        }
        $y += 24;

        $doc->Rect($x, $y, $w, 28);
        $doc->SetXY($x + 1.5, $y + 1.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(0, 4.5, 'Reasons for Referral:', 0, 1, 'L');
        if ($reasons !== '') {
            $doc->SetXY($x + 2, $y + 6.5);
            $doc->SetFont('Arial', '', 9);
            $doc->MultiCell($w - 4, 4.2, $doc->conv($reasons), 0, 'L');
        }
        $y += 28;

        $doc->Rect($x, $y, $w, 20);
        $doc->SetXY($x + 14, $y + 9.5);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell(60, 4.2, $doc->conv(strtoupper($physicianName)), 0, 1, 'C');
        $doc->SetX($x + 14);
        $doc->SetFont('Arial', '', 9);
        $doc->Cell(60, 4, 'School Physician', 0, 1, 'C');
        if ($license !== '') {
            $doc->SetX($x + 14);
            $doc->SetFont('Arial', '', 8);
            $doc->Cell(60, 3.5, $doc->conv("License #: {$license}"), 0, 1, 'C');
        }
    }

    public function clinicVisitSlip(ClinicVisit $visit, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $visit->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 5- Consultation Form', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(43);
        $doc->SetFont('Arial', 'B', 11);
        $doc->Cell(0, 5, 'CONSULTATION FORM', 0, 1, 'C');
        $doc->Ln(3);

        $x = 20;
        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(23, 5.5, 'Patient Name:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->full_name ?? '', 95, 'L', true);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(19, 5.5, ' Civil Status:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->civil_status ?? '', 33, 'C', false);
        $doc->Ln(7);

        $courseOrOcc = $patient?->type === 'student'
            ? trim(($patient->program?->code ?? '').' '.($patient->year_level ?? ''))
            : ($patient?->position ?? ($visit->classification_label ?: ''));

        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(9, 5.5, 'Age:', 0, 0, 'L');
        $doc->drawInlineBlank((string) ($patient?->age ?? ''), 22, 'C', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(31, 5.5, ' Course/Occupation:', 0, 0, 'L');
        $doc->drawInlineBlank($courseOrOcc, 72, 'L', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(9, 5.5, ' Sex:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->gender ?? '', 27, 'C', false);
        $doc->Ln(7);

        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(15, 5.5, 'Address:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->address ?: $this->defaultAddress($settings), 105, 'L', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(14, 5.5, ' Cel. No.', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->phone ?? '', 36, 'C', false);
        $doc->Ln(8);

        $col1 = 28;
        $col2 = 88;
        $col3 = 54;
        $tableW = $col1 + $col2 + $col3;
        $tableStartY = $doc->GetY();
        $headerH = 6.5;
        $rowH = 8.5;
        $totalRows = 20;

        $doc->SetLineWidth(0.25);
        $doc->SetX($x);
        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($col1, $headerH, 'DATE', 1, 0, 'C');
        $doc->Cell($col2, $headerH, 'COMPLAINTS/ FINDINGS', 1, 0, 'C');
        $doc->Cell($col3, $headerH, 'TREATMENT', 1, 1, 'C');

        $visitsToRender = collect([$visit]);
        if ($patient) {
            $otherVisits = ClinicVisit::with(['medicalConsultation', 'dentalConsultation', 'emergencyRecord', 'firstAidRecord'])
                ->where('patient_id', $patient->id)
                ->where('id', '!=', $visit->id)
                ->orderBy('visit_date')
                ->limit(19)
                ->get();
            $visitsToRender = $visitsToRender->concat($otherVisits);
        }

        $entries = [];
        foreach ($visitsToRender as $vItem) {
            $vitalsParts = array_filter([
                $vItem->blood_pressure ? "BP {$vItem->blood_pressure}" : null,
                $vItem->temperature ? "T {$vItem->temperature}C" : null,
                $vItem->pulse_rate ? "PR {$vItem->pulse_rate}" : null,
            ]);
            $vitalsStr = $vitalsParts ? ' ['.implode(', ', $vitalsParts).']' : '';
            $complaintText = trim("[{$vItem->visit_number}] ".($vItem->chief_complaint ?: '').($vItem->summary ? ' / '.$vItem->summary : '').$vitalsStr);

            $treatmentText = $vItem->medicalConsultation?->treatment_plan
                ?? ($vItem->medicalConsultation?->medications_prescribed
                ?? ($vItem->firstAidRecord?->treatment_notes
                ?? ($vItem->remarks ?: $vItem->disposition_label)));

            $entries[] = [
                'date' => $vItem->visit_date->format('m/d/Y'),
                'complaints' => $complaintText,
                'treatment' => (string) $treatmentText,
            ];
        }

        for ($r = 0; $r < $totalRows; $r++) {
            $ry = $tableStartY + $headerH + ($r * $rowH);
            $doc->Rect($x, $ry, $col1, $rowH);
            $doc->Rect($x + $col1, $ry, $col2, $rowH);
            $doc->Rect($x + $col1 + $col2, $ry, $col3, $rowH);

            if (isset($entries[$r])) {
                $doc->SetFont('Arial', '', 8);
                $doc->SetXY($x + 1, $ry + 2);
                $doc->Cell($col1 - 2, 4.5, $doc->conv($entries[$r]['date']), 0, 0, 'C');

                $doc->SetXY($x + $col1 + 1.5, $ry + 2);
                $doc->Cell($col2 - 3, 4.5, $doc->conv(mb_strimwidth($entries[$r]['complaints'], 0, 62, '...')), 0, 0, 'L');

                $doc->SetXY($x + $col1 + $col2 + 1.5, $ry + 2);
                $doc->Cell($col3 - 3, 4.5, $doc->conv(mb_strimwidth($entries[$r]['treatment'], 0, 36, '...')), 0, 0, 'L');
            }
        }

        return $doc;
    }

    public function physicalExamination(PhysicalExamination $exam, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $exam->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 1- Physical Examination Form', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'PHYSICAL EXAMINATION RECORD', 0, 1, 'C');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(55, 5, 'PE No: '.$exam->pe_number, 0, 0, 'L');
        $doc->Cell(56, 5, 'Purpose: '.$exam->purpose, 0, 0, 'C');
        $doc->Cell(55, 5, 'Date: '.$exam->exam_date->format('M d, Y'), 0, 1, 'R');
        $doc->Ln(1);

        $doc->SetFont('Arial', 'B', 9);
        $doc->SetFillColor(235, 238, 242);
        $doc->Cell(166, 5, ' I. PATIENT INFORMATION', 1, 1, 'L', true);

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(32, 5, 'Full Name:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(68, 5, $doc->conv($patient->full_name), 1, 0, 'L');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(32, 5, 'ID / Type:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(34, 5, $doc->conv($patient->primary_identifier.' ('.ucfirst($patient->type).')'), 1, 1, 'L');

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(32, 5, 'College / Program:', 1, 0, 'L');
        $doc->Cell(68, 5, $doc->conv(($patient->college?->code ?: '—').' - '.($patient->program?->code ?: '—')), 1, 0, 'L');
        $doc->Cell(32, 5, 'Sex / Age:', 1, 0, 'L');
        $doc->Cell(34, 5, $doc->conv(($patient->gender ?: '—').' / '.($patient->age ? "{$patient->age} y/o" : '—')), 1, 1, 'L');

        $doc->Cell(32, 5, 'Address & Phone:', 1, 0, 'L');
        $doc->Cell(68, 5, $doc->conv(($patient->address ?: $this->defaultAddress($settings)).' | '.($patient->phone ?: '—')), 1, 0, 'L');
        $doc->Cell(32, 5, 'Blood / Civil:', 1, 0, 'L');
        $doc->Cell(34, 5, $doc->conv(($patient->blood_type ?: 'O+').' / '.($patient->civil_status ?: 'Single')), 1, 1, 'L');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(166, 5, ' II. VITAL SIGNS & ANTHROPOMETRIC MEASUREMENTS', 1, 1, 'L', true);
        $doc->SetFont('Arial', '', 8.5);

        $bp = $exam->blood_pressure ?: '—';
        $pr = $exam->pulse_rate ? "{$exam->pulse_rate} bpm" : '—';
        $rr = $exam->respiratory_rate ? "{$exam->respiratory_rate} cpm" : '—';
        $temp = $exam->temperature ? "{$exam->temperature} C" : '—';
        $spo2 = $exam->oxygen_saturation ? "{$exam->oxygen_saturation}%" : '—';

        $doc->Cell(33.2, 5, "BP: {$bp}", 1, 0, 'C');
        $doc->Cell(33.2, 5, "Pulse: {$pr}", 1, 0, 'C');
        $doc->Cell(33.2, 5, "Resp: {$rr}", 1, 0, 'C');
        $doc->Cell(33.2, 5, "Temp: {$temp}", 1, 0, 'C');
        $doc->Cell(33.2, 5, "SpO2: {$spo2}", 1, 1, 'C');

        $ht = $exam->height_cm ? "{$exam->height_cm} cm" : '—';
        $wt = $exam->weight_kg ? "{$exam->weight_kg} kg" : '—';
        $bmi = $exam->bmi ? "{$exam->bmi} ({$exam->bmi_category})" : '—';
        $od = $exam->visual_acuity_right ?: '20/20';
        $os = $exam->visual_acuity_left ?: '20/20';

        $doc->Cell(33.2, 5, "Height: {$ht}", 1, 0, 'C');
        $doc->Cell(33.2, 5, "Weight: {$wt}", 1, 0, 'C');
        $doc->Cell(49.8, 5, "BMI: {$bmi}", 1, 0, 'C');
        $doc->Cell(49.8, 5, "Vision: R {$od} / L {$os}", 1, 1, 'C');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(166, 5, ' III. SYSTEMIC PHYSICAL EXAMINATION FINDINGS', 1, 1, 'L', true);
        $doc->SetFont('Arial', '', 8);

        $sys = (array) ($exam->systems ?? []);
        $systemsList = [
            'General Appearance' => 'Well-nourished, alert, oriented',
            'Skin & Integumentary' => 'No suspicious lesions, jaundice, or cyanosis',
            'Head, Eyes, Ears, Nose' => 'Sclera anicteric, pupils equal and reactive',
            'Mouth, Teeth & Throat' => 'Good oral hygiene, no tonsillopharyngeal congestion',
            'Neck & Thyroid' => 'Supple, no palpable lymphadenopathy or goiter',
            'Chest & Lungs' => 'Clear vesicular breath sounds, no rales or wheezes',
            'Cardiovascular System' => 'Normal rate, regular rhythm, no cardiac murmurs',
            'Abdomen' => 'Soft, non-tender, no organomegaly',
            'Musculoskeletal & Spine' => 'Full range of motion, no deformities',
            'Neurological' => 'Grossly intact cranial nerves and reflexes',
        ];

        foreach ($systemsList as $name => $defaultDesc) {
            $status = $sys[$name]['status'] ?? 'normal';
            $remarks = $sys[$name]['remarks'] ?? $defaultDesc;

            $doc->SetFont('Arial', 'B', 8);
            $doc->Cell(46, 4.5, $doc->conv($name), 1, 0, 'L');
            $doc->SetFont('Arial', '', 8);
            $doc->Cell(22, 4.5, ucfirst($status), 1, 0, 'C');
            $doc->Cell(98, 4.5, $doc->conv($remarks), 1, 1, 'L');
        }
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(166, 5, ' IV. DIAGNOSTIC SCREENING & OVERALL FITNESS ASSESSMENT', 1, 1, 'L', true);
        $doc->SetFont('Arial', '', 8.5);
        $cxr = $exam->chest_xray_findings ?: 'Chest X-Ray: Normal lung fields.';
        $lab = $exam->laboratory_notes ?: 'Routine laboratory tests within normal range.';
        $doc->MultiCell(166, 4.5, $doc->conv("{$cxr} | {$lab}"), 1, 'L');

        $fitness = $exam->fitness_assessment ?: 'Class A';
        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(46, 5.5, "Clearance: {$fitness}", 1, 0, 'C');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(120, 5.5, $doc->conv($exam->clinical_findings ?: 'Fit for academic and university activities.'), 1, 1, 'L');

        $physicianName = $this->resolvePhysicianName($settings, $physician, $exam->examiner_name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $exam->examiner_license_number);

        $doc->drawHsdSignatureBlock($physicianName, 'School Physician', $license, 122, 240);

        return $doc;
    }

    public function medicineDispensingSlip(MedicineDispensing $dispensing, array $settings, ?Clinician $physician = null): ClinicFpdf
    {
        $patient = $dispensing->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 7- Pharmacy Dispensing Slip', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'CLINIC PHARMACY DISPENSING SLIP', 0, 1, 'C');
        $doc->Ln(3);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(60, 5, 'Slip No: '.$dispensing->dispensing_number, 0, 0, 'L');
        $doc->Cell(50, 5, 'Total Items: '.$dispensing->total_items, 0, 0, 'C');
        $doc->Cell(56, 5, 'Date: '.$dispensing->dispensed_date->format('M d, Y h:i A'), 0, 1, 'R');
        $doc->Ln(2);

        $doc->SetFillColor(235, 238, 242);
        $doc->Cell(166, 5.5, ' 1. PATIENT & PRESCRIBING CLINICIAN', 1, 1, 'L', true);
        $doc->SetFont('Arial', '', 8.5);

        $name = $patient?->full_name ?? 'Walk-in Patient';
        $idNo = $patient?->primary_identifier ?? 'N/A';
        $prog = $patient?->program?->code ?? ($patient?->type === 'student' ? 'Student' : 'Employee');
        $prescriber = $dispensing->clinician?->name ?? $this->resolvePhysicianName($settings, $physician);

        $doc->Cell(35, 5, 'Patient Name:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(71, 5, $doc->conv($name), 1, 0, 'L');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(28, 5, 'ID Number:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(32, 5, $doc->conv($idNo), 1, 1, 'L');

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(35, 5, 'Course / Dept:', 1, 0, 'L');
        $doc->Cell(71, 5, $doc->conv($prog), 1, 0, 'L');
        $doc->Cell(28, 5, 'Prescriber:', 1, 0, 'L');
        $doc->Cell(32, 5, $doc->conv($prescriber), 1, 1, 'L');

        $doc->Cell(35, 5, 'Diagnosis:', 1, 0, 'L');
        $doc->Cell(131, 5, $doc->conv($dispensing->diagnosis ?: 'Symptomatic medical relief'), 1, 1, 'L');
        $doc->Ln(3);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(166, 5.5, ' 2. DISPENSED MEDICINES & INSTRUCTIONS', 1, 1, 'L', true);
        $doc->SetFont('Arial', 'B', 8);

        $doc->Cell(10, 5, '#', 1, 0, 'C');
        $doc->Cell(64, 5, 'Medicine / Generic (Brand)', 1, 0, 'L');
        $doc->Cell(25, 5, 'Strength', 1, 0, 'C');
        $doc->Cell(18, 5, 'Qty', 1, 0, 'C');
        $doc->Cell(49, 5, 'Instructions / Sig', 1, 1, 'L');

        $doc->SetFont('Arial', '', 8);
        $dispensing->loadMissing(['items.medicine']);
        $idx = 1;
        foreach ($dispensing->items as $item) {
            $med = $item->medicine;
            $medName = $med ? "{$med->generic_name} ({$med->brand_name})" : 'Medicine';
            $strength = $med ? "{$med->strength} {$med->dosage_form}" : '—';
            $qty = "{$item->quantity} {$med?->unit}";
            $sig = $item->instructions ?: ($dispensing->instructions ?: 'Take as directed');

            $doc->Cell(10, 5, (string) $idx++, 1, 0, 'C');
            $doc->Cell(64, 5, $doc->conv($medName), 1, 0, 'L');
            $doc->Cell(25, 5, $doc->conv($strength), 1, 0, 'C');
            $doc->Cell(18, 5, $doc->conv($qty), 1, 0, 'C');
            $doc->Cell(49, 5, $doc->conv($sig), 1, 1, 'L');
        }

        $physicianName = $this->resolvePhysicianName($settings, $physician, $dispensing->clinician?->name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $dispensing->clinician?->license_number);

        $doc->drawHsdSignatureBlock($physicianName, 'Attending Physician', $license, 122, 236);

        return $doc;
    }

    public function physicalExamScheduleRoster(PhysicalExamSchedule $schedule, array $settings): ClinicFpdf
    {
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 8- PE Screening Roster', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'PHYSICAL EXAMINATION SCREENING ROSTER', 0, 1, 'C');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(83, 5, 'Campaign: '.$doc->conv($schedule->title), 0, 0, 'L');
        $doc->Cell(83, 5, 'Schedule No: '.$schedule->schedule_number, 0, 1, 'R');

        $dateFormatted = $schedule->scheduled_date->format('F d, Y');
        $timeWindow = "{$schedule->start_time} - {$schedule->end_time}";
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(83, 4.5, "Date & Time: {$dateFormatted} ({$timeWindow})", 0, 0, 'L');
        $doc->Cell(83, 4.5, $doc->conv("Venue: {$schedule->venue} | Capacity: {$schedule->capacity}"), 0, 1, 'R');
        $doc->Ln(2);

        $doc->SetFillColor(235, 238, 242);
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(10, 5, '#', 1, 0, 'C', true);
        $doc->Cell(60, 5, 'Patient Name', 1, 0, 'L', true);
        $doc->Cell(34, 5, 'ID / Classification', 1, 0, 'L', true);
        $doc->Cell(40, 5, 'College / Program', 1, 0, 'L', true);
        $doc->Cell(22, 5, 'Status', 1, 1, 'C', true);

        $schedule->loadMissing(['attendees.patient.college', 'attendees.patient.program']);
        $doc->SetFont('Arial', '', 8);

        $idx = 1;
        foreach ($schedule->attendees as $att) {
            $p = $att->patient;
            $name = $p?->full_name ?? 'N/A';
            $id = $p?->primary_identifier ?? '—';
            $type = ucfirst($p?->type ?? 'student');
            $prog = ($p?->college?->code ?: '—').' / '.($p?->program?->code ?: '—');
            $status = ucfirst($att->status);

            $doc->Cell(10, 4.5, (string) $idx++, 1, 0, 'C');
            $doc->Cell(60, 4.5, $doc->conv($name), 1, 0, 'L');
            $doc->Cell(34, 4.5, $doc->conv("{$id} ({$type})"), 1, 0, 'L');
            $doc->Cell(40, 4.5, $doc->conv($prog), 1, 0, 'L');
            $doc->Cell(22, 4.5, $status, 1, 1, 'C');
        }

        $physicianName = $this->resolvePhysicianName($settings, $schedule->clinician);
        $license = $this->resolvePhysicianLicense($settings, $schedule->clinician);

        $doc->drawHsdSignatureBlock($physicianName, 'School Physician', $license, 122, 236);

        return $doc;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $settings
     */
    public function clinicMorbidityReport(array $data, array $settings): ClinicFpdf
    {
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'NVC HSD - Morbidity & Clinic Utilization Report', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'CLINICAL MORBIDITY & UTILIZATION REPORT', 0, 1, 'C');
        $doc->SetFont('Arial', 'I', 8.5);
        $doc->Cell(0, 4.5, 'Aggregated Health Services Statistics: '.($data['period']['label'] ?? ''), 0, 1, 'C');
        $doc->Ln(2);

        $w = 170;

        $doc->SetFont('Arial', 'B', 9);
        $doc->SetFillColor(235, 238, 242);
        $doc->Cell($w, 5, ' I. EXECUTIVE UTILIZATION SUMMARY', 1, 1, 'L', true);

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(56, 5, 'Total Encounters / Visits:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(29, 5, (string) ($data['summary']['total_visits'] ?? 0), 1, 0, 'C');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(56, 5, 'Unduplicated Patients Served:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(29, 5, (string) ($data['summary']['unique_patients'] ?? 0), 1, 1, 'C');

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(56, 5, 'Total Medicines Dispensed:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(29, 5, number_format($data['summary']['total_medicines_dispensed'] ?? 0).' units', 1, 0, 'C');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(56, 5, 'Reporting Period:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(29, 5, $doc->conv($data['period']['label'] ?? 'All Time'), 1, 1, 'C');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' II. SERVICE ENCOUNTER BREAKDOWN', 1, 1, 'L', true);
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(120, 5, 'Service / Consultation Category', 1, 0, 'L');
        $doc->Cell(50, 5, 'Encounters Recorded', 1, 1, 'C');
        $doc->SetFont('Arial', '', 8);
        foreach ($data['service_distribution'] as $serv => $cnt) {
            $doc->Cell(120, 4.5, $doc->conv($serv), 1, 0, 'L');
            $doc->Cell(50, 4.5, (string) $cnt, 1, 1, 'C');
        }
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' III. COMMUNICABLE & INFECTIOUS MORBIDITY (Leading Causes)', 1, 1, 'L', true);
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(110, 5, 'Diagnosed Infectious / Respiratory Condition', 1, 0, 'L');
        $doc->Cell(30, 5, 'Cases', 1, 0, 'C');
        $doc->Cell(30, 5, '% Share', 1, 1, 'C');
        $doc->SetFont('Arial', '', 8);
        $commList = array_slice($data['morbidity']['communicable'] ?? [], 0, 7);
        if (empty($commList)) {
            $doc->Cell($w, 4.5, 'No communicable conditions recorded in this period.', 1, 1, 'C');
        } else {
            foreach ($commList as $item) {
                $doc->Cell(110, 4.5, $doc->conv($item['name']), 1, 0, 'L');
                $doc->Cell(30, 4.5, (string) $item['count'], 1, 0, 'C');
                $doc->Cell(30, 4.5, $item['percentage'].'%', 1, 1, 'C');
            }
        }
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' IV. CHRONIC & NON-COMMUNICABLE CONDITIONS (Leading Causes)', 1, 1, 'L', true);
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(110, 5, 'Diagnosed Non-Communicable Condition', 1, 0, 'L');
        $doc->Cell(30, 5, 'Cases', 1, 0, 'C');
        $doc->Cell(30, 5, '% Share', 1, 1, 'C');
        $doc->SetFont('Arial', '', 8);
        $nonCommList = array_slice($data['morbidity']['non_communicable'] ?? [], 0, 7);
        if (empty($nonCommList)) {
            $doc->Cell($w, 4.5, 'No non-communicable conditions recorded in this period.', 1, 1, 'C');
        } else {
            foreach ($nonCommList as $item) {
                $doc->Cell(110, 4.5, $doc->conv($item['name']), 1, 0, 'L');
                $doc->Cell(30, 4.5, (string) $item['count'], 1, 0, 'C');
                $doc->Cell(30, 4.5, $item['percentage'].'%', 1, 1, 'C');
            }
        }
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' V. TOP CHIEF COMPLAINTS ON PRESENTATION', 1, 1, 'L', true);
        $doc->SetFont('Arial', 'B', 8);
        $doc->Cell(110, 5, 'Reported Symptom / Reason for Visit', 1, 0, 'L');
        $doc->Cell(30, 5, 'Visits', 1, 0, 'C');
        $doc->Cell(30, 5, '% Share', 1, 1, 'C');
        $doc->SetFont('Arial', '', 8);
        $topComp = array_slice($data['complaints'] ?? [], 0, 5);
        if (empty($topComp)) {
            $doc->Cell($w, 4.5, 'No chief complaints logged in this period.', 1, 1, 'C');
        } else {
            foreach ($topComp as $comp) {
                $doc->Cell(110, 4.5, $doc->conv($comp['name']), 1, 0, 'L');
                $doc->Cell(30, 4.5, (string) $comp['count'], 1, 0, 'C');
                $doc->Cell(30, 4.5, $comp['percentage'].'%', 1, 1, 'C');
            }
        }
        $doc->Ln(5);

        $physicianName = $this->resolvePhysicianName($settings);
        $license = $this->resolvePhysicianLicense($settings);

        $doc->drawHsdSignatureBlock($physicianName, 'Head, Health Services Department', $license, 122);

        return $doc;
    }
}
