<?php

namespace App\Http\Controllers\Report;

use Inertia\Inertia;
use App\Models\Country;
use App\Models\Master\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Master\BreedType;
use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use \Carbon\Carbon;


class TransferReceiveReportController extends Controller
{

    public function index(Request $request)
    {
        $data = $this->buildReportDataFromFilters($request);

        return Inertia::render('report/bird-transfer-receive-report', $data);
    }

    // 🔧 This fixes the error you're seeing
    public function show(string $id)
    {
        // Reuse the same page, passing the {id} from the URL
        return $this->renderPage($id);
    }

    /** Shared renderer so index/show behave the same */
    private function renderPage($id = null)
    {
        $fromCompanyName = '-';
        $toCompanyName   = '-';
        $batches         = [];
        $totals          = [
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

        if ($id) {
            $item = BirdTransfer::with([
                'flock',
                'fromCompany',
                'toCompany',
                'fromShed',
                'toShed',
                'batchAssign.batch',
                'firmReceive.psReceive',
                'firmReceive.psReceive.company',
                'firmReceive.psReceive.country',
            ])->find($id);

            if ($item && $item->firmReceive) {
                $firmReceive = $item->firmReceive;

                $breeds    = BreedType::pluck('name', 'id')->toArray();
                $breedtype = $item->breed_type ?? [];
                if (!is_array($breedtype)) $breedtype = is_null($breedtype) ? [] : [$breedtype];
                $breedName = implode(', ', array_filter(array_map(fn($bid) => $breeds[$bid] ?? null, $breedtype)));

                $fromCompany     = Company::find($item->from_company_id);
                $toCompany       = Company::find($item->to_company_id);
                $countryName     = optional(\App\Models\Country::find($item->country_of_origin))->name ?? 'N/A';
                $fromCompanyName = $fromCompany?->name ?? '-';
                $toCompanyName   = $toCompany?->name ?? '-';

                $batchAssign = $item->batchAssign;

                $row = [
                    'delivery_date'            => optional($firmReceive->psReceive->created_at)->format('Y-m-d'),
                    'breed_type'               => $breedName,
                    'batch_no'                 => $batchAssign?->batch?->name ?? 'N/A',

                    // Transfer Farm
                    'register_female'          => null,
                    'register_male'            => null,
                    'erp_female'               => 0,
                    'erp_male'                 => 0,
                    'challan_female'           => $item->transfer_female_qty ?? 0,
                    'challan_male'             => $item->transfer_male_qty ?? 0,
                    'medical_female'           => $firmReceive->medical_female_qty ?? 0,
                    'medical_male'             => $firmReceive->medical_male_qty ?? 0,
                    'deviation_female'         => ($firmReceive->firm_female_qty ?? 0) - ($item->transfer_female_qty ?? 0),
                    'deviation_male'           => ($firmReceive->firm_male_qty ?? 0) - ($item->transfer_male_qty ?? 0),

                    // Receive Farm
                    'received_female'          => $firmReceive->firm_female_qty ?? 0,
                    'received_male'            => $firmReceive->firm_male_qty ?? 0,
                    'mortality_female'         => $firmReceive->mortality_female_qty ?? 0,
                    'mortality_male'           => $firmReceive->mortality_male_qty ?? 0,
                    'total_received_female'    => ($firmReceive->firm_female_qty ?? 0) + ($firmReceive->mortality_female_qty ?? 0),
                    'total_received_male'      => ($firmReceive->firm_male_qty ?? 0) + ($firmReceive->mortality_male_qty ?? 0),

                    // Deviations
                    'actual_deviation_female'  => ($firmReceive->firm_female_qty ?? 0) - ($item->transfer_female_qty ?? 0),
                    'actual_deviation_male'    => ($firmReceive->firm_male_qty ?? 0) - ($item->transfer_male_qty ?? 0),
                    'challan_deviation_female' => ($item->transfer_female_qty ?? 0) - ($firmReceive->firm_female_qty ?? 0),
                    'challan_deviation_male'   => ($item->transfer_male_qty ?? 0) - ($firmReceive->firm_male_qty ?? 0),
                ];

                $batches = [$row];

                foreach ($batches as $b) {
                    foreach ($totals as $key => $val) {
                        $totals[$key] += (float) ($b[$key] ?? 0);
                    }
                }
            }
        }

        return Inertia::render('report/bird-transfer-receive-report', [
            'from_company' => $fromCompanyName,
            'to_company'   => $toCompanyName,
            'batches'      => $batches,
            'totals'       => $totals,
        ]);
    }


    private function buildReportDataFromFilters(Request $request): array
    {
        $validated = $request->validate([
            'date_from'       => ['nullable', 'date'],
            'date_to'         => ['nullable', 'date', 'after_or_equal:date_from'],
            'from_company_id' => ['nullable', 'integer'],
            'to_company_id'   => ['nullable', 'integer'],
            'flock_id'        => ['nullable', 'integer'],
        ]);

        $dateFrom = $validated['date_from'] ?? Carbon::now()->subDays(30)->toDateString();
        $dateTo   = $validated['date_to']   ?? Carbon::now()->toDateString();

        $query = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
            'batchAssign.batch',
            'firmReceive.psReceive',
            'firmReceive.psReceive.company',
            'firmReceive.psReceive.country',
        ])->whereBetween('transfer_date', [$dateFrom, $dateTo]);

