<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bird Transfer Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        
        .info-section {
            flex: 1;
            margin-right: 20px;
        }
        
        .info-section:last-child {
            margin-right: 0;
        }
        
        .info-section h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: bold;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
        }
        
        .info-value {
            color: #6c757d;
            flex: 1;
        }
        
        .quantities-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .quantities-table th,
        .quantities-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
        }
        
        .quantities-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .quantities-table .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bird Transfer Report</h1>
        <p>Generated on: {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="report-info">
        <div class="info-section">
            <h3>Transfer Information</h3>
            <div class="info-row">
                <div class="info-label">Job No:</div>
                <div class="info-value">{{ $job_no ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Transaction No:</div>
                <div class="info-value">{{ $transaction_no ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Transfer Date:</div>
                <div class="info-value">{{ $transfer_date ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="{{ $status == 1 ? 'status-active' : 'status-inactive' }}">
                        {{ $status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Flock Information</h3>
            <div class="info-row">
                <div class="info-label">Flock Name:</div>
                <div class="info-value">{{ $flock_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Flock ID:</div>
                <div class="info-value">{{ $flock_id ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Breed Type:</div>
                <div class="info-value">{{ $breed_type ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Country of Origin:</div>
                <div class="info-value">{{ $country_of_origin ?? '-' }}</div>
            </div>
        </div>

        <div class="info-section">
            <h3>Company & Location</h3>
            <div class="info-row">
                <div class="info-label">From Company:</div>
                <div class="info-value">{{ $from_company_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">To Company:</div>
                <div class="info-value">{{ $to_company_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">From Shed:</div>
                <div class="info-value">{{ $from_shed_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">To Shed:</div>
                <div class="info-value">{{ $to_shed_name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <table class="quantities-table">
        <thead>
            <tr>
                <th colspan="4" class="section-header">Transfer Quantities</th>
            </tr>
            <tr>
                <th>Type</th>
                <th>Female</th>
                <th>Male</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Transfer Quantity</strong></td>
                <td>{{ number_format($transfer_female_qty ?? 0) }}</td>
                <td>{{ number_format($transfer_male_qty ?? 0) }}</td>
                <td><strong>{{ number_format($transfer_total_qty ?? 0) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Medical Quantity</strong></td>
                <td>{{ number_format($medical_female_qty ?? 0) }}</td>
                <td>{{ number_format($medical_male_qty ?? 0) }}</td>
                <td><strong>{{ number_format($medical_total_qty ?? 0) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Deviation Quantity</strong></td>
                <td>{{ number_format($deviation_female_qty ?? 0) }}</td>
                <td>{{ number_format($deviation_male_qty ?? 0) }}</td>
                <td><strong>{{ number_format($deviation_total_qty ?? 0) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="report-info">
        <div class="info-section">
            <h3>Additional Information</h3>
            <div class="info-row">
                <div class="info-label">LC No:</div>
                <div class="info-value">{{ $lc_no ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Transport Type:</div>
                <div class="info-value">{{ $transport_type ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Created At:</div>
                <div class="info-value">{{ $created_at ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This report was generated automatically by the Provita Flock Management System.</p>
    </div>
</body>
</html>

