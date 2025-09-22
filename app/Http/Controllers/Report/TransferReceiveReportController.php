<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Country;
use Barryvdh\DomPDF\Facade\Pdf;

class TransferReceiveReportController extends Controller
{
    public function generateReportPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Load BirdTransfer with relationships for both transfer and receive data
        $item = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
            'batchAssign.batch',
            'firmReceive.psReceive', // Load PsFirmReceive and its related PsReceive
            'firmReceive.psReceive.company', // Load company from PsReceive model
            'firmReceive.psReceive.country', // Load country from PsReceive model
        ])->findOrFail($id);

        // Access the associated PsFirmReceive through the firmReceive relationship
        $firmReceive = $item->firmReceive;

        // Ensure PsFirmReceive is loaded properly
        if (!$firmReceive) {
            abort(404, 'Firm receive data not found.');
        }

        // Get breed type names
        $breeds = BreedType::pluck('name', 'id')->toArray();
        $breedtype = $item->breed_type ?? [];
        if (!is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype];
        }
        $breedAll = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);

        // Get companies and country names
        $fromCompany = Company::find($item->from_company_id);
        $toCompany = Company::find($item->to_company_id);
        $country = Country::find($item->country_of_origin);
        $countryName = $country ? $country->name : 'N/A';

        // Prepare batch info for transfer and receive
        $batchAssign = $item->batchAssign;
        $batches = [
            [
                'batch_no' => $batchAssign?->batch?->name ?? 'N/A',
                'challan_female' => $item->transfer_female_qty,
                'challan_male' => $item->transfer_male_qty,
                'challan_total' => $item->transfer_total_qty,
                'physical_female' => $firmReceive->firm_female_qty ?? 0,
                'physical_male' => $firmReceive->firm_male_qty ?? 0,
                'total' => $firmReceive->firm_total_qty ?? 0,
                'medical_female' => $firmReceive->medical_female_qty ?? 0,
                'medical_male' => $firmReceive->medical_male_qty ?? 0,
                'deviation_female' => $firmReceive->firm_female_qty - $item->transfer_female_qty,
                'deviation_male' => $firmReceive->firm_male_qty - $item->transfer_male_qty,
                'deviation_total' => $firmReceive->firm_total_qty - $item->transfer_total_qty,
                'from_company_name' => $fromCompany?->name ?? 'N/A',
                'to_company_name' => $toCompany?->name ?? 'N/A',
                'country_of_origin' => $countryName,
                'remarks' => $firmReceive->remarks ?? 'N/A',
                // Ensure these fields are set correctly
                'received_female' => $firmReceive->firm_female_qty ?? 0,
                'received_male' => $firmReceive->firm_male_qty ?? 0,
                'mortality_female' => $firmReceive->mortality_female_qty ?? 0,
                'mortality_male' => $firmReceive->mortality_male_qty ?? 0,
                'total_received_female' => $firmReceive->firm_female_qty + $firmReceive->mortality_female_qty ?? 0,
                'total_received_male' => $firmReceive->firm_male_qty + $firmReceive->mortality_male_qty ?? 0,
                'actual_deviation_female' => $firmReceive->firm_female_qty - $item->transfer_female_qty,
                'actual_deviation_male' => $firmReceive->firm_male_qty - $item->transfer_male_qty,
                'challan_deviation_female' => $item->transfer_female_qty - $firmReceive->firm_female_qty,
                'challan_deviation_male' => $item->transfer_male_qty - $firmReceive->firm_male_qty,
                'delivery_date' => optional($firmReceive->psReceive->created_at)->format('Y-m-d'),
                'breed_type' => $breedName,
            ],
        ];

        // Calculate totals
        $totals = [
            'challan_female' => 0,
            'challan_male' => 0,
            'erp_female' => 0,
            'erp_male' => 0,
            'medical_female' => 0,
            'medical_male' => 0,
            'deviation_female' => 0,
            'deviation_male' => 0,
            'received_female' => 0,
            'received_male' => 0,
            'mortality_female' => 0,
            'mortality_male' => 0,
            'total_received_female' => 0,
            'total_received_male' => 0,
            'actual_deviation_female' => 0,
            'actual_deviation_male' => 0,
            'challan_deviation_female' => 0,
            'challan_deviation_male' => 0,
        ];

        foreach ($batches as $batch) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $batch[$key] ?? 0;
            }
        }

        // Prepare data for the Blade template
        $data = [
            'id' => $item->id,
            'job_no' => $item->job_no,
            'transaction_no' => $item->transaction_no,
            'flock_name' => $item->flock->name ?? '-',
            'from_company' => $fromCompany?->name ?? '-',
            'to_company' => $toCompany?->name ?? '-',
            'firm_female_qty' => $item->transfer_female_qty,
            'firm_male_qty' => $item->transfer_male_qty,
            'firm_total_qty' => $item->transfer_total_qty,
            'remarks' => $firmReceive->remarks ?? '-',
            'receive_date' => optional($item->transfer_date)->format('Y-m-d'),
            'delivery_date' => optional($firmReceive->psReceive->created_at)->format('Y-m-d'),
            'invoice_numbers' => $firmReceive->psReceive?->order_no ?? 'N/A',
            'batches' => $batches,
            'totals' => $totals,  // Add totals to the data
            'country_of_origin' => $countryName,
            'generatedAt' => now(),
        ];

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // Generate the PDF using the Blade view
        $pdf = Pdf::loadView('reports.transfer-receive-report.transfer-receive-report', $data)
            ->setPaper('a4', 'landscape');

        // Return the PDF response (download or stream)
        return request()->query('download')
            ? $pdf->download("transfer-receive-report-{$item->id}.pdf")
            : $pdf->stream("transfer-receive-report-{$item->id}.pdf");
    }
}
