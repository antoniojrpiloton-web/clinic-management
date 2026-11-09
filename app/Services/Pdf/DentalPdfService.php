<?php

namespace App\Services\Pdf;

use App\Models\ClinicVisit;
use App\Models\DentalConsultation;
use App\Models\Patient;

class DentalPdfService
{
    protected const PAGE1_YEAR_COLS = ['1st Yr', '2nd Yr', '3rd Yr', '4th Yr', '5th/Emp'];

    protected const PAGE2_YEAR_COLS = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Yr / Emp'];

    public function oralHealthCard(ClinicVisit $visit, array $settings): ClinicFpdf
    {
        $patient = $visit->patient;
        $detail = $visit->dentalConsultation;

        $doc = new ClinicFpdf(
            'P',
            'mm',
            'A4',
            $settings,
            'SCHOOL ORAL HEALTH EXAMINATION CARD',
            'HSD FORM 7- School Oral Health Examination Card',
            true,
            'single_room'
        );
        $doc->SetMargins(12, 12, 12);
        $doc->SetAutoPageBreak(false);
        $doc->AliasNbPages();

        $doc->AddPage();
        $this->renderDentalExaminationPage($doc, $visit, $patient, $detail, $settings);

        $doc->AddPage();
        $this->renderGeneralHealthExaminationPage($doc, $visit, $patient, $detail, $settings);

        return $doc;
    }

    protected function resolveActiveColumnIndex(?Patient $patient): int
    {
        if (! $patient) {
            return 0;
        }

        if ($patient->type !== 'student') {
            return 4;
        }

        $yl = strtolower((string) ($patient->year_level ?? ''));

        if (str_contains($yl, '2')) {
            return 1;
        }
        if (str_contains($yl, '3')) {
            return 2;
        }
        if (str_contains($yl, '4')) {
            return 3;
        }
        if (str_contains($yl, '5') || str_contains($yl, 'grad')) {
            return 4;
        }

        return 0;
    }

    protected function drawCompactBlank(
        ClinicFpdf $doc,
        string $value,
        float $width,
        string $align = 'C',
        bool $bold = true,
        float $maxFont = 7.8
    ): void {
        $x = $doc->GetX();
        $y = $doc->GetY();
        $converted = $doc->conv(trim($value));

        $fontSize = $maxFont;
        $style = $bold ? 'B' : '';
        $doc->SetFont('Arial', $style, $fontSize);

        while ($fontSize > 5.2 && $doc->GetStringWidth($converted) > ($width - 1.6)) {
            $fontSize -= 0.4;
            $doc->SetFont('Arial', $style, $fontSize);
        }

        if ($doc->GetStringWidth($converted) > ($width - 1.2)) {
            while ($converted !== '' && $doc->GetStringWidth($converted.'...') > ($width - 1.2)) {
                $converted = substr($converted, 0, -1);
            }
            $converted .= '...';
        }

        $doc->Cell($width, 3.6, $converted, 0, 0, $align);
        $doc->SetLineWidth(0.2);
        $doc->Line($x, $y + 3.7, $x + $width, $y + 3.7);
    }

    protected function drawCheckBlank(ClinicFpdf $doc, bool $checked, float $width): void
    {
        $x = $doc->GetX();
        $y = $doc->GetY();

        if ($checked) {
            $doc->SetFont('ZapfDingbats', '', 8.5);
            $doc->Cell($width, 3.5, '4', 0, 0, 'C');
        } else {
            $doc->Cell($width, 3.5, '', 0, 0, 'C');
        }

        $doc->SetLineWidth(0.2);
        $doc->Line($x, $y + 3.7, $x + $width, $y + 3.7);
    }

    protected function drawCellCheckmark(ClinicFpdf $doc, float $x, float $y, float $w, float $h, float $fontSize = 7.4): void
    {
        $doc->SetFont('ZapfDingbats', '', $fontSize);
        $doc->SetXY($x, $y + (($h - 2.6) / 2));
        $doc->Cell($w, 2.6, '4', 0, 0, 'C');
    }

    protected function drawFittedCell(
        ClinicFpdf $doc,
        float $x,
        float $y,
        float $w,
        float $h,
        string $text,
        string $align = 'L',
        float $maxFont = 5.6,
        float $minFont = 4.2,
        bool $bold = false
    ): void {
        $clean = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($clean === '') {
            return;
        }

        $conv = $doc->conv($clean);
        $usableW = $w - 1.2;
        $style = $bold ? 'B' : '';

        $fontSize = $maxFont;
        $doc->SetFont('Arial', $style, $fontSize);
        while ($fontSize > $minFont && $doc->GetStringWidth($conv) > $usableW) {
            $fontSize -= 0.3;
            $doc->SetFont('Arial', $style, $fontSize);
        }

        if ($doc->GetStringWidth($conv) <= $usableW) {
            $doc->SetXY($x + 0.6, $y + (($h - 2.4) / 2));
            $doc->Cell($usableW, 2.4, $conv, 0, 0, $align);

            return;
        }

        $words = explode(' ', $conv);
        $line1 = '';
        $line2 = '';
        foreach ($words as $word) {
            $candidate = $line1 === '' ? $word : $line1.' '.$word;
            if ($line2 === '' && $doc->GetStringWidth($candidate) <= $usableW) {
                $line1 = $candidate;
            } else {
                $line2 = $line2 === '' ? $word : $line2.' '.$word;
            }
        }

        if ($line1 === '') {
            $line1 = $conv;
            while ($line1 !== '' && $doc->GetStringWidth($line1) > $usableW) {
                $line1 = substr($line1, 0, -1);
            }
        }

        while ($line2 !== '' && $doc->GetStringWidth($line2) > $usableW) {
            $line2 = substr($line2, 0, -1);
        }

        if ($line2 === '') {
            $doc->SetXY($x + 0.6, $y + (($h - 2.4) / 2));
            $doc->Cell($usableW, 2.4, $line1, 0, 0, $align);
        } else {
            $doc->SetXY($x + 0.6, $y + 0.25);
            $doc->Cell($usableW, 1.65, $line1, 0, 0, $align);
            $doc->SetXY($x + 0.6, $y + 1.85);
            $doc->Cell($usableW, 1.65, $line2, 0, 0, $align);
        }
    }

