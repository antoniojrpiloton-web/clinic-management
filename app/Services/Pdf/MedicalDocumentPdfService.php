<?php

namespace App\Services\Pdf;

use App\Models\Clinician;
use App\Models\ClinicVisit;
use App\Models\MedicineDispensing;
use App\Models\PhysicalExamination;
use App\Models\PhysicalExamSchedule;
use App\Models\User;

class MedicalDocumentPdfService
{
    protected function defaultAddress(array $settings): string
    {
        return (string) ($settings['university_campus'] ?? ($settings['university_address'] ?? 'Surigao City'));
    }

    protected function resolvePhysicianName(array $settings, Clinician|User|null $physician = null, ?string $examinerName = null): string
    {
        if (! empty($settings['clinic_head'])) {
            return (string) $settings['clinic_head'];
        }

        return (string) ($physician?->name ?: ($examinerName ?: 'Dr. Maria Elena Santos, MD'));
    }

    protected function resolvePhysicianLicense(array $settings, Clinician|User|null $physician = null, ?string $examinerLicense = null): string
    {
        if (! empty($settings['clinic_license_no'])) {
            return (string) $settings['clinic_license_no'];
        }

        $license = $physician instanceof Clinician ? $physician->license_number : null;

        return (string) ($license ?: ($examinerLicense ?: 'DOH-R13-CLN-2026-088'));
    }

    public function medicalCertificate(PhysicalExamination $exam, array $settings, Clinician|User|null $physician = null): ClinicFpdf
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

    public function medicalExemption(PhysicalExamination $exam, array $settings, Clinician|User|null $physician = null): ClinicFpdf
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

    public function medicalReferral(PhysicalExamination $exam, array $settings, Clinician|User|null $physician = null): ClinicFpdf
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

