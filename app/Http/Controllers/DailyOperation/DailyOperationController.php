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
    public function index()
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(DailyOperation $dailyOperation)
    {
        //
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
}
