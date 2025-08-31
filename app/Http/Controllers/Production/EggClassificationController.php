<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;

class EggClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
        $dummyData = [
        [
            'id' => 1,
            'grading_type' => 'Commercial',
            'grade' => 'A',
            'classification' => 'Large',
            'qty' => 120,
        ],
        [
            'id' => 2,
            'grading_type' => 'Commercial',
            'grade' => 'B',
            'classification' => 'Medium',
            'qty' => 95,
        ],
        [
            'id' => 3,
            'grading_type' => 'Hatching',
            'grade' => 'H1',
            'classification' => null, // no classification for hatching
            'qty' => 60,
        ],
    ];

    return Inertia::render('production/egg-classification/List', [
        'classifications' => [
            'data' => $dummyData,
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => count($dummyData),
            ],
        ],
        'filters' => request()->only(['search', 'per_page', 'page']),
    ]);


    


        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $flocks = [
            ['id' => 1, 'flock_code' => '1-22A'],
            ['id' => 2, 'flock_code' => '1-22B'],
            ['id' => 3, 'flock_code' => '2-22A'],
        ];


        return Inertia::render('production/egg-classification/Create', [
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
}