        $w = 166.0;
        $doc->SetLineWidth(0.22);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Date:', 'style' => 'B', 'size' => 9],
            ['w' => 138, 'text' => $date, 'style' => '', 'size' => 9],
        ], 4.5, 6.5);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Refer to:', 'style' => 'B', 'size' => 9],
            ['w' => 138, 'text' => $referTo ?: 'Attending Medical Specialist / Hospital', 'style' => 'B', 'size' => 9],
        ], 4.5, 12.0);

        $doc->drawSectionHeader('Patient Information:', $w, 6.0);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Name:', 'style' => 'B', 'size' => 9],
            ['w' => 138, 'text' => $patientName, 'style' => 'B', 'size' => 9.5],
        ], 4.5, 7.5);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Date of Birth:', 'style' => 'B', 'size' => 8.5],
            ['w' => 55, 'text' => $dob ?: '—', 'style' => '', 'size' => 9],
            ['w' => 16, 'text' => 'Age:', 'style' => 'B', 'size' => 8.5],
            ['w' => 25, 'text' => $age ?: '—', 'style' => '', 'size' => 9, 'align' => 'C'],
            ['w' => 18, 'text' => 'Gender:', 'style' => 'B', 'size' => 8.5],
            ['w' => 24, 'text' => ucfirst($gender) ?: '—', 'style' => '', 'size' => 9, 'align' => 'C'],
        ], 4.5, 7.5);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Address:', 'style' => 'B', 'size' => 8.5],
            ['w' => 82, 'text' => $address ?: '—', 'style' => '', 'size' => 8.5],
            ['w' => 26, 'text' => 'Contact No.:', 'style' => 'B', 'size' => 8.5],
            ['w' => 30, 'text' => $phone ?: '—', 'style' => '', 'size' => 8.5],
        ], 4.3, 8.0);

        $doc->drawSectionHeader('In case of Emergency:', $w, 6.0);

        $doc->drawRow([
            ['w' => 32, 'text' => 'Contact Name:', 'style' => 'B', 'size' => 8.5],
            ['w' => 78, 'text' => $emergencyName ?: '—', 'style' => '', 'size' => 8.5],
            ['w' => 26, 'text' => 'Contact No.:', 'style' => 'B', 'size' => 8.5],
            ['w' => 30, 'text' => $emergencyPhone ?: '—', 'style' => '', 'size' => 8.5],
        ], 4.3, 8.0);

        $this->renderDynamicTextBox($doc, 'Complaints/ Findings:', $complaints, $w, 28.0);
        $this->renderDynamicTextBox($doc, 'Treatment:', $treatment, $w, 22.0);
        $this->renderDynamicTextBox($doc, 'Reasons for Referral:', $reasons, $w, 24.0);

        $sigBoxH = 22.0;
        $doc->checkPageBreak($sigBoxH);
        $x = 22.0;
        $y = $doc->GetY();
        $doc->Rect($x, $y, $w, $sigBoxH);

        $sigW = 68.0;
        $sigX = $x + $w - $sigW - 6.0;
        $doc->Line($sigX, $y + 10.5, $sigX + $sigW, $y + 10.5);

        $doc->SetXY($sigX, $y + 11.2);
        $doc->SetFont('Arial', 'B', 9.5);
        $doc->Cell($sigW, 4.2, $doc->conv(strtoupper($physicianName)), 0, 1, 'C');
        $doc->SetX($sigX);
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell($sigW, 3.8, 'School Physician', 0, 1, 'C');
        if ($license !== '') {
            $doc->SetX($sigX);
            $doc->SetFont('Arial', '', 8);
            $doc->Cell($sigW, 3.5, $doc->conv("License #: {$license}"), 0, 1, 'C');
        }
        $doc->SetY($y + $sigBoxH);
    }

    protected function renderDynamicTextBox(ClinicFpdf $doc, string $label, string $content, float $w = 166.0, float $minHeight = 24.0): void
    {
        $doc->SetFont('Arial', '', 9);
        $converted = $doc->conv(trim($content));
        $lines = $converted !== '' ? $doc->nbLines($w - 5.0, $converted) : 1;
        $textH = $lines * 4.3;
        $boxH = max($minHeight, 8.0 + $textH + 2.5);

        $doc->checkPageBreak($boxH);
        $x = 22.0;
        $y = $doc->GetY();

        $doc->Rect($x, $y, $w, $boxH);
        $doc->SetXY($x + 1.8, $y + 1.4);
        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell(0, 4.5, $doc->conv($label), 0, 1, 'L');

        if ($converted !== '') {
            $doc->SetXY($x + 2.5, $y + 6.5);
            $doc->SetFont('Arial', '', 9);
            $doc->MultiCell($w - 5.0, 4.3, $converted, 0, 'L');
        }

        $doc->SetXY($x, $y + $boxH);
    }

    public function clinicVisitSlip(ClinicVisit $visit, array $settings, Clinician|User|null $physician = null): ClinicFpdf
    {
        $patient = $visit->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 5- Consultation Form', true, 'single_room');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(43);
        $doc->SetFont('Arial', 'B', 11.5);
        $doc->Cell(0, 5, 'CONSULTATION FORM', 0, 1, 'C');
        $doc->Ln(3);

        $x = 22.0;
        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(23, 5.5, 'Patient Name:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->full_name ?? '', 92, 'L', true);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(19, 5.5, ' Civil Status:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->civil_status ?? '', 32, 'C', false);
        $doc->Ln(7);

        $courseOrOcc = $patient?->type === 'student'
            ? trim(($patient->program?->code ?? '').' '.($patient->year_level ?? ''))
            : ($patient?->position ?? ($visit->classification_label ?: ''));

        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(9, 5.5, 'Age:', 0, 0, 'L');
        $doc->drawInlineBlank((string) ($patient?->age ?? ''), 20, 'C', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(31, 5.5, ' Course/Occupation:', 0, 0, 'L');
        $doc->drawInlineBlank($courseOrOcc, 71, 'L', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(9, 5.5, ' Sex:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->gender ?? '', 26, 'C', false);
        $doc->Ln(7);

        $doc->SetX($x);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(15, 5.5, 'Address:', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->address ?: $this->defaultAddress($settings), 102, 'L', false);
        $doc->SetFont('Arial', '', 9.5);
        $doc->Cell(14, 5.5, ' Cel. No.', 0, 0, 'L');
        $doc->drawInlineBlank($patient?->phone ?? '', 35, 'C', false);
        $doc->Ln(8);

        $col1 = 24.0;
        $col2 = 88.0;
        $col3 = 54.0;

        $doc->SetLineWidth(0.22);
        $doc->SetFillColor(242, 244, 247);
        $doc->drawRow([
            ['w' => $col1, 'text' => 'DATE', 'style' => 'B', 'size' => 8.5, 'align' => 'C', 'fill' => true],
            ['w' => $col2, 'text' => 'COMPLAINTS/ FINDINGS', 'style' => 'B', 'size' => 8.5, 'align' => 'C', 'fill' => true],
            ['w' => $col3, 'text' => 'TREATMENT', 'style' => 'B', 'size' => 8.5, 'align' => 'C', 'fill' => true],
        ], 4.5, 6.5);

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

            $doc->drawRow([
                ['w' => $col1, 'text' => $vItem->visit_date->format('m/d/Y'), 'style' => '', 'size' => 8, 'align' => 'C'],
                ['w' => $col2, 'text' => $complaintText, 'style' => '', 'size' => 8],
                ['w' => $col3, 'text' => (string) $treatmentText, 'style' => '', 'size' => 8],
            ], 4.0, 8.0);
        }

        $blankRowH = 8.0;
        while ($doc->GetY() + $blankRowH <= ($doc->GetPageHeight() - 22)) {
            $ry = $doc->GetY();
            $doc->Rect($x, $ry, $col1, $blankRowH);
            $doc->Rect($x + $col1, $ry, $col2, $blankRowH);
            $doc->Rect($x + $col1 + $col2, $ry, $col3, $blankRowH);
            $doc->SetY($ry + $blankRowH);
        }

        return $doc;
    }

    public function physicalExamination(PhysicalExamination $exam, array $settings, Clinician|User|null $physician = null): ClinicFpdf
    {
        $patient = $exam->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 1- Physical Examination Form', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'PHYSICAL EXAMINATION RECORD', 0, 1, 'C');
        $doc->Ln(1.5);

        $w = 166.0;
        $doc->SetLineWidth(0.2);

        $doc->SetFillColor(246, 247, 249);
        $doc->drawRow([
            ['w' => 55, 'text' => 'PE No: '.$exam->pe_number, 'style' => 'B', 'size' => 8.5, 'fill' => true],
            ['w' => 61, 'text' => 'Purpose: '.$exam->purpose, 'style' => 'B', 'size' => 8.5, 'align' => 'C', 'fill' => true],
            ['w' => 50, 'text' => 'Date: '.$exam->exam_date->format('M d, Y'), 'style' => 'B', 'size' => 8.5, 'align' => 'R', 'fill' => true],
        ], 4.2, 5.5);
        $doc->Ln(1.5);

        $doc->drawSectionHeader('I. PATIENT INFORMATION', $w, 5.2);

        $doc->drawRow([
            ['w' => 30, 'text' => 'Full Name:', 'style' => 'B', 'size' => 8],
            ['w' => 70, 'text' => $patient->full_name, 'style' => 'B', 'size' => 8.5],
            ['w' => 28, 'text' => 'ID / Type:', 'style' => 'B', 'size' => 8],
            ['w' => 38, 'text' => $patient->primary_identifier.' ('.ucfirst($patient->type).')', 'style' => '', 'size' => 8],
        ], 4.0, 5.2);

        $doc->drawRow([
            ['w' => 30, 'text' => 'College / Prog:', 'style' => 'B', 'size' => 8],
            ['w' => 70, 'text' => ($patient->college?->code ?: '—').' - '.($patient->program?->code ?: '—'), 'style' => '', 'size' => 8],
            ['w' => 28, 'text' => 'Sex / Age:', 'style' => 'B', 'size' => 8],
            ['w' => 38, 'text' => ucfirst((string) ($patient->gender ?: '—')).' / '.($patient->age ? "{$patient->age} y/o" : '—'), 'style' => '', 'size' => 8],
        ], 4.0, 5.2);

        $doc->drawRow([
            ['w' => 30, 'text' => 'Address & Tel:', 'style' => 'B', 'size' => 8],
            ['w' => 70, 'text' => ($patient->address ?: $this->defaultAddress($settings)).' | '.($patient->phone ?: '—'), 'style' => '', 'size' => 8],
            ['w' => 28, 'text' => 'Blood / Civil:', 'style' => 'B', 'size' => 8],
            ['w' => 38, 'text' => ($patient->blood_type ?: 'O+').' / '.($patient->civil_status ?: 'Single'), 'style' => '', 'size' => 8],
        ], 4.0, 5.2);
        $doc->Ln(1.5);

        $doc->drawSectionHeader('II. VITAL SIGNS & ANTHROPOMETRIC MEASUREMENTS', $w, 5.2);

        $bp = $exam->blood_pressure ?: '—';
        $pr = $exam->pulse_rate ? "{$exam->pulse_rate} bpm" : '—';
        $rr = $exam->respiratory_rate ? "{$exam->respiratory_rate} cpm" : '—';
        $temp = $exam->temperature ? "{$exam->temperature} C" : '—';
        $spo2 = $exam->oxygen_saturation ? "{$exam->oxygen_saturation}%" : '—';

        $doc->drawRow([
            ['w' => 33.2, 'text' => "BP: {$bp}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 33.2, 'text' => "Pulse: {$pr}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 33.2, 'text' => "Resp: {$rr}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 33.2, 'text' => "Temp: {$temp}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 33.2, 'text' => "SpO2: {$spo2}", 'style' => '', 'size' => 8, 'align' => 'C'],
        ], 4.0, 5.2);

        $ht = $exam->height_cm ? "{$exam->height_cm} cm" : '—';
        $wt = $exam->weight_kg ? "{$exam->weight_kg} kg" : '—';
        $bmi = $exam->bmi ? "{$exam->bmi} ({$exam->bmi_category})" : '—';
        $od = $exam->visual_acuity_right ?: '20/20';
        $os = $exam->visual_acuity_left ?: '20/20';

        $doc->drawRow([
            ['w' => 33.2, 'text' => "Height: {$ht}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 33.2, 'text' => "Weight: {$wt}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 49.8, 'text' => "BMI: {$bmi}", 'style' => '', 'size' => 8, 'align' => 'C'],
            ['w' => 49.8, 'text' => "Vision: R {$od} / L {$os}", 'style' => '', 'size' => 8, 'align' => 'C'],
        ], 4.0, 5.2);
        $doc->Ln(1.5);

        $doc->drawSectionHeader('III. SYSTEMIC PHYSICAL EXAMINATION FINDINGS', $w, 5.2);

        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 46, 'text' => 'Anatomical System', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 22, 'text' => 'Assessment', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 98, 'text' => 'Clinical Remarks & Observations', 'style' => 'B', 'size' => 8, 'fill' => true],
        ], 4.0, 5.0);

        $systemsList = [
            'general_appearance' => ['label' => 'General Appearance', 'default' => 'Well-nourished, alert, oriented'],
            'skin' => ['label' => 'Skin & Integumentary', 'default' => 'No suspicious lesions, jaundice, or cyanosis'],
            'heent' => ['label' => 'Head, Eyes, Ears, Nose', 'default' => 'Sclera anicteric, pupils equal and reactive'],
            'dental' => ['label' => 'Mouth, Teeth & Throat', 'default' => 'Good oral hygiene, no tonsillopharyngeal congestion'],
            'neck' => ['label' => 'Neck & Thyroid', 'default' => 'Supple, no palpable lymphadenopathy or goiter'],
            'chest_lungs' => ['label' => 'Chest & Lungs', 'default' => 'Clear vesicular breath sounds, no rales or wheezes'],
            'heart' => ['label' => 'Cardiovascular System', 'default' => 'Normal rate, regular rhythm, no cardiac murmurs'],
            'abdomen' => ['label' => 'Abdomen', 'default' => 'Soft, non-tender, no organomegaly'],
            'musculoskeletal' => ['label' => 'Musculoskeletal & Spine', 'default' => 'Full range of motion, no deformities'],
            'neurological' => ['label' => 'Neurological', 'default' => 'Grossly intact cranial nerves and reflexes'],
        ];

        foreach ($systemsList as $column => $meta) {
            $name = $meta['label'];
            $defaultDesc = $meta['default'];
            $raw = $exam->{$column} ?? null;

            if ($column === 'general_appearance') {
                $status = 'normal';
                $remarks = is_string($raw) && trim($raw) !== '' ? $raw : $defaultDesc;
            } elseif (is_array($raw)) {
                $status = (string) ($raw['status'] ?? 'normal');
                $remarks = ! empty($raw['remarks']) ? (string) $raw['remarks'] : $defaultDesc;
            } else {
                $status = 'normal';
                $remarks = $defaultDesc;
            }

            $doc->drawRow([
                ['w' => 46, 'text' => $name, 'style' => 'B', 'size' => 8],
                ['w' => 22, 'text' => ucfirst($status), 'style' => '', 'size' => 8, 'align' => 'C'],
                ['w' => 98, 'text' => $remarks, 'style' => '', 'size' => 8],
            ], 3.8, 4.8, 0.7);
        }
        $doc->Ln(1.5);

        $doc->drawSectionHeader('IV. DIAGNOSTIC SCREENING & OVERALL FITNESS ASSESSMENT', $w, 5.2);
        $cxr = $exam->chest_xray_findings ?: 'Chest X-Ray: Normal lung fields.';
        $lab = $exam->laboratory_notes ?: 'Routine laboratory tests within normal range.';

        $doc->drawRow([
            ['w' => 36, 'text' => 'Ancillary & Lab:', 'style' => 'B', 'size' => 8],
            ['w' => 130, 'text' => "{$cxr} | {$lab}", 'style' => '', 'size' => 8],
        ], 4.0, 5.5);

        $fitness = $exam->fitness_assessment ?: 'Class A';
        $doc->drawRow([
            ['w' => 36, 'text' => "Clearance: {$fitness}", 'style' => 'B', 'size' => 8.5, 'align' => 'C'],
            ['w' => 130, 'text' => $exam->clinical_findings ?: 'Fit for academic and university activities.', 'style' => '', 'size' => 8.5],
        ], 4.0, 5.8);

        $physicianName = $this->resolvePhysicianName($settings, $physician, $exam->examiner_name);
        $license = $this->resolvePhysicianLicense($settings, $physician, $exam->examiner_license_number);

        $doc->drawHsdSignatureBlock($physicianName, 'School Physician', $license, 122, 240);

        return $doc;
    }

    public function medicineDispensingSlip(MedicineDispensing $dispensing, array $settings, Clinician|User|null $physician = null): ClinicFpdf
    {
        $patient = $dispensing->patient;
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', 'HSD FORM 7- Pharmacy Dispensing Slip', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.5, 'CLINIC PHARMACY DISPENSING SLIP', 0, 1, 'C');
        $doc->Ln(2);

        $w = 166.0;
        $doc->SetLineWidth(0.2);

        $doc->SetFillColor(246, 247, 249);
        $doc->drawRow([
            ['w' => 62, 'text' => 'Slip No: '.$dispensing->dispensing_number, 'style' => 'B', 'size' => 8.5, 'fill' => true],
            ['w' => 44, 'text' => 'Total Items: '.$dispensing->total_items, 'style' => 'B', 'size' => 8.5, 'align' => 'C', 'fill' => true],
            ['w' => 60, 'text' => 'Date: '.$dispensing->dispensed_date->format('M d, Y h:i A'), 'style' => 'B', 'size' => 8.5, 'align' => 'R', 'fill' => true],
        ], 4.2, 5.5);
        $doc->Ln(2);

        $doc->drawSectionHeader('1. PATIENT & PRESCRIBING CLINICIAN', $w, 5.5);

        $name = $patient?->full_name ?? 'Walk-in Patient';
        $idNo = $patient?->primary_identifier ?? 'N/A';
        $prog = $patient?->program?->code ?? ($patient?->type === 'student' ? 'Student' : 'Employee');
        $prescriber = $dispensing->clinician?->name ?? $this->resolvePhysicianName($settings, $physician);

        $doc->drawRow([
            ['w' => 32, 'text' => 'Patient Name:', 'style' => 'B', 'size' => 8.5],
            ['w' => 70, 'text' => $name, 'style' => 'B', 'size' => 8.5],
            ['w' => 28, 'text' => 'ID Number:', 'style' => 'B', 'size' => 8.5],
            ['w' => 36, 'text' => $idNo, 'style' => '', 'size' => 8.5],
        ], 4.2, 5.5);

        $doc->drawRow([
            ['w' => 32, 'text' => 'Course / Dept:', 'style' => 'B', 'size' => 8.5],
            ['w' => 70, 'text' => $prog, 'style' => '', 'size' => 8.5],
            ['w' => 28, 'text' => 'Prescriber:', 'style' => 'B', 'size' => 8.5],
            ['w' => 36, 'text' => $prescriber, 'style' => '', 'size' => 8.5],
        ], 4.2, 5.5);

        $doc->drawRow([
            ['w' => 32, 'text' => 'Diagnosis:', 'style' => 'B', 'size' => 8.5],
            ['w' => 134, 'text' => $dispensing->diagnosis ?: 'Symptomatic medical relief', 'style' => '', 'size' => 8.5],
        ], 4.2, 5.5);
        $doc->Ln(2.5);

        $doc->drawSectionHeader('2. DISPENSED MEDICINES & INSTRUCTIONS', $w, 5.5);

        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 10, 'text' => '#', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 64, 'text' => 'Medicine / Generic (Brand)', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 25, 'text' => 'Strength', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 18, 'text' => 'Qty', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 49, 'text' => 'Instructions / Sig', 'style' => 'B', 'size' => 8, 'fill' => true],
        ], 4.0, 5.5);

        $dispensing->loadMissing(['items.medicine']);
        $idx = 1;
        foreach ($dispensing->items as $item) {
            $med = $item->medicine;
            $medName = $med ? "{$med->generic_name} ({$med->brand_name})" : 'Medicine';
            $strength = $med ? trim("{$med->strength} {$med->dosage_form}") : '—';
            $qty = trim("{$item->quantity} {$med?->unit}");
            $sig = $item->dosage_instruction ?: ($item->instructions ?: ($dispensing->instructions ?: 'Take as directed'));

            $doc->drawRow([
                ['w' => 10, 'text' => (string) $idx++, 'style' => '', 'size' => 8, 'align' => 'C'],
                ['w' => 64, 'text' => $medName, 'style' => 'B', 'size' => 8],
                ['w' => 25, 'text' => $strength ?: '—', 'style' => '', 'size' => 8, 'align' => 'C'],
                ['w' => 18, 'text' => $qty, 'style' => 'B', 'size' => 8, 'align' => 'C'],
                ['w' => 49, 'text' => (string) $sig, 'style' => '', 'size' => 8],
            ], 4.0, 5.5);
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

        $w = 166.0;
        $doc->SetLineWidth(0.2);

        $dateFormatted = $schedule->scheduled_date->format('F d, Y');
        $timeWindow = "{$schedule->start_time} - {$schedule->end_time}";

        $doc->drawRow([
            ['w' => 28, 'text' => 'Campaign:', 'style' => 'B', 'size' => 8.5],
            ['w' => 76, 'text' => $schedule->title, 'style' => 'B', 'size' => 8.5],
            ['w' => 26, 'text' => 'Schedule No:', 'style' => 'B', 'size' => 8.5],
            ['w' => 36, 'text' => $schedule->schedule_number, 'style' => '', 'size' => 8.5],
        ], 4.0, 5.5);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Date & Time:', 'style' => 'B', 'size' => 8.5],
            ['w' => 76, 'text' => "{$dateFormatted} ({$timeWindow})", 'style' => '', 'size' => 8.5],
            ['w' => 26, 'text' => 'Venue / Cap:', 'style' => 'B', 'size' => 8.5],
            ['w' => 36, 'text' => "{$schedule->venue} ({$schedule->capacity})", 'style' => '', 'size' => 8.5],
        ], 4.0, 5.5);
        $doc->Ln(2);

        $doc->SetFillColor(242, 244, 247);
        $doc->drawRow([
            ['w' => 10, 'text' => '#', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 58, 'text' => 'Patient Name', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 36, 'text' => 'ID / Classification', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 40, 'text' => 'College / Program', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 22, 'text' => 'Status', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
        ], 4.0, 5.5);

        $schedule->loadMissing(['attendees.patient.college', 'attendees.patient.program']);

        $idx = 1;
        foreach ($schedule->attendees as $att) {
            $p = $att->patient;
            $name = $p?->full_name ?? 'N/A';
            $id = $p?->primary_identifier ?? '—';
            $type = ucfirst($p?->type ?? 'student');
            $prog = ($p?->college?->code ?: '—').' / '.($p?->program?->code ?: '—');
            $status = ucfirst($att->status);

            $doc->drawRow([
                ['w' => 10, 'text' => (string) $idx++, 'style' => '', 'size' => 8, 'align' => 'C'],
                ['w' => 58, 'text' => $name, 'style' => 'B', 'size' => 8],
                ['w' => 36, 'text' => "{$id} ({$type})", 'style' => '', 'size' => 8],
                ['w' => 40, 'text' => $prog, 'style' => '', 'size' => 8],
                ['w' => 22, 'text' => $status, 'style' => '', 'size' => 8, 'align' => 'C'],
            ], 4.0, 5.0);
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

        $w = 166.0;
        $doc->SetLineWidth(0.2);

        $doc->drawSectionHeader('I. EXECUTIVE UTILIZATION SUMMARY', $w, 5.5);

        $doc->drawRow([
            ['w' => 54, 'text' => 'Total Encounters / Visits:', 'style' => 'B', 'size' => 8.5],
            ['w' => 29, 'text' => number_format((int) ($data['summary']['total_visits'] ?? 0)), 'style' => 'B', 'size' => 8.5, 'align' => 'C'],
            ['w' => 54, 'text' => 'Unduplicated Patients Served:', 'style' => 'B', 'size' => 8.5],
            ['w' => 29, 'text' => number_format((int) ($data['summary']['unique_patients'] ?? 0)), 'style' => 'B', 'size' => 8.5, 'align' => 'C'],
        ], 4.2, 5.5);

        $doc->drawRow([
            ['w' => 54, 'text' => 'Total Medicines Dispensed:', 'style' => 'B', 'size' => 8.5],
            ['w' => 29, 'text' => number_format((int) ($data['summary']['total_medicines_dispensed'] ?? 0)).' units', 'style' => 'B', 'size' => 8.5, 'align' => 'C'],
            ['w' => 54, 'text' => 'Reporting Period:', 'style' => 'B', 'size' => 8.5],
            ['w' => 29, 'text' => (string) ($data['period']['label'] ?? 'All Time'), 'style' => '', 'size' => 8, 'align' => 'C'],
        ], 4.2, 5.5);
        $doc->Ln(2);

        $doc->drawSectionHeader('II. SERVICE ENCOUNTER BREAKDOWN', $w, 5.5);
        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 118, 'text' => 'Service / Consultation Category', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 48, 'text' => 'Encounters Recorded', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
        ], 4.0, 5.2);

        foreach ($data['service_distribution'] as $serv => $cnt) {
            $doc->drawRow([
                ['w' => 118, 'text' => (string) $serv, 'style' => '', 'size' => 8],
                ['w' => 48, 'text' => number_format((int) $cnt), 'style' => 'B', 'size' => 8, 'align' => 'C'],
            ], 4.0, 4.8);
        }
        $doc->Ln(2);

        $doc->drawSectionHeader('III. COMMUNICABLE & INFECTIOUS MORBIDITY (Leading Causes)', $w, 5.5);
        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 108, 'text' => 'Diagnosed Infectious / Respiratory Condition', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 29, 'text' => 'Cases', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 29, 'text' => '% Share', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
        ], 4.0, 5.2);

        $commList = array_slice($data['morbidity']['communicable'] ?? [], 0, 7);
        if (empty($commList)) {
            $doc->drawRow([
                ['w' => $w, 'text' => 'No communicable conditions recorded in this period.', 'style' => 'I', 'size' => 8, 'align' => 'C'],
            ], 4.0, 5.0);
        } else {
            foreach ($commList as $item) {
                $doc->drawRow([
                    ['w' => 108, 'text' => (string) $item['name'], 'style' => '', 'size' => 8],
                    ['w' => 29, 'text' => (string) $item['count'], 'style' => 'B', 'size' => 8, 'align' => 'C'],
                    ['w' => 29, 'text' => $item['percentage'].'%', 'style' => '', 'size' => 8, 'align' => 'C'],
                ], 4.0, 4.8);
            }
        }
        $doc->Ln(2);

        $doc->drawSectionHeader('IV. CHRONIC & NON-COMMUNICABLE CONDITIONS (Leading Causes)', $w, 5.5);
        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 108, 'text' => 'Diagnosed Non-Communicable Condition', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 29, 'text' => 'Cases', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 29, 'text' => '% Share', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
        ], 4.0, 5.2);

        $nonCommList = array_slice($data['morbidity']['non_communicable'] ?? [], 0, 7);
        if (empty($nonCommList)) {
            $doc->drawRow([
                ['w' => $w, 'text' => 'No non-communicable conditions recorded in this period.', 'style' => 'I', 'size' => 8, 'align' => 'C'],
            ], 4.0, 5.0);
        } else {
            foreach ($nonCommList as $item) {
                $doc->drawRow([
                    ['w' => 108, 'text' => (string) $item['name'], 'style' => '', 'size' => 8],
                    ['w' => 29, 'text' => (string) $item['count'], 'style' => 'B', 'size' => 8, 'align' => 'C'],
                    ['w' => 29, 'text' => $item['percentage'].'%', 'style' => '', 'size' => 8, 'align' => 'C'],
                ], 4.0, 4.8);
            }
        }
        $doc->Ln(2);

        $doc->drawSectionHeader('V. TOP CHIEF COMPLAINTS ON PRESENTATION', $w, 5.5);
        $doc->SetFillColor(248, 249, 250);
        $doc->drawRow([
            ['w' => 108, 'text' => 'Reported Symptom / Reason for Visit', 'style' => 'B', 'size' => 8, 'fill' => true],
            ['w' => 29, 'text' => 'Visits', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
            ['w' => 29, 'text' => '% Share', 'style' => 'B', 'size' => 8, 'align' => 'C', 'fill' => true],
        ], 4.0, 5.2);

        $topComp = array_slice($data['complaints'] ?? [], 0, 5);
        if (empty($topComp)) {
            $doc->drawRow([
                ['w' => $w, 'text' => 'No chief complaints logged in this period.', 'style' => 'I', 'size' => 8, 'align' => 'C'],
            ], 4.0, 5.0);
        } else {
            foreach ($topComp as $comp) {
                $doc->drawRow([
                    ['w' => 108, 'text' => (string) $comp['name'], 'style' => '', 'size' => 8],
                    ['w' => 29, 'text' => (string) $comp['count'], 'style' => 'B', 'size' => 8, 'align' => 'C'],
                    ['w' => 29, 'text' => $comp['percentage'].'%', 'style' => '', 'size' => 8, 'align' => 'C'],
                ], 4.0, 4.8);
            }
        }

        $physicianName = $this->resolvePhysicianName($settings);
        $license = $this->resolvePhysicianLicense($settings);

        $doc->drawHsdSignatureBlock($physicianName, 'Head, Health Services Department', $license, 122);

        return $doc;
    }

    /**
     * Generate Official Landscape PDF for Report 1: MEDICAL AND DENTAL RECORDS (Annual 12-Month Matrix).
     *
     * @param  array<string, mixed>  $matrix
     * @param  array<string, mixed>  $settings
     */
    public function medicalAndDentalRecordsReport(array $matrix, array $settings): ClinicFpdf
    {
        $year = (int) ($matrix['year'] ?? now()->year);
        $doc = new ClinicFpdf('L', 'mm', 'A4', $settings, '', "HSD REPORT - Medical and Dental Records ({$year})", true, 'two_rooms');
        $doc->SetMargins(12, 12, 12);
        $doc->SetAutoPageBreak(false);
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(38);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.0, 'MEDICAL AND DENTAL RECORDS', 0, 1, 'C');
        $doc->SetFont('Arial', 'B', 11);
        $doc->Cell(0, 4.8, (string) $year, 0, 1, 'C');
        $doc->Ln(1.5);

        $groups = $matrix['groups'] ?? [];
        $totalDataRows = 0;
        foreach ($groups as $g) {
            $totalDataRows += count($g['rows'] ?? []);
        }

        $xStart = 12.0;
        $yStart = $doc->GetY();
        $wYear = 15.0;
        $wGroup = 29.0;
        $wCat = 30.0;
        $wServices = $wGroup + $wCat;
        $wMonth = 15.0;
        $wTotal = 19.0;
        $headerH = 7.8;

        $availableBodyH = 175.0 - ($yStart + $headerH);
        $rowH = min(5.4, max(3.8, $availableBodyH / max(1, $totalDataRows + 1)));
        $cellTextOffsetY = max(0.5, ($rowH - 3.2) / 2);
        $valFontSize = $rowH < 4.5 ? 6.8 : 7.5;

        $doc->SetLineWidth(0.25);
        $doc->SetFillColor(118, 146, 60);
        $doc->SetFont('Arial', 'B', 7.5);

        $doc->Rect($xStart, $yStart, $wYear, $headerH, 'DF');
        $doc->SetXY($xStart, $yStart + 2.1);
        $doc->Cell($wYear, 3.6, 'YEAR', 0, 0, 'C');

        $doc->Rect($xStart + $wYear, $yStart, $wServices, $headerH, 'DF');
        $doc->SetXY($xStart + $wYear, $yStart + 2.1);
        $doc->Cell($wServices, 3.6, 'SERVICES', 0, 0, 'C');

        $months = $matrix['months'] ?? [];
        $curX = $xStart + $wYear + $wServices;
        for ($m = 1; $m <= 12; $m++) {
            $mLabel = (string) ($months[$m] ?? '');
            $doc->Rect($curX, $yStart, $wMonth, $headerH, 'DF');
            $doc->SetFont('Arial', 'B', strlen($mLabel) >= 9 ? 6.0 : 6.8);
            $doc->SetXY($curX, $yStart + 2.1);
            $doc->Cell($wMonth, 3.6, $mLabel, 0, 0, 'C');
            $curX += $wMonth;
        }

        $doc->Rect($curX, $yStart, $wTotal, $headerH, 'DF');
        $doc->SetFont('Arial', 'B', 6.5);
        $doc->SetXY($curX, $yStart + 0.9);
        $doc->MultiCell($wTotal, 3.0, "TOTAL FOR\nYEAR", 0, 'C');

        $bodyStartY = $yStart + $headerH;
        $yearColH = ($totalDataRows + 1) * $rowH;
        $doc->SetFillColor(118, 146, 60);
        $doc->Rect($xStart, $bodyStartY, $wYear, $yearColH, 'DF');
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->SetXY($xStart, $bodyStartY + $yearColH - $rowH + $cellTextOffsetY);
        $doc->Cell($wYear, 3.2, "{$year}.0", 0, 0, 'L');

        $curY = $bodyStartY;
        foreach ($groups as $g) {
            $groupRows = $g['rows'] ?? [];
            $groupCount = count($groupRows);
            if ($groupCount === 0) {
                continue;
            }

            $groupH = $groupCount * $rowH;
            [$gr, $gg, $gb] = $this->resolveReportGroupRgb((string) ($g['color'] ?? 'slate'));

            $doc->SetFillColor($gr, $gg, $gb);
            $doc->Rect($xStart + $wYear, $curY, $wGroup, $groupH, 'DF');

            $doc->SetFont('Arial', 'B', $rowH < 4.5 ? 6.4 : 6.8);
            $groupText = $doc->conv((string) ($g['group'] ?? ''));
            $gLines = $doc->nbLines($wGroup - 2.0, $groupText);
            $gLineH = min(3.1, max(2.6, ($groupH - 0.8) / max(1, $gLines)));
            $gTextH = $gLines * $gLineH;
            $gOffsetY = max(0.4, ($groupH - $gTextH) / 2);
            $doc->SetXY($xStart + $wYear + 1.0, $curY + $gOffsetY);
            $doc->MultiCell($wGroup - 2.0, $gLineH, $groupText, 0, 'C');

            foreach ($groupRows as $row) {
                $doc->SetFillColor($gr, $gg, $gb);
                $doc->Rect($xStart + $wYear + $wGroup, $curY, $wCat, $rowH, 'DF');
                $labelLen = strlen((string) $row['label']);
                $labelSize = $labelLen > 16 ? 5.8 : ($labelLen > 13 ? 6.2 : ($rowH < 4.5 ? 6.5 : 7.0));
                $doc->SetFont('Arial', 'B', $labelSize);
                $doc->SetXY($xStart + $wYear + $wGroup + 0.6, $curY + $cellTextOffsetY);
                $doc->Cell($wCat - 1.2, 3.2, $doc->conv((string) $row['label']), 0, 0, 'C');

                $mx = $xStart + $wYear + $wServices;
                $doc->SetFont('Arial', 'B', $valFontSize);
                for ($m = 1; $m <= 12; $m++) {
                    $val = (int) ($row['months'][$m] ?? 0);
                    $doc->Rect($mx, $curY, $wMonth, $rowH, 'D');
                    $doc->SetXY($mx, $curY + $cellTextOffsetY);
                    $doc->Cell($wMonth - 1.0, 3.2, (string) $val, 0, 0, 'R');
                    $mx += $wMonth;
                }

                $doc->SetFillColor(245, 204, 38);
                $doc->Rect($mx, $curY, $wTotal, $rowH, 'DF');
                $doc->SetXY($mx, $curY + $cellTextOffsetY);
                $doc->Cell($wTotal - 1.2, 3.2, (string) ($row['total'] ?? 0), 0, 0, 'R');

                $curY += $rowH;
            }
        }

        $doc->SetFillColor(118, 146, 60);
        $doc->Rect($xStart + $wYear, $curY, $wServices, $rowH, 'DF');
        $doc->SetFont('Arial', 'B', $rowH < 4.5 ? 6.8 : 7.2);
        $doc->SetXY($xStart + $wYear, $curY + $cellTextOffsetY);
        $doc->Cell($wServices, 3.2, 'TOTAL FOR THE MONTH', 0, 0, 'C');

        $monthlyTotals = $matrix['monthly_totals'] ?? [];
        $mx = $xStart + $wYear + $wServices;
        $doc->SetFillColor(245, 204, 38);
        $doc->SetFont('Arial', 'B', $valFontSize);
        for ($m = 1; $m <= 12; $m++) {
            $mSum = (int) ($monthlyTotals[$m] ?? 0);
            $doc->Rect($mx, $curY, $wMonth, $rowH, 'DF');
            $doc->SetXY($mx, $curY + $cellTextOffsetY);
            $doc->Cell($wMonth - 1.0, 3.2, (string) $mSum, 0, 0, 'R');
            $mx += $wMonth;
        }

        $doc->SetFillColor(148, 108, 30);
        $doc->Rect($mx, $curY, $wTotal, $rowH, 'DF');
        $doc->SetXY($mx, $curY + $cellTextOffsetY);
        $doc->Cell($wTotal - 1.2, 3.2, (string) ($matrix['grand_total'] ?? 0), 0, 0, 'R');

        $this->drawAnnualReportSignatories($doc, $settings, $xStart, $curY + $rowH + 4.5);

        return $doc;
    }

    /**
     * Generate Official Landscape PDF for Report 2: MEDICAL AND DENTAL LOG-IN RECORDS (Annual 12-Month Matrix).
     *
     * @param  array<string, mixed>  $loginData
     * @param  array<string, mixed>  $settings
     */
    public function medicalAndDentalLoginRecordsReport(array $loginData, array $settings): ClinicFpdf
    {
        $year = (int) ($loginData['year'] ?? now()->year);
        $doc = new ClinicFpdf('L', 'mm', 'A4', $settings, '', "HSD REPORT - Medical and Dental Log-In Records ({$year})", true, 'two_rooms');
        $doc->SetMargins(12, 12, 12);
        $doc->SetAutoPageBreak(false);
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(38);
        $doc->SetFont('Arial', 'B', 12);
        $doc->Cell(0, 5.0, 'MEDICAL AND DENTAL LOG-IN RECORDS', 0, 1, 'C');
        $doc->SetFont('Arial', 'B', 11);
        $doc->Cell(0, 4.8, (string) $year, 0, 1, 'C');
        $doc->Ln(2.0);

        $rows = $loginData['rows'] ?? [];
        $rowCount = count($rows);

        $xStart = 12.0;
        $yStart = $doc->GetY();
        $wYear = 18.0;
        $wGroup = 25.0;
        $wCat = 32.0;
        $wServices = $wGroup + $wCat;
        $wMonth = 15.0;
        $wTotal = 18.0;
        $headerH = 8.0;

        $availableBodyH = 175.0 - ($yStart + $headerH);
        $rowH = min(6.2, max(4.0, $availableBodyH / max(1, $rowCount + 1)));
        $cellTextOffsetY = max(0.6, ($rowH - 3.4) / 2);
        $valFontSize = $rowH < 4.8 ? 7.2 : 7.8;

        $doc->SetLineWidth(0.25);
        $doc->SetFillColor(118, 146, 60);
        $doc->SetFont('Arial', 'B', 7.5);

        $doc->Rect($xStart, $yStart, $wYear, $headerH, 'DF');
        $doc->SetXY($xStart, $yStart + 2.2);
        $doc->Cell($wYear, 3.6, 'YEAR', 0, 0, 'C');

        $doc->Rect($xStart + $wYear, $yStart, $wServices, $headerH, 'DF');
        $doc->SetXY($xStart + $wYear, $yStart + 2.2);
        $doc->Cell($wServices, 3.6, 'SERVICES', 0, 0, 'C');

        $months = $loginData['months'] ?? [];
        $curX = $xStart + $wYear + $wServices;
        for ($m = 1; $m <= 12; $m++) {
            $mLabel = (string) ($months[$m] ?? '');
            $doc->Rect($curX, $yStart, $wMonth, $headerH, 'DF');
            $doc->SetFont('Arial', 'B', strlen($mLabel) >= 9 ? 6.0 : 6.8);
            $doc->SetXY($curX, $yStart + 2.2);
            $doc->Cell($wMonth, 3.6, $mLabel, 0, 0, 'C');
            $curX += $wMonth;
        }

        $doc->Rect($curX, $yStart, $wTotal, $headerH, 'DF');
        $doc->SetFont('Arial', 'B', 6.5);
        $doc->SetXY($curX, $yStart + 1.0);
        $doc->MultiCell($wTotal, 3.0, "TOTAL FOR\nYEAR", 0, 'C');

        $bodyStartY = $yStart + $headerH;

        $yearColH = ($rowCount + 1) * $rowH;
        $doc->SetFillColor(118, 146, 60);
        $doc->Rect($xStart, $bodyStartY, $wYear, $yearColH, 'DF');
        $doc->SetFont('Arial', 'B', 7.5);
        $doc->SetXY($xStart + 0.5, $bodyStartY + $yearColH - $rowH + $cellTextOffsetY);
        $doc->Cell($wYear - 1.0, 3.4, "{$year}.0", 0, 0, 'L');

        $doc->SetFillColor(217, 172, 170);
        $doc->Rect($xStart + $wYear, $bodyStartY, $wGroup, $rowCount * $rowH, 'DF');

        $curY = $bodyStartY;
        foreach ($rows as $row) {
            $doc->SetFillColor(230, 184, 183);
            $doc->Rect($xStart + $wYear + $wGroup, $curY, $wCat, $rowH, 'DF');
            $labelLen = strlen((string) $row['label']);
            $labelSize = $labelLen > 16 ? 6.0 : ($labelLen > 13 ? 6.4 : ($rowH < 4.8 ? 6.8 : 7.2));
            $doc->SetFont('Arial', 'B', $labelSize);
            $doc->SetXY($xStart + $wYear + $wGroup + 0.8, $curY + $cellTextOffsetY);
            $doc->Cell($wCat - 1.6, 3.4, $doc->conv((string) $row['label']), 0, 0, 'C');

            $mx = $xStart + $wYear + $wServices;
            $doc->SetFont('Arial', 'B', $valFontSize);
            for ($m = 1; $m <= 12; $m++) {
                $val = (int) ($row['months'][$m] ?? 0);
                $doc->Rect($mx, $curY, $wMonth, $rowH, 'D');
                $doc->SetXY($mx, $curY + $cellTextOffsetY);
                $doc->Cell($wMonth, 3.4, (string) $val, 0, 0, 'C');
                $mx += $wMonth;
            }

            $doc->SetFillColor(245, 204, 38);
            $doc->Rect($mx, $curY, $wTotal, $rowH, 'DF');
            $doc->SetXY($mx, $curY + $cellTextOffsetY);
            $doc->Cell($wTotal, 3.4, (string) ($row['total'] ?? 0), 0, 0, 'C');

            $curY += $rowH;
        }

        $doc->SetFillColor(118, 146, 60);
        $doc->Rect($xStart + $wYear, $curY, $wServices, $rowH, 'DF');
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->SetXY($xStart + $wYear, $curY + $cellTextOffsetY);
        $doc->Cell($wServices, 3.4, 'TOTAL FOR THE MONTH', 0, 0, 'C');

        $monthlyTotals = $loginData['monthly_totals'] ?? [];
        $mx = $xStart + $wYear + $wServices;
        $doc->SetFillColor(245, 204, 38);
        $doc->SetFont('Arial', 'B', $valFontSize);
        for ($m = 1; $m <= 12; $m++) {
            $mSum = (int) ($monthlyTotals[$m] ?? 0);
            $doc->Rect($mx, $curY, $wMonth, $rowH, 'DF');
            $doc->SetXY($mx, $curY + $cellTextOffsetY);
            $doc->Cell($wMonth, 3.4, (string) $mSum, 0, 0, 'C');
            $mx += $wMonth;
        }

        $doc->SetFillColor(148, 108, 30);
        $doc->Rect($mx, $curY, $wTotal, $rowH, 'DF');
        $doc->SetXY($mx, $curY + $cellTextOffsetY);
        $doc->Cell($wTotal, 3.4, (string) ($loginData['grand_total'] ?? 0), 0, 0, 'C');

        $this->drawAnnualReportSignatories($doc, $settings, $xStart + 6.0, $curY + $rowH + 5.0);

        return $doc;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected function resolveReportGroupRgb(string $colorKey): array
    {
        return match ($colorKey) {
            'blue' => [218, 238, 243],
            'pink' => [230, 184, 183],
            'peach' => [252, 213, 180],
            'slate' => [184, 180, 196],
            'rose' => [242, 220, 219],
            default => [235, 238, 242],
        };
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    protected function drawAnnualReportSignatories(ClinicFpdf $doc, array $settings, float $x, float $y): void
    {
        $nurse = Clinician::where('type', 'Nurse')->where('is_active', true)->first();
        $doctor = Clinician::where('type', 'Doctor')->where('is_active', true)->first();

        $nurseName = $nurse?->name ?: 'KIEN I. IGUP, RN';
        $nurseTitle = $nurse?->title ?: 'School Nurse';

        $doctorName = ! empty($settings['clinic_head'])
            ? (string) $settings['clinic_head']
            : ($doctor?->name ?: 'DR. VICENTE S. QUIMPO, MD');
        $doctorTitle = $doctor?->title ?: 'School Physician';
        $doctorLicense = ! empty($settings['clinic_license_no'])
            ? (string) $settings['clinic_license_no']
            : ($doctor?->license_number ?: '0052551');

        $doc->SetXY($x, $y);
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(65, 4.2, 'Prepared by:', 0, 0, 'L');
        $doc->SetX($x + 95);
        $doc->Cell(75, 4.2, 'Noted by:', 0, 1, 'L');

        $sigY = $y + 11.0;
        $doc->SetXY($x, $sigY);
        $doc->SetFont('Arial', 'B', 9.0);
        $doc->Cell(65, 4.2, $doc->conv(strtoupper($nurseName)), 0, 0, 'L');

        $doc->SetX($x + 95);
        $doc->Cell(75, 4.2, $doc->conv(strtoupper($doctorName)), 0, 1, 'L');

        $doc->SetXY($x, $sigY + 4.2);
        $doc->SetFont('Arial', 'B', 8.0);
        $doc->Cell(65, 3.8, $doc->conv($nurseTitle), 0, 0, 'L');

        $doc->SetX($x + 95);
        $doc->Cell(75, 3.8, $doc->conv($doctorTitle.' (Lic. No. '.$doctorLicense.')'), 0, 1, 'L');
    }

    /**
     * Generate Official Portrait PDF for Report 3: COMMUNICABLE DISEASES DATA (Horizontal Bar Chart).
     *
     * @param  array<string, mixed>  $chartData
     * @param  array<string, mixed>  $settings
     */
    public function communicableDiseasesReport(array $chartData, array $settings): ClinicFpdf
    {
        $year = (int) ($chartData['year'] ?? now()->year);
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', "HSD REPORT - Communicable Diseases ({$year})", true, 'two_rooms');
        $doc->SetMargins(14, 14, 14);
        $doc->SetAutoPageBreak(false);
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(44);

        $boxX = 15.0;
        $boxY = 46.0;
        $boxW = 180.0;
        $boxH = 190.0;

        $doc->SetDrawColor(215, 220, 228);
        $doc->SetLineWidth(0.25);
        $doc->Rect($boxX, $boxY, $boxW, $boxH, 'D');

        $title = (string) ($chartData['title'] ?? "COMMUNICABLE DISEASES {$year} DATA");
        $doc->SetTextColor(25, 25, 25);
        $doc->SetFont('Arial', 'B', 12.5);
        $doc->SetXY($boxX, $boxY + 6.0);
        $doc->Cell($boxW, 6.0, $doc->conv($title), 0, 1, 'C');

        $items = $chartData['items'] ?? [];
        $itemCount = max(1, count($items));

        $chartLeftX = $boxX + 87.0;
        $chartRightX = $boxX + $boxW - 10.0;
        $chartW = $chartRightX - $chartLeftX;
        $chartTopY = $boxY + 18.0;
        $chartBottomY = $boxY + 148.0;
        $chartH = $chartBottomY - $chartTopY;

        $maxVal = 0;
        foreach ($items as $it) {
            $maxVal = max($maxVal, (int) ($it['count'] ?? 0));
        }
        $step = $maxVal > 50 ? 10 : 5;
        $maxScale = max(20, (int) ceil(($maxVal + $step) / $step) * $step);
        $tickCount = (int) round($maxScale / $step);

        $doc->SetDrawColor(225, 229, 235);
        $doc->SetLineWidth(0.2);
        $doc->SetFont('Arial', 'B', 7.5);
        $doc->SetTextColor(40, 40, 40);

        for ($t = 0; $t <= $tickCount; $t++) {
            $val = $t * $step;
            $tx = $chartLeftX + ($val / $maxScale) * $chartW;
            $doc->Line($tx, $chartTopY, $tx, $chartBottomY);
            $doc->SetXY($tx - 4.0, $chartBottomY + 2.0);
            $doc->Cell(8.0, 4.0, (string) $val, 0, 0, 'C');
        }

        $doc->SetDrawColor(190, 196, 205);
        $doc->Line($chartLeftX, $chartTopY, $chartLeftX, $chartBottomY);

        $slotH = $chartH / $itemCount;
        $barH = min(16.0, $slotH * 0.42);
        $labelMaxW = $chartLeftX - ($boxX + 4.0) - 3.0;

        foreach ($items as $idx => $item) {
            $label = (string) ($item['label'] ?? '');
            $count = (int) ($item['count'] ?? 0);
            $centerY = $chartTopY + ($idx + 0.5) * $slotH;
            $barY = $centerY - ($barH / 2);

            $doc->SetFont('Arial', 'B', 7.2);
            $doc->SetTextColor(30, 30, 30);
            $lines = $doc->nbLines($labelMaxW, $doc->conv($label));
            $lineH = 3.5;
            $lblY = $centerY - (($lines * $lineH) / 2);
            $doc->SetXY($boxX + 4.0, $lblY);
            $doc->MultiCell($labelMaxW, $lineH, $doc->conv($label), 0, 'R');

            if ($count > 0) {
                $barW = max(2.2, ($count / $maxScale) * $chartW);
                $doc->SetFillColor(108, 164, 16);
                $doc->SetDrawColor(45, 75, 10);
                $doc->SetLineWidth(0.35);
                $doc->Rect($chartLeftX, $barY, $barW, $barH, 'DF');

                $doc->SetFont('Arial', 'B', 7.5);
                if ($barW >= 8.0) {
                    $doc->SetTextColor(255, 255, 255);
                    $doc->SetXY($chartLeftX + $barW - 7.5, $centerY - 2.0);
                    $doc->Cell(6.0, 4.0, (string) $count, 0, 0, 'R');
                } else {
                    $doc->SetTextColor(30, 30, 30);
                    $doc->SetXY($chartLeftX + $barW + 1.5, $centerY - 2.0);
                    $doc->Cell(8.0, 4.0, (string) $count, 0, 0, 'L');
                }
            }
        }

        $legendY = $chartBottomY + 11.0;
        $legendX = $boxX + 40.0;
        $reversedItems = array_reverse($items);
        foreach ($reversedItems as $item) {
            $doc->SetFillColor(108, 164, 16);
            $doc->SetDrawColor(45, 75, 10);
            $doc->SetLineWidth(0.25);
            $doc->Rect($legendX, $legendY + 0.8, 2.6, 2.6, 'DF');

            $doc->SetFont('Arial', 'B', 8.5);
            $doc->SetTextColor(30, 30, 30);
            $doc->SetXY($legendX + 4.0, $legendY);
            $doc->Cell(125.0, 4.2, $doc->conv((string) ($item['label'] ?? '')), 0, 1, 'L');
            $legendY += 6.2;
        }

        $doc->SetTextColor(0, 0, 0);
        $this->drawAnnualReportSignatories($doc, $settings, 22.0, $boxY + $boxH + 7.0);

        return $doc;
    }

    /**
     * Generate Official Portrait PDF for Report 4: NON-COMMUNICABLE DISEASES DATA (Horizontal Bar Chart).
     *
     * @param  array<string, mixed>  $chartData
     * @param  array<string, mixed>  $settings
     */
    public function nonCommunicableDiseasesReport(array $chartData, array $settings): ClinicFpdf
    {
        $year = (int) ($chartData['year'] ?? now()->year);
        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, '', "HSD REPORT - Non-Communicable Diseases ({$year})", true, 'two_rooms');
        $doc->SetMargins(14, 14, 14);
        $doc->SetAutoPageBreak(false);
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetXY(15.0, 42.0);
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->SetTextColor(25, 25, 25);
        $doc->Cell(100.0, 4.5, 'NON- COMMUNICABLE DISEASES', 0, 1, 'L');

        $boxX = 15.0;
        $boxY = 48.0;
        $boxW = 180.0;
        $boxH = 192.0;

        $doc->SetDrawColor(215, 220, 228);
        $doc->SetLineWidth(0.25);
        $doc->Rect($boxX, $boxY, $boxW, $boxH, 'D');

        $title = (string) ($chartData['title'] ?? "NON- COMMUNICABLE DISEASES {$year} DATA");
        $doc->SetFont('Arial', 'B', 12.5);
        $doc->SetXY($boxX, $boxY + 5.0);
        $doc->Cell($boxW, 6.0, $doc->conv($title), 0, 1, 'C');

        $items = $chartData['items'] ?? [];
        $itemCount = max(1, count($items));

        $chartLeftX = $boxX + 87.0;
        $chartRightX = $boxX + $boxW - 10.0;
        $chartW = $chartRightX - $chartLeftX;
        $chartTopY = $boxY + 15.0;
        $chartBottomY = $boxY + $boxH - 12.0;
        $chartH = $chartBottomY - $chartTopY;

        $maxVal = 0;
        foreach ($items as $it) {
            $maxVal = max($maxVal, (int) ($it['count'] ?? 0));
        }
        $step = $maxVal > 24 ? ($maxVal > 60 ? 10 : 5) : 2;
        $maxScale = max(16, (int) ceil(($maxVal + $step) / $step) * $step);
        $tickCount = (int) round($maxScale / $step);

        $doc->SetDrawColor(225, 229, 235);
        $doc->SetLineWidth(0.2);
        $doc->SetFont('Arial', 'B', 7.5);
        $doc->SetTextColor(40, 40, 40);

        for ($t = 0; $t <= $tickCount; $t++) {
            $val = $t * $step;
            $tx = $chartLeftX + ($val / $maxScale) * $chartW;
            $doc->Line($tx, $chartTopY, $tx, $chartBottomY);
            $doc->SetXY($tx - 4.0, $chartBottomY + 2.0);
            $doc->Cell(8.0, 4.0, (string) $val, 0, 0, 'C');
        }

        $doc->SetDrawColor(190, 196, 205);
        $doc->Line($chartLeftX, $chartTopY, $chartLeftX, $chartBottomY);

        $slotH = $chartH / $itemCount;
        $barH = min(8.2, $slotH * 0.56);
        $labelMaxW = $chartLeftX - ($boxX + 3.0) - 3.0;

        foreach ($items as $idx => $item) {
            $label = (string) ($item['label'] ?? '');
            $count = (int) ($item['count'] ?? 0);
            $centerY = $chartTopY + ($idx + 0.5) * $slotH;
            $barY = $centerY - ($barH / 2);

            $doc->SetFont('Arial', 'B', 6.8);
            $doc->SetTextColor(30, 30, 30);
            $lines = $doc->nbLines($labelMaxW, $doc->conv($label));
            $lineH = 3.1;
            $lblY = $centerY - (($lines * $lineH) / 2);
            $doc->SetXY($boxX + 3.0, $lblY);
            $doc->MultiCell($labelMaxW, $lineH, $doc->conv($label), 0, 'C');

            if ($count > 0) {
                $barW = max(3.5, ($count / $maxScale) * $chartW);
                $doc->SetFillColor(48, 108, 172);
                $doc->SetDrawColor(22, 52, 92);
                $doc->SetLineWidth(0.35);
                $doc->Rect($chartLeftX, $barY, $barW, $barH, 'DF');

                $doc->SetFont('Arial', 'B', 7.2);
                if ($barW >= 6.5) {
                    $doc->SetTextColor(255, 255, 255);
                    $doc->SetXY($chartLeftX + $barW - 6.2, $centerY - 1.8);
                    $doc->Cell(5.0, 3.6, (string) $count, 0, 0, 'R');
                } else {
                    $doc->SetTextColor(30, 30, 30);
                    $doc->SetXY($chartLeftX + $barW + 1.2, $centerY - 1.8);
                    $doc->Cell(7.0, 3.6, (string) $count, 0, 0, 'L');
                }
            }
        }

        $doc->SetTextColor(0, 0, 0);
        $this->drawAnnualReportSignatories($doc, $settings, 22.0, $boxY + $boxH + 6.0);

        return $doc;
    }
}
