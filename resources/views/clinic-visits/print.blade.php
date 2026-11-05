<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Consultation Form (HSD Form 5) - {{ $visit->visit_number }} - {{ $patient->full_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            color: #000;
            background: #fff;
            padding: 30px 40px;
            max-width: 820px;
            margin: 0 auto;
        }
        .inst-title {
            font-family: "Times New Roman", Times, serif;
            font-size: 15pt;
            font-weight: bold;
            font-style: italic;
        }
        .dept-title {
            font-size: 10.5pt;
            font-weight: bold;
        }
        .dept-sub {
            font-size: 9pt;
            color: #222;
        }
        .form-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 18px 0 14px 0;
            text-transform: uppercase;
        }
        .underline-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 6px;
            font-weight: bold;
        }
        .consultation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .consultation-table th {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .consultation-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            height: 28px;
            font-size: 9pt;
            vertical-align: middle;
        }
        .footer-tag {
            margin-top: 18px;
            font-size: 8pt;
            color: #444;
            font-style: italic;
        }
        @media print {
            body {
                padding: 15px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-end mb-3">
        <a href="{{ route('clinic-visits.print', $visit) }}" class="btn btn-danger btn-sm px-3">
            Open FPDF Document
        </a>
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm px-3">
            Print Consultation Form
        </button>
        <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm px-3">
            Close
        </button>
    </div>

    <div class="text-center mb-2">
        <div class="inst-title">{{ $settings['university_name'] ?? 'Surigao del Norte State University' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'SNSU University Clinic' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? 'Ground Floor, Student Services Center, SNSU Main Campus' }}</div>
        <div class="dept-sub">{{ $settings['university_address'] ?? 'Narciso Street, Surigao City, Surigao del Norte, Philippines' }}</div>
    </div>

    <div class="form-title">CONSULTATION FORM</div>

    @php
        $courseOrOcc = $patient->type === 'student'
            ? trim(($patient->program?->code ?? '').' '.($patient->year_level ?? ''))
            : ($patient->position ?? ($visit->classification_label ?: ''));
    @endphp

    <div class="mb-2" style="line-height: 2.0;">
        <div>
            <span>Patient Name:</span>
            <span class="underline-field" style="min-width: 420px;">{{ $patient->full_name }}</span>
            <span class="ms-2">Civil Status:</span>
            <span class="underline-field" style="min-width: 140px; text-align: center;">{{ $patient->civil_status ?: '' }}</span>
        </div>
        <div>
            <span>Age:</span>
            <span class="underline-field" style="min-width: 90px; text-align: center;">{{ $patient->age }}</span>
            <span class="ms-2">Course/Occupation:</span>
            <span class="underline-field" style="min-width: 310px;">{{ $courseOrOcc }}</span>
            <span class="ms-2">Sex:</span>
            <span class="underline-field" style="min-width: 120px; text-align: center;">{{ $patient->gender }}</span>
        </div>
        <div>
            <span>Address:</span>
            <span class="underline-field" style="min-width: 450px;">{{ $patient->address ?: ($settings['clinic_address'] ?? 'Surigao City') }}</span>
            <span class="ms-2">Cel. No.</span>
            <span class="underline-field" style="min-width: 160px; text-align: center;">{{ $patient->phone ?: '' }}</span>
        </div>
    </div>

    @php
        $vitalsParts = array_filter([
            $visit->blood_pressure ? "BP {$visit->blood_pressure}" : null,
            $visit->temperature ? "T {$visit->temperature}C" : null,
            $visit->pulse_rate ? "PR {$visit->pulse_rate}" : null,
        ]);
        $vitalsStr = $vitalsParts ? ' ['.implode(', ', $vitalsParts).']' : '';
        $complaintText = trim("[{$visit->visit_number}] ".($visit->chief_complaint ?: '').($visit->summary ? ' / '.$visit->summary : '').$vitalsStr);
        $treatmentText = $visit->medicalConsultation?->treatment_plan
            ?? ($visit->medicalConsultation?->medications_prescribed
            ?? ($visit->firstAidRecord?->treatment_notes
            ?? ($visit->remarks ?: $visit->disposition_label)));
    @endphp

    <table class="consultation-table">
        <thead>
            <tr>
                <th style="width: 16%;">DATE</th>
                <th style="width: 52%;">COMPLAINTS/ FINDINGS</th>
                <th style="width: 32%;">TREATMENT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $visit->visit_date->format('m/d/Y') }}</td>
                <td>{{ $complaintText }}</td>
                <td>{{ $treatmentText }}</td>
            </tr>
            @for ($i = 1; $i < 20; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer-tag">
        HSD FORM 5- Consultation Form
    </div>
</body>
</html>