    protected function renderDentalExaminationPage(
        ClinicFpdf $doc,
        ClinicVisit $visit,
        ?Patient $patient,
        ?DentalConsultation $detail,
        array $settings
    ): void {
        $x = 12.0;
        $activeCol = $this->resolveActiveColumnIndex($patient);

        $doc->SetY(35.5);
        $doc->SetFont('Arial', 'B', 10.5);
        $doc->Cell(0, 4.2, 'SCHOOL ORAL HEALTH EXAMINATION CARD', 0, 1, 'C');
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell(0, 3.5, 'College Students & University Personnel', 0, 1, 'C');
        $doc->Ln(1.2);

        $schoolAffiliation = (string) ($settings['university_name'] ?? 'Surigao del Norte State University');
        if ($patient?->type === 'student' && ($patient->program?->code || $patient->year_level)) {
            $schoolAffiliation .= ' — '.trim(($patient->program?->code ?? '').' '.($patient->year_level ?? ''));
        } elseif ($patient?->department) {
            $schoolAffiliation .= ' — '.$patient->department;
        }

        $doc->SetX($x);
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell(13.0, 3.8, 'NAME:', 0, 0, 'L');
        $this->drawCompactBlank($doc, (string) ($patient?->full_name ?? ''), 120.0, 'L', true, 8.2);
        $doc->Ln(4.8);

        $doc->SetX($x);
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell(27.0, 3.8, 'COLLEGE / DEPT.:', 0, 0, 'L');
        $this->drawCompactBlank($doc, $schoolAffiliation, 146.0, 'L', false, 7.8);
        $doc->Ln(5.0);

        $sec1Y = $doc->GetY();
        $medHistX = $x;
        $medHistW = 68.0;
        $guidedX = $x + 73.0;

        $doc->SetXY($medHistX, $sec1Y);
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell($medHistW, 3.6, 'MEDICAL HISTORY', 0, 0, 'L');

        $doc->SetXY($guidedX, $sec1Y);
        $doc->Cell(100.0, 3.6, 'Guided Questions', 0, 1, 'L');

        $tableY = $sec1Y + 4.0;
        $colCondW = 48.0;
        $colYesW = 10.0;
        $colNoW = 10.0;
        $mhRowH = 3.1;

        $doc->SetLineWidth(0.22);
        $doc->SetFont('Arial', 'B', 6.6);
        $doc->Rect($medHistX, $tableY, $colCondW, $mhRowH);
        $doc->Rect($medHistX + $colCondW, $tableY, $colYesW, $mhRowH);
        $doc->Rect($medHistX + $colCondW + $colYesW, $tableY, $colNoW, $mhRowH);
        $doc->SetXY($medHistX + $colCondW, $tableY + 0.3);
        $doc->Cell($colYesW, 2.5, 'YES', 0, 0, 'C');
        $doc->Cell($colNoW, 2.5, 'NO', 0, 0, 'C');

        $medHistoryText = strtolower(
            ($patient?->allergies ?? '').' '.
            ($patient?->medical_history ?? '').' '.
            ($detail?->systemic_allergy_details ?? '')
        );

        $medHistoryRows = [
            ['label' => 'Allergy', 'yes' => (bool) ($detail?->systemic_allergies || ($patient?->allergies && ! str_contains(strtolower($patient->allergies), 'none')))],
            ['label' => 'Asthma', 'yes' => str_contains($medHistoryText, 'asthma')],
            ['label' => 'Anemia', 'yes' => str_contains($medHistoryText, 'anemia')],
            ['label' => 'Bleeding Problem', 'yes' => (bool) ($detail?->systemic_bleeding_disorder || str_contains($medHistoryText, 'bleed'))],
            ['label' => 'Heart Ailment', 'yes' => (bool) ($detail?->systemic_heart_disease || str_contains($medHistoryText, 'heart'))],
            ['label' => 'Diabetes', 'yes' => (bool) ($detail?->systemic_diabetes || str_contains($medHistoryText, 'diabet'))],
            ['label' => 'Kidney Disease', 'yes' => str_contains($medHistoryText, 'kidney') || str_contains($medHistoryText, 'renal')],
            ['label' => 'Convulsion', 'yes' => str_contains($medHistoryText, 'convuls') || str_contains($medHistoryText, 'seizure')],
            ['label' => 'Fainting', 'yes' => str_contains($medHistoryText, 'faint') || str_contains($medHistoryText, 'syncope')],
        ];

        $curMhY = $tableY + $mhRowH;
        foreach ($medHistoryRows as $mhRow) {
            $doc->Rect($medHistX, $curMhY, $colCondW, $mhRowH);
            $doc->Rect($medHistX + $colCondW, $curMhY, $colYesW, $mhRowH);
            $doc->Rect($medHistX + $colCondW + $colYesW, $curMhY, $colNoW, $mhRowH);

            $doc->SetFont('Arial', 'B', 6.6);
            $doc->SetXY($medHistX + 1.0, $curMhY + 0.3);
            $doc->Cell($colCondW - 2.0, 2.5, $mhRow['label'], 0, 0, 'L');

            if ($mhRow['yes']) {
                $this->drawCellCheckmark($doc, $medHistX + $colCondW, $curMhY, $colYesW, $mhRowH, 7.0);
            } else {
                $this->drawCellCheckmark($doc, $medHistX + $colCondW + $colYesW, $curMhY, $colNoW, $mhRowH, 7.0);
            }

            $curMhY += $mhRowH;
        }

        $hasBrush = $detail?->oral_hygiene_status !== 'Poor';
        $brushOnce = $detail?->oral_hygiene_status === 'Poor';
        $brush2x = $detail?->oral_hygiene_status === 'Fair';
        $brush3x = $detail?->oral_hygiene_status === 'Good' || ! $detail?->oral_hygiene_status;
        $visitOnce = $detail?->dental_treatment_frequency === 'occasional' || ! $detail?->dental_treatment_frequency;
        $visit2x = $detail?->dental_treatment_frequency === 'regular';

        $gqY = $tableY + 0.5;
        $doc->SetFont('Arial', '', 7.2);

        $doc->SetXY($guidedX, $gqY);
        $doc->Cell(34.0, 3.6, 'Do you have a toothbrush?', 0, 0, 'L');
        $this->drawCheckBlank($doc, $hasBrush, 14.0);
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->Cell(4.5, 3.6, 'Y', 0, 0, 'C');
        $this->drawCheckBlank($doc, ! $hasBrush, 14.0);
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->Cell(4.5, 3.6, 'N', 0, 1, 'C');

        $gqY += 6.0;
        $doc->SetXY($guidedX, $gqY);
        $doc->SetFont('Arial', '', 7.2);
        $doc->Cell(48.0, 3.6, 'How many times do you brush your teeth?', 0, 0, 'L');
        $this->drawCheckBlank($doc, $brushOnce, 8.5);
        $doc->SetFont('Arial', '', 7.0);
        $doc->Cell(7.5, 3.6, 'once', 0, 0, 'C');
        $this->drawCheckBlank($doc, $brush2x, 8.5);
        $doc->SetFont('Arial', '', 7.0);
        $doc->Cell(5.5, 3.6, '2x', 0, 0, 'C');
        $this->drawCheckBlank($doc, $brush3x, 8.5);
        $doc->SetFont('Arial', '', 7.0);
        $doc->Cell(5.5, 3.6, '3x', 0, 1, 'C');

        $gqY += 6.0;
        $doc->SetXY($guidedX, $gqY);
        $doc->SetFont('Arial', '', 7.2);
        $doc->Cell(65.0, 3.6, 'How many times do you change your toothbrush in a year?', 0, 0, 'L');
        $this->drawCompactBlank($doc, '3-4x', 18.0, 'C', false, 7.2);

        $gqY += 6.0;
        $doc->SetXY($guidedX, $gqY);
        $doc->SetFont('Arial', '', 7.2);
        $doc->Cell(44.0, 3.6, 'Do you use toothpaste in brushing?', 0, 0, 'L');
        $this->drawCheckBlank($doc, true, 14.0);
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->Cell(4.5, 3.6, 'Y', 0, 0, 'C');
        $this->drawCheckBlank($doc, false, 14.0);
        $doc->SetFont('Arial', 'B', 7.2);
        $doc->Cell(4.5, 3.6, 'N', 0, 1, 'C');

        $gqY += 6.0;
        $doc->SetXY($guidedX, $gqY);
        $doc->SetFont('Arial', '', 7.2);
        $doc->Cell(57.0, 3.6, 'How many times do you visit the dentist in a year?', 0, 0, 'L');
        $this->drawCheckBlank($doc, $visitOnce, 10.0);
        $doc->SetFont('Arial', '', 7.0);
        $doc->Cell(7.5, 3.6, 'once', 0, 0, 'C');
        $this->drawCheckBlank($doc, $visit2x, 10.0);
        $doc->SetFont('Arial', '', 7.0);
        $doc->Cell(5.5, 3.6, '2x', 0, 1, 'C');

        $sec2Y = $curMhY + 2.5;
        $odontX = $x;
        $odontW = 106.0;
        $oralCondX = $x + 110.0;
        $oralCondW = 76.0;

        $doc->SetXY($odontX, $sec2Y);
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell($odontW, 3.6, 'CONDITION AND TREATMENT NEEDS', 0, 0, 'C');

        $doc->SetXY($oralCondX, $sec2Y);
        $doc->Cell($oralCondW, 3.6, 'ORAL HEALTH CONDITION', 0, 1, 'C');

        $odontBodyY = $sec2Y + 4.0;
        $this->drawOfficialOdontogram($doc, $odontX, $odontBodyY, $detail);
        $this->drawOralHealthConditionTable($doc, $oralCondX, $odontBodyY, $oralCondW, $detail, $activeCol);

        $sec3Y = $odontBodyY + 51.0;
        $procW = 111.0;
        $idxX = $x + 114.5;
        $idxW = 71.5;

        $this->drawDentalProceduresTable($doc, $odontX, $sec3Y, $procW, $visit, $detail, $activeCol);
        $this->drawDftAndDmftIndexTables($doc, $idxX, $sec3Y, $idxW, $detail, $activeCol);

        $sec4Y = $sec3Y + 51.8;
        $this->drawBottomSymbolsLegend($doc, $x, $sec4Y);
    }

