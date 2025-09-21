<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Master\EggType;
use App\Models\Master\Unit;
use App\Models\Production\EggClassification;
use App\Models\Production\EggClassificationRejected;
use App\Models\Production\EggClassificationTechnical;
use App\Models\Shed\BatchAssign;
use App\Models\DailyOperation\DailyOperation;
use App\Models\DailyOperation\DailyEggCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EggClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EggClassification::with([
            'batchAssign.shed',
            'batchAssign.batch',
            'technicalEggs.eggType',
            'rejectedEggs.eggType',
        ]);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('batchAssign', function ($subQuery) use ($request) {
                    $subQuery->where('transaction_no', 'like', "%{$request->search}%")
                        ->orWhere('name', 'like', "%{$request->search}%");
                });
            });
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('classification_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('classification_date', '<=', $request->date_to);
        }

        // Batch filter
        if ($request->filled('batch')) {
            $query->whereHas('batchAssign', function ($q) use ($request) {
                $q->where('id', $request->batch);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'classification_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort_by to prevent SQL injection
        $allowedSortFields = [
            'classification_date', 'total_eggs', 'hatching_eggs',
            'commercial_eggs', 'rejected_eggs', 'technical_eggs',
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('classification_date', 'desc');
        }

        $classifications = $query->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('production/egg-classification/List', [
            'classifications' => $classifications,
            'filters' => $request->only([
                'search', 'per_page', 'page', 'date_from',
                'date_to', 'batch', 'sort_by', 'sort_order',
            ]),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $batchAssign = BatchAssign::with(['flock', 'shed', 'batch'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'label' => "{$batch->transaction_no}-{$batch->shed?->name}-{$batch->batch?->name}",
                ];
            });

        return Inertia::render('production/egg-classification/Create', [
            'batchAssign' => $batchAssign,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $rejected_total = ($request->double_yolk) +
                          ($request->double_yolk_broken) +
                          ($request->commercial) +
                          ($request->commercial_broken) +
                          ($request->liquid) +
                          ($request->damage);

        $technical_total = ($request->floor_egg) +
                           ($request->thin_egg) +
                           ($request->misshape_egg) +
                           ($request->white_egg) +
                           ($request->dirty_egg);

        $commercial_total = ($request->commercial);
        $hatching_egg = ($request->total_egg) - $rejected_total;

        // 1️⃣ Create main classification record
        $classification = EggClassification::create([
            'batchassign_id' => $request->batchassign_id,
            'classification_date' => $request->operation_date,
            'total_eggs' => $request->total_egg,
            'hatching_eggs' => $hatching_egg,
            'commercial_eggs' => $rejected_total,
            'rejected_eggs' => $rejected_total,
            'technical_eggs' => $technical_total,
            'created_by' => Auth::id(),
        ]);

        // 2️⃣ Save rejected eggs
        $rejectedEggs = [
            'double_yolk' => $request->double_yolk,
            'double_yolk_broken' => $request->double_yolk_broken,
            'commercial' => $request->commercial,
            'commercial_broken' => $request->commercial_broken,
            'liquid' => $request->liquid,
            'damage' => $request->damage,
        ];

        foreach ($rejectedEggs as $key => $qty) {
            if ($qty > 0 || $request->input($key.'_note')) {
                $eggType = EggType::where('name', $key)->where('category', 1)->first();
                if ($eggType) {
                    EggClassificationRejected::create([
                        'classification_id' => $classification->id,
                        'egg_type_id' => $eggType->id,
                        'quantity' => $qty ?? 0,
                        'note' => $request->input($key.'_note'),
                    ]);
                }
            }
        }

        // 3️⃣ Save technical eggs
        $technicalEggs = [
            'floor_egg' => $request->floor_egg,
            'thin_egg' => $request->thin_egg,
            'misshape_egg' => $request->misshape_egg,
            'white_egg' => $request->white_egg,
            'dirty_egg' => $request->dirty_egg,
        ];

        foreach ($technicalEggs as $key => $qty) {
            if ($qty > 0 || $request->input($key.'_note')) {
                $eggType = EggType::where('name', $key)->where('category', 2)->first();
                if ($eggType) {
                    EggClassificationTechnical::create([
                        'classification_id' => $classification->id,
                        'egg_type_id' => $eggType->id,
                        'quantity' => $qty ?? 0,
                        'note' => $request->input($key.'_note'),
                    ]);
                }
            }
        }

        return redirect()->route('egg-classification.index')->with('success', 'Egg Classification.');

        dd($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getTotalEggs(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'batchassign_id' => 'required|integer|exists:batch_assigns,id',
            'operation_date' => 'required|date',
        ]);

        // Fetch the DailyOperation ID for the given batch and date
        $dailyOperation = DailyOperation::where('batchassign_id', $validated['batchassign_id'])
            ->where('operation_date', $validated['operation_date'])
            ->first();

        // If no daily operation exists, return 0
        if (!$dailyOperation) {
            return response()->json(['total_egg' => 0]);
        }

        // Fetch the DailyEggCollection for that operation
        $dailyEgg = DailyEggCollection::where('daily_operation_id', $dailyOperation->id)
            ->first();

        // Return quantity or 0 if record not found
        $totalEggs = $dailyEgg ? $dailyEgg->quantity : 0;

        return response()->json(['total_egg' => $totalEggs]);
    }

    /**
     * Get batch data for egg classification statistics
     */
    public function getBatchData($batchId)
    {
        $batch = BatchAssign::with(['flock', 'shed', 'batch', 'shedReceive'])
            ->find($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        // Calculate age from shed receive date
        $age = '0 weeks 0 days';
        if ($batch->shedReceive && $batch->shedReceive->created_at) {
            $startDate = $batch->shedReceive->created_at;
            $days = $startDate->diffInDays(now());
            $weeks = floor($days / 7);
            $remainingDays = $days % 7;
            $age = "{$weeks} weeks {$remainingDays} days";
        }

        // Get latest daily operation data for this batch
        $latestOperation = DailyOperation::where('batchassign_id', $batchId)
            ->with([
                'mortalities', 'destroys', 'sexingErrors', 'cullings',
                'feeds', 'waters', 'lights', 'weights', 'temperatures', 
                'humidities', 'eggCollections', 'medicines', 'vaccines'
            ])
            ->latest('operation_date')
            ->first();

        $tabData = [
            'total_eggs' => 0,
            'daily_mortality' => 0,
            'destroy' => 0,
            'sexing_error' => 0,
            'cull' => 0,
            'feed_consumption' => '0 Kg',
            'water_consumption' => '0 L',
            'light_hour' => '0 H',
            'weight' => '0 gm',
            'temperature' => 0,
            'humidity' => 0,
            'egg_collection' => 0,
            'medicine' => 0,
            'vaccine' => 0,
        ];

        if ($latestOperation) {
            // Get mortality data
            $mortality = $latestOperation->mortalities()->first();
            if ($mortality) {
                $tabData['daily_mortality'] = $mortality->female_qty + $mortality->male_qty;
            }

            // Get destroy data
            $destroy = $latestOperation->destroys()->first();
            if ($destroy) {
                $tabData['destroy'] = $destroy->female_qty + $destroy->male_qty;
            }

            // Get sexing error data
            $sexingError = $latestOperation->sexingErrors()->first();
            if ($sexingError) {
                $tabData['sexing_error'] = $sexingError->female_qty + $sexingError->male_qty;
            }

            // Get cull data
            $cull = $latestOperation->cullings()->first();
            if ($cull) {
                $tabData['cull'] = $cull->female_qty + $cull->male_qty;
            }

            // Get feed data
            $feed = $latestOperation->feeds()->first();
            if ($feed) {
                $unit = Unit::find($feed->unit_id);
                $unitName = $unit ? $unit->name : 'Kg';
                $tabData['feed_consumption'] = $feed->quantity . ' ' . $unitName;
            }

            // Get water data
            $water = $latestOperation->waters()->first();
            if ($water) {
                $unit = Unit::find($water->unit_id);
                $unitName = $unit ? $unit->name : 'L';
                $tabData['water_consumption'] = $water->quantity . ' ' . $unitName;
            }

            // Get light data
            $light = $latestOperation->lights()->first();
            if ($light) {
                $tabData['light_hour'] = $light->hour . ' H';
            }

            // Get weight data
            $weight = $latestOperation->weights()->first();
            if ($weight) {
                $tabData['weight'] = $weight->weight . ' gm';
            }

            // Get temperature data
            $temperature = $latestOperation->temperatures()->first();
            if ($temperature) {
                $tabData['temperature'] = $temperature->inside_temp;
            }

            // Get humidity data
            $humidity = $latestOperation->humidities()->first();
            if ($humidity) {
                $tabData['humidity'] = $humidity->today_humidity;
            }

            // Get egg collection data
            $eggCollection = $latestOperation->eggCollections()->first();
            if ($eggCollection) {
                $tabData['egg_collection'] = $eggCollection->total_egg;
                $tabData['total_eggs'] = $eggCollection->total_egg;
            }

            // Get medicine data
            $medicine = $latestOperation->medicines()->first();
            if ($medicine) {
                $tabData['medicine'] = $medicine->quantity;
            }

            // Get vaccine data
            $vaccine = $latestOperation->vaccines()->first();
            if ($vaccine) {
                $tabData['vaccine'] = $vaccine->quantity;
            }
        }

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'total_birds' => $batch->batch_total_qty,
                'current_birds' => $batch->batch_total_qty - $batch->batch_total_mortality,
                'age' => $age,
                'flock_name' => $batch->flock?->name ?? 'N/A',
                'shed_name' => $batch->shed?->name ?? 'N/A',
                'batch_name' => $batch->batch?->name ?? 'N/A',
            ],
            'statistics' => $tabData
        ]);
    }
}
