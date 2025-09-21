<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://public-frontend-cos.metadl.com/mgx/img/favicon.png" type="image/png" />
    <title>Daily Flock Report - Provita Chicks Limited</title>
    <style>
        /* ===============================
         Global Reset & Base Styles
      ================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            background: #fff;
            color: #000;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
        }

        /* ===============================
         Header Section
      ================================= */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            border: 2px solid #000;
            padding: 10px;
        }

        .left-section,
        .right-section {
            flex: 1;
        }

        .center-logo {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }

        .center-logo .logo-placeholder {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .center-logo h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .center-logo h2 {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .center-logo h3 {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* ===============================
         Tables - General
      ================================= */
        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #000;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 9px;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background: #f5f5f5;
        }

        .no-border th {
            border: none !important;
            background: transparent !important;
        }

        /* ===============================
         Batch Table
      ================================= */
        .batch-table {
            font-size: 8px;
        }

        .batch-table th,
        .batch-table td {
            padding: 1px 2px;
        }

        /* ===============================
         Temperature, Grade, Humidity
      ================================= */
        .temp-table,
        .grade-table,
        .humidity-table {
            width: 100%;
            margin-bottom: 5px;
            font-size: 8px;
        }

        .outside-temp {
            font-size: 9px;
            margin-bottom: 2px;
            text-align: center;
        }

        .grade-temp,
        .humidity {
            margin-top: 10px;
        }

        .grade-temp span,
        .humidity span {
            font-size: 9px;
            display: block;
            margin-bottom: 2px;
        }

        /* ===============================
         Main Flock Table
      ================================= */
        .main-flock-section {
            margin: 15px 0;
        }

        .main-flock-table {
            font-size: 8px;
        }

        .main-flock-table th,
        .main-flock-table td {
            padding: 1px 2px;
            border: 1px solid #000;
        }

        /* ===============================
         Medicine Section
      ================================= */
        .medicine-section {
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }

        .medicine-section h4 {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .medicine-table {
            font-size: 8px;
        }

        .medicine-table th,
        .medicine-table td {
            padding: 1px 2px;
        }

        /* ===============================
         Feed Section
      ================================= */
        .feed-section {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }

        .feed-info {
            width: 100%;
        }

        .feed-info h4 {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .feed-stats {
            margin-bottom: 10px;
        }

        .feed-stats div {
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* ===============================
         Bottom Section
      ================================= */
        .bottom-section {
            margin: 15px 0;
        }

        .bottom-table {
            font-size: 7px;
        }

        .bottom-table th,
        .bottom-table td {
            padding: 5px 10px;
        }

        .bottom-table th {
            background: #f9f9f9;
        }

        /* ===============================
         Mortality + Reject Egg Section
      ================================= */
        .tables-wrapper-m {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }

        .mortality-section {
            display: flex;
            align-items: stretch;
            border: 1px solid #000;
            width: fit-content;
        }

        .title {
            border-right: 1px solid #000;
            padding: 5px;
            background: #f2f2f2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rotate-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            margin: 0;
            font-weight: bold;
            text-align: center;
        }

        .data-table {
            flex: 1;
        }

        /* ===============================
         Signature Section
      ================================= */
        .signature-section {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .signature-item {
            text-align: center;
            margin: 10px 5px;
            flex: 1;
            min-width: 120px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin-bottom: 5px;
            position: relative;
        }

        .signature-item span {
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="left-section">
                <div class="batch-info">
                    <table class="batch-table">
                        <tr>
                            <th rowspan="9" class="upsidedown-text">Body Weight (gm)</th>
                        </tr>
                        <tr>
                            <th rowspan="2">Batch No</th>
                            <th rowspan="2">Age (wks)</th>
                            <th colspan="3">Female</th>
                            <th colspan="3">Male</th>
                        </tr>
                        <tr>
                            <th>Act</th>
                            <th>Std</th>
                            <th>Uni (%)</th>
                            <th>Act</th>
                            <th>Std</th>
                            <th>Uni (%)</th>
                        </tr>
                        @foreach ($batchData as $batch)
                            <tr>
                                <td>{{ $batch['batch_no'] }}</td>
                                <td>{{ $batch['age'] }}</td>
                                <td>{{ $batch['female_act'] }}</td>
                                <td>{{ $batch['female_std'] }}</td>
                                <td>{{ $batch['female_uni'] }}</td>
                                <td>{{ $batch['male_act'] }}</td>
                                <td>{{ $batch['male_std'] }}</td>
                                <td>{{ $batch['male_uni'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>

            <div class="center-logo">
                <div class="logo-placeholder">🐔</div>
                <h1>Provita Chicks Limited-01</h1>
                <h2>Jahazmara, Noakhali.</h2>
                <h3>Daily Flock Report</h3>
            </div>

            <div class="right-section">
                <div class="grade-temp">
                    <span>Outside Tem (°C)</span>
                    <table class="temp-table">
                        <tr>
                            <th>Max</th>
                            <th>Min</th>
                        </tr>
                        <tr>
                            <td>{{ $outsideTemp['max'] }}</td>
                            <td>{{ $outsideTemp['min'] }}</td>
                        </tr>
                    </table>
                </div>
                <div class="grade-temp">
                    <span>Inside Tem (°C)</span>
                    <table class="grade-table">
                        <tr>
                            <th>Max</th>
                            <th>Min</th>
                        </tr>
                        <tr>
                            <td>{{ $insideTemp['max'] }}</td>
                            <td>{{ $insideTemp['min'] }}</td>
                        </tr>
                    </table>
                </div>
                <div class="humidity">
                    <span>Humidity %</span>
                    <table class="humidity-table">
                        <tr>
                            <th>Max</th>
                            <th>Min</th>
                        </tr>
                        <tr>
                            <td>{{ $humidity['max'] }}</td>
                            <td>{{ $humidity['min'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Main Flock Data Section -->
        <div class="main-flock-section">
            <table class="main-flock-table">
                <thead>
                    <tr class="no-border">
                        <th colspan="2">Breed</th>
                        <th colspan="3">IR</th>
                        <th>Mail Flock No</th>
                        <th colspan="2">39</th>
                        <th colspan="5">Shed No:01</th>
                        <th>Date</th>
                        <th>{{ $date }}</th>
                    </tr>
                    <tr>
                        <th rowspan="3" class="upsidedown-text">Batch</th>
                        <th rowspan="3">Age <br />(Wk)</th>
                        <th colspan="2" rowspan="2">Opening Birds No.</th>
                        <th colspan="4">Mortality</th>
                        <th colspan="4">Sold/Cull</th>
                        <th colspan="2">Closing <br />Birds No.</th>
                        <th colspan="3">Production Egg</th>
                        <th colspan="3">Hatching Egg</th>
                        <th colspan="2">Egg Wt.<br />(gm)</th>
                        <th colspan="4">Feed Consumption</th>
                        <th rowspan="2" class="upsidedown-text">Light (hrs)</th>
                        <th rowspan="2" class="upsidedown-text">
                            Water<br />Intake (Lit)
                        </th>
                        <th colspan="2">FFT</th>
                        <th rowspan="3">Type of Feed</th>
                    </tr>
                    <tr>
                        <th>Daily<br />(F)</th>
                        <th>Cum<br />(F)</th>
                        <th>Daily<br />(M)</th>
                        <th>Cum<br />(M)</th>
                        <th>Daily<br />(F)</th>
                        <th>Cum<br />(F)</th>
                        <th>Daily<br />(M)</th>
                        <th>Cum<br />(M)</th>
                        <th rowspan="2">Female</th>
                        <th rowspan="2">Male</th>
                        <th>Qty</th>
                        <th rowspan="2">Act<br />%</th>
                        <th rowspan="2">Std<br />%</th>
                        <th>Qty</th>
                        <th rowspan="2">Act<br />%</th>
                        <th rowspan="2">Std<br />%</th>
                        <th rowspan="2">Act</th>
                        <th rowspan="2">Std</th>
                        <th rowspan="2">F. gm<br />F</th>
                        <th rowspan="2">F. gm<br />M</th>
                        <th rowspan="2">F. kg<br />F</th>
                        <th rowspan="2">F. kg<br />M</th>
                        <th rowspan="2">F</th>
                        <th rowspan="2">M</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flockData as $flock)
                        <tr>
                            <td>{{ $flock['batch'] }}</td>
                            <td>{{ $flock['age'] }}</td>
                            <td>{{ $flock['opening_female'] }}</td>
                            <td>{{ $flock['opening_male'] }}</td>
                            <td>{{ $flock['mortality_female'] }}</td>
                            <td>{{ $flock['mortality_male'] }}</td>
                            <td>{{ $flock['sold_female'] }}</td>
                            <td>{{ $flock['sold_male'] }}</td>
                            <td>{{ $flock['closing_female'] }}</td>
                            <td>{{ $flock['closing_male'] }}</td>
                            <td>{{ $flock['egg_qty'] }}</td>
                            <td>{{ $flock['egg_female_percent'] }}</td>
                            <td>{{ $flock['egg_male_percent'] }}</td>
                            <td>{{ $flock['feed_qty'] }}</td>
                            <td>{{ $flock['feed_female_percent'] }}</td>
                            <td>{{ $flock['feed_male_percent'] }}</td>
                            <td>{{ $flock['feed_female_gm'] }}</td>
                            <td>{{ $flock['feed_male_gm'] }}</td>
                            <td>{{ $flock['feed_female_kg'] }}</td>
                            <td>{{ $flock['feed_male_kg'] }}</td>
                            <td>{{ $flock['feed_type'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Prepared By</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Security Officer</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Store Incharge</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Admin</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Shed Incharge</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>Project Incharge</span>
                </div>
                <div class="signature-item">
                    <div class="signature-line"></div>
                    <span>General Manager</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