    protected function drawOfficialOdontogram(ClinicFpdf $doc, float $x, float $y, ?DentalConsultation $detail): void
    {
        $permChart = (array) ($detail?->tooth_chart ?? []);
        $primChart = (array) ($detail?->primary_tooth_chart ?? []);

        $cellW = 5.2;
        $condH = 2.9;
        $numH = 2.8;
        $toothH = 3.7;

        $permStartX = $x + 12.0;
        $tempStartX = $permStartX + (3 * $cellW);
        $upperTempTeeth = [55, 54, 53, 52, 51, 61, 62, 63, 64, 65];

        $doc->SetFont('Arial', 'B', 5.8);
        $doc->SetXY($x, $y + 0.3);
        $doc->Cell(26.0, 2.6, 'RIGHT', 0, 0, 'C');
        $doc->SetXY($x, $y + 2.9);
        $doc->Cell(26.0, 2.6, 'CONDITION', 0, 0, 'C');
        $doc->SetXY($x, $y + 5.5);
        $doc->Cell(26.0, 2.6, 'TEMPORARY TEETH', 0, 0, 'C');

        $doc->SetXY($tempStartX + (10 * $cellW) + 2.0, $y + 1.5);
        $doc->Cell(12.0, 2.6, 'LEFT', 0, 0, 'L');

        for ($r = 0; $r < 2; $r++) {
            $ry = $y + ($r * $condH);
            foreach ($upperTempTeeth as $idx => $tNum) {
                $cx = $tempStartX + ($idx * $cellW);
                $doc->Rect($cx, $ry, $cellW, $condH);
                if ($r === 1 && ! empty($primChart[(string) $tNum])) {
                    $doc->SetFont('Arial', 'B', 5.8);
                    $doc->SetXY($cx, $ry + 0.25);
                    $doc->Cell($cellW, 2.4, $doc->conv((string) $primChart[(string) $tNum]), 0, 0, 'C');
                }
            }
        }

        $numY = $y + (2 * $condH);
        $doc->SetFont('Arial', 'B', 6.0);
        foreach ($upperTempTeeth as $idx => $tNum) {
            $cx = $tempStartX + ($idx * $cellW);
            $doc->Rect($cx, $numY, $cellW, $numH);
            $doc->SetXY($cx, $numY + 0.2);
            $doc->Cell($cellW, 2.4, (string) $tNum, 0, 0, 'C');
        }

        $tBoxY = $numY + $numH;
        foreach ($upperTempTeeth as $idx => $tNum) {
            $cx = $tempStartX + ($idx * $cellW);
            $this->drawCrossedToothBox($doc, $cx, $tBoxY, $cellW, $toothH, (string) ($primChart[(string) $tNum] ?? ''));
        }

        $permY = $tBoxY + $toothH;
        $upperPermTeeth = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
        $lowerPermTeeth = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];

        $doc->SetFont('Arial', 'B', 4.6);
        $doc->SetXY($x, $permY + 8.2);
        $doc->Cell(11.5, 2.2, 'PERMANENT', 0, 0, 'C');
        $doc->SetXY($x, $permY + 10.5);
        $doc->Cell(11.5, 2.2, 'TEETH', 0, 0, 'C');

        for ($r = 0; $r < 2; $r++) {
            $ry = $permY + ($r * $condH);
            foreach ($upperPermTeeth as $idx => $tNum) {
                $cx = $permStartX + ($idx * $cellW);
                $doc->Rect($cx, $ry, $cellW, $condH);
                if ($r === 1 && ! empty($permChart[(string) $tNum])) {
                    $doc->SetFont('Arial', 'B', 5.8);
                    $doc->SetXY($cx, $ry + 0.25);
                    $doc->Cell($cellW, 2.4, $doc->conv((string) $permChart[(string) $tNum]), 0, 0, 'C');
                }
            }
        }

        $upNumY = $permY + (2 * $condH);
        $doc->SetFont('Arial', 'B', 5.8);
        foreach ($upperPermTeeth as $idx => $tNum) {
            $cx = $permStartX + ($idx * $cellW);
            $doc->Rect($cx, $upNumY, $cellW, $numH);
            $doc->SetXY($cx, $upNumY + 0.2);
            $doc->Cell($cellW, 2.4, (string) $tNum, 0, 0, 'C');
        }

        $upToothY = $upNumY + $numH;
        foreach ($upperPermTeeth as $idx => $tNum) {
            $cx = $permStartX + ($idx * $cellW);
            $this->drawCrossedToothBox($doc, $cx, $upToothY, $cellW, $toothH, (string) ($permChart[(string) $tNum] ?? ''));
        }

        $lowToothY = $upToothY + $toothH;
        foreach ($lowerPermTeeth as $idx => $tNum) {
            $cx = $permStartX + ($idx * $cellW);
            $this->drawCrossedToothBox($doc, $cx, $lowToothY, $cellW, $toothH, (string) ($permChart[(string) $tNum] ?? ''));
        }

        $lowNumY = $lowToothY + $toothH;
        $doc->SetFont('Arial', 'B', 5.8);
        foreach ($lowerPermTeeth as $idx => $tNum) {
            $cx = $permStartX + ($idx * $cellW);
            $doc->Rect($cx, $lowNumY, $cellW, $numH);
            $doc->SetXY($cx, $lowNumY + 0.2);
            $doc->Cell($cellW, 2.4, (string) $tNum, 0, 0, 'C');
        }