        if (!empty($validated['from_company_id'])) {
            $query->where('from_company_id', $validated['from_company_id']);
        }
        if (!empty($validated['to_company_id'])) {
            $query->where('to_company_id', $validated['to_company_id']);
        }
        if (!empty($validated['flock_id'])) {
            $query->where('flock_id', $validated['flock_id']);
        }

        $items = $query->orderBy('transfer_date')->get();
        $breedNamesById = BreedType::pluck('name', 'id')->toArray();

        $batches = [];
        $totals = [
            'challan_female'            => 0,
            'challan_male'              => 0,
            'erp_female'                => 0,
            'erp_male'                  => 0,
            'medical_female'            => 0,
            'medical_male'              => 0,
            'deviation_female'          => 0,
            'deviation_male'            => 0,
            'received_female'           => 0,
            'received_male'             => 0,
            'mortality_female'          => 0,
            'mortality_male'            => 0,
            'total_received_female'     => 0,
            'total_received_male'       => 0,
            'actual_deviation_female'   => 0,
            'actual_deviation_male'     => 0,
            'challan_deviation_female'  => 0,
            'challan_deviation_male'    => 0,
            'register_female'           => 0,
            'register_male'             => 0,
        ];

        $fromNames = [];
        $toNames   = [];

        foreach ($items as $item) {
            $firmReceive = $item->firmReceive;

            $breedtype = $item->breed_type ?? [];
            if (!is_array($breedtype)) $breedtype = is_null($breedtype) ? [] : [$breedtype];
            $breedName = implode(', ', array_filter(array_map(
                fn($bid) => $breedNamesById[$bid] ?? null,
                $breedtype
            )));

            $fromCompanyName = optional($item->fromCompany)->name ?? '-';
            $toCompanyName   = optional($item->toCompany)->name ?? '-';
            $fromNames[$fromCompanyName] = true;
            $toNames[$toCompanyName]     = true;

            $batchAssign = $item->batchAssign;

            $row = [
                'delivery_date'            => optional(optional($firmReceive)->psReceive?->created_at)->format('Y-m-d') ?? optional($item->transfer_date)->format('Y-m-d'),
                'breed_type'               => $breedName ?: 'N/A',
                'batch_no'                 => $batchAssign?->batch?->name ?? 'N/A',

                // Transfer
                'register_female'          => null,
                'register_male'            => null,
                'erp_female'               => 0,
                'erp_male'                 => 0,
                'challan_female'           => (int) ($item->transfer_female_qty ?? 0),
                'challan_male'             => (int) ($item->transfer_male_qty ?? 0),
                'medical_female'           => (int) ($firmReceive->medical_female_qty ?? 0),
                'medical_male'             => (int) ($firmReceive->medical_male_qty ?? 0),
                'deviation_female'         => (int) (($firmReceive->firm_female_qty ?? 0) - ($item->transfer_female_qty ?? 0)),
                'deviation_male'           => (int) (($firmReceive->firm_male_qty ?? 0) - ($item->transfer_male_qty ?? 0)),

                // Receive
                'received_female'          => (int) ($firmReceive->firm_female_qty ?? 0),
                'received_male'            => (int) ($firmReceive->firm_male_qty ?? 0),
                'mortality_female'         => (int) ($firmReceive->mortality_female_qty ?? 0),
                'mortality_male'           => (int) ($firmReceive->mortality_male_qty ?? 0),
                'total_received_female'    => (int) (($firmReceive->firm_female_qty ?? 0) + ($firmReceive->mortality_female_qty ?? 0)),
                'total_received_male'      => (int) (($firmReceive->firm_male_qty ?? 0) + ($firmReceive->mortality_male_qty ?? 0)),

                // Deviations summary
                'actual_deviation_female'  => (int) (($firmReceive->firm_female_qty ?? 0) - ($item->transfer_female_qty ?? 0)),
                'actual_deviation_male'    => (int) (($firmReceive->firm_male_qty ?? 0) - ($item->transfer_male_qty ?? 0)),
                'challan_deviation_female' => (int) (($item->transfer_female_qty ?? 0) - ($firmReceive->firm_female_qty ?? 0)),
                'challan_deviation_male'   => (int) (($item->transfer_male_qty ?? 0) - ($firmReceive->firm_male_qty ?? 0)),
            ];

            $batches[] = $row;

            foreach ($totals as $key => $acc) {
                $totals[$key] += (float) ($row[$key] ?? 0);
            }
        }

