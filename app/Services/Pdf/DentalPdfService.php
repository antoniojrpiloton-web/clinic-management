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

        $doc->SetY(44);
        $doc->SetFont('Arial', 'B', 11);
        $doc->Cell(0, 5.5, 'ORAL HEALTH EXAMINATION CARD', 0, 1, 'C');
        $doc->Ln(2);

        $x = 20;
        $w = 170;

        $doc->SetFont('Arial', 'B', 9);
        $doc->SetFillColor(235, 238, 242);
        $doc->Cell($w, 5, ' I. PATIENT INFORMATION', 1, 1, 'L', true);

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(28, 5, 'Full Name:', 1, 0, 'L');
        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(72, 5, $doc->conv($patient?->full_name ?? ''), 1, 0, 'L');
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(22, 5, 'Date:', 1, 0, 'L');
        $doc->Cell(48, 5, $doc->conv($visit->visit_date->format('M d, Y')), 1, 1, 'L');

        $doc->Cell(28, 5, 'Age / Sex:', 1, 0, 'L');
        $doc->Cell(42, 5, $doc->conv(($patient?->age ?? '—').' y/o / '.($patient?->gender ?? '—')), 1, 0, 'L');
        $doc->Cell(30, 5, 'Course / Yr:', 1, 0, 'L');
        $doc->Cell(70, 5, $doc->conv(($patient?->program?->code ?? '—').' '.($patient?->year_level ?? '')), 1, 1, 'L');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' II. DENTAL HISTORY', 1, 1, 'L', true);

        $freq = $detail?->dental_treatment_frequency;
        $freqLabel = $freq ? (DentalConsultation::TREATMENT_FREQUENCIES[$freq] ?? $freq) : '—';
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(50, 5, 'Treatment Frequency:', 1, 0, 'L');
        $doc->Cell(50, 5, $doc->conv($freqLabel), 1, 0, 'L');
        $doc->Cell(30, 5, 'Last Dental Visit:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv($detail?->last_dental_visit?->format('M d, Y') ?? '—'), 1, 1, 'L');

        $doc->Cell(40, 5, 'Dental Fears:', 1, 0, 'L');
        $doc->Cell(130, 5, $doc->conv($detail?->dental_fears ? 'Yes' : 'No'), 1, 1, 'L');

        $concerns = $detail?->dental_concerns ?? '—';
        $doc->Cell(40, 5, 'Dental Concerns:', 1, 0, 'L');
        $doc->MultiCell(130, 5, $doc->conv(mb_strimwidth($concerns, 0, 160, '...')), 'TBR', 'L');
        $doc->Ln(1);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' III. ORAL HEALTH CONDITIONS', 1, 1, 'L', true);

        $oralConditions = [
            'has_toothache' => 'Toothache',
            'has_bleeding_gums' => 'Bleeding Gums',
            'has_bad_breath' => 'Bad Breath',
            'has_tooth_sensitivity' => 'Tooth Sensitivity',
            'has_jaw_pain' => 'Jaw Pain',
            'has_loose_teeth' => 'Loose Teeth',
            'has_swelling' => 'Swelling',
        ];

        $doc->SetFont('Arial', '', 8.5);
        $colW = $w / 4;
        $i = 0;
        foreach ($oralConditions as $field => $label) {
            $checked = $detail?->{$field} ?? false;
            $doc->drawCheckbox($checked, $label, $colW);
            $i++;
            if ($i % 4 === 0) {
                $doc->Ln(5);
                $doc->SetX($x);
            }
        }
        if ($i % 4 !== 0) {
            $doc->Ln(5);
        }
        $doc->Ln(1);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' IV. SYSTEMIC CONDITIONS', 1, 1, 'L', true);

        $systemicConditions = [
            'systemic_diabetes' => 'Diabetes',
            'systemic_hypertension' => 'Hypertension',
            'systemic_heart_disease' => 'Heart Disease',
            'systemic_bleeding_disorder' => 'Bleeding Disorder',
            'systemic_allergies' => 'Allergies',
        ];

        $doc->SetFont('Arial', '', 8.5);
        $i = 0;
        foreach ($systemicConditions as $field => $label) {
            $checked = $detail?->{$field} ?? false;
            $doc->drawCheckbox($checked, $label, $colW);
            $i++;
            if ($i % 4 === 0) {
                $doc->Ln(5);
                $doc->SetX($x);
            }
        }
        if ($i % 4 !== 0) {
            $doc->Ln(5);
        }

        if ($detail?->systemic_allergies && $detail?->systemic_allergy_details) {
            $doc->SetFont('Arial', '', 8.5);
            $doc->Cell(40, 5, 'Allergy Details:', 1, 0, 'L');
            $doc->Cell(130, 5, $doc->conv($detail->systemic_allergy_details), 1, 1, 'L');
        }
        $doc->Ln(2);

        $doc->AddPage();

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' V. SOFT TISSUE EXAMINATION', 1, 1, 'L', true);

        $softTissues = [
            'soft_tissue_lips' => 'Lips',
            'soft_tissue_cheeks' => 'Cheeks',
            'soft_tissue_tongue' => 'Tongue',
            'soft_tissue_palate' => 'Palate',
            'soft_tissue_gingiva' => 'Gingiva',
            'soft_tissue_floor_of_mouth' => 'Floor of Mouth',
        ];

        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(50, 5, 'Area', 1, 0, 'C');
        $doc->Cell(120, 5, 'Status', 1, 1, 'C');

        $doc->SetFont('Arial', '', 8.5);
        foreach ($softTissues as $field => $areaLabel) {
            $val = $detail?->{$field} ?? '';
            $statusLabel = $val ? (DentalConsultation::SOFT_TISSUE_STATUSES[$val] ?? $val) : 'Normal';
            $doc->Cell(50, 5, $doc->conv($areaLabel), 1, 0, 'L');
            $doc->Cell(120, 5, $doc->conv($statusLabel), 1, 1, 'L');
        }
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' VI. PERIODONTAL ASSESSMENT', 1, 1, 'L', true);

        $doc->SetFont('Arial', '', 8.5);
        $perioLabel = $detail?->periodontal_status ? (DentalConsultation::PERIODONTAL_STATUSES[$detail->periodontal_status] ?? $detail->periodontal_status) : '—';
        $calculusLabel = $detail?->calculus_index ? (DentalConsultation::CALCULUS_INDICES[$detail->calculus_index] ?? $detail->calculus_index) : '—';
        $gingivalLabel = $detail?->gingival_index ? (DentalConsultation::GINGIVAL_INDICES[$detail->gingival_index] ?? $detail->gingival_index) : '—';
        $plaqueLabel = $detail?->plaque_index ? (DentalConsultation::PLAQUE_INDICES[$detail->plaque_index] ?? $detail->plaque_index) : '—';

        $doc->Cell(45, 5, 'Periodontal Status:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv($perioLabel), 1, 0, 'L');
        $doc->Cell(45, 5, 'Calculus Index:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv($calculusLabel), 1, 1, 'L');

        $doc->Cell(45, 5, 'Gingival Index:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv($gingivalLabel), 1, 0, 'L');
        $doc->Cell(45, 5, 'Plaque Index:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv($plaqueLabel), 1, 1, 'L');

        $doc->Cell(45, 5, 'Oral Hygiene Status:', 1, 0, 'L');
        $doc->Cell(125, 5, $doc->conv($detail?->oral_hygiene_status ?? '—'), 1, 1, 'L');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' VII. TOOTH CHART (PERMANENT TEETH)', 1, 1, 'L', true);

        $this->renderToothChartSection($doc, DentalConsultation::permanentTeeth(), $detail?->tooth_chart ?? [], $x, $w);
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' VIII. TOOTH CHART (PRIMARY / DECIDUOUS TEETH)', 1, 1, 'L', true);

        $this->renderToothChartSection($doc, DentalConsultation::deciduousTeeth(), $detail?->primary_tooth_chart ?? [], $x, $w);
        $doc->Ln(2);

        $doc->AddPage();

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' IX. CLINICAL MANAGEMENT', 1, 1, 'L', true);

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(28, 5, 'Diagnosis:', 1, 0, 'L');
        $doc->MultiCell(142, 5, $doc->conv(mb_strimwidth($detail?->diagnosis ?? '—', 0, 180, '...')), 'TBR', 'L');

        $procedures = implode(', ', $detail?->procedures ?? []);
        $doc->Cell(28, 5, 'Procedures:', 1, 0, 'L');
        $doc->MultiCell(142, 5, $doc->conv(mb_strimwidth($procedures ?: '—', 0, 180, '...')), 'TBR', 'L');

        $doc->Cell(28, 5, 'Teeth Involved:', 1, 0, 'L');
        $doc->Cell(72, 5, $doc->conv($detail?->teeth_involved ?? '—'), 1, 0, 'L');
        $doc->Cell(30, 5, 'Anesthesia:', 1, 0, 'L');
        $doc->Cell(40, 5, $doc->conv(mb_strimwidth($detail?->anesthesia_used ?? '—', 0, 40, '...')), 1, 1, 'L');

        $doc->Cell(28, 5, 'Priority:', 1, 0, 'L');
        $priorityLabel = $detail?->priority_level ? (DentalConsultation::PRIORITY_LEVELS[$detail->priority_level] ?? $detail->priority_level) : '—';
        $doc->Cell(142, 5, $doc->conv($priorityLabel), 1, 1, 'L');

        $treatmentRendered = $detail?->treatment_rendered ?? '—';
        $doc->Cell(28, 5, 'Treatment:', 1, 0, 'L');
        $doc->MultiCell(142, 5, $doc->conv(mb_strimwidth($treatmentRendered, 0, 240, '...')), 'TBR', 'L');

        $recs = $detail?->recommendations ?? '—';
        $doc->Cell(28, 5, 'Recommendations:', 1, 0, 'L');
        $doc->MultiCell(142, 5, $doc->conv(mb_strimwidth($recs, 0, 240, '...')), 'TBR', 'L');
        $doc->Ln(2);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' X. DMFT / DMFS INDICES', 1, 1, 'L', true);

        $doc->SetFont('Arial', 'B', 8.5);
        $doc->Cell(28, 5, 'Index', 1, 0, 'C');
        $doc->Cell(28, 5, 'Decayed (D)', 1, 0, 'C');
        $doc->Cell(28, 5, 'Missing (M)', 1, 0, 'C');
        $doc->Cell(28, 5, 'Filled (F)', 1, 0, 'C');
        $doc->Cell(58, 5, 'Total (D+M+F)', 1, 1, 'C');

        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(28, 5, 'DMFT', 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmft_decayed ?? '—')), 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmft_missing ?? '—')), 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmft_filled ?? '—')), 1, 0, 'C');
        $doc->Cell(58, 5, $doc->conv($detail ? (string) $detail->dmft_total : '—'), 1, 1, 'C');

        $doc->Cell(28, 5, 'DMFS', 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmfs_decayed ?? '—')), 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmfs_missing ?? '—')), 1, 0, 'C');
        $doc->Cell(28, 5, $doc->conv((string) ($detail?->dmfs_filled ?? '—')), 1, 0, 'C');
        $doc->Cell(58, 5, $doc->conv($detail ? (string) $detail->dmfs_total : '—'), 1, 1, 'C');
        $doc->Ln(3);

        $doc->SetFont('Arial', 'B', 9);
        $doc->Cell($w, 5, ' XI. TREATMENT PLAN & FOLLOW-UP', 1, 1, 'L', true);

        $treatmentPlan = $detail?->treatment_plan ?? '—';
        $doc->SetFont('Arial', '', 8.5);
        $doc->Cell(38, 5, 'Treatment Plan:', 1, 0, 'L');
        $doc->MultiCell(132, 5, $doc->conv(mb_strimwidth($treatmentPlan, 0, 300, '...')), 'TBR', 'L');

        $doc->Cell(38, 5, 'Follow-up Date:', 1, 0, 'L');
        $doc->Cell(72, 5, $doc->conv($detail?->follow_up_date?->format('M d, Y') ?? '—'), 1, 0, 'L');
        $doc->Cell(30, 5, 'Disposition:', 1, 0, 'L');
        $doc->Cell(30, 5, $doc->conv($visit->disposition_label ?? '—'), 1, 1, 'L');
        $doc->Ln(8);

        $clinician = $visit->clinician;
        $dentistName = $clinician?->name ?? ($settings['clinic_head'] ?? 'Attending Dentist');
        $license = $clinician?->license_number ?? ($settings['clinic_license_no'] ?? '');

        $doc->drawHsdSignatureBlock($dentistName, 'School Dentist', $license, 122);

        return $doc;
    }

    /**
     * @param  array<string|int, string>  $chart
     * @param  array{upper: list<int>, lower: list<int>}  $teethDef
     */
    protected function renderToothChartSection(ClinicFpdf $doc, array $teethDef, array $chart, float $x, float $w): void
    {
        $doc->SetFont('Arial', 'I', 7.5);
        $doc->Cell($w, 4, 'MAXILLARY / UPPER ARCH', 0, 1, 'C');

        $doc->SetFont('Arial', 'B', 7);
        $cellW = $w / count($teethDef['upper']);
        foreach ($teethDef['upper'] as $tooth) {
            $condition = $chart[(string) $tooth] ?? '';
            $doc->Cell($cellW, 4, $doc->conv($condition ? "#{$tooth}\n{$condition}" : "#{$tooth}"), 1, 0, 'C');
        }
        $doc->Ln(4);

        $doc->SetFont('Arial', 'I', 7.5);
        $doc->Cell($w, 4, 'MANDIBULAR / LOWER ARCH', 0, 1, 'C');

        $doc->SetFont('Arial', 'B', 7);
        $cellW = $w / count($teethDef['lower']);
        foreach ($teethDef['lower'] as $tooth) {
            $condition = $chart[(string) $tooth] ?? '';
            $doc->Cell($cellW, 4, $doc->conv($condition ? "#{$tooth}\n{$condition}" : "#{$tooth}"), 1, 0, 'C');
        }
        $doc->Ln(4);
    }
}
