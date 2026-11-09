<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>HSD FORM 1 Full Physical Examination - {{ $exam->pe_number }}</title>
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #000;
            background: #f1f5f9;
            padding: 20px;
        }
        .pe-sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px auto;
            padding: 15mm 17mm;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            box-sizing: border-box;
        }
        .pe-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .pe-table th, .pe-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .line-blank {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 4px;
            min-height: 16px;
        }
        .oval-pill {
            display: inline-block;
            width: 22px;
            height: 12px;
            border: 1.5px solid #000;
            border-radius: 50%;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
            font-size: 8pt;
            font-weight: bold;
        }
        .form-footer {
            position: absolute;
            bottom: 12mm;
            left: 17mm;
            font-style: italic;
            font-size: 8pt;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .pe-sheet {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-center">
        <a href="{{ route('physical-examinations.print-pe', $exam) }}" class="btn btn-success btn-sm px-3 me-2">
            Download Official 2-Page PDF
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 me-2">
            Print 2-Page Form
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">
            Close
        </button>
    </div>

    <div class="pe-sheet">
        <div class="text-center mb-2">
            <div class="fw-bold" style="font-size: 12pt;">{{ strtoupper($settings['university_name'] ?? 'NORTHWESTERN VISAYAN COLLEGES') }}</div>
            <div class="fw-bold" style="font-size: 10pt;">{{ $settings['clinic_name'] ?? 'Health Services Department' }}</div>
            <div style="font-size: 9pt;">{{ $settings['clinic_address'] ?? '2nd Floor, Room 201 ASQ Building Capitol Site' }}</div>
            <div style="font-size: 9pt;">{{ $settings['university_address'] ?? 'Kalibo, Aklan' }}</div>
        </div>

        <div class="text-center fw-bold my-2" style="font-size: 11pt;">PHYSICAL EXAMINATION</div>

        <div class="mb-2" style="line-height: 1.7;">
            <div><strong>Name:</strong> <span class="line-blank" style="width: 88%;">{{ $patient->full_name }}</span></div>
            <div class="d-flex justify-content-between">
                <div style="width: 62%;"><strong>Work/Course:</strong> <span class="line-blank" style="width: 74%;">{{ $patient->program?->code ?? $patient->position ?? ucfirst($patient->type) }}</span></div>
                <div style="width: 35%;"><strong>Sex:</strong> <span class="line-blank" style="width: 78%;">{{ ucfirst($patient->gender) }}</span></div>
            </div>
            <div class="d-flex justify-content-between">
                <div style="width: 62%;"><strong>Date of Birth:</strong> <span class="line-blank" style="width: 74%;">{{ $patient->date_of_birth?->format('F d, Y') }}</span></div>
                <div style="width: 35%;"><strong>Age:</strong> <span class="line-blank" style="width: 78%;">{{ $patient->age }}</span></div>
            </div>
            <div class="d-flex justify-content-between">
                <div style="width: 62%;"><strong>Address:</strong> <span class="line-blank" style="width: 81%;">{{ $patient->address }}</span></div>
                <div style="width: 35%;"><strong>Contact Number:</strong> <span class="line-blank" style="width: 52%;">{{ $patient->phone }}</span></div>
            </div>
            <div><strong>In case of Emergency:</strong></div>
            <div class="d-flex justify-content-between">
                <div style="width: 62%;"><strong>Contact Name:</strong> <span class="line-blank" style="width: 73%;">{{ $patient->emergency_contact_name }}</span></div>
                <div style="width: 35%;"><strong>Contact Number:</strong> <span class="line-blank" style="width: 52%;">{{ $patient->emergency_contact_phone }}</span></div>
            </div>
        </div>

        <div class="text-center fw-bold my-1">VITAL SIGNS:</div>
        <div class="row mb-2">
            <div class="col-6">
                <div><strong>Height:</strong> <span class="line-blank" style="width: 75%;">{{ $exam->height_cm ? "{$exam->height_cm} cm" : '' }}</span></div>
                <div><strong>Weight:</strong> <span class="line-blank" style="width: 75%;">{{ $exam->weight_kg ? "{$exam->weight_kg} kg" : '' }}</span></div>
                <div><strong>BMI:</strong> Wt ({{ $exam->weight_kg ?: '___' }} kg) &divide; Ht ({{ $exam->height_cm ? number_format($exam->height_cm / 100, 2) : '___' }} m)&sup2; {{ $exam->bmi ? "= {$exam->bmi}" : '' }}</div>
            </div>
            <div class="col-6">
                <div><strong>Temperature:</strong> <span class="line-blank" style="width: 68%;">{{ $exam->temperature ? "{$exam->temperature} °C" : '' }}</span></div>
                <div><strong>Pulse Rate:</strong> <span class="line-blank" style="width: 70%;">{{ $exam->pulse_rate ? "{$exam->pulse_rate} bpm" : '' }}</span></div>
                <div><strong>Respiratory Rate:</strong> <span class="line-blank" style="width: 60%;">{{ $exam->respiratory_rate ? "{$exam->respiratory_rate} cpm" : '' }}</span></div>
                <div><strong>Blood Pressure:</strong> <span class="line-blank" style="width: 62%;">{{ $exam->blood_pressure }}</span></div>
            </div>
        </div>

        <div class="form-footer">HSD FORM 1  Full Physical Examination</div>
    </div>
</body>
</html>
