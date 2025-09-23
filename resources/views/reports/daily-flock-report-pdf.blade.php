<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Flock Report - PDF</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
        }
        
        .report-container {
            width: 100%;
            max-width: 100%;
        }
        
        /* Header Styles */
        .report-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .company-info {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .report-period {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        /* Table Styles */
        .table-container {
            margin-bottom: 10px;
            overflow: visible;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-bottom: 8px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        /* Main Table Specific */
        .main-table {
            font-size: 6px;
        }
        
        .main-table th,
        .main-table td {
            padding: 2px 3px;
        }
        
        /* Body Weight Table */
        .body-weight-section {
            margin-bottom: 8px;
        }
        
        .body-weight-table {
            display: flex;
            align-items: flex-start;
        }
        
        .bw-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 3px 2px;
            font-weight: bold;
            font-size: 7px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bw-table {
            border-collapse: collapse;
            border: 1px solid #000;
            margin-left: 2px;
            font-size: 6px;
        }
        
        .bw-table th,
        .bw-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .bw-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        /* Bottom Tables */
        .bottom-tables {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .medicine-egg-section {
            display: flex;
            gap: 8px;
        }
        
        .medicine-table,
        .egg-quality-table {
            flex: 1;
        }
        
        .mortality-tech-section {
            display: flex;
            gap: 8px;
        }
        
        .mortality-details,
        .technical-info {
            flex: 1;
        }
        
        .feed-summary {
            width: 180px;
            margin-left: auto;
        }
        
        .table-title {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 3px 2px;
            font-weight: bold;
            font-size: 7px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2px;
        }
        
        .medicine-table,
        .egg-quality-table,
        .mortality-details,
        .technical-info {
            display: flex;
            align-items: flex-start;
        }
        
        .medicine-table table,
        .egg-quality-table table,
        .mortality-details table,
        .technical-info table {
            border-collapse: collapse;
            border: 1px solid #000;
            margin-left: 2px;
            font-size: 6px;
        }
        
        .medicine-table table th,
        .medicine-table table td,
        .egg-quality-table table th,
        .egg-quality-table table td,
        .mortality-details table th,
        .mortality-details table td,
        .technical-info table th,
        .technical-info table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .medicine-table table th,
        .egg-quality-table table th,
        .mortality-details table th,
        .technical-info table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .feed-content {
            border: 1px solid #000;
            padding: 4px;
            font-size: 7px;
        }
        
        .feed-content > div {
            margin-bottom: 2px;
        }
        
        /* Footer */
        .report-footer {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            font-weight: bold;
        }
        
        .signature-box {
            width: 150px;
            height: 40px;
            border: 1px solid #000;
            margin-top: 5px;
        }
        
        /* Environment Info */
        .env-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 7px;
        }
        
        .env-table {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            min-width: 80px;
        }
        
        /* Total row styling */
        .total-row {
            font-weight: bold;
            background-color: #f8f8f8;
        }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        
        /* Ensure tables don't break awkwardly */
        table {
            page-break-inside: avoid;
        }
        
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="company-info">{{ $from_company }}</div>
            <div class="report-title">DAILY FLOCK REPORT</div>
            <div class="report-period">
                @if(isset($filters['date_from']) && isset($filters['date_to']) && !empty($filters['date_from']) && !empty($filters['date_to']))
                    Period: {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}
                @elseif(isset($filters['date_from']) && !empty($filters['date_from']))
                    From: {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }}
                @else
                    Report Date: {{ \Carbon\Carbon::now()->format('M d, Y') }}
                @endif
            </div>
        </div>

        <!-- Environment Information -->
        <div class="env-info">
            <div class="env-table">Temperature: 25°C</div>
            <div class="env-table">Humidity: 60%</div>
            <div class="env-table">Ventilation: Good</div>
        </div>

        <!-- Body Weight Table -->
        <div class="body-weight-section">
            <div class="body-weight-table">
                <div class="bw-header">Body Weight (g)</div>
                <table class="bw-table">
                    <thead>
                        <tr>
                            <th>Age (Days)</th>
                            <th>Weight</th>
                            <th>Age (Days)</th>
                            <th>Weight</th>
                            <th>Age (Days)</th>
                            <th>Weight</th>
                            <th>Age (Days)</th>
                            <th>Weight</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>45</td>
                            <td>8</td>
                            <td>120</td>
                            <td>15</td>
                            <td>200</td>
                            <td>22</td>
                            <td>280</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>50</td>
                            <td>9</td>
                            <td>130</td>
                            <td>16</td>
                            <td>210</td>
                            <td>23</td>
                            <td>290</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Main Table -->
        <div class="table-container">
            <table class="main-table">
                <thead>
                    <tr>
                        <th rowspan="2">Delivery Date</th>
                        <th rowspan="2">Breed Type</th>
                        <th rowspan="2">Batch No</th>
                        <th rowspan="2">Qty</th>
                        <th rowspan="2">Age (Days)</th>
                        <th rowspan="2">Body Weight (g)</th>
                        <th colspan="3">Feed Consumption</th>
                        <th colspan="2">Water</th>
                        <th colspan="3">Production</th>
                        <th colspan="2">Mortality</th>
                        <th rowspan="2">Remarks</th>
                    </tr>
                    <tr>
                        <th>Starter</th>
                        <th>Grower</th>
                        <th>Layer</th>
                        <th>Consumption (L)</th>
                        <th>Quality</th>
                        <th>Eggs</th>
                        <th>Grade A</th>
                        <th>Grade B</th>
                        <th>Count</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td>{{ $batch['delivery_date'] ?? '-' }}</td>
                        <td>{{ $batch['breed_type'] ?? '-' }}</td>
                        <td>{{ $batch['batch_no'] ?? '-' }}</td>
                        <td>{{ $batch['qty'] ?? '-' }}</td>
                        <td>{{ $batch['age'] ?? '-' }}</td>
                        <td>{{ $batch['body_weight'] ?? '-' }}</td>
                        <td>{{ $batch['starter_feed'] ?? '-' }}</td>
                        <td>{{ $batch['grower_feed'] ?? '-' }}</td>
                        <td>{{ $batch['layer_feed'] ?? '-' }}</td>
                        <td>{{ $batch['water_consumption'] ?? '-' }}</td>
                        <td>{{ $batch['water_quality'] ?? '-' }}</td>
                        <td>{{ $batch['eggs_produced'] ?? '-' }}</td>
                        <td>{{ $batch['grade_a'] ?? '-' }}</td>
                        <td>{{ $batch['grade_b'] ?? '-' }}</td>
                        <td>{{ $batch['mortality_count'] ?? '-' }}</td>
                        <td>{{ $batch['mortality_percentage'] ?? '-' }}</td>
                        <td>{{ $batch['remarks'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" style="text-align: center; font-style: italic;">No data available</td>
                    </tr>
                    @endforelse
                    
                    <!-- Totals Row -->
                    <tr class="total-row">
                        <td colspan="3"><strong>TOTALS</strong></td>
                        <td><strong>{{ $totals['total_qty'] ?? '0' }}</strong></td>
                        <td>-</td>
                        <td>-</td>
                        <td><strong>{{ $totals['total_starter'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_grower'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_layer'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_water'] ?? '0' }}</strong></td>
                        <td>-</td>
                        <td><strong>{{ $totals['total_eggs'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_grade_a'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_grade_b'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['total_mortality'] ?? '0' }}</strong></td>
                        <td><strong>{{ $totals['avg_mortality'] ?? '0' }}%</strong></td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bottom Tables -->
        <div class="bottom-tables">
            <!-- Medicine & Egg Quality Section -->
            <div class="medicine-egg-section">
                <div class="medicine-table">
                    <div class="table-title">Medicine & Vaccine</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Time</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Vitamin A</td>
                                <td>2ml/L</td>
                                <td>Morning</td>
                                <td>Good</td>
                            </tr>
                            <tr>
                                <td>Antibiotic</td>
                                <td>1ml/L</td>
                                <td>Evening</td>
                                <td>Good</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="egg-quality-table">
                    <div class="table-title">Egg Quality/Defect</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>%</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Normal</td>
                                <td>850</td>
                                <td>85%</td>
                                <td>Market</td>
                            </tr>
                            <tr>
                                <td>Cracked</td>
                                <td>50</td>
                                <td>5%</td>
                                <td>Reject</td>
                            </tr>
                            <tr>
                                <td>Small</td>
                                <td>100</td>
                                <td>10%</td>
                                <td>Local</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mortality & Technical Section -->
            <div class="mortality-tech-section">
                <div class="mortality-details">
                    <div class="table-title">Mortality Details</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Cause</th>
                                <th>Count</th>
                                <th>Age</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Disease</td>
                                <td>5</td>
                                <td>15 days</td>
                                <td>180g</td>
                            </tr>
                            <tr>
                                <td>Accident</td>
                                <td>2</td>
                                <td>20 days</td>
                                <td>220g</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="technical-info">
                    <div class="table-title">Technical Information About Reject Egg</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Issue</th>
                                <th>Count</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Shell Defect</td>
                                <td>30</td>
                                <td>Thin Shell</td>
                            </tr>
                            <tr>
                                <td>Size Issue</td>
                                <td>20</td>
                                <td>Too Small</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Feed Summary -->
            <div class="feed-summary">
                <div class="table-title">Total Feed</div>
                <div class="feed-content">
                    <div><strong>Starter:</strong> {{ $totals['total_starter'] ?? '0' }} kg</div>
                    <div><strong>Grower:</strong> {{ $totals['total_grower'] ?? '0' }} kg</div>
                    <div><strong>Layer:</strong> {{ $totals['total_layer'] ?? '0' }} kg</div>
                    <div><strong>Total:</strong> {{ ($totals['total_starter'] ?? 0) + ($totals['total_grower'] ?? 0) + ($totals['total_layer'] ?? 0) }} kg</div>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="report-footer">
            <div class="signature-row">
                <div>
                    <div>Prepared by:</div>
                    <div class="signature-box"></div>
                </div>
                <div>
                    <div>Checked by:</div>
                    <div class="signature-box"></div>
                </div>
                <div>
                    <div>Approved by:</div>
                    <div class="signature-box"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