        $lowCondY = $lowNumY + $numH;
        for ($r = 0; $r < 2; $r++) {
            $ry = $lowCondY + ($r * $condH);
            foreach ($lowerPermTeeth as $idx => $tNum) {
                $cx = $permStartX + ($idx * $cellW);
                $doc->Rect($cx, $ry, $cellW, $condH);
                if ($r === 0 && ! empty($permChart[(string) $tNum])) {
                    $doc->SetFont('Arial', 'B', 5.8);
                    $doc->SetXY($cx, $ry + 0.25);
                    $doc->Cell($cellW, 2.4, $doc->conv((string) $permChart[(string) $tNum]), 0, 0, 'C');
                }
            }
        }

        $lowTempY = $lowCondY + (2 * $condH);
        $lowerTempTeeth = [85, 84, 83, 82, 81, 71, 72, 73, 74, 75];

        foreach ($lowerTempTeeth as $idx => $tNum) {
            $cx = $tempStartX + ($idx * $cellW);
            $this->drawCrossedToothBox($doc, $cx, $lowTempY, $cellW, $toothH, (string) ($primChart[(string) $tNum] ?? ''));
        }

        $lowTempNumY = $lowTempY + $toothH;
        $doc->SetFont('Arial', 'B', 6.0);
        foreach ($lowerTempTeeth as $idx => $tNum) {
            $cx = $tempStartX + ($idx * $cellW);
            $doc->Rect($cx, $lowTempNumY, $cellW, $numH);
            $doc->SetXY($cx, $lowTempNumY + 0.2);
            $doc->Cell($cellW, 2.4, (string) $tNum, 0, 0, 'C');
        }

        $lowTempCondY = $lowTempNumY + $numH;
        for ($r = 0; $r < 2; $r++) {
            $ry = $lowTempCondY + ($r * $condH);
            foreach ($lowerTempTeeth as $idx => $tNum) {
                $cx = $tempStartX + ($idx * $cellW);
                $doc->Rect($cx, $ry, $cellW, $condH);
                if ($r === 0 && ! empty($primChart[(string) $tNum])) {
                    $doc->SetFont('Arial', 'B', 5.8);
                    $doc->SetXY($cx, $ry + 0.25);
                    $doc->Cell($cellW, 2.4, $doc->conv((string) $primChart[(string) $tNum]), 0, 0, 'C');
                }
            }
        }

        $doc->SetFont('Arial', 'B', 5.8);
        $doc->SetXY($x, $lowTempY + 2.4);
        $doc->Cell(26.0, 2.6, 'TEMPORARY TEETH', 0, 0, 'C');
        $doc->SetXY($x, $lowTempY + 5.0);
        $doc->Cell(26.0, 2.6, 'CONDITION', 0, 0, 'C');
        $doc->SetXY($x, $lowTempY + 7.6);
        $doc->Cell(26.0, 2.6, 'RIGHT', 0, 0, 'C');

