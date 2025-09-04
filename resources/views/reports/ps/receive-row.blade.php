<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PS Receive Report - {{ $pi_no }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PS Receive Report</h1>
        <h3>PI No: {{ $pi_no }}</h3>
        <p>Generated at: {{ $generatedAt->format('Y-m-d H:i') }}</p>
    </div>

    <div class="section">
        <strong>General Info:</strong>
        <table>
            <tr>
                <th>Shipment Type</th>
                <td>{{ $shipment_type }}</td>
            </tr>
            <tr>
                <th>Receive Date</th>
                <td>{{ $receive_date }}</td>
            </tr>
            <tr>
                <th>Supplier</th>
                <td>{{ $supplier }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{{ $remarks }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <strong>Chick Counts:</strong>
        <table>
            <tr>
                <th>Male Boxes</th>
                <th>Male Qty</th>
                <th>Female Boxes</th>
                <th>Female Qty</th>
                <th>Total Qty</th>
                <th>Total Boxes</th>
            </tr>
            <tr>
                <td>{{ $chick_counts->ps_male_rec_box ?? 0 }}</td>
                <td>{{ $chick_counts->ps_male_qty ?? 0 }}</td>
                <td>{{ $chick_counts->ps_female_rec_box ?? 0 }}</td>
                <td>{{ $chick_counts->ps_female_qty ?? 0 }}</td>
                <td>{{ $chick_counts->ps_total_qty ?? 0 }}</td>
                <td>{{ $chick_counts->ps_total_re_box_qty ?? 0 }}</td>
            </tr>
        </table>
    </div>

    @if ($lab_transfers->isNotEmpty())
        <div class="section">
            <strong>Lab Transfers:</strong>
            <table>
                <tr>
                    <th>Lab Type</th>
                    <th>Male Qty</th>
                    <th>Female Qty</th>
                    <th>Total Qty</th>
                    <th>Notes</th>
                </tr>
                @foreach ($lab_transfers as $lab)
                    <tr>
                        <td>{{ $lab->lab_type == 1 ? 'Gov Lab' : 'Provita Lab' }}</td>
                        <td>{{ $lab->lab_receive_male_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_receive_female_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_receive_total_qty ?? 0 }}</td>
                        <td>{{ $lab->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

</body>

</html>
