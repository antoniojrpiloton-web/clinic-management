<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Certificate - {{ $patient->full_name }}</title>
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
            letter-spacing: 0.5px;
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
            letter-spacing: 1px;
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
        .remarks-box {
            min-height: 80px;
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
            line-height: 1.8;
            font-style: italic;
        }
        .circle-check {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #000;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
            text-align: center;
            line-height: 13px;
            font-size: 10pt;
            font-weight: bold;
        }
        .circle-check.active {
            background-color: #000;
            color: #fff;
        }
        .activity-row {
            margin: 10px 0;
            display: flex;
            align-items: center;
        }
        .disclaimer-text {
            margin-top: 35px;
            font-size: 10.5pt;
            line-height: 1.6;
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
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3">Print Certificate</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Close</button>
    </div>

    <div class="text-center mb-4">
        <div class="inst-title">{{ $settings['university_name'] ?? 'Northwestern Visayan Colleges' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'HEALTH SERVICES DEPARTMENT' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? '2nd Floor, Room 201 and Room 202, ASQ Building' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address_line2'] ?? 'Capitol Site, Kalibo, Aklan' }}</div>
    </div>

    <div class="text-end mb-4">
        <span>Date: </span>
        <span class="underline-field" style="min-width: 150px;">{{ $exam->exam_date->format('F d, Y') }}</span>
    </div>

    <div class="cert-title">MEDICAL CERTIFICATE</div>

    <div class="mb-3">
        <strong>To Whom It May Concern:</strong>
    </div>

    <div class="content-body">
        This is to certify that
        <span class="underline-field" style="min-width: 250px;">{{ $patient->full_name }}</span>,
        <span class="underline-field" style="min-width: 40px; text-align: center;">{{ $patient->age }}</span>
        years old, {{ $patient->gender }} and a resident of
        <span class="underline-field" style="min-width: 320px;">{{ $patient->address ?: 'Kalibo, Aklan' }}</span>
        has been seen and examined by the undersigned.
    </div>

    <div class="remarks-box mt-3 mb-3">
        {{ $exam->clinical_findings ?: ($exam->medical_recommendations ?: 'Essentially normal physical examination findings. Cleared for institutional and academic activities.') }}
    </div>

    <div class="mt-4">
        <div class="mb-2"><strong>He/ She is:</strong></div>
        @php
            $acts = (array) ($exam->fitness_activities ?? []);
        @endphp

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['physical_activities'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['physical_activities'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['physical_activities'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['physical_activities'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>to undergo Physical Activities/ Trainings</span>
        </div>

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['on_the_job_training'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['on_the_job_training'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['on_the_job_training'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['on_the_job_training'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>On-the-Job Training</span>
        </div>

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['work_immersion'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['work_immersion'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['work_immersion'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['work_immersion'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>Work Immersion</span>
        </div>

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['to_work'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['to_work'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['to_work'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['to_work'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>to Work</span>
        </div>

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['to_travel'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['to_travel'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['to_travel'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['to_travel'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>to Travel</span>
        </div>

        <div class="activity-row">
            <span class="me-3">
                <span class="circle-check {{ ($acts['other_requirements'] ?? 'fit') === 'fit' ? 'active' : '' }}">
                    {{ ($acts['other_requirements'] ?? 'fit') === 'fit' ? '&check;' : '' }}
                </span> Fit
                <span class="circle-check ms-2 {{ ($acts['other_requirements'] ?? '') === 'unfit' ? 'active' : '' }}">
                    {{ ($acts['other_requirements'] ?? '') === 'unfit' ? '&check;' : '' }}
                </span> Unfit
            </span>
            <span>other Requirements</span>
        </div>
    </div>

    <div class="disclaimer-text">
        This certificate is being issued upon the request of the above person for whatever purpose it may serve. This certificate is not intended for use in legal matters.
    </div>

    <div class="clearfix">
        <div class="signature-section">
            <div style="height: 45px;"></div>
            <div class="doc-name">{{ $exam->examiner_name ?: ($settings['clinic_physician'] ?? 'DR. VICENTE S. QUIMPO, MD') }}</div>
            <div>Attending Physician</div>
            <div>License #: {{ $exam->examiner_license_number ?: ($settings['clinic_physician_license'] ?? '0052551') }}</div>
        </div>
    </div>

    <div class="footer-tag">
        HSD FORM 4- Personal Copy
    </div>
</body>
</html>

