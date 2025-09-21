<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://public-frontend-cos.metadl.com/mgx/img/favicon.png" type="image/png">
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
         Depletion Rate
      ================================= */
        .depletion-rate {
            margin-top: 5px;
            font-size: 10px;
            text-align: left;
        }

        .depletion-rate span {
            margin-right: 20px;
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

        .vaccine-icons {
            margin-bottom: 10px;
        }

        .vaccine-check {
            font-size: 14px;
            margin-right: 10px;
            color: green;
            transform: rotate(-5deg);
            font-weight: bold;
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
         Notes Section
      ================================= */
        .note-section {
            margin-top: 10px;
        }

        .note-section h4 {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .note-box {
            border: 1px solid #000;
            height: 50px;
            width: 100%;
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

        /* ===============================
         Utilities
      ================================= */
        .border {
            border: 1px solid #000;
        }

        .upsidedown-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            vertical-align: middle;
        }

        /* ===============================
         Special/Error States
      ================================= */
        .total-row td:contains("####"),
        .total-row td:contains("#DIV/0!") {
            background-color: #ffe6e6;
            color: red;
            font-size: 7px;
        }

        /* ===============================
         Responsive Design
      ================================= */
        @media print {
            body {
                font-size: 8px;
            }

            .form-container {
                padding: 5px;
            }

            .header-section {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
            }

            .left-section,
            .center-logo,
            .right-section {
                width: 100%;
                margin-bottom: 10px;
            }

            .signature-row {
                flex-direction: column;
            }

            .signature-item {
                margin: 5px 0;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <!-- Content from your original HTML goes here -->
    </div>
</body>

</html>
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
         Depletion Rate
      ================================= */
        .depletion-rate {
            margin-top: 5px;
            font-size: 10px;
            text-align: left;
        }

        .depletion-rate span {
            margin-right: 20px;
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

        .vaccine-icons {
            margin-bottom: 10px;
        }

        .vaccine-check {
            font-size: 14px;
            margin-right: 10px;
            color: green;
            transform: rotate(-5deg);
            font-weight: bold;
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
         Notes Section
      ================================= */
        .note-section {
            margin-top: 10px;
        }

        .note-section h4 {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .note-box {
            border: 1px solid #000;
            height: 50px;
            width: 100%;
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

        /* ===============================
         Utilities
      ================================= */
        .border {
            border: 1px solid #000;
        }

        .upsidedown-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            vertical-align: middle;
        }

        /* ===============================
         Special/Error States
      ================================= */
        .total-row td:contains("####"),
        .total-row td:contains("#DIV/0!") {
            background-color: #ffe6e6;
            color: red;
            font-size: 7px;
        }

        /* ===============================
         Responsive Design
      ================================= */
        @media print {
            body {
                font-size: 8px;
            }

            .form-container {
                padding: 5px;
            }

            .header-section {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
            }

            .left-section,
            .center-logo,
            .right-section {
                width: 100%;
                margin-bottom: 10px;
            }

            .signature-row {
                flex-direction: column;
            }

            .signature-item {
                margin: 5px 0;
            }
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
                        <!-- Batch Table with dynamic data -->
                        <tr>
                            <th rowspan="9">Body Weight (gm)</th>
                        </tr>
                        <tr>
                            <th>Batch No</th>
                            <th>Age (wks)</th>
                            <th colspan="3">Female</th>
                            <th colspan="3">Male</th>
                        </tr>
                        @foreach ($dailyOperation->feeds as $feed)
                            <tr>
                                <td>{{ $feed->feedType->name }}</td>
                                <td>{{ $feed->quantity }}</td>
                                <!-- Insert more dynamic rows as per your data -->
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
                <!-- Temperature and Humidity tables -->
                <div class="grade-temp">
                    <span>Outside Temp (°C)</span>
                    <table class="temp-table">
                        <tr>
                            <th>Max</th>
                            <th>Min</th>
                        </tr>
                        @foreach ($dailyOperation->temperatures as $temperature)
                            <tr>
                                <td>{{ $temperature->outside_temp }}</td>
                                <td>{{ $temperature->std_outside_temp }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="grade-temp">
                        <span>Inside Temp (°C)</span>
                        <table class="grade-table">
                            <tr>
                                <th>Max</th>
                                <th>Min</th>
                            </tr>
                            @foreach ($dailyOperation->temperatures as $temperature)
                                <tr>
                                    <td>{{ $temperature->inside_temp }}</td>
                                    <td>{{ $temperature->std_inside_temp }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="humidity">
                        <span>Humidity %</span>
                        <table class="humidity-table">
                            <tr>
                                <th>Max</th>
                                <th>Min</th>
                            </tr>
                            @foreach ($dailyOperation->humidities as $humidity)
                                <tr>
                                    <td>{{ $humidity->today_humidity }}</td>
                                    <td>{{ $humidity->std_humidity }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Flock Data Section -->
        <div class="main-flock-section">
            <table class="main-flock-table">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Age (Wk)</th>
                        <th>Opening Birds No.</th>
                        <th>Mortality</th>
                        <!-- More columns as per your structure -->
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dailyOperation->mortalities as $mortality)
                        <tr>
                            <td>{{ $mortality->dailyOperation->batch_no }}</td>
                            <td>{{ $mortality->dailyOperation->age }}</td>
                            <td>{{ $mortality->female_qty }}</td>
                            <td>{{ $mortality->male_qty }}</td>
                            <!-- Populate other fields dynamically -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Medicine & Vaccine Section -->
        <div class="medicine-section">
            <h4>Medicine & Vaccine</h4>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dailyOperation->medicines as $medicine)
                        <tr>
                            <td>{{ $medicine->dailyOperation->batch_no }}</td>
                            <td>{{ $medicine->medicine->name }}</td>
                            <td>{{ $medicine->quantity }}</td>
                            <td>{{ $medicine->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Additional sections like Mortality, Reject Eggs, etc. -->
    </div>

    <script type="module" src="./script.js"></script>
</body>

</html>
