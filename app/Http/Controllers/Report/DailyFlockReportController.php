<?php

namespace App\Http\Controllers\Report;

use App\Models\DailyOperation\DailyOperation;
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

class DailyFlockReportController extends Controller
{
    public function downloadPdf()
    {
        // Get today's date
        $today = Carbon::today(); // Get today's date

        // Fetch the daily operation record for today
        $dailyOperation = DailyOperation::with([
            'mortalities',
            'destroys',
            'cullings',
            'eggCollections',
            'feeds',
            'humidities',
            'lights',
            'medicines',
            'temperatures',
            'vaccines',
        ])
            ->whereDate('operation_date', $today) // Filter by today's date
            ->first(); // Fetch the first operation record for today (if exists)

        // If there's no operation for today, return a message or error
        if (!$dailyOperation) {
            return response()->json(['message' => 'No daily operation found for today'], 404);
        }

        // Prepare the data for the PDF
        $data = [
            'dailyOperation' => $dailyOperation,
            'mortalities' => $dailyOperation->mortalities,
            'destroys' => $dailyOperation->destroys,
            'cullings' => $dailyOperation->cullings,
            'eggCollections' => $dailyOperation->eggCollections,
            'feeds' => $dailyOperation->feeds,
            'humidities' => $dailyOperation->humidities,
            'lights' => $dailyOperation->lights,
            'medicines' => $dailyOperation->medicines,
            'temperatures' => $dailyOperation->temperatures,
            'vaccines' => $dailyOperation->vaccines,
            'outsideTemp' => $this->getTemperatureData('outside'),  // Example of getting outside temp data
            'insideTemp' => $this->getTemperatureData('inside'),    // Example of getting inside temp data
            'humidity' => $this->getHumidityData(),                  // Example of getting humidity data
            'batchData' => $this->getBatchData($dailyOperation),    // Data for batch info table
            'flockData' => $this->getFlockData($dailyOperation),    // Data for flock info table
            'generatedAt' => now(),  // Timestamp for when the report was generated
            'date' => $today->format('d-M-Y'),  // Date for the report
        ];

        // Set PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // Generate the PDF using the Blade view
        $pdf = Pdf::loadView('reports.daily-report.daily-report', $data)
            ->setPaper('a4', 'portrait');  // You can change to 'landscape' if needed

        // Return the PDF response (download or stream based on the 'download' query parameter)
        return request()->query('download')
            ? $pdf->download('daily_flock_report_' . $today->format('Y-m-d') . '.pdf')
            : $pdf->stream('daily_flock_report_' . $today->format('Y-m-d') . '.pdf');
    }

    // Helper method to get temperature data
    private function getTemperatureData($type)
    {
        // Example: return static data or fetch from the daily operation data
        if ($type == 'outside') {
            return ['max' => 30.0, 'min' => 22.0]; // Example values
        } elseif ($type == 'inside') {
            return ['max' => 31.0, 'min' => 28.0]; // Example values
        }
        return [];
    }

    // Helper method to get humidity data
    private function getHumidityData()
    {
        return ['max' => 80, 'min' => 60]; // Example values
    }

    // Helper method to get batch data
    private function getBatchData($dailyOperation)
    {
        // You can pull batch data from related models or use hardcoded values
        return [
            [
                'batch_no' => 'A',
                'age' => '36+2',
                'female_act' => 0,
                'female_std' => 0,
                'female_uni' => 0.0,
                'male_act' => 0,
                'male_std' => 0,
                'male_uni' => 0.0,
            ],
            [
                'batch_no' => 'B',
                'age' => '36+3',
                'female_act' => 0,
                'female_std' => 0,
                'female_uni' => 0.0,
                'male_act' => 0,
                'male_std' => 0,
                'male_uni' => 0.0,
            ],
            [
                'batch_no' => 'C',
                'age' => '30+7',
                'female_act' => 1185,
                'female_std' => 1200,
                'female_uni' => 66.0,
                'male_act' => 1847,
                'male_std' => 1780,
                'male_uni' => 0.0,
            ]
        ];
    }

    // Helper method to get flock data
    private function getFlockData($dailyOperation)
    {
        // Example: return static data or fetch from the daily operation model
        return [
            [
                'batch' => 'A',
                'age' => '36+2',
                'opening_female' => 10200,
                'opening_male' => 3600,
                'mortality_female' => 5,
                'mortality_male' => 2,
                'sold_female' => 35,
                'sold_male' => 3,
                'closing_female' => 10200,
                'closing_male' => 3600,
                'egg_qty' => 2500,
                'egg_female_percent' => 85.0,
                'egg_male_percent' => 86.0,
                'feed_qty' => 2300,
                'feed_female_percent' => 78.0,
                'feed_male_percent' => 80.0,
                'feed_female_gm' => 720,
                'feed_male_gm' => 115,
                'feed_female_kg' => 16,
                'feed_male_kg' => 1800,
                'feed_type' => 'IR Developer +IR Grower',
            ],
            // Repeat for other flocks (B, C, D, etc.)
        ];
    }


    public function index(Request $request)
    {
        //$data = $this->buildReportDataFromFilters($request);
        $data = [];
        return Inertia::render('report/daily-flock-report', $data);
    }



    private function buildReportDataFromFilters(Request $request): array
    {
        $validated = $request->validate([
            'date_from'       => ['nullable', 'date'],
            'date_to'         => ['nullable', 'date', 'after_or_equal:date_from'],
            'company_id' => ['nullable', 'integer'],
            'shed_id'   => ['nullable', 'integer'],
        ]);

        $dateFrom = $validated['date_from'] ?? Carbon::now()->subDays(30)->toDateString();
        $dateTo   = $validated['date_to']   ?? Carbon::now()->toDateString();

        // Eager load daily operation with relations
        $dailyoperation = DailyOperation::with([
            'batchAssign.flock',
            // Grouped relations
            'mortalities',
            'cullings',
            'destroys',       // add destroy
            'sexingErrors',
            'lights',
            'weights',
            'temperatures',
            'feedingPrograms',
            'feedFinishings',
            'humidities',
            'eggCollections',
            'medicines.medicine',
            'medicines.unit',
            'vaccines.vaccine',
            'vaccines.unit',
        ])->whereBetween('operation_date', [$dateFrom, $dateTo]);
            
        // Map breed_type IDs to names
        $breeds = BreedType::pluck('name', 'id')->toArray();
        $breedtype = $dailyoperation->breed_type ?? [1,2]; // <-- changed to dynamic
        if (! is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype];
        }

        $breedAll = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);

        // Prepare data for Blade view
        $data = [
            'job_no'        => $dailyoperation->batchAssign->job_no ?? '-',
            'transaction_no'=> $dailyoperation->batchAssign->transaction_no ?? '-',
            'flock_name'    => $dailyoperation->batchAssign->flock->name ?? '-',
            'flock_id'      => $dailyoperation->batchAssign->flock_id ?? '-',
            'status'        => $dailyoperation->status,
            'breedName'     => $breedName,
            'created_at'    => $dailyoperation->created_at->format('Y-m-d H:i:s'),
            'generatedAt'   => now(),
            'dailyoperation'=> $dailyoperation, // pass whole model with relations
        ];

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.daily-report.daily-report', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("daily-operation-{$dailyoperation->id}.pdf")
            : $pdf->stream("daily-operation-{$dailyoperation->id}.pdf");
    }


}
