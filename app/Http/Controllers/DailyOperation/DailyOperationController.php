<?php

namespace App\Http\Controllers\DailyOperation;

use App\Http\Controllers\Controller;
use App\Models\DailyOperation\DailyOperation;
use App\Models\Master\Feed;
use App\Models\Master\Medicine;
use App\Models\Master\Unit;
use App\Models\Master\Vaccine;
use App\Models\Shed\BatchAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DailyOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?string $stage = null)
    {
        // Default stage = brooding if not provided
        $stage = $stage ?? 'brooding';

        // Convert stage to numeric value for database query
        $stageValue = match ($stage) {
            'brooding' => 1,
            'growing' => 2,
            'laying' => 3,
            default => 1
        };

        // Build query with relationships
        $query = DailyOperation::with([
            'batchAssign.flock',
            'batchAssign.shed',
            'batchAssign.company',
            'batchAssign.batch',
            'mortalities',
            'feeds.feedType',
            'waters',
            'lights',
            'weights',
            'temperatures',
            'eggCollections',
            'medicines.medicine',
            'vaccines.vaccine',
            'creator',
        ])
            ->whereHas('batchAssign', function ($q) use ($stageValue) {
                $q->where('stage', $stageValue);
            });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('batchAssign.flock', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('batchAssign.shed', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('batchAssign.company', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('operation_date', 'like', "%{$search}%");
            });
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->where('operation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('operation_date', '<=', $request->date_to);
        }

        // Apply flock filter
        if ($request->filled('flock_id')) {
            $query->whereHas('batchAssign', function ($q) use ($request) {
                $q->where('flock_id', $request->flock_id);
            });
        }

        // Apply shed filter
        if ($request->filled('shed_id')) {
            $query->whereHas('batchAssign', function ($q) use ($request) {
                $q->where('shed_id', $request->shed_id);
            });
        }

        // Apply company filter
        if ($request->filled('company_id')) {
            $query->whereHas('batchAssign', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // Order by operation date desc
        $query->orderBy('operation_date', 'desc');

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $dailyOperations = $query->paginate($perPage);

        // Get filter options
        $flocks = \App\Models\Master\Flock::select('id', 'name')->orderBy('name')->get();
        $sheds = \App\Models\Master\Shed::select('id', 'name')->orderBy('name')->get();
        $companies = \App\Models\Master\Company::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('dailyoperation/List', [
            'dailyOperations' => $dailyOperations->through(function ($item) {
                // Calculate totals from related data
                $totalMortality = $item->mortalities->sum('female_qty') + $item->mortalities->sum('male_qty');
                $totalFeed = $item->feeds->sum('quantity');
                $totalWater = $item->waters->sum('quantity');
                $totalEggs = $item->eggCollections->sum('quantity');

                return [
                    'id' => $item->id,
                    'operation_date' => $item->operation_date,
                    'flock_name' => $item->batchAssign->flock->name ?? 'N/A',
                    'shed_name' => $item->batchAssign->shed->name ?? 'N/A',
                    'company_name' => $item->batchAssign->company->name ?? 'N/A',
                    'batch_name' => $item->batchAssign->batch->name ?? 'N/A',
                    'job_no' => $item->batchAssign->job_no,
                    'transaction_no' => $item->batchAssign->transaction_no,
                    'male_mortality' => $item->mortalities->sum('male_qty'),
                    'female_mortality' => $item->mortalities->sum('female_qty'),
                    'total_mortality' => $totalMortality,
                    'feed_consumption' => $totalFeed > 0 ? $totalFeed.' '.($item->feeds->first()->unit ?? 'Kg') : '0 Kg',
                    'water_consumption' => $totalWater > 0 ? $totalWater.' L' : '0 L',
                    'light_hour' => $item->lights->first()->hour ?? 0,
                    'egg_collection' => $totalEggs,
                    'created_by_name' => $item->creator->name ?? 'N/A',
                    'created_at' => $item->created_at,
                    'status' => $item->status,
                    // Include relationship data for filters
                    'flock' => $item->batchAssign->flock ? ['id' => $item->batchAssign->flock->id, 'name' => $item->batchAssign->flock->name] : null,
                    'shed' => $item->batchAssign->shed ? ['id' => $item->batchAssign->shed->id, 'name' => $item->batchAssign->shed->name] : null,
                    'company' => $item->batchAssign->company ? ['id' => $item->batchAssign->company->id, 'name' => $item->batchAssign->company->name] : null,
                ];
            }),
            'filters' => $request->only(['search', 'per_page', 'flock_id', 'shed_id', 'company_id', 'date_from', 'date_to']),
            'stage' => $stage,
            'flocks' => $flocks,
            'sheds' => $sheds,
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($stage)
    {

        // Get all batch assignments with related flock info
        $st = 0;
        if ($stage == 'brooding') {
            $st = 1;
        } elseif ($stage == 'growing') {
            $st = 2;
        } else {
            $st = 3;
        }

        $flocks = BatchAssign::with(['flock', 'shed', 'batch'])
            ->where('stage', $st)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'flock' => $batch->flock?->name ?? 'N/A',
                    'batch_no' => $batch->batch_no,
                    'batch' => $batch->batch?->name ?? 'N/A',
                    'shed_id' => $batch->shed_id,
                    'shed' => $batch->shed?->name ?? 'N/A',
                    'label' => "{$batch->transaction_no}-{$batch->shed?->name}-{$batch->batch?->name}",
                ];
            });

        // You can pass dummy tab counts or fetch from DB if needed
        $tabCounts = [
            1 => [
                'daily_mortality' => 10,
                'feed_consumption' => '200 Kg',
                'water_consumption' => '500 L',
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            2 => [
                'daily_mortality' => 10,
                'feed_consumption' => '200 Kg',
                'water_consumption' => '500 L',
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            3 => [
                'daily_mortality' => 10,
                'feed_consumption' => '200 Kg',
                'water_consumption' => '500 L',
                'culling' => 5,
                'egg_collection' => 10000,
            ],
        ];

        $waters = [
            ['id' => 1, 'name' => 'Normal Water'],
            ['id' => 2, 'name' => 'Mineral Water'],
            ['id' => 3, 'name' => 'Vitamin Mixed Water'],
        ];

        return Inertia::render('dailyoperation/Create', [
            'stage' => $stage,   // ✅ Pass stage here
            'flocks' => $flocks,
            'tabCounts' => $tabCounts,
            'feeds' => Feed::all(),
            'units' => Unit::all(),
            'medicines' => Medicine::all(),
            'vaccines' => Vaccine::all(),
            'waters' => $waters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        
        
        
        
        
        
        $dailyOperation = DailyOperation::create([
            'batchassign_id' => $request->batchassign_id,
            'operation_date' => $request->operation_date,
            'created_by' => Auth::id(),
            'status' => 1,
        ]);

        // Mortality
        if ($request->female_mortality || $request->male_mortality) {
            $dailyOperation->mortalities()->create([
                'female_qty' => $request->female_mortality,
                'male_qty' => $request->male_mortality,
                'female_mortality_reason' => $request->female_reason,
                'male_mortality_reason' => $request->male_reason,
                'note' => $request->mortalitynote,
            ]);
        }

        // Feed
        if ($request->feed_type_id) {
            $dailyOperation->feeds()->create([
                'feed_type_id' => $request->feed_type_id,
                'quantity' => $request->feed_quantity,
                'unit' => $request->feed_unit,
                'note' => $request->feed_note,
            ]);
        }

        // Water
        if ($request->water_quantity) {
            $dailyOperation->waters()->create([
                'water_type_id' => $request->water_type,
                'quantity' => $request->water_quantity,
                'unit_id' => $request->feed_unit,
                'note' => $request->water_note,
            ]);
        }

        // Light
        if ($request->light_hour || $request->light_minute) {
            $dailyOperation->lights()->create([
                'hour' => $request->light_hour,
                'minute' => $request->light_minute,
                'note' => $request->light_note,
            ]);
        }

        // Destroy
        if ($request->destroy_male || $request->destroy_female) {
            $dailyOperation->destroys()->create([
                'male_qty' => $request->destroy_male,
                'female_qty' => $request->destroy_female,
                'male_destroy_reason' => $request->destroy_male_reason,
                'female_destroy_reason' => $request->destroy_female_reason,
                'note' => $request->destroy_note,
            ]);
        }

        if ($request->cull_male_qty || $request->cull_female_qty) {
            $dailyOperation->cullings()->create([
                'male_qty' => $request->cull_male_qty,
                'female_qty' => $request->cull_female_qty,
                'male_culling_reason' => $request->cull_male_reason,
                'female_culling_reason' => $request->cull_female_reason,
                'note' => $request->culling_note,
            ]);
        }

        // Sexing error
        if ($request->sexing_error_female || $request->sexing_error_male) {
            $dailyOperation->sexingErrors()->create([
                'female_qty' => $request->sexing_error_female,
                'male_qty' => $request->sexing_error_male,
                'note' => $request->serror_note,
            ]);
        }

        // Weight
        if ($request->weight_female || $request->weight_male) {
            $dailyOperation->weights()->create([
                'female_weight' => $request->weight_female,
                'male_weight' => $request->weight_male,
                'note' => $request->weight_note,
            ]);
        }

        // Temperature
        if ($request->temp_inside || $request->temp_outside) {
            $dailyOperation->temperatures()->create([
                'inside_temp' => $request->temp_inside,
                'std_inside_temp' => $request->temp_inside_std,
                'outside_temp' => $request->temp_outside,
                'std_outside_temp' => $request->temp_outside_std,
                'note' => $request->temperature_note,
            ]);
        }

        // Humidity
        if ($request->humidity_today || $request->humidity_std) {
            $dailyOperation->humidities()->create([
                'today_humidity' => $request->humidity_today,
                'std_humidity' => $request->humidity_std,
                'note' => $request->humidity_note,
            ]);
        }

        // Egg collection
        if ($request->egg_collection) {
            $dailyOperation->eggCollections()->create([
                'quantity' => $request->egg_collection,
                'note' => $request->eggcollection_note ?? null,
            ]);
        }

        // Medicine
        if ($request->medicine_id) {
            $dailyOperation->medicines()->create([
                'medicine_id' => $request->medicine_id,
                'quantity' => $request->medicine_qty,
                'unit_id' => $request->medicine_unit,
                'dose' => $request->medicine_dose,
                'note' => $request->medicine_note,
            ]);
        }

        // Store feed finishing
        if ($request->finishtime_female) {
            $dailyOperation->feedFinishings()->create([
                'female_finishing_time' => $request->finishtime_female,
                'male_finishing_time' => $request->finishtime_male,
                'note' => $request->finishtime_note,
            ]);
        }

        // feeding program
        if ($request->feeding_pro_male) {
            $dailyOperation->feedingPrograms()->create([
                'female_program' => $request->feeding_pro_female,
                'male_program' => $request->feeding_pro_male,
                'note' => $request->feeding_pro_note,
            ]);
        }

        // Vaccine
        if ($request->vaccine_id) {
            $vaccineData = [
                'vaccine_id' => $request->vaccine_id,
                'dose' => $request->vaccine_dose,
                'unit' => $request->vaccine_unit,
                'note' => $request->vaccine_note,
            ];

            if ($request->hasFile('vaccine_file')) {
                $vaccineData['file'] = $request->file('vaccine_file')->store('vaccines');
            }

            $dailyOperation->vaccines()->create($vaccineData);
        }

        $stage = BatchAssign::findOrFail($dailyOperation->batch_assign_id);

        
        if ($stage == 1) {
            $stageName = 'brooding';
        } elseif ($stage == 2) {
            $stageName = 'growing';
        } elseif ($stage == 3) {
            $stageName = 'laying';
        } elseif ($stage == 4) {
            $stageName = 'closing';
        } else {
            $stageName = 'brooding'; // fallback
        }

        return redirect()
        ->route('daily-operation.stage', ['stage' => $stage])
        ->with('success', ucfirst($stage) . ' data saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $flockId)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Dummy daily data for the flock (for now)
        $dailyData = [
            ['date' => '2025-08-01', 'mortality' => 2, 'feed' => 10, 'water' => 8],
            ['date' => '2025-08-02', 'mortality' => 3, 'feed' => 12, 'water' => 9],
            ['date' => '2025-08-03', 'mortality' => 1, 'feed' => 11, 'water' => 7],
        ];

        return Inertia::render('dailyoperation/Details', [
            'dailyData' => $dailyData,
            'flockId' => $flockId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyOperation $dailyOperation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyOperation $dailyOperation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyOperation $dailyOperation)
    {
        //
    }

    public function mortality()
    {

        // Dummy flocks
        $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];

        return Inertia::render('dailyoperation/Edit', [
            'flocks' => $flocks,
        ]);
    }

    // Dummy flocks

    public function overview()
    {
        $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];

        // You can calculate these summaries from DB later
        $dummySummary = [
            1 => [
                'mortality' => 25,
                'feed' => 200,
                'water' => 150,
            ],
            2 => [
                'mortality' => 15,
                'feed' => 180,
                'water' => 130,
            ],
            3 => [
                'mortality' => 10,
                'feed' => 190,
                'water' => 160,
            ],
        ];

        return Inertia::render('dailyoperation/Overview', [
            'flocks' => $flocks,
            'dummySummary' => $dummySummary,
        ]);
    }
}
