<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Birds Transfer & Receive Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            margin: 0;
            padding: 1cm;
            color: #333;
        }

        h1,
        h2 {
            text-align: center;
            margin: 0.3em 0;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 0.5em;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .footer {
            margin-top: 1em;
            font-size: 9.5px;
            text-align: right;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <h1>MIS Report</h1>
    <h2>Provita Group – Birds Transfer & Receive Report</h2>

    <table>
        <thead>
            <tr>
                <th rowspan="4">Delivery Date</th>
                <th rowspan="4">Strain</th>
                <th rowspan="4">Batch</th>
                <th colspan="10">Transfer Farm</th>
                <th colspan="6">Receive Farm</th>
                <th colspan="2" rowspan="3">Actual Deviation<br />(Register vs Received)</th>
                <th colspan="2" rowspan="3">Deviation<br />(Challan vs Received)</th>
            </tr>
            <tr>
                <th colspan="10">{{ $from_company }}</th>
                <th colspan="6">{{ $to_company }}</th>
            </tr>
            <tr>
                <th colspan="2">Register</th>
                <th colspan="2">ERP</th>
                <th colspan="2">Challan</th>
                <th colspan="2">Medical</th>
                <th colspan="2">Deviation</th>
                <th colspan="2">Received</th>
                <th colspan="2">Mortality</th>
                <th colspan="2">Total</th>
            </tr>
            <tr>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($batches as $batch)
                <tr>
                    <td>{{ $batch['delivery_date'] ?? 'N/A' }}</td>
                    <td>{{ $batch['breed_type'] ?? 'N/A' }}</td>
                    <td>{{ $batch['batch_no'] ?? 'N/A' }}</td>

                    {{-- Transfer Farm --}}
                    <td>{{ $batch['challan_female'] }}</td>
                    <td>{{ $batch['challan_male'] }}</td>
                    <td>{{ $batch['erp_female'] ?? 'N/A' }}</td>
                    <td>{{ $batch['erp_male'] ?? 'N/A' }}</td>
                    <td>{{ $batch['challan_female'] }}</td>
                    <td>{{ $batch['challan_male'] }}</td>
                    <td>{{ $batch['medical_female'] }}</td>
                    <td>{{ $batch['medical_male'] }}</td>
                    <td>{{ $batch['deviation_female'] }}</td>
                    <td>{{ $batch['deviation_male'] }}</td>

                    {{-- Receive Farm --}}
                    <td>{{ $batch['received_female'] ?? 'N/A' }}</td>
                    <td>{{ $batch['received_male'] ?? 'N/A' }}</td>
                    <td>{{ $batch['mortality_female'] ?? 'N/A' }}</td>
                    <td>{{ $batch['mortality_male'] ?? 'N/A' }}</td>
                    <td>{{ $batch['total_received_female'] ?? 'N/A' }}</td>
                    <td>{{ $batch['total_received_male'] ?? 'N/A' }}</td>

                    {{-- Deviations --}}
                    <td>{{ $batch['actual_deviation_female'] }}</td>
                    <td>{{ $batch['actual_deviation_male'] }}</td>
                    <td>{{ $batch['challan_deviation_female'] }}</td>
                    <td>{{ $batch['challan_deviation_male'] }}</td>
                </tr>
            @endforeach
            <!-- Total Row -->
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <!-- Transfer Farm Total -->
                <td><strong>{{ $totals['challan_female'] }}</strong></td>
                <td><strong>{{ $totals['challan_male'] }}</strong></td>
                <td><strong>{{ $totals['erp_female'] }}</strong></td>
                <td><strong>{{ $totals['erp_male'] }}</strong></td>
                <td><strong>{{ $totals['challan_female'] }}</strong></td>
                <td><strong>{{ $totals['challan_male'] }}</strong></td>
                <td><strong>{{ $totals['medical_female'] }}</strong></td>
                <td><strong>{{ $totals['medical_male'] }}</strong></td>
                <td><strong>{{ $totals['deviation_female'] }}</strong></td>
                <td><strong>{{ $totals['deviation_male'] }}</strong></td>

                <!-- Receive Farm Total -->
                <td><strong>{{ $totals['received_female'] }}</strong></td>
                <td><strong>{{ $totals['received_male'] }}</strong></td>
                <td><strong>{{ $totals['mortality_female'] }}</strong></td>
                <td><strong>{{ $totals['mortality_male'] }}</strong></td>
                <td><strong>{{ $totals['total_received_female'] }}</strong></td>
                <td><strong>{{ $totals['total_received_male'] }}</strong></td>

                <!-- Deviations Total -->
                <td><strong>{{ $totals['actual_deviation_female'] }}</strong></td>
                <td><strong>{{ $totals['actual_deviation_male'] }}</strong></td>
                <td><strong>{{ $totals['challan_deviation_female'] }}</strong></td>
                <td><strong>{{ $totals['challan_deviation_male'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">Prepared by: MIS Department, Provita Group</div>
</body>

</html>
