<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Master\Flock;

use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyEggCollection;
use App\Models\Shed\BatchAssign;
use Illuminate\Support\Facades\DB;


use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
       // --- Filter options
        $filterOptions = [
            'company' => ['pcl', 'phl', 'pbl'],
            'project' => ['phl1', 'phl2'],
            'flock' => ['12', '13'],
            'shed' => ['1', '2', '3'],
            'batch' => ['A', 'B'],
            'date' => ['Last 7 Days', 'Last 1 Month', 'Custom'],
        ];

        // --- Get selected filters, default to empty string
        $filters = array_merge([
            'company' => '',
            'project' => '',
            'flock' => '',
            'shed' => '',
            'batch' => '',
            'date' => '',
            'date_from' => null,
            'date_to' => null,
        ], $request->only(['company','project','flock','shed','batch','date','date_from','date_to']));

        // --- Determine multiplier based on selected filters
        $multiplier = 1;
        if ($filters['company'] === 'pcl') $multiplier = 1;
        elseif ($filters['company'] === 'phl') $multiplier = 2;
        elseif ($filters['company'] === 'pbl') $multiplier = 3;

        // Increase multiplier if project or flock selected
        if ($filters['project'] !== '') $multiplier += 1;
        if ($filters['flock'] !== '') $multiplier += 1;

        // --- Dummy cards (scaled by multiplier)
        $cards = [
            [
                'title' => 'Total Flock',
                'value' => Flock::where('status', '1')->count(),
                'icon'  => 'User',
            ],
            [
                'title' => 'Total Chicks',
                'value' => BatchAssign::where('status','1')->sum('batch_total_qty'),
                'icon'  => 'Drumstick',
            ],
            [
                'title' => 'Female Chicks Qty',
                'value' => BatchAssign::where('status','1')->sum('batch_female_qty'),
                'icon'  => 'Drumstick',
            ],
            [
                'title' => 'Male Chicks Qty',
                'value' => BatchAssign::where('status','1')->sum('batch_male_qty'),
                'icon'  => 'Drumstick',
            ],
            [
                'title' => 'Total Mortality',
                'value' => DailyMortality::query()
                ->join('daily_operations', 'daily_operations.id', '=', 'daily_mortalities.daily_operation_id')
                ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
                ->where('batch_assigns.status', 1) // only active batches
                ->sum(DB::raw('daily_mortalities.female_qty + daily_mortalities.male_qty')),
                'icon'  => 'ShieldX',
            ],
            [
                'title' => 'Total Egg Collection', 
                'value' => DailyEggCollection::query()
                ->join('daily_operations', 'daily_operations.id', '=', 'daily_egg_collections.daily_operation_id')
                ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
                ->where('batch_assigns.status', 1) // only active batches
                ->sum('daily_egg_collections.quantity'),
                'icon' => 'Egg'
            ],
            [
                'title' => 'Sent for Lab From PS', 
                'value' => rand(10, 50) * $multiplier, 
                'icon' => 'FlaskConical'
            ],
            ['title' => 'Sent for Lab From Batch', 'value' => rand(10, 50) * $multiplier, 'icon' => 'FlaskConical'],
            ['title' => 'Total Male Chicks', 'value' => rand(100, 300) * $multiplier, 'icon' => 'BabyChick'],
            ['title' => 'Total Female Chicks', 'value' => rand(100, 300) * $multiplier, 'icon' => 'FlaskConical'],
            ['title' => 'Total Hatching Egg', 'value' => rand(500, 2000) * $multiplier, 'icon' => 'PackageSearch'],
            ['title' => 'Total Commercial Egg', 'value' => rand(500, 2000) * $multiplier, 'icon' => 'PackageSearch'],
            ['title' => 'Total Feed Consumption', 'value' => rand(500, 1500) * $multiplier, 'icon' => 'Factory'],
            ['title' => 'Total Vaccination', 'value' => rand(20, 100) * $multiplier, 'icon' => 'Syringe'],
            [
                'title' => 'Total Active Batchs', 
                'value' => BatchAssign::where('status','1')->count(), 
                'icon' => 'Archive'],
        ];

        // --- Dummy progress bars
        $progressBars = [
            ['title' => 'Total Eggs', 'progress' => rand(10, 100), 'extra' => 'Goal: 1000 eggs'],
            ['title' => 'Hatchable Eggs', 'progress' => rand(10, 100), 'extra' => 'Goal: 1000 eggs'],
            ['title' => 'Commercial', 'progress' => rand(10, 100), 'extra' => 'Goal: 1000 eggs'],
        ];

        // --- Dummy circle bars
        $circleBars = [
            ['title' => 'Mortality', 'value' => rand(1, 50), 'type' => 'rounded'],
            ['title' => 'Culling', 'value' => rand(1, 50), 'type' => 'rounded'],
            ['title' => 'Male Chicks', 'value' => rand(1, 100), 'type' => 'rounded'],
            ['title' => 'Female Chicks', 'value' => rand(50, 100), 'type' => 'straight'],
            ['title' => 'Excess', 'value' => rand(1, 50), 'type' => 'straight'],
            ['title' => 'Worker', 'value' => rand(50, 100), 'type' => 'straight'],
        ];

        // --- Dummy bird stage
        $birdStage = [
            'bordingTotal' => rand(50, 200) * $multiplier,
            'growingTotal' => rand(50, 200) * $multiplier,
            'productionTotal' => rand(50, 300) * $multiplier,
        ];

        return Inertia::render('Dashboard', [
            'cards' => $cards,
            'progressBars' => $progressBars,
            'circleBars' => $circleBars,
            'birdStage' => $birdStage,
            'filterOptions' => $filterOptions,
            'filters' => $filters,
        ]);
    }
}
