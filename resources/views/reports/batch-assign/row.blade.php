<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Batch Assignment Report - {{ $batchAssign->id ?? 'N/A' }}</title>
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
    </style>
</head>

<body>
    <!-- Unified Report Header -->
    <div style="text-align: center; margin-bottom: 25px; font-family: DejaVu Sans, sans-serif; color: #1a202c;">
        <!-- Company Info -->
        <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: #d97706;">
            PROVITA GROUP
        </h1>
        <p style="margin: 4px 0; font-size: 14px; color: #4a5568;">
            Provita Tower, House #21, Road #35, Gulshan-02, Dhaka 1212
        </p>
        <p style="margin: 2px 0; font-size: 14px; color: #4a5568;">
            Phone: +880248811872 | Email: hrm@provitagroupbd.com
        </p>

        <!-- Divider (orange highlight for batch assignment) -->
        <hr style="border: 0; border-top: 2px solid #f97316; margin: 14px 0;">

        <!-- Report Title -->
        <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: #c2410c;">
            📦 Batch Assignment Report
        </h2>
        <h3 style="margin: 4px 0; font-size: 16px; font-weight: normal; color: #9a3412;">
            Assignment ID: <span style="color: #ea580c;">{{ $batchAssign->id ?? 'N/A' }}</span>
        </h3>
        <p style="margin: 2px 0; font-size: 13px; color: #4a5568;">
            Generated at: <span style="color: #c2410c;">{{ $generatedAt?->format('Y-m-d H:i') }}</span>
        </p>
    </div>

    {{-- General Info --}}
    <div class="section">
        <strong>General Information:</strong>
        <table>
            <tr>
                <th>Flock Name</th>
                <td>{{ $flock_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Shed Name</th>
                <td>{{ $shed_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Company</th>
                <td>{{ $company_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Shed Receive Transaction</th>
                <td>{{ $shed_receive_transaction ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Job No</th>
                <td>{{ $batchAssign->job_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Level</th>
                <td>{{ $batchAssign->level ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Batch No</th>
                <td>{{ $batchAssign->batch_no ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Quantities --}}
    <div class="section">
        <strong>Batch Quantities:</strong>
        <table>
            <tr>
                <th>Quantity Type</th>
                <th>Female</th>
                <th>Male</th>
                <th>Total</th>
            </tr>
            <tr>
                <td><strong>Main Quantities</strong></td>
                <td>{{ $batchAssign->batch_female_qty ?? 0 }}</td>
                <td>{{ $batchAssign->batch_male_qty ?? 0 }}</td>
                <td><strong>{{ $batchAssign->batch_total_qty ?? 0 }}</strong></td>
            </tr>
            <tr>
                <td><strong>Mortality</strong></td>
                <td>{{ $batchAssign->batch_female_mortality ?? 0 }}</td>
                <td>{{ $batchAssign->batch_male_mortality ?? 0 }}</td>
                <td><strong>{{ $batchAssign->batch_total_mortality ?? 0 }}</strong></td>
            </tr>
            <tr>
                <td><strong>Excess</strong></td>
                <td>{{ $batchAssign->batch_excess_female ?? 0 }}</td>
                <td>{{ $batchAssign->batch_excess_male ?? 0 }}</td>
                <td><strong>{{ ($batchAssign->batch_excess_female ?? 0) + ($batchAssign->batch_excess_male ?? 0) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Shortage</strong></td>
                <td>{{ $batchAssign->batch_sortage_female ?? 0 }}</td>
                <td>{{ $batchAssign->batch_sortage_male ?? 0 }}</td>
                <td><strong>{{ ($batchAssign->batch_sortage_female ?? 0) + ($batchAssign->batch_sortage_male ?? 0) }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Additional Details --}}
    <div class="section">
        <strong>Additional Details:</strong>
        <table>
            <tr>
                <th>Percentage</th>
                <td>{{ $batchAssign->percentage ?? 0 }}%</td>
            </tr>
            <tr>
                <th>Stage</th>
                <td>{{ $batchAssign->stage ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Growing Start Date</th>
                <td>{{ $batchAssign->growing_start_date ? \Carbon\Carbon::parse($batchAssign->growing_start_date)->format('d-M-Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Transfer Date</th>
                <td>{{ $batchAssign->transfer_date ? \Carbon\Carbon::parse($batchAssign->transfer_date)->format('d-M-Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Created Date</th>
                <td>{{ $batchAssign->created_at ? $batchAssign->created_at->format('d-M-Y H:i') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Signature Matrix --}}
    <div class="signatures">
        <div class="signature">
            <div class="signature-line"></div>
            <span>Prepared By</span>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <span>Checked By</span>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <span>Approved By</span>
        </div>
    </div>
</body>

</html>
