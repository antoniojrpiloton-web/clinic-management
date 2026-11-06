<?php

namespace App\Services\Pdf;

use App\Models\ClinicVisit;
use App\Models\DentalConsultation;

class DentalPdfService
{
    public function oralHealthCard(ClinicVisit $visit, array $settings): ClinicFpdf
    {
        $patient = $visit->patient;
        $detail = $visit->dentalConsultation;

        $doc = new ClinicFpdf('P', 'mm', 'A4', $settings, 'ORAL HEALTH EXAMINATION CARD', 'HSD FORM 7- Oral Health Card', true, 'two_rooms');
        $doc->AliasNbPages();
        $doc->AddPage();

        $doc->SetY(42);
        $doc->SetFont('Arial', 'B', 11.5);
        $doc->Cell(0, 5.0, 'ORAL HEALTH EXAMINATION CARD', 0, 1, 'C');
        $doc->Ln(1.0);

        $x = $doc->GetX();
        $w = 166.0;

        $doc->drawSectionHeader('I. PATIENT INFORMATION', $w, 4.6);

        $courseYr = $patient?->type === 'student'
            ? trim(($patient->college?->code ?? '').($patient->program?->code ? ' / '.$patient->program->code : '').($patient->year_level ? ' - '.$patient->year_level : ''))
            : trim(($patient?->position ?? '').($patient?->office_department ? ' / '.$patient->office_department : ''));

        $doc->drawRow([
            ['w' => 26, 'text' => 'Full Name:', 'size' => 8.0],
            ['w' => 70, 'text' => $patient?->full_name ?? '-', 'style' => 'B', 'size' => 8.0],
            ['w' => 22, 'text' => 'Date:', 'size' => 8.0],
            ['w' => 48, 'text' => $visit->visit_date->format('M d, Y'), 'style' => 'B', 'size' => 8.0],
        ], 3.8, 4.6, 0.7);

        $doc->drawRow([
            ['w' => 26, 'text' => 'Age / Sex:', 'size' => 8.0],
            ['w' => 42, 'text' => ($patient?->age ?? '-').' y/o / '.($patient?->gender ?? '-'), 'size' => 8.0],
            ['w' => 28, 'text' => 'Course / Dept:', 'size' => 8.0],
            ['w' => 70, 'text' => $courseYr ?: '-', 'size' => 8.0],
        ], 3.8, 4.6, 0.7);
        $doc->Ln(1.0);

        $doc->drawSectionHeader('II. DENTAL HISTORY', $w, 4.6);

        $freq = $detail?->dental_treatment_frequency;
        $freqLabel = $freq ? (DentalConsultation::TREATMENT_FREQUENCIES[$freq] ?? $freq) : '-';

        $doc->drawRow([
            ['w' => 36, 'text' => 'Treatment Frequency:', 'size' => 8.0],
            ['w' => 47, 'text' => $freqLabel, 'size' => 8.0],
            ['w' => 30, 'text' => 'Last Dental Visit:', 'size' => 8.0],
            ['w' => 25, 'text' => $detail?->last_dental_visit?->format('M d, Y') ?? '-', 'size' => 8.0],
            ['w' => 18, 'text' => 'Fears:', 'size' => 8.0],
            ['w' => 10, 'text' => $detail?->dental_fears ? 'Yes' : 'No', 'align' => 'C', 'size' => 8.0],
        ], 3.8, 4.6, 0.7);

        $doc->drawRow([
            ['w' => 36, 'text' => 'Dental Concerns:', 'size' => 8.0],
            ['w' => 130, 'text' => $detail?->dental_concerns ?: 'None reported', 'size' => 8.0],
        ], 3.8, 4.6, 0.7);
        $doc->Ln(1.0);

        $doc->drawSectionHeader('III. ORAL HEALTH & SYSTEMIC CONDITIONS', $w, 4.6);

        $oralConditions = [
            'has_toothache' => 'Toothache',
            'has_bleeding_gums' => 'Bleeding Gums',
            'has_bad_breath' => 'Bad Breath',
            'has_tooth_sensitivity' => 'Tooth Sensitivity',
            'has_jaw_pain' => 'Jaw Pain',
            'has_loose_teeth' => 'Loose Teeth',
            'has_swelling' => 'Swelling',
        ];
        $this->renderCheckboxGridBox($doc, 'Presenting Oral Symptoms:', $oralConditions, $detail, $x, $w);

        $systemicConditions = [
            'systemic_diabetes' => 'Diabetes',
            'systemic_hypertension' => 'Hypertension',
            'systemic_heart_disease' => 'Heart Disease',
            'systemic_bleeding_disorder' => 'Bleeding Disorder',
            'systemic_allergies' => 'Allergies',
        ];
        $this->renderCheckboxGridBox($doc, 'Systemic / Medical History:', $systemicConditions, $detail, $x, $w);

        if ($detail?->systemic_allergies && $detail?->systemic_allergy_details) {
            $doc->drawRow([
                ['w' => 36, 'text' => 'Allergy Details:', 'style' => 'B', 'size' => 8.0],
                ['w' => 130, 'text' => $detail->systemic_allergy_details, 'size' => 8.0],
            ], 3.6, 4.5, 0.6);
        }
        $doc->Ln(1.0);

        $doc->drawSectionHeader('IV. SOFT TISSUE & PERIODONTAL EXAMINATION', $w, 4.6);

        $softStatus = fn (string $field): string => ($detail?->{$field} ?? '')
            ? (DentalConsultation::SOFT_TISSUE_STATUSES[$detail->{$field}] ?? $detail->{$field})
            : 'Normal';

        $doc->drawRow([
            ['w' => 24, 'text' => 'Lips:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.3, 'text' => $softStatus('soft_tissue_lips'), 'size' => 8.0],
            ['w' => 24, 'text' => 'Cheeks:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.3, 'text' => $softStatus('soft_tissue_cheeks'), 'size' => 8.0],
            ['w' => 24, 'text' => 'Tongue:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.4, 'text' => $softStatus('soft_tissue_tongue'), 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 24, 'text' => 'Palate:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.3, 'text' => $softStatus('soft_tissue_palate'), 'size' => 8.0],
            ['w' => 24, 'text' => 'Gingiva:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.3, 'text' => $softStatus('soft_tissue_gingiva'), 'size' => 8.0],
            ['w' => 24, 'text' => 'Floor/Mouth:', 'style' => 'B', 'size' => 8.0, 'fill' => true],
            ['w' => 31.4, 'text' => $softStatus('soft_tissue_floor_of_mouth'), 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $perioLabel = $detail?->periodontal_status ? (DentalConsultation::PERIODONTAL_STATUSES[$detail->periodontal_status] ?? $detail->periodontal_status) : '-';
        $calculusLabel = $detail?->calculus_index ? (DentalConsultation::CALCULUS_INDICES[$detail->calculus_index] ?? $detail->calculus_index) : '-';
        $gingivalLabel = $detail?->gingival_index ? (DentalConsultation::GINGIVAL_INDICES[$detail->gingival_index] ?? $detail->gingival_index) : '-';
        $plaqueLabel = $detail?->plaque_index ? (DentalConsultation::PLAQUE_INDICES[$detail->plaque_index] ?? $detail->plaque_index) : '-';

        $doc->drawRow([
            ['w' => 28, 'text' => 'Periodontal:', 'style' => 'B', 'size' => 8.0],
            ['w' => 27.3, 'text' => $perioLabel, 'size' => 8.0],
            ['w' => 24, 'text' => 'Calculus:', 'style' => 'B', 'size' => 8.0],
            ['w' => 31.3, 'text' => $calculusLabel, 'size' => 8.0],
            ['w' => 24, 'text' => 'Oral Hygiene:', 'style' => 'B', 'size' => 8.0],
            ['w' => 31.4, 'text' => $detail?->oral_hygiene_status ?? '-', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Gingival Index:', 'style' => 'B', 'size' => 8.0],
            ['w' => 55.3, 'text' => $gingivalLabel, 'size' => 8.0],
            ['w' => 28, 'text' => 'Plaque Index:', 'style' => 'B', 'size' => 8.0],
            ['w' => 54.7, 'text' => $plaqueLabel, 'size' => 8.0],
        ], 3.6, 4.5, 0.6);
        $doc->Ln(1.0);

        $doc->drawSectionHeader('V. ODONTOGRAM / TOOTH CHART (PERMANENT & PRIMARY DENTITION)', $w, 4.6);
        $this->renderToothChartSection($doc, 'PERMANENT TEETH (FDI 11-48)', DentalConsultation::permanentTeeth(), $detail?->tooth_chart ?? [], $x, $w);
        $this->renderToothChartSection($doc, 'PRIMARY / DECIDUOUS TEETH (FDI 51-85)', DentalConsultation::deciduousTeeth(), $detail?->primary_tooth_chart ?? [], $x, $w);
        $doc->Ln(1.0);

        $doc->drawSectionHeader('VI. DMFT / DMFS DENTAL CARIES INDICES', $w, 4.6);

        $doc->drawRow([
            ['w' => 26, 'text' => 'Index', 'style' => 'B', 'align' => 'C', 'size' => 8.0, 'fill' => true],
            ['w' => 35, 'text' => 'Decayed (D)', 'style' => 'B', 'align' => 'C', 'size' => 8.0, 'fill' => true],
            ['w' => 35, 'text' => 'Missing (M)', 'style' => 'B', 'align' => 'C', 'size' => 8.0, 'fill' => true],
            ['w' => 35, 'text' => 'Filled (F)', 'style' => 'B', 'align' => 'C', 'size' => 8.0, 'fill' => true],
            ['w' => 35, 'text' => 'Total Score', 'style' => 'B', 'align' => 'C', 'size' => 8.0, 'fill' => true],
        ], 3.6, 4.4, 0.6);

        $doc->drawRow([
            ['w' => 26, 'text' => 'DMFT', 'style' => 'B', 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmft_decayed ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmft_missing ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmft_filled ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => $detail ? (string) $detail->dmft_total : '0', 'style' => 'B', 'align' => 'C', 'size' => 8.0],
        ], 3.6, 4.4, 0.6);

        $doc->drawRow([
            ['w' => 26, 'text' => 'DMFS', 'style' => 'B', 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmfs_decayed ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmfs_missing ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => (string) ($detail?->dmfs_filled ?? '0'), 'align' => 'C', 'size' => 8.0],
            ['w' => 35, 'text' => $detail ? (string) $detail->dmfs_total : '0', 'style' => 'B', 'align' => 'C', 'size' => 8.0],
        ], 3.6, 4.4, 0.6);
        $doc->Ln(1.0);

        $doc->drawSectionHeader('VII. CLINICAL MANAGEMENT, TREATMENT & FOLLOW-UP', $w, 4.6);

        $procedures = implode(', ', $detail?->procedures ?? []);
        $priorityLabel = $detail?->priority_level ? (DentalConsultation::PRIORITY_LEVELS[$detail->priority_level] ?? $detail->priority_level) : 'Routine';

        $doc->drawRow([
            ['w' => 28, 'text' => 'Diagnosis:', 'style' => 'B', 'size' => 8.0],
            ['w' => 96, 'text' => $detail?->diagnosis ?: '-', 'size' => 8.0],
            ['w' => 18, 'text' => 'Priority:', 'style' => 'B', 'size' => 8.0],
            ['w' => 24, 'text' => $priorityLabel, 'align' => 'C', 'style' => 'B', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Procedures:', 'style' => 'B', 'size' => 8.0],
            ['w' => 138, 'text' => $procedures ?: '-', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Teeth Involved:', 'style' => 'B', 'size' => 8.0],
            ['w' => 55, 'text' => $detail?->teeth_involved ?: '-', 'size' => 8.0],
            ['w' => 24, 'text' => 'Anesthesia:', 'style' => 'B', 'size' => 8.0],
            ['w' => 59, 'text' => $detail?->anesthesia_used ?: 'None', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Treatment:', 'style' => 'B', 'size' => 8.0],
            ['w' => 138, 'text' => $detail?->treatment_rendered ?: '-', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Instructions:', 'style' => 'B', 'size' => 8.0],
            ['w' => 138, 'text' => $detail?->recommendations ?: '-', 'size' => 8.0],
        ], 3.6, 4.5, 0.6);

        $doc->drawRow([
            ['w' => 28, 'text' => 'Treatment Plan:', 'style' => 'B', 'size' => 8.0],
            ['w' => 55, 'text' => $detail?->treatment_plan ?: '-', 'size' => 8.0],
            ['w' => 24, 'text' => 'Follow-up:', 'style' => 'B', 'size' => 8.0],
            ['w' => 22, 'text' => $detail?->follow_up_date?->format('M d, Y') ?? '-', 'size' => 8.0],
            ['w' => 37, 'text' => $visit->disposition_label ?? '-', 'align' => 'C', 'size' => 7.5],
        ], 3.6, 4.5, 0.6);

        $clinician = $visit->clinician;
        $dentistName = $clinician?->name ?? ($settings['clinic_head'] ?? 'Attending Dentist');
        $license = $clinician?->license_number ?? ($settings['clinic_license_no'] ?? '');

        $doc->drawHsdSignatureBlock($dentistName, 'School Dentist', $license, 122);

        return $doc;
    }

    /**
     * @param  array<string, string>  $conditions
     */
    protected function renderCheckboxGridBox(ClinicFpdf $doc, string $groupLabel, array $conditions, ?DentalConsultation $detail, float $x, float $w): void
    {
        $itemsPerRow = 4;
        $rowsCount = (int) ceil(count($conditions) / $itemsPerRow);
        $boxH = 4.5 + ($rowsCount * 4.5);

        $doc->checkPageBreak($boxH);
        $startY = $doc->GetY();
        $doc->Rect($x, $startY, $w, $boxH);

        $doc->SetXY($x + 1.5, $startY + 0.6);
        $doc->SetFont('Arial', 'B', 7.5);
        $doc->Cell($w - 3, 3.8, $doc->conv($groupLabel), 0, 1, 'L');

        $colW = ($w - 4) / $itemsPerRow;
        $i = 0;
        $curY = $startY + 4.2;
        $doc->SetXY($x + 2, $curY);

        foreach ($conditions as $field => $label) {
            $checked = (bool) ($detail?->{$field} ?? false);
            $doc->drawCheckbox($checked, $label, $colW);
            $i++;
            if ($i % $itemsPerRow === 0 && $i < count($conditions)) {
                $curY += 4.4;
                $doc->SetXY($x + 2, $curY);
            }
        }

        $doc->SetXY($x, $startY + $boxH);
    }

    /**
     * @param  array<string|int, string>  $chart
     * @param  array{upper: list<int>, lower: list<int>}  $teethDef
     */
    protected function renderToothChartSection(ClinicFpdf $doc, string $sectionLabel, array $teethDef, array $chart, float $x, float $w): void
    {
        $doc->checkPageBreak(19);

        $doc->SetFont('Arial', 'B', 7.5);
        $doc->SetFillColor(248, 249, 250);
        $doc->Cell($w, 4.2, '  '.$doc->conv($sectionLabel).' — Upper (Maxillary) & Lower (Mandibular)', 1, 1, 'L', true);

        foreach (['upper', 'lower'] as $arch) {
            $teeth = $teethDef[$arch];
            $cellW = $w / count($teeth);
            $rowY = $doc->GetY();
            $rowH = 6.8;

            foreach ($teeth as $idx => $tooth) {
                $cx = $x + ($idx * $cellW);
                $condition = (string) ($chart[(string) $tooth] ?? '');

                if ($condition !== '') {
                    $doc->SetFillColor(255, 248, 230);
                    $doc->Rect($cx, $rowY, $cellW, $rowH, 'DF');
                } else {
                    $doc->Rect($cx, $rowY, $cellW, $rowH, 'D');
                }

                $doc->SetFont('Arial', 'B', 6.8);
                $doc->SetXY($cx, $rowY + 0.5);
                $doc->Cell($cellW, 3.0, "#{$tooth}", 0, 0, 'C');

                $doc->SetFont('Arial', $condition !== '' ? 'B' : '', 6.5);
                $doc->SetXY($cx, $rowY + 3.4);
                $doc->Cell($cellW, 3.0, $doc->conv($condition !== '' ? $condition : '—'), 0, 0, 'C');
            }

            $doc->SetXY($x, $rowY + $rowH);
        }
    }
}