        $from_company = count($fromNames) === 1 ? array_key_first($fromNames) : (count($fromNames) ? 'Multiple' : '-');
        $to_company   = count($toNames)   === 1 ? array_key_first($toNames)   : (count($toNames) ? 'Multiple' : '-');

        return [
            'from_company' => $from_company,
            'to_company'   => $to_company,
            'batches'      => $batches,
            'totals'       => $totals,
            'filters'      => [
                'date_from'       => $dateFrom,
                'date_to'         => $dateTo,
                'from_company_id' => $validated['from_company_id'] ?? null,
                'to_company_id'   => $validated['to_company_id'] ?? null,
                'flock_id'        => $validated['flock_id'] ?? null,
            ],
        ];
    }
    public function generateRangePdf(Request $request)
    {
        // same data as the UI table
        $data = $this->buildReportDataFromFilters($request);

        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView(
            'reports.transfer-receive-report.transfer-receive-report',
            $data
        )->setPaper('a4', 'landscape');

        // stream by default, allow ?download=1 to force download
        return $request->boolean('download')
            ? $pdf->download('transfer-receive-report.pdf')
            : $pdf->stream('transfer-receive-report.pdf');
    }
    public function exportRangeExcel(Request $request)
    {
        $data = $this->buildReportDataFromFilters($request);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transfer-receive-report.csv"',
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $columns = [
            'Delivery Date',
            'Strain',
            'Batch',
            // Transfer (10)
            'Register Female',
            'Register Male',
            'ERP Female',
            'ERP Male',
            'Challan Female',
            'Challan Male',
            'Medical Female',
            'Medical Male',
            'Deviation Female',
            'Deviation Male',
            // Receive (6)
            'Received Female',
            'Received Male',
            'Mortality Female',
            'Mortality Male',
            'Total Received Female',
            'Total Received Male',
            // Deviations (4)
            'Actual Dev Female',
            'Actual Dev Male',
            'Challan Dev Female',
            'Challan Dev Male',
        ];

        $callback = function () use ($data, $columns) {
            $out = fopen('php://output', 'w');

            // Optional: UTF-8 BOM for Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Provita Group – Birds Transfer & Receive Report']);
            fputcsv($out, ["From: {$data['from_company']}  To: {$data['to_company']}"]);
            fputcsv($out, ["Date Range: {$data['filters']['date_from']} to {$data['filters']['date_to']}"]);
            fputcsv($out, []); // empty line

            fputcsv($out, $columns);

            foreach ($data['batches'] as $b) {
                fputcsv($out, [
                    $b['delivery_date'] ?? 'N/A',
                    $b['breed_type'] ?? 'N/A',
                    $b['batch_no'] ?? 'N/A',
                    $b['register_female'] ?? '',
                    $b['register_male'] ?? '',
                    $b['erp_female'] ?? 0,
                    $b['erp_male'] ?? 0,
                    $b['challan_female'] ?? 0,
                    $b['challan_male'] ?? 0,
                    $b['medical_female'] ?? 0,
                    $b['medical_male'] ?? 0,
                    $b['deviation_female'] ?? 0,
                    $b['deviation_male'] ?? 0,
                    $b['received_female'] ?? 0,
                    $b['received_male'] ?? 0,
                    $b['mortality_female'] ?? 0,
                    $b['mortality_male'] ?? 0,
                    $b['total_received_female'] ?? 0,
                    $b['total_received_male'] ?? 0,
                    $b['actual_deviation_female'] ?? 0,
                    $b['actual_deviation_male'] ?? 0,
                    $b['challan_deviation_female'] ?? 0,
                    $b['challan_deviation_male'] ?? 0,
                ]);
            }

            // Totals row
            $t = $data['totals'];
            fputcsv($out, []); // space
            fputcsv($out, array_merge(
                ['Total', '', ''], // first 3 cells merged conceptually
                [
                    $t['register_female'] ?? '',
                    $t['register_male'] ?? '',
                    $t['erp_female'] ?? 0,
                    $t['erp_male'] ?? 0,
                    $t['challan_female'] ?? 0,
                    $t['challan_male'] ?? 0,
                    $t['medical_female'] ?? 0,
                    $t['medical_male'] ?? 0,
                    $t['deviation_female'] ?? 0,
                    $t['deviation_male'] ?? 0,
                    $t['received_female'] ?? 0,
                    $t['received_male'] ?? 0,
                    $t['mortality_female'] ?? 0,
                    $t['mortality_male'] ?? 0,
                    $t['total_received_female'] ?? 0,
                    $t['total_received_male'] ?? 0,
                    $t['actual_deviation_female'] ?? 0,
                    $t['actual_deviation_male'] ?? 0,
                    $t['challan_deviation_female'] ?? 0,
                    $t['challan_deviation_male'] ?? 0,
                ]
            ));

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
