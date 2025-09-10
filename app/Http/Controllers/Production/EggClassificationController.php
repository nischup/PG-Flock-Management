<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Shed\BatchAssign;
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
                'date' => '2025-08-31',
                'flock' => 'Flock 22',
                'batch' => 'Batch A',
                'hatching_qty' => 1200,
                'commercial_qty' => 50, // renamed
            ],
            [
                'id' => 2,
                'date' => '2025-08-31',
                'flock' => 'Flock 22',
                'batch' => 'Batch B',
                'hatching_qty' => 950,
                'commercial_qty' => 30,
            ],
            [
                'id' => 3,
                'date' => '2025-08-31',
                'flock' => 'Flock 22',
                'batch' => 'Batch C',
                'hatching_qty' => 1100,
                'commercial_qty' => 60,
            ],
        ];

        // Fake pagination meta (Laravel paginator style)
        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 1,
            'path' => url()->current(),
            'per_page' => 10,
            'to' => count($dummyData),
            'total' => count($dummyData),
        ];


    return Inertia::render('production/egg-classification/List', [
        'classifications' => [
            'data' => $dummyData,
            'meta' => $meta,
        ],
        'filters' => request()->only(['search', 'per_page', 'page']),
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
                'id'        => $batch->id,
                'label'     => "{$batch->transaction_no}-{$batch->shed?->name}-{$batch->batch?->name}",
            ];
        });

       
        return Inertia::render('production/egg-classification/Create', [
            'batchAssign' => $batchAssign
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
}
