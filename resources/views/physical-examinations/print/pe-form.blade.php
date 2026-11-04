<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Physical Examination Record - {{ $exam->pe_number }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 10pt;
            color: #333;
        }
        .form-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .section-header {
            font-weight: bold;
            background: #e9ecef;
            padding: 4px 8px;
            font-size: 10pt;
            margin-top: 10px;
            border-left: 4px solid #000;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 10pt;
        }
        .vitals-grid {
            border: 1px solid #000;
            border-collapse: collapse;
            width: 100%;
        }
        .vitals-grid td, .vitals-grid th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9.5pt;
        }
        .signature-block {
            margin-top: 40px;
            text-align: center;
            float: right;
            width: 280px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 45px;
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3">
            Print Record
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">
            Close
        </button>
    </div>

    <div class="text-center border-bottom pb-2 mb-2">
        <div class="header-title">{{ $settings['university_name'] ?? 'Surigao del Norte State University' }}</div>
        <div class="header-sub fw-bold">{{ $settings['clinic_name'] ?? 'HEALTH SERVICES DEPARTMENT' }}</div>
        <div class="header-sub">{{ $settings['clinic_address'] ?? 'Capitol Site, Surigao City, Philippines' }}</div>
        <div class="header-sub">Contact: {{ $settings['clinic_phone'] ?? '(086) 826-0135' }} &bull; {{ $settings['clinic_email'] ?? 'clinic@snsu.edu.ph' }}</div>
    </div>

    <div class="form-title">PHYSICAL EXAMINATION RECORD</div>

    <table class="meta-table w-100 mb-2">
        <tr>
            <td style="width: 15%;" class="fw-bold">PE Number:</td>
            <td style="width: 35%;">{{ $exam->pe_number }}</td>
            <td style="width: 15%;" class="fw-bold">Exam Date:</td>
            <td style="width: 35%;">{{ $exam->exam_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Patient Name:</td>
            <td class="fw-bold">{{ $patient->full_name }}</td>
            <td class="fw-bold">Patient Type:</td>
            <td>{{ ucfirst($patient->type) }} (ID: {{ $patient->primary_identifier }})</td>
        </tr>
        <tr>
            <td class="fw-bold">Age / Gender:</td>
            <td>{{ $patient->age }} y/o &bull; {{ $patient->gender }}</td>
            <td class="fw-bold">Civil Status / Blood:</td>
            <td>{{ $patient->civil_status }} &bull; Blood: {{ $patient->blood_type ?: 'Unknown' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Academic Unit:</td>
            <td>
                @if ($patient->type === 'student' && $patient->studentProfile)
                    {{ $patient->studentProfile->college->code ?? '' }} - {{ $patient->studentProfile->program->code ?? '' }} ({{ $patient->studentProfile->year_level }})
                @elseif ($patient->type === 'employee' && $patient->employeeProfile)
                    {{ $patient->employeeProfile->position }} - {{ $patient->employeeProfile->office_department }}
                @else
                    General Patient
                @endif
            </td>
            <td class="fw-bold">Exam Purpose:</td>
            <td class="fw-bold">{{ $exam->purpose }}</td>
        </tr>
    </table>

    <div class="section-header">1. VITAL SIGNS &amp; ANTHROPOMETRIC MEASUREMENTS</div>
    <table class="vitals-grid my-2">
        <tr class="text-center bg-light">
            <th>Blood Pressure</th>
            <th>Pulse Rate</th>
            <th>Respiratory Rate</th>
            <th>Temperature</th>
            <th>Oxygen Sat. (SpO2)</th>
            <th>Height</th>
            <th>Weight</th>
            <th>BMI &amp; Category</th>
        </tr>
        <tr class="text-center">
            <td class="fw-bold">{{ $exam->blood_pressure ?: '—' }}</td>
            <td>{{ $exam->pulse_rate ? "{$exam->pulse_rate} bpm" : '—' }}</td>
            <td>{{ $exam->respiratory_rate ? "{$exam->respiratory_rate} cpm" : '—' }}</td>
            <td>{{ $exam->temperature ? "{$exam->temperature} °C" : '—' }}</td>
            <td>{{ $exam->oxygen_saturation ? "{$exam->oxygen_saturation}%" : '—' }}</td>
            <td>{{ $exam->height_cm ? "{$exam->height_cm} cm" : '—' }}</td>
            <td>{{ $exam->weight_kg ? "{$exam->weight_kg} kg" : '—' }}</td>
            <td class="fw-bold">{{ $exam->bmi ? "{$exam->bmi} ({$exam->bmi_category})" : '—' }}</td>
        </tr>
    </table>

    <table class="meta-table w-100 my-1">
        <tr>
            <td style="width: 25%;" class="fw-bold">Visual Acuity:</td>
            <td style="width: 25%;">OD: {{ $exam->visual_acuity_right }} | OS: {{ $exam->visual_acuity_left }} {{ $exam->visual_acuity_is_corrected ? '(Corrected)' : '' }}</td>
            <td style="width: 25%;" class="fw-bold">Color Vision / Hearing:</td>
            <td style="width: 25%;">{{ $exam->color_vision }} / {{ $exam->hearing }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Known Allergies:</td>
            <td colspan="3">{{ $patient->medicalProfile?->allergies_summary ?? ($patient->allergies ?: 'No known allergies') }}</td>
        </tr>
    </table>

    <div class="section-header">2. SYSTEMATIC PHYSICAL EXAMINATION FINDINGS</div>
    <table class="vitals-grid my-2">
        <tr class="bg-light">
            <th style="width: 30%;">ORGAN SYSTEM</th>
            <th style="width: 20%;" class="text-center">STATUS</th>
            <th style="width: 50%;">REMARKS</th>
        </tr>
        @php
            $sysLabels = [
                'skin' => 'Skin & Integument',
                'heent' => 'HEENT',
                'neck' => 'Neck & Lymph Nodes',
                'chest_lungs' => 'Chest & Lungs',
                'heart' => 'Cardiovascular',
                'abdomen' => 'Abdomen & Viscera',
                'musculoskeletal' => 'Musculoskeletal & Spine',
                'neurological' => 'Neurological / Reflexes',
                'dental' => 'Dental / Oral'
            ];
        @endphp
        @foreach ($sysLabels as $sKey => $sTitle)
            @php
                $item = $exam->{$sKey} ?? [];
                $stat = is_array($item) ? ($item['status'] ?? 'normal') : 'normal';
                $rem = is_array($item) ? ($item['remarks'] ?? null) : null;
            @endphp
            <tr>
                <td class="fw-semibold">{{ $sTitle }}</td>
                <td class="text-center fw-bold">{{ strtoupper($stat) }}</td>
                <td>{{ $rem ?: 'Essentially normal' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="section-header">3. DIAGNOSTIC RESULTS, IMPRESSION &amp; RECOMMENDATIONS</div>
    <table class="meta-table w-100 my-1">
        <tr>
            <td style="width: 25%;" class="fw-bold">Chest X-Ray:</td>
            <td colspan="3">{{ $exam->chest_xray_findings ?: 'Normal chest radiograph' }} {{ $exam->chest_xray_date ? '('.$exam->chest_xray_date->format('M d, Y').')' : '' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Laboratory Notes:</td>
            <td colspan="3">{{ $exam->laboratory_notes ?: 'Routine tests unremarkable' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Clinical Impression:</td>
            <td colspan="3" class="fw-bold">{{ $exam->clinical_findings ?: 'Essentially normal physical examination' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Recommendations:</td>
            <td colspan="3">{{ $exam->medical_recommendations ?: 'Maintain balanced nutrition and adequate hydration.' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Fitness Classification:</td>
            <td colspan="3" class="fw-bold" style="font-size: 11pt;">{{ $exam->fitness_assessment ?: 'Class A - Physically Fit' }}</td>
        </tr>
    </table>

    <div class="clearfix">
        <div class="signature-block">
            <div class="sig-line">{{ $exam->examiner_name ?: 'DR. VICENTE S. QUIMPO, MD' }}</div>
            <div>School Physician / Attending Medical Officer</div>
            <div>License #: {{ $exam->examiner_license_number ?: '0052551' }}</div>
        </div>
    </div>
</body>
</html>

