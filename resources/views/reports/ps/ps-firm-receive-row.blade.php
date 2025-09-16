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
            /* removes gaps between cells */
        }

        .details-table td,
        .details-table th {
            padding: 0;
            /* remove all padding */
            margin: 0;
            /* remove any margin */
            border: none;
            /* remove borders if any */
            line-height: 1;
            /* optional: make text compact vertically */
            vertical-align: middle;
            /* keeps text aligned */
        }

        .chip {
            margin: 0;
            /* remove chip spacing if needed */
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
            /* evenly distribute space between items */
            margin-top: 90px;
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
            margin-top: 14px;
            font-weight: 800;
            font-size: 15px;
            padding-bottom: 4px;
        }

        .counting-team-container {
            width: 50%;
            margin-left: 0;
            margin-top: 10px;
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
            /* padding: 12px 5px; */
            padding: 5px;
            text-align: left;
        }

        .team th {
            background: #f1f5f9;
            text-align: left;
        }

        .footer {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            color: var(--muted);
            font-size: 11px;
            padding-top: 6px;
        }

        .details-td {
            text-align: left;
            padding-left: 0px;
            margin-left: 0px;
        }
    </style>
</head>

<body>

    <body>
        <div class="sheet">
            <!-- Header -->
            <div class="header">
                <div class="brand">
                    <span class="logo" aria-hidden="true"></span>
                </div>
                <div class="header-titles">
                    <div class="title-head">{{ $company_name ?? '-' }}</div>
                    <div class="title">{{ $flock_name ?? '-' }}</div>
                    <div class="title">Pullet Birds Receive Details</div>
                </div>
            </div>

            <!-- Details -->
            <div>
                <div>
                    <span class="label">Date:</span>
                    <span class="value">{{ $receive_date }}</span>
                </div>
                <div class="doc-meta">
                    <span class="label">Form:</span>
                    <span class="value">{{ $receive_date }} PBL-PBRD</span>
                </div>
            </div>

            <!-- Borderless Details Table -->
            <div class="details details-bg">
                <table class="details-table">
                    <tbody>
                        <tr>
                            <td class="bold">Flock :</td>
                            <td class="details-td">{{ $flock_name }}</td>
                            {{-- <td class="bold">Batch :</td>
                            <td class="details-td">{{ $batches[0]['batch_no'] ?? '-' }}</td> --}}
                            {{-- <td class="bold">Shed :</td>
                            <td class="details-td">{{ $source_id }}</td> --}}
                        </tr>
                        <tr>
                            <td class="bold">Flock origin :</td>
                            <td class="details-td">{{ $source_type }}</td>
                            <td class="bold">Breed :</td>
                            <td class="details-td">{{ $breed_type ?? '-' }}</td>
                            <td class="bold">Invoice / Gate pass :</td>
                            <td class="details-td">
                                @foreach ($batches as $batch)
                                    <span class="chip details-td">{{ $batch['batch_no'] }}</span>
                                @endforeach
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Data table -->
            <table aria-label="Pullet birds receive table">
                <thead>
                    <tr>
                        <th rowspan="3">Batch</th>
                        <th colspan="9">Birds Number</th>
                        <th colspan="3" rowspan="2">Deviation</th>
                        <th rowspan="3">Remarks</th>
                    </tr>
                    <tr>
                        <th colspan="3">As per challan</th>
                        <th colspan="6">As physical count</th>
                    </tr>
                    <tr>
                        <th>Female Box</th>
                        <th>Male Box</th>
                        <th>Total Box</th>
                        <th>Female Box</th>
                        <th>Shortage Female BOX</th>
                        {{-- <th>Total (Female)</th> --}}
                        <th>Male Box</th>
                        <th>Shortage Male BOX</th>
                        <th>Total Shortage</th>
                        <th>Total Box</th>
                        <th>Female BOX</th>
                        <th>Male Box</th>
                        <th>Total BOX</th>
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
                            <td>{{ $batch['box_f'] }}</td>
                            {{-- <td>{{ $batch['total_female'] }}</td> --}}
                            <td>{{ $batch['physical_male'] }}</td>
                            <td>{{ $batch['box_m'] }}</td>
                            <td>{{ $batch['box_shortage'] }}</td>
                            <td>{{ $batch['total'] }}</td>
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
                <div>Powered by Provita Chicks Ltd. © 2024</div>
                <div>www.provitachicks.com</div>
            </div>
        </div>
    </body>

</html>
