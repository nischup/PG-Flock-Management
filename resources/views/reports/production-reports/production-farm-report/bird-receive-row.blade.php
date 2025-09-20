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

        .title {
            font-weight: 800;
            font-size: 14px;
            color: #000;
        }

        .bold {
            font-weight: bold;
        }

        .doc-meta {
            text-align: right;
            color: #000;
            font-weight: 500;
        }

        .details {
            margin: 8px 0;
            padding: 8px;
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 4px;
        }

        .details-table {
            border-collapse: collapse;
            background: #f1f5f9;
        }

        .details-table td {
            padding: 2px 4px;
            border: none;
            background: #f1f5f9;
        }

        .bg-f1f5f9 {
            background: #f1f5f9;
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

        .signatures {
            display: flex;
            justify-content: space-evenly;
            /* evenly distribute space between items */
            margin-top: 70px;
            font-size: 10px;
        }

        .signature {
            text-align: center;
            display: flex;
            flex-direction: column;
            /* stack line above name */
            align-items: center;
            min-width: 80px;
            /* optional: ensures minimum line width */
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 4px;
            width: 100%;
            /* full width of signature block */
        }

        .sin {
            display: inline-flex;
            justify-content: space-between;
            align-items: center;
            padding-right: 38;
            padding-left: 38px;
        }

        .section-title {
            margin-top: 5px;
            font-weight: 800;
            font-size: 15px;
            padding-bottom: 4px;
        }

        .counting-team-container {
            width: 50%;
            margin-top: 8px;
        }

        .team {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 11px;
        }

        .team th,
        .team td {
            border: 1px solid var(--border);
            padding: 5px;
            text-align: left;
        }

        .team th {
            background: #f1f5f9;
        }

        .footer {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            color: var(--muted);
            font-size: 10px;
            padding-top: 6px;
        }
    </style>
</head>

<body>
    <div class="sheet">
        <!-- Header -->
        <div class="header">
            <div class="brand"><span class="logo"></span></div>
            <div class="header-titles">
                <div class="title-head">Provita Chicks Ltd.</div>
                <div class="title">{{ $flock_name ?? 'N/A' }}</div>
                <div class="title">Pullet Birds Receive Details</div>
            </div>
        </div>

        <!-- Details -->
        <div>
            <div><span class="label">Date:</span> <span>{{ $receive_date ?? now()->format('Y-m-d') }}</span></div>
            <div class="doc-meta"><span>Form: {{ $from_company }}</span></div>
            <div class="doc-meta"><span>To: {{ $to_company }}</span></div>
        </div>

        <!-- Details Table -->
        <div class="details">
            <table class="details-table">
                <tr>
                    <td class="bold">Flock:</td>
                    <td>{{ $flock_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="bold " style="background-color: #f1f5f9;">Flock origin:</td>
                    <td style="background-color: #f1f5f9;">{{ $country_of_origin ?? 'N/A' }}</td>
                    <td class="bold" style="background-color: #f1f5f9;">Breed:</td>
                    <td style="background-color: #f1f5f9;">{{ $breed_type ?? 'N/A' }}</td>
                    <td class="bold" style="background-color: #f1f5f9;">Invoice / Gate pass:</td>
                    <td style="background-color: #f1f5f9;">{{ $invoice_numbers ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th rowspan="3">Batch</th>
                    <th colspan="10">Birds Number</th>
                    <th colspan="3" rowspan="2">Deviation <br> Short/Excess</th>
                    <th rowspan="3">Remarks</th>
                </tr>
                <tr>
                    <th colspan="3">As per challan</th>
                    <th colspan="7">As physical count</th>
                </tr>
                <tr>
                    <th>Female</th>
                    <th>Male</th>
                    <th>Total</th>
                    <th>Female</th>
                    <th>Box F <br> Mortality</th>
                    <th>Total<br> (Female)</th>
                    <th>Male</th>
                    <th>Box M <br> Mortality</th>
                    <th>Total <br> (Male)</th>
                    <th>Total</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr>
                        <td>{{ $batch['batch_no'] ?? '-' }}</td>

                        <!-- Challan -->
                        <td>{{ $batch['challan_female'] ?? 0 }}</td>
                        <td>{{ $batch['challan_male'] ?? 0 }}</td>
                        <td>{{ $batch['challan_total'] ?? 0 }}</td>

                        <!-- Physical -->
                        <td>{{ $batch['physical_female'] ?? 0 }}</td>
                        <td>{{ $batch['medical_female'] ?? 0 }}</td>
                        <td>{{ ($batch['physical_female'] ?? 0) - ($batch['medical_female'] ?? 0) }}</td>
                        <td>{{ $batch['physical_male'] ?? 0 }}</td>
                        <td>{{ $batch['medical_male'] ?? 0 }}</td>
                        <td>{{ ($batch['physical_male'] ?? 0) - ($batch['medical_male'] ?? 0) }}</td>
                        <td>{{ $batch['total'] ?? 0 }}</td>

                        <!-- Deviation -->
                        <td>{{ $batch['deviation_female'] ?? 0 }}</td>
                        <td>{{ $batch['deviation_male'] ?? 0 }}</td>
                        <td>{{ $batch['deviation_total'] ?? 0 }}</td>

                        <td>{{ $batch['remarks'] ?? '-' }}</td>
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

        <!-- Counting team -->
        <div class="counting-team-container">
            <div class="section-title">Counting team</div>
            <table class="team">
                <thead>
                    <tr>
                        <th style="width: 40px">SL</th>
                        <th>Name</th>
                        <th style="width: 24%">Signature</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Mostafizur Rahman(Sr. Officer — Production)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Md. Mizanur Uddin (Officer — Store)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Md. Mohsin Reza Chowdhury (Audit)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Md. Mahfuz Rahman (Asst. Manager — A/C)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Md. Mizanur Rahman (Asst. Supervisor)</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Powered by Provita Chicks Ltd. © {{ now()->year }}</div>
            <div>www.provitachicks.com</div>
        </div>
    </div>
</body>

</html>
