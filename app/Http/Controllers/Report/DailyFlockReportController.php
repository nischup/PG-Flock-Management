<?php

namespace App\Http\Controllers\Report;

use App\Models\DailyOperation\DailyOperation;
use App\Models\DailyOperation\DailyCulling;
use App\Models\DailyOperation\DailyDestroy;
use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyFeed;
use App\Models\DailyOperation\DailyHumidity;
use App\Models\DailyOperation\DailyLight;
use App\Models\DailyOperation\DailyMedicine;
use App\Models\DailyOperation\DailyTemperature;
use App\Models\DailyOperation\DailyVaccine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DailyFlockReportController extends Controller
{
    public function show($id)
    {
        // Fetch the daily operation record for the given id
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
        ])->findOrFail($id);

        // Pass the data to the view
        return view('reports.daily-report.daily-report', [
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
            'vaccines' => $dailyOperation->vaccines
        ]);
    }
}
