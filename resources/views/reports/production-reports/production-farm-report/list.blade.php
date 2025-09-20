<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Farm Receive List Report</title>
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
        
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            color: #495057;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .filter-value {
            color: #6c757d;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
            font-size: 11px;
        }
        
        .table td {
            font-size: 10px;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background-color: #e9ecef;
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
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Production Farm Receive List Report</h1>
        <p>Generated on: {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    @if(!empty(array_filter($filters)))
    <div class="filters">
        <h3>Applied Filters:</h3>
        <div class="filter-row">
            @if($filters['search'])
            <div class="filter-item">
                <div class="filter-label">Search:</div>
                <div class="filter-value">{{ $filters['search'] }}</div>
            </div>
            @endif
            
            @if($filters['from_company_id'])
            <div class="filter-item">
                <div class="filter-label">From Company:</div>
                <div class="filter-value">{{ $filters['from_company_id'] }}</div>
            </div>
            @endif
            
            @if($filters['flock_id'])
            <div class="filter-item">
                <div class="filter-label">Flock:</div>
                <div class="filter-value">{{ $filters['flock_id'] }}</div>
            </div>
            @endif
            
            @if($filters['date_from'])
            <div class="filter-item">
                <div class="filter-label">Date From:</div>
                <div class="filter-value">{{ \Carbon\Carbon::parse($filters['date_from'])->format('M j, Y') }}</div>
            </div>
            @endif
            
            @if($filters['date_to'])
            <div class="filter-item">
                <div class="filter-label">Date To:</div>
                <div class="filter-value">{{ \Carbon\Carbon::parse($filters['date_to'])->format('M j, Y') }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($transfers->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Job No</th>
                <th>Transaction No</th>
                <th>Flock Name</th>
                <th>From Company</th>
                <th>To Company</th>
                <th>From Shed</th>
                <th>To Shed</th>
                <th>Transfer Date</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $transfer)
            <tr>
                <td>{{ $transfer->job_no ?? '-' }}</td>
                <td>{{ $transfer->transaction_no ?? '-' }}</td>
                <td>{{ $transfer->flock?->name ?? '-' }}</td>
                <td>{{ $transfer->fromCompany?->name ?? '-' }}</td>
                <td>{{ $transfer->toCompany?->name ?? '-' }}</td>
                <td>{{ $transfer->fromShed?->name ?? '-' }}</td>
                <td>{{ $transfer->toShed?->name ?? '-' }}</td>
                <td>{{ $transfer->transfer_date?->format('M j, Y') ?? '-' }}</td>
                <td>
                    <span class="{{ $transfer->status == 1 ? 'status-active' : 'status-inactive' }}">
                        {{ $transfer->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>{{ $transfer->created_at->format('M j, Y g:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>No production farm receive records found with the applied filters.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Provita Flock Management System.</p>
        <p>Total Records: {{ $transfers->count() }}</p>
    </div>
</body>
</html>