        $doc->SetXY($tempStartX + (10 * $cellW) + 2.0, $lowTempY + 7.6);
        $doc->Cell(12.0, 2.6, 'LEFT', 0, 0, 'L');
    }

    protected function drawCrossedToothBox(ClinicFpdf $doc, float $x, float $y, float $w, float $h, string $cond): void
    {
        $doc->Rect($x, $y, $w, $h);
        $doc->Line($x, $y, $x + $w, $y + $h);
        $doc->Line($x + $w, $y, $x, $y + $h);

        $cx = $x + ($w / 2.0);
        $cy = $y + ($h / 2.0);
        $style = ($cond === 'F' || $cond === 'C') ? 'DF' : 'D';
        if ($style === 'DF') {
            $doc->SetFillColor(40, 40, 40);
        }
        $doc->drawOval($cx, $cy, 1.35, 1.05, $style);
    }

    protected function drawOralHealthConditionTable(
        ClinicFpdf $doc,
        float $x,
        float $y,
        float $w,
        ?DentalConsultation $detail,
        int $activeCol
    ): void {
        $cols = self::PAGE1_YEAR_COLS;
        $colLabelW = 33.5;
        $colYearW = ($w - $colLabelW) / count($cols);
        $rowH = 3.75;

        $doc->SetFont('Arial', 'B', 5.8);
        $doc->Rect($x, $y, $colLabelW, $rowH);
        foreach ($cols as $idx => $colTitle) {
            $gx = $x + $colLabelW + ($idx * $colYearW);
            $doc->Rect($gx, $y, $colYearW, $rowH);
            $doc->SetXY($gx, $y + 0.6);
            $doc->Cell($colYearW, 2.5, $colTitle, 0, 0, 'C');
        }

        $perio = strtolower((string) ($detail?->periodontal_status ?? ''));
        $gingival = strtolower((string) ($detail?->gingival_index ?? ''));
        $calc = strtolower((string) ($detail?->calculus_index ?? ''));
        $findingsText = strtolower((string) ($detail?->oral_examination_findings ?? '').' '.($detail?->diagnosis ?? ''));
        $chartValues = array_values((array) ($detail?->tooth_chart ?? []));

        $conditions = [
            ['label' => 'Gingivitis', 'active' => $perio === 'gingivitis' || in_array($gingival, ['mild', 'moderate', 'severe'], true) || (bool) $detail?->has_bleeding_gums],
            ['label' => 'Periodontal Disease', 'active' => str_contains($perio, 'periodontitis')],
            ['label' => 'Malocclussion', 'active' => str_contains($findingsText, 'malocclusion')],
            ['label' => 'Supernumerary teeth', 'active' => str_contains($findingsText, 'supernumerary')],
            ['label' => 'Retained Deciduous teeth', 'active' => str_contains($findingsText, 'retained')],
            ['label' => 'Decubital Ulcer', 'active' => $detail?->soft_tissue_lips === 'ulcerated' || $detail?->soft_tissue_cheeks === 'ulcerated' || str_contains($findingsText, 'ulcer')],
            ['label' => 'Calculus', 'active' => in_array($calc, ['light', 'moderate', 'heavy'], true)],
            ['label' => 'Cleft lip/Palate', 'active' => str_contains($findingsText, 'cleft')],
            ['label' => 'Root fragment', 'active' => in_array('RF', $chartValues, true) || str_contains($findingsText, 'root fragment')],
            ['label' => 'Flourosis', 'active' => str_contains($findingsText, 'fluorosis')],
            ['label' => 'Other, Specify', 'active' => false],
        ];

        $curY = $y + $rowH;
        foreach ($conditions as $condRow) {
            $doc->Rect($x, $curY, $colLabelW, $rowH);
            $doc->SetFont('Arial', 'B', 6.2);
            $doc->SetXY($x + 0.8, $curY + 0.6);
            $doc->Cell($colLabelW - 1.6, 2.5, $condRow['label'], 0, 0, 'L');

            foreach ($cols as $idx => $colTitle) {
                $gx = $x + $colLabelW + ($idx * $colYearW);
                $doc->Rect($gx, $curY, $colYearW, $rowH);
                if ($idx === $activeCol && $condRow['active']) {
                    $this->drawCellCheckmark($doc, $gx, $curY, $colYearW, $rowH, 7.4);
                }
            }
            $curY += $rowH;
        }
    }

    protected function drawDentalProceduresTable(
        ClinicFpdf $doc,
        float $x,
        float $y,
        float $w,
        ClinicVisit $visit,
        ?DentalConsultation $detail,
        int $activeCol
    ): void {
        $doc->SetXY($x, $y);
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell($w, 3.5, 'DENTAL PROCEDURES', 0, 1, 'C');

        $cols = self::PAGE1_YEAR_COLS;
        $tableY = $y + 3.7;
        $colProcW = 28.0;
        $colYearW = 8.2;
        $colRemW = $w - ($colProcW + (count($cols) * $colYearW));
        $rowH = 3.55;

        $doc->SetFont('Arial', 'B', 5.8);
        $doc->Rect($x, $tableY, $colProcW, $rowH);
        foreach ($cols as $idx => $colTitle) {
            $gx = $x + $colProcW + ($idx * $colYearW);
            $doc->Rect($gx, $tableY, $colYearW, $rowH);
            $doc->SetXY($gx, $tableY + 0.5);
            $doc->Cell($colYearW, 2.5, $colTitle, 0, 0, 'C');
        }

        $remX = $x + $colProcW + (count($cols) * $colYearW);
        $doc->Rect($remX, $tableY, $colRemW, $rowH);
        $doc->SetXY($remX, $tableY + 0.5);
        $doc->Cell($colRemW, 2.5, 'Remarks', 0, 0, 'C');

        $procs = array_map('strtolower', (array) ($detail?->procedures ?? []));
        $hasProc = static function (string $needle) use ($procs): bool {
            foreach ($procs as $p) {
                if (str_contains($p, $needle)) {
                    return true;
                }
            }

            return false;
        };

        $dentistInitials = $visit->clinician?->name ?? 'University Dentist';
        $anesthesiaRemark = trim((string) ($detail?->anesthesia_used ?? ''));
        $treatmentRemark = (string) ($detail?->treatment_rendered ?: ($detail?->diagnosis ?? ''));

        $rows = [
            ['label' => 'DATE', 'val' => $visit->visit_date->format('m/d/y'), 'remark' => $detail?->teeth_involved ? 'Teeth: '.$detail->teeth_involved : ''],
            ['label' => 'Examination', 'val' => $hasProc('examination') || count($procs) > 0 ? '/' : '', 'remark' => (string) ($detail?->diagnosis ?? '')],
            ['label' => 'Sealant ( G I )', 'val' => $hasProc('sealant') ? '/' : '', 'remark' => ''],
            ['label' => 'Gum Treatment', 'val' => $hasProc('gum') ? '/' : '', 'remark' => ''],
            ['label' => 'Permanent Filling', 'val' => $hasProc('permanent filling') ? '/' : '', 'remark' => ''],
            ['label' => 'ART', 'val' => $hasProc('temporary filling') ? '/' : '', 'remark' => ''],
            ['label' => 'Extraction', 'val' => $hasProc('extraction') ? '/' : '', 'remark' => $anesthesiaRemark],
            ['label' => 'Oral Prophylaxis', 'val' => $hasProc('prophylaxis') ? '/' : '', 'remark' => ''],
            ['label' => 'Referral', 'val' => $hasProc('referral') ? '/' : '', 'remark' => ''],
            ['label' => 'Other oral treatment', 'val' => $hasProc('fluoride') || $hasProc('prescription') ? '/' : '', 'remark' => $treatmentRemark],
            ['label' => 'Post-Op / Advice', 'val' => '', 'remark' => (string) ($detail?->recommendations ?? '')],
            ['label' => 'Examined by:', 'val' => '', 'remark' => $dentistInitials],
        ];

        $curY = $tableY + $rowH;
        foreach ($rows as $r) {
            $doc->Rect($x, $curY, $colProcW, $rowH);
            $doc->SetFont('Arial', 'B', 6.0);
            $doc->SetXY($x + 0.7, $curY + 0.5);
            $doc->Cell($colProcW - 1.2, 2.5, $r['label'], 0, 0, 'L');

            foreach ($cols as $idx => $colTitle) {
                $gx = $x + $colProcW + ($idx * $colYearW);
                $doc->Rect($gx, $curY, $colYearW, $rowH);
                if ($idx === $activeCol && $r['val'] !== '') {
                    if ($r['val'] === '/') {
                        $this->drawCellCheckmark($doc, $gx, $curY, $colYearW, $rowH, 7.4);
                    } else {
                        $doc->SetFont('Arial', 'B', $r['label'] === 'DATE' ? 5.2 : 6.5);
                        $doc->SetXY($gx, $curY + 0.5);
                        $doc->Cell($colYearW, 2.5, $doc->conv($r['val']), 0, 0, 'C');
                    }
                }
            }

            $doc->Rect($remX, $curY, $colRemW, $rowH);
            if ($r['remark'] !== '') {
                $this->drawFittedCell(
                    $doc,
                    $remX,
                    $curY,
                    $colRemW,
                    $rowH,
                    $r['remark'],
                    'L',
                    5.4,
                    4.1,
                    $r['label'] === 'Examined by:'
                );
            }

            $curY += $rowH;
        }
    }

    protected function drawDftAndDmftIndexTables(
        ClinicFpdf $doc,
        float $x,
        float $y,
        float $w,
        ?DentalConsultation $detail,
        int $activeCol
    ): void {
        $cols = self::PAGE1_YEAR_COLS;
        $labelW = 26.5;
        $gridX = $x + $labelW;
        $colYearW = ($w - $labelW) / count($cols);
        $rowH = 3.5;

        $doc->SetFont('Arial', 'B', 6.2);
        $doc->SetXY($gridX, $y);
        $doc->Cell($w - $labelW, 3.3, 'dft Index', 0, 1, 'C');

        $t1Y = $y + 3.5;
        $doc->SetXY($x, $t1Y);
        $doc->SetFont('Arial', 'B', 6.0);
        $doc->Cell($labelW, $rowH, 'TEMPORARY TEETH', 0, 0, 'L');

        $doc->SetFont('Arial', 'B', 5.6);
        foreach ($cols as $idx => $colTitle) {
            $gx = $gridX + ($idx * $colYearW);
            $doc->Rect($gx, $t1Y, $colYearW, $rowH);
            $doc->SetXY($gx, $t1Y + 0.5);
            $doc->Cell($colYearW, 2.5, $colTitle, 0, 0, 'C');
        }

        $primChart = (array) ($detail?->primary_tooth_chart ?? []);
        $dftDecayed = count(array_filter($primChart, fn ($v) => in_array($v, ['C', 'X', 'RF'], true)));
        $dftFilled = count(array_filter($primChart, fn ($v) => $v === 'F'));
        $dftTotal = $dftDecayed + $dftFilled;

        $dftRows = [
            ['label' => 'Index: d.f.t.', 'val' => ''],
            ['label' => 'No. T/decayed', 'val' => (string) $dftDecayed],
            ['label' => 'No. T/filled', 'val' => (string) $dftFilled],
            ['label' => 'Total: d.f.t', 'val' => (string) $dftTotal],
        ];

        $curY = $t1Y + $rowH;
        foreach ($dftRows as $idx => $dRow) {
            $doc->SetFont('Arial', $idx === 0 ? 'B' : '', 6.0);
            $doc->SetXY($x, $curY + 0.5);
            $doc->Cell($labelW, 2.5, $dRow['label'], 0, 0, 'L');

            if ($idx > 0) {
                foreach ($cols as $cIdx => $colTitle) {
                    $gx = $gridX + ($cIdx * $colYearW);
                    $doc->Rect($gx, $curY, $colYearW, $rowH);
                    if ($cIdx === $activeCol && $dRow['val'] !== '') {
                        $doc->SetFont('Arial', 'B', 6.2);
                        $doc->SetXY($gx, $curY + 0.5);
                        $doc->Cell($colYearW, 2.5, $dRow['val'], 0, 0, 'C');
                    }
                }
            }
            $curY += $rowH;
        }

        $t2HeaderY = $curY + 2.2;
        $doc->SetFont('Arial', 'B', 6.2);
        $doc->SetXY($gridX, $t2HeaderY);
        $doc->Cell($w - $labelW, 3.3, 'DMFMT Index', 0, 1, 'C');

        $t2Y = $t2HeaderY + 3.5;
        $doc->SetXY($x, $t2Y);
        $doc->SetFont('Arial', 'B', 6.0);
        $doc->Cell($labelW, $rowH, 'PERMANENT TEETH', 0, 0, 'L');

        $doc->SetFont('Arial', 'B', 5.6);
        foreach ($cols as $idx => $colTitle) {
            $gx = $gridX + ($idx * $colYearW);
            $doc->Rect($gx, $t2Y, $colYearW, $rowH);
            $doc->SetXY($gx, $t2Y + 0.5);
            $doc->Cell($colYearW, 2.5, $colTitle, 0, 0, 'C');
        }

        $d = (int) ($detail?->dmft_decayed ?? 0);
        $m = (int) ($detail?->dmft_missing ?? 0);
        $f = (int) ($detail?->dmft_filled ?? 0);
        $dmftTot = $d + $m + $f;
        $soundTeeth = max(0, 32 - $dmftTot);

        $dmftRows = [
            ['label' => 'Index: D.M.F.T', 'val' => ''],
            ['label' => 'No. T/decayed', 'val' => (string) $d],
            ['label' => 'No. T/Missing', 'val' => (string) $m],
            ['label' => 'No. T/filled', 'val' => (string) $f],
            ['label' => 'Total D.M.F.T.', 'val' => (string) $dmftTot],
            ['label' => 'Total Sound Teeth', 'val' => (string) $soundTeeth],
        ];

        $curY = $t2Y + $rowH;
        foreach ($dmftRows as $idx => $mRow) {
            $doc->SetFont('Arial', $idx === 0 || $idx >= 4 ? 'B' : '', 6.0);
            $doc->SetXY($x, $curY + 0.5);
            $doc->Cell($labelW, 2.5, $mRow['label'], 0, 0, 'L');

            if ($idx > 0) {
                foreach ($cols as $cIdx => $colTitle) {
                    $gx = $gridX + ($cIdx * $colYearW);
                    $doc->Rect($gx, $curY, $colYearW, $rowH);
                    if ($cIdx === $activeCol && $mRow['val'] !== '') {
                        $doc->SetFont('Arial', 'B', 6.2);
                        $doc->SetXY($gx, $curY + 0.5);
                        $doc->Cell($colYearW, 2.5, $mRow['val'], 0, 0, 'C');
                    }
                }
            }
            $curY += $rowH;
        }
    }

    protected function drawBottomSymbolsLegend(ClinicFpdf $doc, float $x, float $y): void
    {
        $doc->SetFont('Arial', 'B', 7.0);
        $doc->SetXY($x + 18.0, $y);
        $doc->Cell(75.0, 3.4, 'SYMBOL FOR MOUTH EXAMINATION', 0, 0, 'L');

        $doc->SetXY($x + 114.5, $y);
        $doc->Cell(70.0, 3.4, 'SYMBOLS FOR ACCOMPLISHMENT', 0, 1, 'L');

        $col1X = $x;
        $col2X = $x + 54.0;
        $col3X = $x + 114.5;
        $col4X = $x + 152.5;

        $leftCol1 = [
            'X - Tooth indicated for extraction',
            'F - Tooth indicated for filling',
            'Outline of filling - Tooth with',
            '              temporary filling',
            'Heavy Shade - Permanent filling',
            'RD - recurrence of decay',
            'RF - Root fragment',
            'M - Missing tooth',
        ];

        $leftCol2 = [
            'DU - Decubital ulcer',
            'Mal - Malocclusion',
            'ME - Mottled Enamel',
            'Gn - Normal',
            'Grn - Moderate gingvitis (1-2 quadrant)',
            'Gs - Servere gingvitis (3-4 quadrant)',
            'CMR - complete mouth rehab',
            '(/) - Sound/erupted Permanent tooth',
        ];

        $rightCol1 = [
            'Xt - Extracted permanent tooth',
            'xt - Extracted temporary tooth',
            'Ag F - Amalgam filling',
            'Sy F - Synthetic filling',
        ];

        $rightCol2 = [
            'Op - Oral Prophylaxis',
            'ZOE - Zinc Oxide Eugenol filling',
            'TF - Temporary filling',
            'R - Referred',
        ];

        $doc->SetFont('Arial', 'B', 5.8);
        $ly = $y + 3.8;
        foreach ($leftCol1 as $i => $line) {
            $doc->SetXY($col1X, $ly + ($i * 2.9));
            $doc->Cell(52.0, 2.6, $line, 0, 0, 'L');
        }
        foreach ($leftCol2 as $i => $line) {
            $doc->SetXY($col2X, $ly + ($i * 2.9));
            $doc->Cell(52.0, 2.6, $line, 0, 0, 'L');
        }
        foreach ($rightCol1 as $i => $line) {
            $doc->SetXY($col3X, $ly + ($i * 2.9));
            $doc->Cell(38.0, 2.6, $line, 0, 0, 'L');
        }
        foreach ($rightCol2 as $i => $line) {
            $doc->SetXY($col4X, $ly + ($i * 2.9));
            $doc->Cell(38.0, 2.6, $line, 0, 0, 'L');
        }

        $artY = $ly + (4 * 2.9) + 1.8;
        $doc->SetFont('Arial', 'B', 6.8);
        $doc->SetXY($col3X, $artY);
        $doc->Cell(60.0, 3.2, 'ARTIFICIAL RESTORATION', 0, 1, 'L');

        $doc->SetFont('Arial', 'B', 5.8);
        $doc->SetXY($col3X, $artY + 3.3);
        $doc->Cell(42.0, 2.6, 'JC - Jacket Crown', 0, 1, 'L');
        $doc->SetXY($col3X, $artY + 6.1);
        $doc->Cell(42.0, 2.6, 'I - Inlay', 0, 1, 'L');
    }

    protected function renderGeneralHealthExaminationPage(
        ClinicFpdf $doc,
        ClinicVisit $visit,
        ?Patient $patient,
        ?DentalConsultation $detail,
        array $settings
    ): void {
        $x = 12.0;
        $w = 186.0;
        $activeCol = $this->resolveActiveColumnIndex($patient);

        $doc->SetY(35.5);
        $doc->SetFont('Arial', 'B', 10.5);
        $doc->Cell(0, 4.2, 'SCHOOL ORAL HEALTH EXAMINATION CARD', 0, 1, 'C');
        $doc->SetFont('Arial', 'B', 7.8);
        $doc->Cell(0, 3.5, 'College Students & University Personnel', 0, 1, 'C');
        $doc->Ln(1.5);

        $schoolName = (string) ($settings['university_name'] ?? 'Surigao del Norte State University');
        $courseYear = trim(($patient?->program?->code ?? ($patient?->department ?? '')).' '.($patient?->year_level ?? ''));
        $dobMonth = $patient?->date_of_birth ? $patient->date_of_birth->format('F') : '';
        $dobDay = $patient?->date_of_birth ? $patient->date_of_birth->format('d') : '';
        $dobYear = $patient?->date_of_birth ? $patient->date_of_birth->format('Y') : '';

        $y = $doc->GetY();
        $doc->SetXY($x, $y);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(12.0, 3.6, 'NAME:', 0, 0, 'L');
        $this->drawCompactBlank($doc, (string) ($patient?->last_name ?? ''), 28.0, 'C', true, 7.6);
        $this->drawCompactBlank($doc, (string) ($patient?->first_name ?? ''), 30.0, 'C', true, 7.6);
        $this->drawCompactBlank($doc, (string) ($patient?->middle_name ?? ''), 24.0, 'C', true, 7.6);

        $doc->SetXY($x + 98.0, $y);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(26.0, 3.6, 'COLLEGE / PROG:', 0, 0, 'L');
        $this->drawCompactBlank($doc, $courseYear !== '' ? $courseYear : $schoolName, 62.0, 'L', false, 7.4);

        $doc->SetFont('Arial', 'B', 5.6);
        $doc->SetXY($x + 12.0, $y + 4.1);
        $doc->Cell(28.0, 2.4, 'LAST', 0, 0, 'C');
        $doc->Cell(30.0, 2.4, 'FIRST', 0, 0, 'C');
        $doc->Cell(24.0, 2.4, 'MIDDLE', 0, 1, 'C');

        $y2 = $y + 7.2;
        $doc->SetXY($x, $y2);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(20.0, 3.6, 'Date of Birth:', 0, 0, 'L');
        $this->drawCompactBlank($doc, $dobMonth, 26.0, 'C', false, 7.4);
        $this->drawCompactBlank($doc, $dobDay, 20.0, 'C', false, 7.4);
        $this->drawCompactBlank($doc, $dobYear, 28.0, 'C', false, 7.4);

        $doc->SetXY($x + 98.0, $y2);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(22.0, 3.6, 'Classification:', 0, 0, 'L');
        $patientClass = ucfirst((string) ($patient?->type ?? 'Student')).($patient?->sex ? ' / '.ucfirst($patient->sex) : '');
        $this->drawCompactBlank($doc, $patientClass, 66.0, 'L', false, 7.4);

        $doc->SetFont('Arial', 'B', 5.6);
        $doc->SetXY($x + 20.0, $y2 + 4.1);
        $doc->Cell(26.0, 2.4, 'MONTH', 0, 0, 'C');
        $doc->Cell(20.0, 2.4, 'DAY', 0, 0, 'C');
        $doc->Cell(28.0, 2.4, 'YEAR', 0, 1, 'C');

        $y3 = $y2 + 7.2;
        $doc->SetXY($x, $y3);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(18.0, 3.6, 'Address:', 0, 0, 'L');
        $this->drawCompactBlank($doc, (string) ($patient?->address ?? 'Surigao City'), 76.0, 'L', false, 7.2);

        $doc->SetXY($x + 98.0, $y3);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(18.0, 3.6, 'Institution:', 0, 0, 'L');
        $this->drawCompactBlank($doc, $schoolName, 70.0, 'L', false, 7.2);

        $y4 = $y3 + 4.8;
        $doc->SetXY($x, $y4);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(24.0, 3.6, 'Parent/Guardian:', 0, 0, 'L');
        $this->drawCompactBlank($doc, (string) ($patient?->emergency_contact_name ?? ''), 70.0, 'L', false, 7.2);

        $doc->SetXY($x + 98.0, $y4);
        $doc->SetFont('Arial', 'B', 7.4);
        $doc->Cell(22.0, 3.6, 'Telephone No.:', 0, 0, 'L');
        $this->drawCompactBlank($doc, (string) ($patient?->emergency_contact_phone ?: ($patient?->phone ?? '')), 66.0, 'L', false, 7.2);

        $gridY = $y4 + 5.5;
        $labelColW = 51.0;
        $yearCols = self::PAGE2_YEAR_COLS;
        $yearW = ($w - $labelColW) / count($yearCols);
        $findW = 16.5;
        $intvW = $yearW - $findW;
        $topHeaderH = 4.2;
        $subHeaderH = 8.0;

        $doc->SetLineWidth(0.22);
        $doc->Rect($x, $gridY, $labelColW, $topHeaderH + $subHeaderH);
        $doc->SetFont('Arial', 'B', 6.8);
        $doc->SetXY($x + 1.0, $gridY + 4.6);
        $doc->Cell($labelColW - 2.0, 3.0, 'CLINICAL PARAMETERS', 0, 0, 'L');

        foreach ($yearCols as $idx => $yTitle) {
            $gx = $x + $labelColW + ($idx * $yearW);
            $doc->Rect($gx, $gridY, $yearW, $topHeaderH);
            $doc->SetFont('Arial', 'B', 6.6);
            $doc->SetXY($gx, $gridY + 0.7);
            $doc->Cell($yearW, 2.8, $yTitle, 0, 0, 'C');

            $subY = $gridY + $topHeaderH;
            $doc->Rect($gx, $subY, $findW, $subHeaderH);
            $doc->Rect($gx + $findW, $subY, $intvW, $subHeaderH);

            $doc->SetFont('Arial', 'B', 5.8);
            $doc->SetXY($gx, $subY + 2.6);
            $doc->Cell($findW, 2.8, 'Findings', 0, 0, 'C');
            $doc->SetXY($gx + $findW, $subY + 2.6);
            $doc->Cell($intvW, 2.8, 'Interv.', 0, 0, 'C');
        }

        $tempBp = trim(($visit->temperature ? $visit->temperature.'C' : '').($visit->blood_pressure ? ' / '.$visit->blood_pressure : ''), ' /');
        $hrPrRr = trim(($visit->pulse_rate ? $visit->pulse_rate.'bpm' : '').($visit->respiratory_rate ? ' / '.$visit->respiratory_rate.'cpm' : ''), ' /');
        $htStr = $visit->height_cm ? $visit->height_cm.' cm' : '';
        $wtStr = $visit->weight_kg ? $visit->weight_kg.' kg' : '';

        $examRows = [
            ['label' => 'Date of Examination', 'h' => 4.2, 'finding' => $visit->visit_date->format('m/d/Y'), 'interv' => ''],
            ['label' => 'Temperature/BP', 'h' => 4.2, 'finding' => $tempBp, 'interv' => ''],
            ['label' => 'Heart Rate/Pulse Rate/Respiratory Rate', 'h' => 4.2, 'finding' => $hrPrRr, 'interv' => ''],
            ['label' => 'Height', 'h' => 4.2, 'finding' => $htStr, 'interv' => ''],
            ['label' => 'Weight', 'h' => 4.2, 'finding' => $wtStr, 'interv' => ''],
            ['label' => 'Nutritional Status ( NS )', 'h' => 4.2, 'finding' => 'a', 'interv' => ''],
            ['label' => "Visual Acuity ( Snellen's )\na.N RT b. N Lft c. AbN Rt. d. AbN Lft", 'h' => 6.4, 'finding' => 'a, b', 'interv' => ''],
            ['label' => "Hearing ( Tuning Fork )\na.N RT b. N Lft c. AbN Rt. d. AbN Lft", 'h' => 6.4, 'finding' => 'a, b', 'interv' => ''],
            ['label' => 'Skin/ Scalp', 'h' => 4.2, 'finding' => 'a', 'interv' => ''],
            ['label' => 'Eyes/Ears/Nose', 'h' => 4.2, 'finding' => 'a, b, c', 'interv' => ''],
            ['label' => 'Mouth/Throat/Neck', 'h' => 4.2, 'finding' => 'i', 'interv' => 'e'],
            ['label' => 'Lungs/Heart', 'h' => 4.2, 'finding' => 'a, b', 'interv' => ''],
            ['label' => 'Abdomen/Genitalia', 'h' => 4.2, 'finding' => 'a, b', 'interv' => ''],
            ['label' => 'Spine/Extremities', 'h' => 4.2, 'finding' => 'a, b, c', 'interv' => ''],
            ['label' => 'Other, Specify', 'h' => 4.2, 'finding' => (string) ($detail?->anesthesia_used ?? ''), 'interv' => ''],
            ['label' => 'Examined by:', 'h' => 4.2, 'finding' => (string) ($visit->clinician?->name ?? 'Dentist'), 'interv' => ''],
        ];

        $curY = $gridY + $topHeaderH + $subHeaderH;
        foreach ($examRows as $eRow) {
            $rh = $eRow['h'];
            $doc->Rect($x, $curY, $labelColW, $rh);

            $doc->SetFont('Arial', 'B', 6.2);
            if (str_contains($eRow['label'], "\n")) {
                $doc->SetXY($x + 0.8, $curY + 0.5);
                $doc->MultiCell($labelColW - 1.6, 2.7, $eRow['label'], 0, 'L');
            } else {
                $doc->SetXY($x + 0.8, $curY + (($rh - 2.8) / 2));
                $doc->Cell($labelColW - 1.6, 2.8, $eRow['label'], 0, 0, 'L');
            }

            foreach ($yearCols as $idx => $yTitle) {
                $gx = $x + $labelColW + ($idx * $yearW);
                $doc->Rect($gx, $curY, $findW, $rh);
                $doc->Rect($gx + $findW, $curY, $intvW, $rh);

                if ($idx === $activeCol) {
                    if ($eRow['finding'] !== '') {
                        $this->drawFittedCell(
                            $doc,
                            $gx,
                            $curY,
                            $findW,
                            $rh,
                            $eRow['finding'],
                            'C',
                            5.6,
                            4.2,
                            $eRow['label'] === 'Examined by:'
                        );
                    }
                    if ($eRow['interv'] !== '') {
                        $this->drawFittedCell(
                            $doc,
                            $gx + $findW,
                            $curY,
                            $intvW,
                            $rh,
                            $eRow['interv'],
                            'C',
                            5.6,
                            4.2
                        );
                    }
                }
            }

            $curY += $rh;
        }

        $legY = $curY + 3.5;
        $this->drawGeneralHealthLegendTable($doc, $x, $legY, $w);
    }

    protected function drawGeneralHealthLegendTable(ClinicFpdf $doc, float $x, float $y, float $w): void
    {
        $colWidths = [16.0, 20.0, 23.0, 26.0, 22.0, 24.0, 25.0, 30.0];
        $headers = [
            'NS',
            'Skin/Scalp',
            'Eye/Ear/Nose',
            'Mouth/Neck/Throat',
            'Lungs/Heart',
            'Abdomen/Genitalia',
            'Spine/Extreminities',
            'Remarks /Intervention',
        ];

        $headerH = 4.0;
        $cx = $x;
        $doc->SetFont('Arial', 'B', 5.8);
        foreach ($headers as $idx => $hText) {
            $cw = $colWidths[$idx];
            $doc->Rect($cx, $y, $cw, $headerH);
            $doc->SetXY($cx + 0.4, $y + 0.7);
            $doc->Cell($cw - 0.8, 2.6, $hText, 0, 0, 'L');
            $cx += $cw;
        }

        $matrixRows = [
            ['a.Normal', 'a. Normal', 'a. Normal eye', 'a. Normal mouth', "a. Normal\nlungs", "a. Normal\nAbdomen", 'a. Normal Spine', 'a. Needs Supervision'],
            ['b.Below', "b.\nPediculosis", 'b. Normal ears', 'b. Normal Throat', "b. Normal\nHeart", "b. Normal\ngenitalia", "b. Normal upper\nExtremities", "b. Needs close\nsupervision"],
            ["c. Above\nNormal", "c. Tinea\nFlava", 'c. Normal nose', "c. Enlarged tonsil\nwith/w/out\nexudates", 'c. Rales', 'c. Mass', "c. N. Lower\nExtremities", 'c. Needs Follow - up'],
            ['', 'd. Ringworm', 'd. Squinting', 'd. Lip lesion', 'd. Wheeze', 'd. Hemorrhoids', 'd. Scoliosis', 'd. corrected'],
            ['', 'e. Eczema', "e. Pale\nconjunctive", "e. Enlarged tonsil\nwith/w/out\nexudates", 'e. Murmur', 'e. Tenderness', 'e. Lordosis', 'e. Treated'],
            ['', "f.\nImpetigo/boil", "f. Ear\ndischarge", "f. Inflamed Pharynx\nwith/w/out\nexudates", "f.\nDeformed\nChest", "f. Genital\ndischarge", 'f. Kyphosis', "f.\nAdvised/Counseled"],
            ['', "g.\nHematoma", "g. Impacted\ncerumen", "g. Enlarged Thyroid\ngland", "g. Distant\nheart\nsounds", 'g. Hernia', "g. Bowlegs/knock\nknees", 'g. Reffered'],
            ['', 'h. Bruises', "h. Septal\ndeviation", 'h. Speech defect', "h. Irregular\nheart rate", 'h. Others, specify', 'h. Flat foot', 'h. Parents notified'],
            ['', "i.Cuts/lacera\ntions", "i. Nasal\ndischarge", 'i. Dental problem', "i.Other,\nspecify", '', 'i.Club Foot', 'i. Others, specify'],
            ['', 'j. Allergy', "j. Others,\nspecify", 'j. Other, specify', '', '', '', ''],
            ['', "k. Others,\nspecify", '', '', '', '', 'j. Others, specify', ''],
        ];

        $curY = $y + $headerH;
        $doc->SetFont('Arial', 'B', 5.5);

        foreach ($matrixRows as $rCells) {
            $maxLines = 1;
            foreach ($rCells as $cellText) {
                if ($cellText !== '') {
                    $lines = substr_count($cellText, "\n") + 1;
                    if ($lines > $maxLines) {
                        $maxLines = $lines;
                    }
                }
            }
            $rowH = max(3.6, ($maxLines * 2.5) + 0.8);

            $cx = $x;
            foreach ($rCells as $idx => $cellText) {
                $cw = $colWidths[$idx];
                $doc->Rect($cx, $curY, $cw, $rowH);
                if ($cellText !== '') {
                    $doc->SetXY($cx + 0.5, $curY + 0.4);
                    $doc->MultiCell($cw - 1.0, 2.4, $cellText, 0, 'L');
                }
                $cx += $cw;
            }
            $curY += $rowH;
        }

        $doc->SetXY($x, $curY + 1.5);
        $doc->SetFont('Arial', 'B', 6.8);
        $doc->Cell($w, 3.5, 'Note: Use letter to record ailments and place X if not examined.', 0, 1, 'L');
    }
}
