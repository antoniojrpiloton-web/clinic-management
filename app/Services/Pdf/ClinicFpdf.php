<?php

namespace App\Services\Pdf;

use FPDF;

class ClinicFpdf extends FPDF
{
    protected string $formTitle = '';

    protected string $formCode = '';

    protected array $settings = [];

    protected bool $includeHeader = true;

    protected bool $includeFooter = true;

    protected bool $officialHsdStyle = true;

    protected string $addressStyle = 'two_rooms';

    public function __construct(
        string $orientation = 'P',
        string $unit = 'mm',
        string $size = 'A4',
        array $settings = [],
        string $formTitle = '',
        string $formCode = '',
        bool $officialHsdStyle = true,
        string $addressStyle = 'two_rooms'
    ) {
        parent::__construct($orientation, $unit, $size);
        $this->settings = $settings;
        $this->formTitle = $formTitle;
        $this->formCode = $formCode;
        $this->officialHsdStyle = $officialHsdStyle;
        $this->addressStyle = $addressStyle;
        $this->SetAutoPageBreak(true, 18);
        $this->SetMargins(22, 18, 22);
        $this->SetCompression(false);
    }

    public function setFormMeta(string $title, string $code): void
    {
        $this->formTitle = $title;
        $this->formCode = $code;
    }

    public function toggleHeader(bool $state): void
    {
        $this->includeHeader = $state;
    }

    public function toggleFooter(bool $state): void
    {
        $this->includeFooter = $state;
    }

    public function conv(?string $text): string
    {
        $text = str_replace(['—', '–', '•'], ['-', '-', '*'], (string) $text);
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);

