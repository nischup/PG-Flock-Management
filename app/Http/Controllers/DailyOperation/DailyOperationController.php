<?php

namespace App\Http\Controllers\DailyOperation;
use App\Http\Controllers\Controller;
use App\Models\DailyOperation\DailyOperation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DailyOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       
        // Dummy data
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
                ],
                [
                    'id' => 2,
                    'operation_date' => '2025-08-02',
                    'flock_code' => '1-22B',
                    'male_mortality' => 2,
                    'female_mortality' => 4,
                    'feed_consumption' => '180 Kg',
                    'water_consumption' => '130 L',
                    'light_hour' => 9,
                    'note' => 'Slight heat stress',
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 2,
            ],
        ];

        return Inertia::render('dailyoperation/List', [
            'dailyOperations' => $dailyOperations,
            'filters' => $request->only(['search', 'per_page', 'page']),
        ]);
         
    }



    public function production(){
       $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];


        // Dummy tab counts per flock
        $tabCounts = [
            1 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            2 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            3 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
        ];

        return Inertia::render('production/DailyoperationCreate', [
            'flocks' => $flocks
        ]); 
    }









    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Dummy flocks
        $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];


        // Dummy tab counts per flock
        $tabCounts = [
            1 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            2 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
            3 => [
                'daily_mortality' => 10,
                'feed_consumption' => "200 Kg",
                'water_consumption' => "500 L",
                'culling' => 5,
                'egg_collection' => 10000,
            ],
        ];

        return Inertia::render('dailyoperation/Create', [
            'flocks' => $flocks
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
