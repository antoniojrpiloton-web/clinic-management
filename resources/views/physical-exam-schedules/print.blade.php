<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PE Schedule Roster - {{ $schedule->schedule_number }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            color: #000;
            background: #fff;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        .inst-title {
            font-size: 14pt;
            font-weight: bold;
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
        .sheet-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 15px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .table-roster th, .table-roster td {
            padding: 6px 8px;
            border: 1px solid #333;
            font-size: 9.5pt;
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
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
            Print Roster
        </button>
        <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm">
            Close
        </button>
    </div>

    <div class="text-center mb-3">
        <div class="inst-title">{{ $settings['university_name'] ?? 'Surigao del Norte State University' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'SNSU University Clinic' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? 'Ground Floor, Student Services Center, SNSU Main Campus' }}</div>
    </div>

    <div class="sheet-title">PHYSICAL EXAMINATION ATTENDANCE ROSTER</div>

    <table class="w-100 mb-3" style="font-size: 10pt; line-height: 1.8;">
        <tr>
            <td style="width: 60%;"><strong>Campaign Title:</strong> {{ $schedule->title }}</td>
            <td style="width: 40%;"><strong>Schedule #:</strong> {{ $schedule->schedule_number }}</td>
        </tr>
        <tr>
            <td><strong>Target Cohort:</strong> {{ $schedule->target_group_label }}</td>
            <td><strong>Exam Date:</strong> {{ $schedule->scheduled_date->format('F d, Y') }} ({{ $schedule->time_range }})</td>
        </tr>
        <tr>
            <td><strong>Venue:</strong> {{ $schedule->venue ?: 'Clinic Room' }}</td>
            <td><strong>Examining Physician:</strong> {{ $schedule->clinician?->name ?? 'Attending Clinician' }}</td>
        </tr>
    </table>

    <table class="table-roster w-100 mb-4">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 18%;">Student / Employee ID</th>
                <th style="width: 32%;">Patient Name</th>
                <th style="width: 25%;">College / Program / Dept</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 10%; text-align: center;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendees as $idx => $att)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $att->patient->primary_identifier }}</td>
                    <td><strong>{{ $att->patient->full_name }}</strong></td>
                    <td>{{ $att->patient->program?->code ?? ($att->patient->college?->code ?: ($att->patient->office_department ?: 'N/A')) }}</td>
                    <td style="text-align: center;">{{ $att->status_label }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No attendees registered on this roster.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between mt-5 pt-3">
        <div style="width: 240px; border-top: 1px solid #000; text-align: center; font-size: 9.5pt; padding-top: 4px;">
            <strong>{{ $schedule->clinician?->name ?? ($settings['clinic_head'] ?? 'Dr. Maria Elena Santos, MD') }}</strong><br>
            Lead Examining Physician
        </div>

        <div style="width: 240px; border-top: 1px solid #000; text-align: center; font-size: 9.5pt; padding-top: 4px;">
            <strong>{{ auth()->user()->name }}</strong><br>
            Clinic Head / Health Services Officer
        </div>
    </div>
</body>
</html>

