<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dispensing Slip - {{ $dispensing->dispensing_number }} - {{ $patient->full_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
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
        .slip-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 15px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .table-slip th, .table-slip td {
            padding: 6px 8px;
            border: 1px solid #333;
            font-size: 10pt;
        }
        .underline-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 6px;
            font-weight: bold;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 240px;
            margin-top: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
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
            Print Dispensing Slip
        </button>
        <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm">
            Close
        </button>
    </div>

    <div class="text-center mb-3">
        <div class="inst-title">{{ $settings['clinic_institution'] ?? 'Surigao del Norte State University' }}</div>
        <div class="dept-title">{{ $settings['clinic_name'] ?? 'UNIVERSITY HEALTH SERVICES DEPARTMENT' }}</div>
        <div class="dept-sub">{{ $settings['clinic_address'] ?? 'Surigao City, Philippines' }}</div>
        <div class="dept-sub">Contact: {{ $settings['clinic_phone'] ?? '(086) 826-6346' }}</div>
    </div>

    <div class="slip-title">MEDICINE DISPENSING SLIP</div>

    <table class="w-100 mb-3" style="font-size: 10.5pt; line-height: 1.8;">
        <tr>
            <td style="width: 65%;">
                <strong>Patient Name:</strong>
                <span class="underline-field" style="min-width: 260px;">{{ $patient->full_name }}</span>
            </td>
            <td style="width: 35%;">
                <strong>Date:</strong>
                <span class="underline-field" style="min-width: 120px;">{{ $dispensing->dispensed_date ? $dispensing->dispensed_date->format('M d, Y') : now()->format('M d, Y') }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>ID Number:</strong>
                <span class="underline-field" style="min-width: 160px;">{{ $patient->primary_identifier }}</span>
                <span class="ms-2"><strong>Type:</strong> <span class="underline-field">{{ ucfirst($patient->type) }}</span></span>
            </td>
            <td>
                <strong>Slip #:</strong>
                <span class="underline-field font-monospace" style="min-width: 120px;">{{ $dispensing->dispensing_number }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>College / Dept:</strong>
                <span class="underline-field" style="min-width: 250px;">{{ $patient->college?->name ?? 'N/A' }}</span>
            </td>
            <td>
                <strong>Age / Sex:</strong>
                <span class="underline-field">{{ $patient->age ?? 'N/A' }} / {{ $patient->sex ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Chief Complaint / Diagnosis:</strong>
                <span class="underline-field" style="min-width: 450px;">{{ $dispensing->diagnosis ?: 'N/A' }}</span>
            </td>
        </tr>
    </table>

    <table class="table-slip w-100 mb-3">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 35%;">Medicine (Generic &amp; Brand)</th>
                <th style="width: 15%; text-align: center;">Batch / Lot #</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 35%;">Dosage Instructions (Sig)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dispensing->items as $idx => $item)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->medicine->generic_name }}</strong>
                        @if($item->medicine->brand_name)
                            ({{ $item->medicine->brand_name }})
                        @endif
                        <div style="font-size: 8.5pt; color: #444;">{{ $item->medicine->strength }} {{ $item->medicine->dosage_form }}</div>
                    </td>
                    <td style="text-align: center; font-family: monospace;">{{ $item->batch?->batch_number ?? 'N/A' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $item->quantity }} {{ $item->medicine->unit }}s</td>
                    <td>{{ $item->dosage_instruction ?: 'As directed' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($dispensing->instructions)
        <div class="mb-3" style="font-size: 10pt;">
            <strong>Special Instructions / Precautions:</strong>
            <em>{{ $dispensing->instructions }}</em>
        </div>
    @endif

    <div class="d-flex justify-content-between mt-5 pt-3">
        <div>
            <div class="signature-line">
                {{ $dispensing->clinician?->name ?? 'N/A (Direct OTC)' }}
            </div>
            <div class="text-center" style="font-size: 9pt;">
                {{ $dispensing->clinician ? $dispensing->clinician->title.' • Lic: '.($dispensing->clinician->license_number ?? 'N/A') : 'Attending Health Care Provider' }}
            </div>
        </div>

        <div>
            <div class="signature-line">
                {{ $dispensing->dispenser?->name ?? auth()->user()->name }}
            </div>
            <div class="text-center" style="font-size: 9pt;">
                Clinic Dispensing Officer / Pharmacist
            </div>
        </div>
    </div>
</body>
</html>

