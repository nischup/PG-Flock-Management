<?php

namespace App\Http\Controllers\DailyOperation;
use App\Http\Controllers\Controller;
use App\Models\DailyOperation\DailyOperation;
use App\Models\Shed\BatchAssign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Feed;
use App\Models\Master\Medicine;
use App\Models\Master\Vaccine;
use App\Models\Master\Unit;
use Illuminate\Support\Facades\Auth;

class DailyOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?string $stage = null)
    {
        // Default stage = brooding if not provided
        $stage = $stage ?? 'brooding';

        $dailyOperations = [
            'data' => [
                [
                    'id' => 1,
                    'operation_date' => '2025-08-01',
                    'flock_code' => '1-22A',
                    'male_mortality' => 5,
                    'female_mortality' => 3,
                    'feed_consumption' => '200 Kg',
                    'water_consumption' => '150 L',
                    'light_hour' => 8,
                    'note' => 'Normal day',
                    'stage' => $stage,
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 1,
            ],
        ];

        return Inertia::render('dailyoperation/List', [
            'dailyOperations' => $dailyOperations,
            'filters' => $request->only(['search', 'per_page', 'page']),
            'stage' => $stage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($stage)
    {
       
        // Get all batch assignments with related flock info
        $flocks = BatchAssign::with('flock')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'label' => $batch->job_no . ' - ' . $batch->shed_id,
                    'batch_no' => $batch->batch_no,
                ];
            });

          

        // You can pass dummy tab counts or fetch from DB if needed
        $tabCounts = [
            1 => [
                'daily_mortality'   => 10,
                'feed_consumption'  => "200 Kg",
                'water_consumption' => "500 L",
                'culling'           => 5,
                'egg_collection'    => 10000,
            ],
            2 => [
                'daily_mortality'   => 10,
                'feed_consumption'  => "200 Kg",
                'water_consumption' => "500 L",
                'culling'           => 5,
                'egg_collection'    => 10000,
            ],
            3 => [
                'daily_mortality'   => 10,
                'feed_consumption'  => "200 Kg",
                'water_consumption' => "500 L",
                'culling'           => 5,
                'egg_collection'    => 10000,
            ],
        ];


        
        return Inertia::render('dailyoperation/Create', [
            'stage'      => $stage,   // ✅ Pass stage here
            'flocks'     => $flocks,
            'tabCounts'  => $tabCounts,
            'feeds'=>Feed::all(),
            'units'=>Unit::all(),
            'medicines'=>Medicine::all(),
            'vaccines'=>Vaccine::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            
        
            dd($request->all());
        
        
        
            $dailyOperation = DailyOperation::create([
                'batchassign_id' => $request->batchassign_id,
                'operation_date' => "2025-09-08",
                'created_by' => Auth::id(),
                'status'=>1,
            ]);

            // Mortality
            if ($request->female_mortality || $request->male_mortality) {
                $dailyOperation->mortality()->create([
                    'female_qty' => $request->female_mortality,
                    'male_qty' => $request->male_mortality,
                    'female_reason' => $request->female_reason,
                    'male_reason' => $request->male_reason,
                    'note' => $request->mortalitynote,
                ]);
            }

            // Feed
            if ($request->feed_type_id) {
                $dailyOperation->feed()->create([
                    'feed_type_id' => $request->feed_type_id,
                    'quantity' => $request->feed_quantity,
                    'unit' => $request->feed_unit,
                    'note' => $request->feed_note,
                ]);
            }

            // Water
            if ($request->water_quantity) {
                $dailyOperation->water()->create([
                    'quantity' => $request->water_quantity,
                    'note' => $request->water_note,
                ]);
            }

            // Light
            if ($request->light_hour || $request->light_minute) {
                $dailyOperation->light()->create([
                    'hour' => $request->light_hour,
                    'minute' => $request->light_minute,
                    'note' => $request->light_note,
                ]);
            }

            // Destroy / Culling
            if ($request->destroy_male_qty || $request->destroy_female_qty) {
                $dailyOperation->destroy()->create([
                    'male_qty' => $request->destroy_male_qty,
                    'female_qty' => $request->destroy_female_qty,
                    'male_reason' => $request->destroy_male_reason,
                    'female_reason' => $request->destroy_female_reason,
                    'note' => $request->destroy_note,
                ]);
            }

            if ($request->cull_male_qty || $request->cull_female_qty) {
                $dailyOperation->culling()->create([
                    'male_qty' => $request->cull_male_qty,
                    'female_qty' => $request->cull_female_qty,
                    'male_reason' => $request->cull_male_reason,
                    'female_reason' => $request->cull_female_reason,
                    'note' => $request->culling_note,
                ]);
            }

            // Sexing error
            if ($request->sexing_error_female_qty || $request->sexing_error_male_qty) {
                $dailyOperation->sexingError()->create([
                    'female_qty' => $request->sexing_error_female_qty,
                    'male_qty' => $request->sexing_error_male_qty,
                    'note' => $request->serror_note,
                ]);
            }

            // Weight
            if ($request->weight_female_qty || $request->weight_male_qty) {
                $dailyOperation->weight()->create([
                    'female_qty' => $request->weight_female_qty,
                    'male_qty' => $request->weight_male_qty,
                    'note' => $request->weight_note,
                ]);
            }

            // Temperature
            if ($request->inside_temp || $request->outside_temp) {
                $dailyOperation->temperature()->create([
                    'inside' => $request->inside_temp,
                    'std_inside' => $request->std_inside_temp,
                    'outside' => $request->outside_temp,
                    'std_outside' => $request->std_outside_temp,
                    'note' => $request->temperature_note,
                ]);
            }

            // Humidity
            if ($request->today_humidity || $request->std_humidity) {
                $dailyOperation->humidity()->create([
                    'today' => $request->today_humidity,
                    'std' => $request->std_humidity,
                    'note' => $request->humidity_note,
                ]);
            }

            // Egg collection
            if ($request->egg_collection) {
                $dailyOperation->eggCollection()->create([
                    'quantity' => $request->egg_collection,
                    'note' => $request->egg_collection_note ?? null,
                ]);
            }

            // Medicine
            if ($request->medicine_id) {
                $dailyOperation->medicine()->create([
                    'medicine_id' => $request->medicine_id,
                    'quantity' => $request->medicine_quantity,
                    'unit' => $request->medicine_unit,
                    'time' => $request->medicine_time,
                    'note' => $request->medicine_note,
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

                $dailyOperation->vaccine()->create($vaccineData);
            }
    

        return response()->json(['message' => 'Daily operation saved successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,$flockId)
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
            'endDate' => $endDate
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


    public function mortality(){

        // Dummy flocks
        $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];

        

         return Inertia::render('dailyoperation/Edit', [
            'flocks' => $flocks
        ]);
    }



    // Dummy flocks

    public function overview(){
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
            'dummySummary' => $dummySummary
        ]);
    }
}
