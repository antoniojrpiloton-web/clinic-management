<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Referral Form - {{ $patient->full_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .inst-title {
            font-size: 14pt;
            font-weight: bold;
            font-style: italic;
        }
        .dept-title {
            font-size: 10.5pt;
            font-weight: bold;
        }
        .dept-sub {
            font-size: 9pt;
            color: #333;
        }
        .form-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .referral-table {
            width: 100%;
            border-collapse: collapse;
        }
        .referral-table td, .referral-table th {
            border: 1px solid #777;
            padding: 6px 10px;
            font-size: 10pt;
        }
        .header-cell {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
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
            left: 30px;
            font-size: 8pt;
            color: #666;
            font-style: italic;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3">Print Referral Form</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Close</button>
    </div>

    <div class="text-center mb-3">
        <div class="inst-title">{{ $settings['university_name'] ?? 'Northwestern Visayan Colleges' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'HEALTH SERVICES DEPARTMENT' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? '2nd Floor, Room 201 and Room 202, ASQ Building' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address_line2'] ?? 'Capitol Site, Kalibo, Aklan' }}</div>
    </div>

    <div class="form-title">MEDICAL REFERRAL FORM</div>

    <table class="referral-table mb-3">
        <tr>
            <td style="width: 20%;" class="header-cell">Date:</td>
            <td style="width: 80%;">{{ $exam->exam_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="header-cell">Refer to:</td>
            <td class="fw-bold">Attending Medical Specialist / Provincial Hospital</td>
        </tr>
    </table>

    <table class="referral-table mb-3">
        <tr>
            <th colspan="4" class="header-cell">Patient Information:</th>
        </tr>
        <tr>
            <td style="width: 20%;" class="fw-semibold">Name:</td>
            <td colspan="3" class="fw-bold">{{ $patient->full_name }} (ID: {{ $patient->primary_identifier }})</td>
        </tr>
        <tr>
            <td class="fw-semibold">Date of Birth:</td>
            <td style="width: 30%;">{{ $patient->date_of_birth->format('M d, Y') }}</td>
            <td style="width: 20%;" class="fw-semibold">Age / Gender:</td>
            <td style="width: 30%;">{{ $patient->age }} y/o &bull; {{ $patient->gender }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Address:</td>
            <td>{{ $patient->address ?: 'Kalibo, Aklan' }}</td>
            <td class="fw-semibold">Contact Number:</td>
            <td>{{ $patient->phone ?: '—' }}</td>
        </tr>
        <tr>
            <th colspan="4" class="header-cell">In case of Emergency:</th>
        </tr>
        <tr>
            <td class="fw-semibold">Contact Name:</td>
            <td>{{ $patient->emergency_contact_name ?: 'Parent / Guardian' }}</td>
            <td class="fw-semibold">Contact Number:</td>
            <td>{{ $patient->emergency_contact_phone ?: '—' }}</td>
        </tr>
    </table>

    <table class="referral-table mb-3">
        <tr>
            <th class="header-cell">Complaints / Findings:</th>
        </tr>
        <tr>
            <td style="min-height: 80px; height: 80px; vertical-align: top;">
                {{ $exam->clinical_findings ?: 'Findings noted during physical examination: elevated blood pressure or specific clinical signs requiring secondary evaluation.' }}
                <div class="mt-2 small text-muted">Vitals: BP: {{ $exam->blood_pressure ?: '120/80' }} | Pulse: {{ $exam->pulse_rate ?: '76' }} bpm | Temp: {{ $exam->temperature ?: '36.5' }}°C</div>
            </td>
        </tr>
        <tr>
            <th class="header-cell">Treatment:</th>
        </tr>
        <tr>
            <td style="min-height: 70px; height: 70px; vertical-align: top;">
                {{ $exam->medical_recommendations ?: 'First-aid management and clinical observation provided at Health Services Department.' }}
            </td>
        </tr>
        <tr>
            <th class="header-cell">Reasons for Referral:</th>
        </tr>
        <tr>
            <td style="min-height: 70px; height: 70px; vertical-align: top;">
                For comprehensive clinical work-up, specialized diagnostic evaluation, and medical clearance.
            </td>
        </tr>
    </table>

    <div class="clearfix">
        <div class="signature-section">
            <div style="height: 45px;"></div>
            <div class="doc-name">{{ $exam->examiner_name ?: ($settings['clinic_physician'] ?? 'DR. VICENTE S. QUIMPO, MD') }}</div>
            <div>School Physician</div>
            <div>License #: {{ $exam->examiner_license_number ?: ($settings['clinic_physician_license'] ?? '0052551') }}</div>
        </div>
    </div>

    <div class="footer-tag">
        HSD FORM 3- Referral Form
    </div>
</body>
</html>

