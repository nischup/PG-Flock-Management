<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Pullet Birds Receive Details — Provita Chicks Ltd.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        :root {
            --ink: #111;
            --light: #fafafa;
            --muted: #6f6f6f;
            --border: #1f1f1f;
            --accent: #1b4f72;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: var(--ink);
            font: 12px/1.4 "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #fff;
        }

        .sheet {
            margin: 0 auto;
            padding: 14mm 16mm;
            background: #fff;
            position: relative;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 8px;
            padding-bottom: 8px;
            margin-bottom: 8px;
            text-align: center;
        }

        .header-titles {
            margin: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: var(--accent);
        }

        .brand .logo {
            width: 28px;
            height: 28px;
            border: 2px solid var(--accent);
            border-radius: 4px;
            display: inline-block;
        }

        .title-head {
            font-weight: 900;
            font-size: 20px;
            color: #000;
        }

        .bold {
            border: none;
            font-weight: bold;
        }

        .title {
            font-weight: 800;
            font-size: 14px;
            color: #000;
        }

        .doc-meta {
            text-align: right;
            color: #000;
            font-weight: 500;
        }

        .details {
            margin-top: 8px;
            margin-bottom: 8px;
            padding: 8px;
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 4px;
        }

        .details-bg {
            background: #f1f5f9;
        }

        .details-table {
            border-collapse: collapse;
        }

        .details-table td,
        .details-table th {
            padding: 0;
            margin: 0;
            border: none;
            line-height: 1;
            vertical-align: middle;
        }

        .chip {
            margin: 0;
        }

        table,
        .borderless th,
        .borderless td {
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead th {
            background: #f1f5f9;
            color: #000;
            border: 1px solid var(--border);
            padding: 4px 6px;
            font-weight: 700;
            text-align: center;
        }

        tbody td {
            border: 1px solid var(--border);
            padding: 4px 6px;
            text-align: center;
        }

        tbody tr:nth-child(even) td {
            background: #fcfcfc;
        }

        tfoot td {
            border: 1px solid var(--border);
            padding: 4px 6px;
            font-weight: 700;
            background: #f8fafc;
        }

        .signatures {
            display: flex;
            justify-content: space-evenly;
            margin-top: 100px;
            font-size: 10px;
        }

        .signature {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 4px;
            width: 100%;
        }

        .sin {
            display: inline-flex;
            justify-content: space-between;
            align-items: center;
            padding-right: 38px;
            padding-left: 38px;
        }

        .section-title {
            margin-top: 14px;
            font-weight: 800;
            font-size: 15px;
            padding-bottom: 4px;
        }

        .details-td {
            text-align: left;
            padding-left: 0px;
            margin-left: 0px;
        }
    </style>
</head>

<body>
    <div class="sheet">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <span class="logo" aria-hidden="true"></span>
            </div>
            <div class="header-titles">
                <div class="title-head">{{ $company_name ?? '-' }}</div>
                <div class="title">Pullet Birds Receive Details</div>
                <div class="title">
                    Flock: {{ $flock_name ?? '-' }}
                    {{ $batches[0]['batch_no'] ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Details -->
        <div>
            <div class="doc-meta">
                <span class="label">Form:</span>
                <span class="value">{{ $receive_date }} PBL-PBRD</span>
            </div>
            <div>
                <span class="label">Date:</span>
                <span class="value">{{ $receive_date }}</span>
            </div>
        </div>

        <!-- Borderless Details Table -->
        <div class="details details-bg">
            <table class="details-table">
                <tbody>
                    <tr>
                        <td class="bold"><strong>From:</strong></td>
                        <td class="details-td">{{ $from_company_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="bold"><strong>To:</strong></td>
                        <td class="details-td">{{ $to_company_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Breed:</strong></td>
                        <td>
                            @if (!empty($batches))
                                @foreach ($batches as $index => $batch)
                                    {{ $batch['breed_name'] ?? 'N/A' }}@if ($index < count($batches) - 1)
                                        ,
                                    @endif
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="bold">Invoice / Gate pass :</td>
                        <td>N/A</td>
                    </tr>
                    <tr>
                        <td>LC No</td>
                        <td>{{ $batches[0]['lc_no'] ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Data table -->
        <table aria-label="Pullet birds receive table">
            <thead>
                <tr>
                    <th rowspan="3">Batch</th>
                    <th colspan="12">Birds Number</th>
                    <th colspan="3" rowspan="2">Deviation <br> Short/Excess</th>
                    <th rowspan="3">Remarks</th>
                </tr>
                <tr>
                    <th colspan="3">As per Flock Report</th>
                    <th colspan="3">As Physical Transfer</th>
                    <th colspan="3">Mortality</th>
                    <th colspan="3">Medical Bird</th>
                </tr>
                <tr>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr>
                        <td>{{ $batch['batch_no'] }}</td>
                        <td>{{ $batch['challan_female'] }}</td>
                        <td>{{ $batch['challan_male'] }}</td>
                        <td>{{ $batch['challan_total'] }}</td>
                        <td>{{ $batch['physical_female'] }}</td>
                        <td>{{ $batch['physical_male'] }}</td>
                        <td>{{ $batch['total'] }}</td>
                        <td>{{ $batch['transfer_female_mortality'] ?? 0 }}</td>
                        <td>{{ $batch['transfer_male_mortality'] ?? 0 }}</td>
                        <td>{{ $batch['transfer_total_mortality'] ?? 0 }}</td>
                        <td>{{ $batch['medical_female'] }}</td>
                        <td>{{ $batch['medical_male'] }}</td>
                        <td>{{ $batch['medical_total'] }}</td>
                        <td>{{ $batch['deviation_female'] }}</td>
                        <td>{{ $batch['deviation_male'] }}</td>
                        <td>{{ $batch['deviation_total'] }}</td>
                        <td>{{ $batch['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signatures sin">
            <div class="signature sin pr">
                <div class="signature-line"></div>
                Security
            </div>
            <div class="signature sin">
                <div class="signature-line"></div>
                Store Incharge
            </div>
            <div class="signature sin">
                <div class="signature-line"></div>
                Audit
            </div>
            <div class="signature sin">
                <div class="signature-line"></div>
                Project Incharge
            </div>
            <div class="signature sin">
                <div class="signature-line"></div>
                GM
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Powered by Provita Chicks Ltd. © 2024</div>
            <div>www.provitachicks.com</div>
        </div>
    </div>
</body>

</html>
