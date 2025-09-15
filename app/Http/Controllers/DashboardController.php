<?php

namespace App\Http\Controllers;

use App\Models\DailyOperation\DailyCulling;
use Illuminate\Http\Request;

use App\Models\Master\Flock;

use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyFeed;
use App\Models\Shed\BatchAssign;
use App\Models\Ps\PsLabTest;
use App\Models\Production\EggClassification;
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
        
        $totalChicks = BatchAssign::where('status','1')->sum('batch_total_qty');


        $broodingChicks = BatchAssign::where('status','1')->where('stage','1')->sum('batch_total_qty');
        $growingChicks = BatchAssign::where('status','1')->where('stage','3')->sum('batch_total_qty');
        $layingChicks = BatchAssign::where('status','1')->where('stage','2')->sum('batch_total_qty');





        $maleChicks = BatchAssign::where('status','1')->sum('batch_male_qty');
        $femaleChicks = BatchAssign::where('status','1')->sum('batch_female_qty');
       
        $totalMortalitymale = DailyMortality::query()
        ->join('daily_operations', 'daily_operations.id', '=', 'daily_mortalities.daily_operation_id')
        ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
        ->where('batch_assigns.status', 1) // only active batches
        ->sum('daily_mortalities.male_qty');

        $totalMortalityfemale = DailyMortality::query()
        ->join('daily_operations', 'daily_operations.id', '=', 'daily_mortalities.daily_operation_id')
        ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
        ->where('batch_assigns.status', 1) // only active batches
        ->sum('daily_mortalities.female_qty');

        $totalCullingfemale = DailyCulling::query()
        ->join('daily_operations', 'daily_operations.id', '=', 'daily_cullings.daily_operation_id')
        ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
        ->where('batch_assigns.status', 1) // only active batches
        ->sum('daily_cullings.female_qty');

        $totalCullingMale = DailyCulling::query()
        ->join('daily_operations', 'daily_operations.id', '=', 'daily_cullings.daily_operation_id')
        ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
        ->where('batch_assigns.status', 1) // only active batches
        ->sum('daily_cullings.male_qty');

        $totalCulling = $totalCullingfemale+$totalCullingMale;
        
        $totaleggcollection = DailyEggCollection::query()
                ->join('daily_operations', 'daily_operations.id', '=', 'daily_egg_collections.daily_operation_id')
                ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
                ->where('batch_assigns.status', 1) // only active batches
                ->sum('daily_egg_collections.quantity');

        $currentchicks = $totalChicks-$totalMortalitymale-$totalMortalityfemale;

        $totalHatchableEggs = EggClassification::query()
                ->join('batch_assigns', 'batch_assigns.id', '=', 'egg_classifications.batchassign_id')
                ->where('batch_assigns.status', 1) 
                ->sum('egg_classifications.hatching_eggs');

        $totalCommercialEggs = EggClassification::query()
                ->join('batch_assigns', 'batch_assigns.id', '=', 'egg_classifications.batchassign_id')
                ->where('batch_assigns.status', 1) // only active batches

                ->sum('egg_classifications.commercial_eggs');


                
        $cards = [
            [
                'title' => 'Total Flock',
                'value' => Flock::where('status', '1')->count(),
                'icon'  => 'User',
            ],
            [
                'title' => 'Total Active Batchs', 
                'value' => BatchAssign::where('status','1')->count(), 
                'icon' => 'Archive'
            ],
            [
                'title' => 'Total Chicks',
                'value' => $totalChicks,
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
                'value' => $totalMortalitymale+$totalMortalityfemale,
                'icon'  => 'ShieldX',
            ],
            [
                'title' => 'Male Mortality',
                'value' => $totalMortalitymale,
                'icon'  => 'ShieldX',
            ],
            [
                'title' => 'Female Mortality',
                'value' => $totalMortalityfemale,
                'icon'  => 'ShieldX',
            ],
            [
                'title' => 'Current Chicks',
                'value' =>$currentchicks,
                'icon'  => 'ShieldX',
            ],
            [
                'title' => 'Total Egg Collection', 
                'value' => $totaleggcollection,
                'icon' => 'Egg'
            ],
            
            [
                'title' => 'Total Hatching Eggs', 
                'value' => $totalHatchableEggs,
                'icon' => 'PackageSearch'
            ],
            [
                'title' => 'Total Commercial Eggs', 
                'value' => $totalCommercialEggs, 
                'icon' => 'PackageSearch'
            ],
            //['title' => 'Total Commercial Egg', 'value' => rand(500, 2000) * $multiplier, 'icon' => 'PackageSearch'],
            ['title' => 'Total Feed Consumption', 
            
            'value' => DailyFeed::query()
                ->join('daily_operations', 'daily_operations.id', '=', 'daily_feeds.daily_operation_id')
                ->join('batch_assigns', 'batch_assigns.id', '=', 'daily_operations.batchassign_id')
                ->where('batch_assigns.status', 1) // only active batches
                ->sum('daily_feeds.quantity'),
            
            
            'icon' => 'Factory'],
            ['title' => 'Total Vaccination', 'value' => rand(20, 100) * $multiplier, 'icon' => 'Syringe'],
            [
                'title' => 'Sent for Lab From PS', 
                'value' => PsLabTest::where('status','1')->sum('lab_send_total_qty'), 
                'icon' => 'FlaskConical'
            ],
            ['title' => 'Sent for Lab From Batch', 'value' => 0, 'icon' => 'FlaskConical'],
            
        ];

        // Avoid division by zero
        $eggPercentage        = $currentchicks > 0 ? ($totaleggcollection / $currentchicks) * 100 : 0;
        $hatchablePercentage  = $totaleggcollection > 0 ? ($totalHatchableEggs / $totaleggcollection) * 100 : 0;
        $commercialPercentage = $totaleggcollection > 0 ? ($totalCommercialEggs / $totaleggcollection) * 100 : 0;


        // --- Dummy progress bars
        $progressBars = [
            [
                'title'    => 'Total Eggs',
                'progress' => number_format($eggPercentage, 2),
                'extra'    => "Goal: {$totaleggcollection} eggs"
            ],
            [
                'title'    => 'Hatchable Eggs',
                'progress' => number_format($hatchablePercentage, 2),
                'extra'    => "Goal: {$totalHatchableEggs} eggs"
            ],
            [
                'title'    => 'Commercial',
                'progress' => number_format($commercialPercentage, 2),
                'extra'    => "Goal: {$totalCommercialEggs} eggs"
            ],
        ];

        // --- Dummy circle bars
        // Avoid division by zero
        $denominator = $totalChicks > 0 ? $totalChicks : 1;
        $mortalityPercentage = (($totalMortalitymale + $totalMortalityfemale) / $denominator) * 100;
        $male      = ($maleChicks / $denominator) * 100;
        $female    = ($femaleChicks / $denominator) * 100;

        $cullingTotal = ($totalCulling / $denominator) * 100;
        $circleBars = [
            [
                'title' => 'Mortality', 
                'value' => number_format($mortalityPercentage, 2),
                'type' => 'rounded'
            ],
            [
                'title' => 'Culling', 
                'value' => number_format($cullingTotal, 2), 
                'type' => 'rounded'
            ],
            [
                'title' => 'Male Chicks', 
                'value' => number_format($male, 2),
                'type' => 'rounded'],
            [
                'title' => 'Female Chicks', 
                'value' => number_format($female, 2),
                'type' => 'straight'],
            [
                'title' => 'Excess', 
                'value' => rand(1, 50), 
                'type' => 'straight'],
            [
                'title' => 'Worker', 
                'value' => rand(50, 100), 
                'type' => 'straight'],
        ];

        // --- Dummy bird stage

        $broodingPercentage = ($broodingChicks / $denominator) * 100;
        $growerPercentage   = ($growingChicks / $denominator) * 100;
        $layingPercentage   = ($layingChicks / $denominator) * 100;
        $birdStage = [
            'bordingTotal' => $broodingPercentage,
            'growingTotal' => $growerPercentage,
            'productionTotal' => $layingPercentage,
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
