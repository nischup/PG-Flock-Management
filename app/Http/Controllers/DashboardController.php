<?php

namespace App\Http\Controllers;

use App\Models\Shed\BatchAssign;
use App\Models\Master\Company;
use App\Models\Master\Project;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Master\Batch;
use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyCulling;
use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyFeed;
use App\Models\DailyOperation\DailyVaccine;
use App\Models\Production\EggClassification;
use App\Models\Ps\PsLabTest;
use App\Services\DashboardRealtimeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $realtimeService;

    public function __construct(DashboardRealtimeService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    public function index(Request $request)
    {
        // --- Filter options for dropdowns
        $filterOptions = [
            'company' => Company::pluck('name', 'id')->toArray(),
            'project' => Project::pluck('name', 'id')->toArray(),
            'flock'   => Flock::pluck('name', 'id')->toArray(),
            'shed'    => Shed::pluck('name', 'id')->toArray(),
            'batch'   => Batch::pluck('name', 'id')->toArray(),
            'date'    => ['Last 7 Days', 'Last 1 Month', 'Custom'],
        ];

        // --- Selected filters
        $filters = array_merge([
            'company'   => null,
            'project'   => null,
            'flock'     => null,
            'shed'      => null,
            'batch'     => null,
            'date'      => null,
            'date_from' => null,
            'date_to'   => null,
        ], $request->only([
            'company','project','flock','shed','batch','date','date_from','date_to'
        ]));

        // --- Base BatchAssign query with filters
        $batchQuery = BatchAssign::where('status', 1)
            ->when($filters['company'], fn($q) => $q->where('company_id', $filters['company']))
            ->when($filters['project'], fn($q) => $q->where('project_id', $filters['project']))
            ->when($filters['flock'], fn($q) => $q->where('flock_id', $filters['flock']))
            ->when($filters['shed'], fn($q) => $q->where('shed_id', $filters['shed']))
            ->when($filters['batch'], fn($q) => $q->where('batch_no', $filters['batch']));

        // --- Get filtered batch IDs
        $filteredBatchIds = $batchQuery->pluck('id');

        // --- Bird stages & totals
        $broodingChicks = BatchAssign::whereIn('id', $filteredBatchIds)->where('stage', 1)->sum('batch_total_qty');
        $growingChicks  = BatchAssign::whereIn('id', $filteredBatchIds)->where('stage', 2)->sum('batch_total_qty');
        $layingChicks   = BatchAssign::whereIn('id', $filteredBatchIds)->where('stage', 3)->sum('batch_total_qty');

        $totalChicks = $broodingChicks + $growingChicks + $layingChicks;
        $maleChicks  = BatchAssign::whereIn('id', $filteredBatchIds)->sum('batch_male_qty');
        $femaleChicks= BatchAssign::whereIn('id', $filteredBatchIds)->sum('batch_female_qty');

        $denominator = $totalChicks > 0 ? $totalChicks : 1;

        // --- Daily Operations date + batch filter closure
        $dailyOpsQuery = function($q) use ($filteredBatchIds, $filters) {
            $q->whereIn('batchassign_id', $filteredBatchIds);

            if ($filters['date'] === 'Last 7 Days') {
                $q->where('operation_date', '>=', now()->subDays(7));
            } elseif ($filters['date'] === 'Last 1 Month') {
                $q->where('operation_date', '>=', now()->subMonth());
            } elseif ($filters['date'] === 'Custom' && $filters['date_from'] && $filters['date_to']) {
                $q->whereBetween('operation_date', [$filters['date_from'], $filters['date_to']]);
            }
        };

        // --- Mortality
        $totalMortalityMale   = DailyMortality::whereHas('dailyOperation', $dailyOpsQuery)->sum('male_qty');
        $totalMortalityFemale = DailyMortality::whereHas('dailyOperation', $dailyOpsQuery)->sum('female_qty');

        // --- Culling
        $totalCullingMale   = DailyCulling::whereHas('dailyOperation', $dailyOpsQuery)->sum('male_qty');
        $totalCullingFemale = DailyCulling::whereHas('dailyOperation', $dailyOpsQuery)->sum('female_qty');
        $totalCulling = $totalCullingMale + $totalCullingFemale;

        // --- Egg collection
        $totalEggCollection = DailyEggCollection::whereHas('dailyOperation', $dailyOpsQuery)->sum('quantity');

        $currentChicks = $totalChicks - $totalMortalityMale - $totalMortalityFemale;

        // --- Feed consumption
        $totalFeed = DailyFeed::whereHas('dailyOperation', $dailyOpsQuery)->sum('quantity');
        $totalVaccine = DailyVaccine::whereHas('dailyOperation', $dailyOpsQuery)->count();

        // --- Egg classification
        $totalHatchableEggs = EggClassification::whereIn('batchassign_id', $filteredBatchIds)->sum('hatching_eggs');
        $totalCommercialEggs= EggClassification::whereIn('batchassign_id', $filteredBatchIds)->sum('commercial_eggs');

        // --- PsLabTest (filter by date)
        $totalPsLab = PsLabTest::whereHas('psReceive', function($q) use ($filters) {
            if ($filters['date'] === 'Last 7 Days') {
                $q->where('created_at', '>=', now()->subDays(7));
            } elseif ($filters['date'] === 'Last 1 Month') {
                $q->where('created_at', '>=', now()->subMonth());
            } elseif ($filters['date'] === 'Custom' && $filters['date_from'] && $filters['date_to']) {
                $q->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
        })->sum('lab_send_total_qty');

        // --- Dashboard cards
        $cards = [
            ['title'=>'Total Flock','value'=>Flock::where('status',1)->count(),'icon'=>'User'],
            ['title'=>'Total Active Batches','value'=>$batchQuery->count(),'icon'=>'Archive'],
            ['title'=>'Total Chicks','value'=>$totalChicks,'icon'=>'Drumstick'],
            ['title'=>'Female Chicks Qty','value'=>$femaleChicks,'icon'=>'Drumstick'],
            ['title'=>'Male Chicks Qty','value'=>$maleChicks,'icon'=>'Drumstick'],
            ['title'=>'Total Mortality','value'=>$totalMortalityMale+$totalMortalityFemale,'icon'=>'ShieldX'],
            ['title'=>'Male Mortality','value'=>$totalMortalityMale,'icon'=>'ShieldX'],
            ['title'=>'Female Mortality','value'=>$totalMortalityFemale,'icon'=>'ShieldX'],
            ['title'=>'Current Chicks','value'=>$currentChicks,'icon'=>'ShieldX'],
            ['title'=>'Total Egg Collection','value'=>$totalEggCollection,'icon'=>'Egg'],
            ['title'=>'Total Hatching Eggs','value'=>$totalHatchableEggs,'icon'=>'PackageSearch'],
            ['title'=>'Total Commercial Eggs','value'=>$totalCommercialEggs,'icon'=>'PackageSearch'],
            ['title'=>'Total Feed Consumption','value'=>$totalFeed,'icon'=>'Factory'],
            ['title'=>'Total Vaccination','value'=>$totalVaccine,'icon'=>'Syringe'],
            ['title'=>'Sent for Lab From PS','value'=>$totalPsLab,'icon'=>'FlaskConical'],
            ['title'=>'Sent for Lab From Batch','value'=>0,'icon'=>'FlaskConical'],
        ];

        // --- Progress bars
        $eggPercentage        = $currentChicks > 0 ? ($totalEggCollection / $currentChicks) * 100 : 0;
        $hatchablePercentage  = $totalEggCollection > 0 ? ($totalHatchableEggs / $totalEggCollection) * 100 : 0;
        $commercialPercentage = $totalEggCollection > 0 ? ($totalCommercialEggs / $totalEggCollection) * 100 : 0;

        $progressBars = [
            ['title'=>'Total Eggs','progress'=>number_format($eggPercentage,2),'extra'=>"Goal: {$totalEggCollection} eggs"],
            ['title'=>'Hatchable Eggs','progress'=>number_format($hatchablePercentage,2),'extra'=>"Goal: {$totalHatchableEggs} eggs"],
            ['title'=>'Commercial','progress'=>number_format($commercialPercentage,2),'extra'=>"Goal: {$totalCommercialEggs} eggs"],
        ];

        // --- Circle bars
        $mortalityPercentage = (($totalMortalityMale + $totalMortalityFemale)/$denominator)*100;
        $male = ($maleChicks/$denominator)*100;
        $female = ($femaleChicks/$denominator)*100;
        $cullingTotal = ($totalCulling/$denominator)*100;

        // $circleBars = [
        //     ['title'=>'Mortality','value'=>number_format($mortalityPercentage,2),'type'=>'rounded'],
        //     ['title'=>'Culling','value'=>number_format($cullingTotal,2),'type'=>'rounded'],
        //     ['title'=>'Male Chicks','value'=>number_format($male,2),'type'=>'rounded'],
        //     ['title'=>'Female Chicks','value'=>number_format($female,2),'type'=>'straight'],
        //     ['title'=>'Excess','value'=>rand(1,50),'type'=>'straight'],
        //     ['title'=>'Worker','value'=>rand(50,100),'type'=>'straight'],
        // ];

        // --- Bird stage percentages
        $birdStage = [
            'bordingTotal'    => ($broodingChicks/$denominator)*100,
            'growingTotal'    => ($growingChicks/$denominator)*100,
            'productionTotal' => ($layingChicks/$denominator)*100,
        ];

        // Get real-time data
        $realtimeData = $this->realtimeService->getRealtimeData($filters);

        // --- Return Inertia
        return Inertia::render('Dashboard', [
            'cards'         => $realtimeData['cards'],
            'progressBars'  => $realtimeData['progressBars'],
            'circleBars'    => $realtimeData['circleBars'],
            'birdStage'     => $realtimeData['birdStage'],
            'filterOptions' => $filterOptions,
            'filters'       => $filters,
            'realtimeData'  => $realtimeData,
        ]);
    }
}
