<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Shed\BatchAssign;
use Illuminate\Http\Request;
use App\Models\Production\EggClassification;
use App\Models\Master\EggType;
use App\Models\Production\EggClassificationRejected;
use App\Models\Production\EggClassificationTechnical;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class EggClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
        {
        $query = EggClassification::with([
            'batchAssign', // batch_assign relation
            'technicalEggs.eggType', // rejected eggs and their types
            'rejectedEggs.eggType', // technical eggs and their types
        ]);

        // Optional search/filter
        if ($request->has('search') && $request->search) {
            $query->whereHas('batchAssign', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        $classifications = $query->orderBy('classification_date', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();
        
    return Inertia::render('production/egg-classification/List', [
        'classifications' => $classifications,
        'filters' => $request->only(['search', 'per_page', 'page']),
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
            'commercial_eggs' => $commercial_total,
            'rejected_eggs' => $rejected_total,
            'technical_eggs' => $technical_total,
            'created_by'           => Auth::id(),
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
            if ($qty > 0 || $request->input($key . '_note')) {
                $eggType = EggType::where('name', $key)->where('category', 1)->first();
                if ($eggType) {
                    EggClassificationRejected::create([
                        'classification_id' => $classification->id,
                        'egg_type_id' => $eggType->id,
                        'quantity' => $qty ?? 0,
                        'note' => $request->input($key . '_note'),
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
            if ($qty > 0 || $request->input($key . '_note')) {
                $eggType = EggType::where('name', $key)->where('category', 2)->first();
                if ($eggType) {
                    EggClassificationTechnical::create([
                        'classification_id' => $classification->id,
                        'egg_type_id' => $eggType->id,
                        'quantity' => $qty ?? 0,
                        'note' => $request->input($key . '_note'),
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
}
