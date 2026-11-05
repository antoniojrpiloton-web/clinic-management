<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Exemption Certificate - {{ $patient->full_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .inst-title {
            font-size: 15pt;
            font-weight: bold;
            font-style: italic;
        }
        .dept-title {
            font-size: 11pt;
            font-weight: bold;
        }
        .dept-sub {
            font-size: 9pt;
            color: #222;
        }
        .cert-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 35px 0 25px 0;
            letter-spacing: 0.5px;
        }
        .underline-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 8px;
            font-weight: bold;
        }
        .content-body {
            line-height: 2.1;
            text-align: justify;
        }
        .section-label {
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 5px;
        }
        .ruled-box {
            border-bottom: 1px solid #000;
            min-height: 28px;
            padding: 4px 6px;
            font-style: italic;
        }
        .signature-section {
            margin-top: 50px;
            float: right;
            text-align: center;
            width: 280px;
        }
        .doc-name {
            font-weight: bold;
            font-size: 11pt;
            border-top: 1px solid #000;
            padding-top: 4px;
        }
        .footer-tag {
            position: fixed;
            bottom: 20px;
            left: 40px;
            font-size: 8pt;
            color: #666;
            font-style: italic;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3">Print Exemption Certificate</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Close</button>
    </div>

    <div class="text-center mb-4">
        <div class="inst-title">{{ $settings['university_name'] ?? 'Surigao del Norte State University' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'SNSU University Clinic' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? 'Ground Floor, Student Services Center, SNSU Main Campus' }}</div>
        <div class="dept-sub">{{ $settings['university_address'] ?? 'Narciso Street, Surigao City, Surigao del Norte, Philippines' }}</div>
    </div>

    <div class="cert-title">MEDICAL EXEMPTION CERTIFICATE</div>

    <div class="text-end mb-3">
        <span>Date: </span>
        <span class="underline-field" style="min-width: 150px;">{{ $exam->exam_date->format('F d, Y') }}</span>
    </div>

    <div class="mb-3">
        <strong>To whom it may concern:</strong>
    </div>

    <div class="content-body">
        I hereby certify that I have examined
        <span class="underline-field" style="min-width: 250px;">{{ $patient->full_name }}</span>,
        <span class="underline-field" style="min-width: 40px; text-align: center;">{{ $patient->age }}</span>
        years old, {{ $patient->gender }}, a resident of
        <span class="underline-field" style="min-width: 260px;">{{ $patient->address ?: 'Kalibo, Aklan' }}</span>,
        <span class="underline-field" style="min-width: 200px;">{{ $patient->studentProfile ? ($patient->studentProfile->program->code ?? 'Enrolled') : 'University' }}</span>
        student and found them to have a medical condition that requires them to be temporarily excused from certain school activities/can join activities but with limitation.
    </div>

    <div class="section-label">Medical Condition:</div>
    <div class="ruled-box">
        {{ $exam->clinical_findings ?: ($patient->medicalProfile?->existing_conditions ?: 'Bronchial Asthma / Physical Activity Limitation') }}
    </div>

    <div class="section-label">Exempted Activities:</div>
    <div class="ruled-box">
        {{ $exam->medical_recommendations ?: 'Strenuous outdoor sports, heavy calisthenics, and endurance physical education exercises.' }}
    </div>

    <div class="mt-4 content-body">
        The exemption is effective from
        <span class="underline-field" style="min-width: 130px; text-align: center;">{{ $exam->exam_date->format('M d, Y') }}</span>
        until
        <span class="underline-field" style="min-width: 130px; text-align: center;">{{ $exam->exam_date->addMonths(1)->format('M d, Y') }}</span>.
    </div>

    <div class="mt-3 content-body">
        I certify that the information provided is accurate and medically advised for the student's health and safety.
    </div>

    <div class="clearfix">
        <div class="signature-section">
            <div style="height: 45px;"></div>
            <div class="doc-name">{{ $exam->examiner_name ?: ($settings['clinic_head'] ?? 'Dr. Maria Elena Santos, MD') }}</div>
            <div>School Physician</div>
            <div>License #: {{ $exam->examiner_license_number ?: ($settings['clinic_license_no'] ?? 'DOH-R13-CLN-2026-088') }}</div>
        </div>
    </div>

    <div class="footer-tag">
        HSD FORM 6- Medical Exemption Certificate
    </div>
</body>
</html>

