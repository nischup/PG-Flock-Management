<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bird Transfer Report - {{ $pi_no ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }

        h1,
        h2,
        p {
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
        }

        .header p {
            font-size: 11px;
        }

        .meta {
            font-size: 11px;
            margin-bottom: 5px;
        }

        .meta span {
            font-weight: bold;
        }

        .breed {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 25px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
        }

        th {
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
        }

        /* ✅ Signature section fix */
        .signatures {
            display: flex;
            justify-content: space-evenly;
            /* evenly distribute space between items */
            margin-top: 150px;
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
            /* Additional flexbox properties for alignment and distribution */
            justify-content: space-between;
            /* Example: distributes items evenly with space between them */
            align-items: center;
            /* Example: vertically centers items along the cross-axis */
            padding-right: 51px;
            padding-left: 51px;
        }

        .meta-container {
            display: flex;
            /* makes children horizontal */
            justify-content: space-between;
            /* spread left, center, right */
            align-items: flex-start;
            /* align to top */
            width: 100%;
            font-size: 11px;
            margin-bottom: 10x;
            margin-top: 35px;
        }

        .meta-left,
        .meta-center,
        .meta-right {
            display: inline-flex;
            flex-direction: column;
            /* stack label/value vertically */
            gap: 2px;
            padding-left: 50px;
            margin-right: 150px;



            /* space between rows */
        }

        .meta-left div span,
        .meta-center div span,
        .meta-right div span {
            font-weight: bold;
            min-width: 80px;
            /* makes labels align neatly */
        }

        .meta-center {
            text-align: left;
        }

        .meta-right {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Provita Hatcheries Limited, Unit -02</h1>
        <p>Boropool, Zamiderhat</p>
        <h2 style="margin:4px 0; font-size:13px;">Bird Transfer Details</h2>
        <p>Flock No.{{ $flock_name ?? '37' }} Batch-{{ $batchNo ?? 'C' }}</p>
    </div>

    <div class="meta-container">
        <div class="meta-left">
            <div><span>From:</span> {{ $from ?? 'PHL-02' }}</div>
            <div><span>To:</span> {{ $to ?? 'PCL-01' }}</div>
            <div><span>Breed Name:</span> {{ $breed_name ?? 'Hubbard Colour' }}</div>
        </div>

        <div class="meta-center">
            <div><span>Age:</span> {{ $age ?? '9+4' }}</div>
            <div><span>LC No:</span> {{ $lc_no ?? '0000296525010082' }}</div>
        </div>

        <div class="meta-right">
            <div><span>Date:</span> {{ $date ?? '13.05.25' }}</div>
            <div><span>Origin:</span> {{ $origin ?? 'France' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Batch No.</th>
                <th rowspan="2">Gate Pass No</th>
                <th colspan="3">As per Flock Report</th>
                <th colspan="3">As Physical Transfer</th>
                <th colspan="3">Mortality</th>
                <th colspan="3">Medical Bird</th>
                <th colspan="3">Short/Excess</th>
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
            <tr>
                <td>{{ $batchNo ?? 'C' }}</td>
                <td>{{ $gatePassNo ?? '11334-11338' }}</td>
                <td>{{ number_format($flockReport['female'] ?? 12761) }}</td>
                <td>{{ number_format($flockReport['male'] ?? 1008) }}</td>
                <td>{{ number_format($flockReport['total'] ?? 13769) }}</td>

                <td>{{ number_format($physicalTransfer['female'] ?? 12656) }}</td>
                <td>{{ number_format($physicalTransfer['male'] ?? 990) }}</td>
                <td>{{ number_format($physicalTransfer['total'] ?? 13646) }}</td>

                <td>{{ $mortality['female'] ?? 0 }}</td>
                <td>{{ $mortality['male'] ?? 0 }}</td>
                <td>{{ $mortality['total'] ?? 0 }}</td>

                <td>{{ $medicalBird['female'] ?? 55 }}</td>
                <td>{{ $medicalBird['male'] ?? 12 }}</td>
                <td>{{ $medicalBird['total'] ?? 67 }}</td>

                <td>{{ $deviation['female'] ?? -50 }}</td>
                <td>{{ $deviation['male'] ?? -6 }}</td>
                <td>{{ $deviation['total'] ?? -56 }}</td>
            </tr>

            <tr class="total-row">
                <td>Total</td>
                <td>-</td>
                <td>{{ number_format($flockReport['female'] ?? 12761) }}</td>
                <td>{{ number_format($flockReport['male'] ?? 1008) }}</td>
                <td>{{ number_format($flockReport['total'] ?? 13769) }}</td>

                <td>{{ number_format($physicalTransfer['female'] ?? 12656) }}</td>
                <td>{{ number_format($physicalTransfer['male'] ?? 990) }}</td>
                <td>{{ number_format($physicalTransfer['total'] ?? 13646) }}</td>

                <td>{{ $mortality['female'] ?? 0 }}</td>
                <td>{{ $mortality['male'] ?? 0 }}</td>
                <td>{{ $mortality['total'] ?? 0 }}</td>

                <td>{{ $medicalBird['female'] ?? 55 }}</td>
                <td>{{ $medicalBird['male'] ?? 12 }}</td>
                <td>{{ $medicalBird['total'] ?? 67 }}</td>

                <td>{{ $deviation['female'] ?? -50 }}</td>
                <td>{{ $deviation['male'] ?? -6 }}</td>
                <td>{{ $deviation['total'] ?? -56 }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures sin">
        <div class="signature sin pr">
            <div class="signature-line"></div>
            Store Incharge
        </div>
        <div class="signature sin">
            <div class="signature-line"></div>
            Accounts
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
</body>

</html>
