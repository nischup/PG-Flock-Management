<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PS Receive Report - {{ $pi_no ?? 'N/A' }}</title>
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
            justify-content: space-between;
            align-items: center;
            padding-right: 22px;
            padding-left: 22px;
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

        <!-- Divider (yellow highlight) -->
        <hr style="border: 0; border-top: 2px solid #fbbf24; margin: 14px 0;">

        <!-- Report Title -->
        <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: #b45309;">
            📑 PS Receive Report
        </h2>
        <h3 style="margin: 4px 0; font-size: 16px; font-weight: normal; color: #78350f;">
            PI No: <span style="color: #d97706;">{{ $pi_no ?? 'N/A' }}</span>
        </h3>
        <p style="margin: 2px 0; font-size: 13px; color: #4a5568;">
            Generated at: <span style="color: #ca8a04;">{{ $generatedAt?->format('Y-m-d H:i') }}</span>
        </p>
    </div>

    {{-- General Info --}}
    <div class="section">
        <strong>General Info:</strong>
        <table>
            <tr>
                <th>Supplier</th>
                <td>{{ $supplier ?? 'N/A' }}</td>
                <th>Shipment Type</th>
                <td>{{ $shipment_type ?? 'N/A' }}</td>
            </tr>

            @if ($shipment_type == 'Foreign' || $shipment_type == 2)
                <tr>
                    <th>LC No</th>
                    <td>{{ $lc_no ?? 'N/A' }}</td>
                    <th>LC Date</th>
                    <td>{{ $lc_date ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Country of Origin</th>
                    <td colspan="3">{{ $country_of_origin ?? 'N/A' }}</td>
                </tr>
            @endif

            <tr>
                <th>Breed Types</th>
                <td>
                    @if (!empty($breed_types))
                        {{ implode(', ', $breed_types) }}
                    @else
                        N/A
                    @endif
                </td>
                <th>Order No</th>
                <td>{{ $order_no ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Order Date</th>
                <td>{{ $order_date ?? 'N/A' }}</td>
                <th>Ship To</th>
                <td>{{ $company->name ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Receive Date</th>
                <td>{{ $receive_date ?? 'N/A' }}</td>
                <th>Transport Type</th>
                <td>
                    @switch($transport_type)
                        @case(1)
                            Freezing Microbas
                        @break

                        @case(2)
                            Freezing Bus
                        @break

                        @case(3)
                            Open Truck
                        @break

                        @default
                            N/A
                    @endswitch
                </td>
            </tr>

            <tr>
                <th>Vehicle Temp (°C)</th>
                <td>{{ $vehicle_temp ?? 'N/A' }}°C</td>
                <th>Remarks</th>
                <td>{{ $remarks ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Chick Counts --}}
    @if (!empty($chick_counts))
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
    @endif

    {{-- Lab Transfers --}}
    @if (!empty($lab_transfers) && count($lab_transfers) > 0)
        <div class="section">
            <strong>Lab Transfers:</strong>
            <table>
                <tr>
                    <th>Lab Type</th>
                    <th>Send Male Qty</th>
                    <th>Send Female Qty</th>
                    <th>Send Total Qty</th>
                    <th>Receive Male Qty</th>
                    <th>Receive Female Qty</th>
                    <th>Receive Total Qty</th>
                    <th>Notes</th>
                </tr>
                @foreach ($lab_transfers as $lab)
                    <tr>
                        <td>{{ $lab->lab_type == 1 ? 'Customs' : 'Bio Research Lab' }}</td>
                        <td>{{ $lab->lab_send_male_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_send_female_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_send_total_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_receive_male_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_receive_female_qty ?? 0 }}</td>
                        <td>{{ $lab->lab_receive_total_qty ?? 0 }}</td>
                        <td>{{ $lab->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Attachments --}}
    @if (!empty($attachments) && count($attachments) > 0)
        <div class="section">
            <strong>Attachments:</strong>
            <table>
                <tr>
                    <th>#</th>
                    <th>File Type</th>
                    <th>Path</th>
                </tr>
                @foreach ($attachments as $i => $file)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ strtoupper($file->file_type ?? '-') }}</td>
                        <td>{{ $file->file_path ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Signature Matrix --}}
    {{-- <div class="section" style="margin-top: 40px; page-break-inside: avoid;">
        <strong>Approval & Verification:</strong>
        <table style="margin-top: 10px;">
            <tr>
                <th style="width: 25%; text-align: center; background-color: #f8f9fa; font-weight: bold;">Created By</th>
                <th style="width: 25%; text-align: center; background-color: #f8f9fa; font-weight: bold;">Checked By</th>
                <th style="width: 25%; text-align: center; background-color: #f8f9fa; font-weight: bold;">Audited By</th>
                <th style="width: 25%; text-align: center; background-color: #f8f9fa; font-weight: bold;">GM</th>
            </tr>
            <tr>
                <td style="height: 80px; vertical-align: top; padding: 10px;">
                    <div style="height: 50px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    <div style="text-align: center; font-size: 10px; color: #666;">
                        Name & Signature
                    </div>
                </td>
                <td style="height: 80px; vertical-align: top; padding: 10px;">
                    <div style="height: 50px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    <div style="text-align: center; font-size: 10px; color: #666;">
                        Name & Signature
                    </div>
                </td>
                <td style="height: 80px; vertical-align: top; padding: 10px;">
                    <div style="height: 50px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    <div style="text-align: center; font-size: 10px; color: #666;">
                        Name & Signature
                    </div>
                </td>
                <td style="height: 80px; vertical-align: top; padding: 10px;">
                    <div style="height: 50px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    <div style="text-align: center; font-size: 10px; color: #666;">
                        Name & Signature
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 8px; background-color: #f8f9fa; font-size: 10px; color: #555;">
                    Date: _______________
                </td>
                <td style="text-align: center; padding: 8px; background-color: #f8f9fa; font-size: 10px; color: #555;">
                    Date: _______________
                </td>
                <td style="text-align: center; padding: 8px; background-color: #f8f9fa; font-size: 10px; color: #555;">
                    Date: _______________
                </td>
                <td style="text-align: center; padding: 8px; background-color: #f8f9fa; font-size: 10px; color: #555;">
                    Date: _______________
                </td>
            </tr>
        </table>
    </div> --}}

    {{-- Report Footer --}}

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
    <div style="margin-top: 60px; text-align: center; font-size: 10px; color: #666;">
        <p>This is a system-generated report from Provita Flock Management System</p>
        <p>Confidential Document - For Internal Use Only</p>
    </div>

</body>

</html>