        return $converted !== false ? $converted : utf8_decode($text);
    }

    protected function resolveLogoFile(string $field): ?string
    {
        $relPath = $this->settings[$field] ?? null;
        if (! $relPath || ! is_string($relPath)) {
            return null;
        }

        $candidates = [
            storage_path('app/public/'.$relPath),
            public_path('storage/'.$relPath),
            public_path($relPath),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    return $cand;
                }
            }
        }

        return null;
    }

    public function drawHeaderBranding(): void
    {
        $enabled = (bool) ($this->settings['logo_print_enabled'] ?? true);
        if (! $enabled) {
            return;
        }

        $sizeKey = (string) ($this->settings['logo_print_size'] ?? 'medium');
        $w = match ($sizeKey) {
            'small' => 16.0,
            'large' => 25.0,
            default => 20.0,
        };

        $variant = (string) ($this->settings['logo_print_variant'] ?? 'seal');
        $position = (string) ($this->settings['logo_print_position'] ?? 'left');

        $primaryFile = $this->resolveLogoFile('logo_primary') ?? $this->resolveLogoFile('logo_large') ?? $this->resolveLogoFile('logo_original');
        $transparentFile = $this->resolveLogoFile('logo_transparent');
        $sealFile = $this->resolveLogoFile('logo_seal');
        $grayFile = $this->resolveLogoFile('logo_grayscale');

        $mainFile = match ($variant) {
            'seal' => $sealFile ?? $primaryFile ?? $transparentFile,
            'transparent' => $transparentFile ?? $primaryFile ?? $sealFile,
            'grayscale' => $grayFile ?? $sealFile ?? $primaryFile,
            default => $sealFile ?? $primaryFile ?? $transparentFile,
        };

        if ($position === 'dual') {
            $leftFile = $primaryFile ?? $transparentFile ?? $mainFile;
            $rightFile = $sealFile ?? $mainFile;

            if ($leftFile) {
                $this->Image($leftFile, 24, 16.5, $w);
            }

            if ($rightFile && $rightFile !== $leftFile) {
                $rightX = $this->GetPageWidth() - 24 - $w;
                $this->Image($rightFile, $rightX, 16.5, $w);
            } elseif (! $rightFile || $rightFile === $leftFile) {
                $this->drawHsdSeal($this->GetPageWidth() - 34, 24.5, 8.2);
            }

            return;
        }

        if ($mainFile) {
            $x = 44 - ($w / 2);
            $y = 24.5 - ($w / 2);
            $this->Image($mainFile, $x, $y, $w);

            return;
        }

        $this->drawHsdSeal(44, 24.5, 8.2);
    }

    public function Header(): void
    {
        if (! $this->includeHeader) {
            return;
        }

        if ($this->officialHsdStyle) {
            $this->drawHeaderBranding();

            $inst = $this->conv($this->settings['university_name'] ?? 'Surigao del Norte State University');
            $dept = $this->conv(strtoupper((string) ($this->settings['clinic_name'] ?? 'UNIVERSITY HEALTH DEPARTMENT')));
            $addr1 = $this->conv($this->settings['clinic_address'] ?? 'Ground Floor, Student Services Center, SNSU Main Campus');
            $addr2 = $this->conv($this->settings['university_address'] ?? ($this->settings['university_campus'] ?? 'Narciso Street, Surigao City, Surigao del Norte, Philippines'));

            $this->SetY(17);
            $this->SetFont('Times', 'BI', 14.5);
            $this->Cell(0, 5.5, $inst, 0, 1, 'C');

            $this->SetFont('Arial', 'B', 9.5);
            $this->Cell(0, 4.2, $dept, 0, 1, 'C');

            $this->SetFont('Arial', '', 8.5);
            $this->Cell(0, 3.8, $addr1, 0, 1, 'C');
            $this->Cell(0, 3.8, $addr2, 0, 1, 'C');

            $this->Ln(4);

            return;
        }

        $uni = $this->conv($this->settings['university_name'] ?? 'SURIGAO DEL NORTE STATE UNIVERSITY');
        $dept = $this->conv($this->settings['clinic_name'] ?? 'University Health Department');
        $addr1 = $this->conv($this->settings['university_campus'] ?? 'Main Campus, Surigao City');
        $addr2 = $this->conv($this->settings['university_address'] ?? 'Narciso Street, Surigao City, Surigao del Norte');
        $contact = $this->conv(($this->settings['clinic_phone'] ?? '+63 (86) 826-0135').' | '.($this->settings['clinic_email'] ?? 'clinic@snsu.edu.ph'));

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, $uni, 0, 1, 'C');

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 4.5, $dept, 0, 1, 'C');

        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 3.5, "{$addr1} - {$addr2}", 0, 1, 'C');
        $this->Cell(0, 3.5, $contact, 0, 1, 'C');

        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY() + 2, $this->GetPageWidth() - 15, $this->GetY() + 2);
        $this->SetLineWidth(0.2);
        $this->Ln(6);

        if ($this->formTitle !== '') {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 6, $this->conv($this->formTitle), 0, 1, 'C');
            $this->Ln(2);
        }
    }

    public function Footer(): void
    {
        if (! $this->includeFooter) {
            return;
        }

        if ($this->officialHsdStyle) {
            $this->SetY(-18);
            $this->SetFont('Arial', 'I', 7.5);
            $this->SetTextColor(40, 40, 40);
            $this->Cell(0, 4, $this->conv($this->formCode), 0, 0, 'L');
            $this->SetTextColor(0, 0, 0);

            return;
        }

        $this->SetY(-14);
        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(90, 90, 90);

        $code = $this->conv($this->formCode ?: 'SNSU-HSD FORM');
        $timestamp = $this->conv('Generated on '.now()->format('M d, Y h:i A').' | Page '.$this->PageNo().'/{nb}');

        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() - 1, $this->GetPageWidth() - 15, $this->GetY() - 1);

        $this->Cell(90, 4, $code, 0, 0, 'L');
        $this->Cell(0, 4, $timestamp, 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    public function drawOval(float $cx, float $cy, float $rx, float $ry, string $style = 'D'): void
    {
        $op = match ($style) {
            'F' => 'f',
            'FD', 'DF' => 'B',
            default => 'S',
        };

        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
        $k = $this->k;
        $h = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx + $rx) * $k,
            ($h - $cy) * $k,
            ($cx + $rx) * $k,
            ($h - ($cy - $ly)) * $k,
            ($cx + $lx) * $k,
            ($h - ($cy - $ry)) * $k,
            $cx * $k,
            ($h - ($cy - $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx - $lx) * $k,
            ($h - ($cy - $ry)) * $k,
            ($cx - $rx) * $k,
            ($h - ($cy - $ly)) * $k,
            ($cx - $rx) * $k,
            ($h - $cy) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx - $rx) * $k,
            ($h - ($cy + $ly)) * $k,
            ($cx - $lx) * $k,
            ($h - ($cy + $ry)) * $k,
            $cx * $k,
            ($h - ($cy + $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($cx + $lx) * $k,
            ($h - ($cy + $ry)) * $k,
            ($cx + $rx) * $k,
            ($h - ($cy + $ly)) * $k,
            ($cx + $rx) * $k,
            ($h - $cy) * $k,
            $op
        ));
    }

    public function drawHsdSeal(float $cx, float $cy, float $r): void
    {
        $this->SetDrawColor(120, 45, 45);
        $this->SetLineWidth(0.45);
        $this->drawOval($cx, $cy, $r, $r);

        $this->SetLineWidth(0.25);
        $this->drawOval($cx, $cy, $r - 1.8, $r - 1.8);

        $this->SetLineWidth(0.35);
        $this->Line($cx, $cy - 3.2, $cx, $cy + 3.2);
        $this->Line($cx - 2.2, $cy - 0.8, $cx + 2.2, $cy - 0.8);

        $shortName = strtoupper(trim((string) ($this->settings['university_short_name'] ?? 'SNSU')));
        $sealTop = strlen($shortName) <= 6 ? implode(' ', str_split($shortName)) : substr($shortName, 0, 8);

        $this->SetFont('Times', 'B', 4.5);
        $this->SetTextColor(120, 45, 45);
        $this->SetXY($cx - 6, $cy - 6.8);
        $this->Cell(12, 2, $this->conv($sealTop), 0, 0, 'C');
        $this->SetXY($cx - 6, $cy + 4.8);
        $this->Cell(12, 2, 'CLINIC', 0, 0, 'C');

        $this->SetDrawColor(0, 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.2);
    }

    public function drawOvalCheckItem(float $x, float $y, bool $checked, string $label): void
    {
        $this->SetLineWidth(0.45);
        $this->drawOval($x + 3.2, $y + 2.4, 3.0, 1.7, 'D');
        $this->SetLineWidth(0.2);

        if ($checked) {
            $this->SetFont('Arial', 'B', 8.5);
            $this->Text($x + 2.1, $y + 3.4, '/');
        }

        $this->SetFont('Arial', '', 10);
        $this->SetXY($x + 7.2, $y);
        $this->Cell(12, 5, $this->conv($label), 0, 0, 'L');
    }

    public function drawInlineBlank(string $value, float $width, string $align = 'C', bool $bold = true): void
    {
        $x = $this->GetX();
        $y = $this->GetY();
        $converted = $this->conv($value);

        $fontSize = 10.0;
        $style = $bold ? 'B' : '';
        $this->SetFont('Arial', $style, $fontSize);

        while ($fontSize > 7.0 && $this->GetStringWidth($converted) > ($width - 2.0)) {
            $fontSize -= 0.5;
            $this->SetFont('Arial', $style, $fontSize);
        }

        if ($this->GetStringWidth($converted) > ($width - 1.5)) {
            while ($converted !== '' && $this->GetStringWidth($converted.'...') > ($width - 1.5)) {
                $converted = substr($converted, 0, -1);
            }
            $converted .= '...';
        }

        $this->Cell($width, 5.5, $converted, 0, 0, $align);
        $this->SetLineWidth(0.2);
        $this->Line($x, $y + 5.2, $x + $width, $y + 5.2);
        $this->SetFont('Arial', '', 10);
    }

    public function drawRuledLines(float $x, float $y, float $width, int $lineCount, float $lineHeight, string $text = ''): float
    {
        $this->SetFont('Arial', '', 9.5);
        $converted = $this->conv(trim($text));
        $neededLines = $converted !== '' ? $this->nbLines($width - 2.0, $converted) : 1;
        $actualLines = max($lineCount, $neededLines);

        $this->SetLineWidth(0.2);
        for ($i = 1; $i <= $actualLines; $i++) {
            $lineY = $y + ($i * $lineHeight);
            $this->Line($x, $lineY, $x + $width, $lineY);
        }

        if ($converted !== '') {
            $this->SetXY($x + 1, $y + 1.2);
            $this->MultiCell($width - 2, $lineHeight, $converted, 0, 'L');
        }

        return $y + ($actualLines * $lineHeight);
    }

    public function nbLines(float $w, string $txt): int
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;

                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c] ?? 500;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    public function checkPageBreak(float $h): bool
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(44);

            return true;
        }

        return false;
    }

    public function drawRow(array $cells, float $lineHeight = 4.3, float $minHeight = 5.5, float $padY = 1.0): float
    {
        $maxLines = 1;
        foreach ($cells as $cell) {
            $family = $cell['family'] ?? 'Arial';
            $style = $cell['style'] ?? '';
            $size = $cell['size'] ?? 8.5;
            $this->SetFont($family, $style, $size);
            $converted = $this->conv($cell['text'] ?? '');
            $lines = $this->nbLines(max(2.0, $cell['w'] - 1.5), $converted);
            if ($lines > $maxLines) {
                $maxLines = $lines;
            }
        }

        $rowH = max($minHeight, ($maxLines * $lineHeight) + ($padY * 2));
        $this->checkPageBreak($rowH);

        $startX = $this->lMargin;
        $startY = $this->GetY();
        $curX = $startX;

        foreach ($cells as $cell) {
            $w = (float) $cell['w'];
            $align = $cell['align'] ?? 'L';
            $style = $cell['style'] ?? '';
            $size = $cell['size'] ?? 8.5;
            $family = $cell['family'] ?? 'Arial';
            $fill = (bool) ($cell['fill'] ?? false);
            $converted = $this->conv($cell['text'] ?? '');

            $this->Rect($curX, $startY, $w, $rowH, $fill ? 'DF' : 'D');

            $this->SetFont($family, $style, $size);
            $lines = $this->nbLines(max(2.0, $w - 1.5), $converted);
            $textBlockH = $lines * $lineHeight;
            $offsetY = max($padY, ($rowH - $textBlockH) / 2);

            $this->SetXY($curX + 0.8, $startY + $offsetY);
            $this->MultiCell($w - 1.6, $lineHeight, $converted, 0, $align);

            $curX += $w;
        }

        $this->SetXY($startX, $startY + $rowH);

        return $rowH;
    }

    public function drawSectionHeader(string $title, float $w = 166.0, float $h = 5.5): void
    {
        $this->checkPageBreak($h + 8);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetFillColor(242, 244, 247);
        $this->SetTextColor(20, 25, 35);
        $this->Cell($w, $h, '  '.$this->conv($title), 1, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
    }

    public function drawHsdSignatureBlock(string $name, string $roleLabel, string $license, float $x = 122, float $y = 0): void
    {
        $minAfterContent = $this->GetY() + 10;
        if ($y <= 0) {
            $y = max($minAfterContent, 236);
        } else {
            $y = max($y, $minAfterContent);
        }

        if ($y + 13 > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $y = $this->GetY() + 16;
        }

        $w = 66;
        if ($x + $w > ($this->w - $this->rMargin)) {
            $x = $this->w - $this->rMargin - $w;
        }

        $this->SetLineWidth(0.25);
        $this->Line($x, $y, $x + $w, $y);

        $this->SetXY($x, $y + 1);
        $nameUpper = $this->conv(strtoupper($name));
        $fontSize = 9.5;
        $this->SetFont('Arial', 'B', $fontSize);
        while ($fontSize > 7.5 && $this->GetStringWidth($nameUpper) > ($w - 2)) {
            $fontSize -= 0.5;
            $this->SetFont('Arial', 'B', $fontSize);
        }
        $this->Cell($w, 4.2, $nameUpper, 0, 1, 'C');

        $this->SetX($x);
        $this->SetFont('Arial', '', 8.5);
        $this->Cell($w, 4, $this->conv($roleLabel), 0, 1, 'C');

        if ($license !== '') {
            $this->SetX($x);
            $this->SetFont('Arial', '', 8);
            $this->Cell($w, 3.8, $this->conv("License #: {$license}"), 0, 1, 'C');
        }
    }

    public function drawCheckbox(bool $checked, string $label, float $w = 0): void
    {
        $x = $this->GetX();
        $y = $this->GetY();

        $this->Rect($x, $y + 0.8, 3.5, 3.5);
        if ($checked) {
            $this->SetFont('Arial', 'B', 8);
            $this->Text($x + 0.7, $y + 3.4, 'X');
        }

        $this->SetXY($x + 5, $y);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w > 5 ? $w - 5 : 0, 5, $this->conv($label), 0, 0, 'L');
    }

    public function drawSignatureBlock(string $name, string $title, string $license, ?string $ptr = null, float $x = 115, float $y = 0): void
    {
        if ($y <= 0) {
            $y = $this->GetY();
        }

        $this->SetXY($x, $y + 12);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(80, 4.5, $this->conv($name), 0, 1, 'C');

        $this->SetX($x);
        $this->SetFont('Arial', '', 8.5);
        $this->Cell(80, 4, $this->conv($title), 0, 1, 'C');

        $this->SetX($x);
        $this->Cell(80, 3.5, $this->conv("License #: {$license}"), 0, 1, 'C');

        if ($ptr) {
            $this->SetX($x);
            $this->Cell(80, 3.5, $this->conv("PTR No.: {$ptr}"), 0, 1, 'C');
        }

        $this->Line($x + 5, $y + 11.5, $x + 75, $y + 11.5);
    }

    public function drawPatientSignature(string $patientName, float $x = 15, float $y = 0): void
    {
        if ($y <= 0) {
            $y = $this->GetY();
        }

        $this->SetXY($x, $y + 12);
        $this->SetFont('Arial', 'B', 9.5);
        $this->Cell(75, 4.5, $this->conv($patientName), 0, 1, 'C');

        $this->SetX($x);
        $this->SetFont('Arial', '', 8.5);
        $this->Cell(75, 4, $this->conv('Patient / Student Signature'), 0, 1, 'C');

        $this->Line($x + 5, $y + 11.5, $x + 70, $y + 11.5);
    }
}
