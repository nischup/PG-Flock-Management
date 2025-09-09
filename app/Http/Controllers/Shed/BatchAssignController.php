<?php

namespace App\Http\Controllers\Shed;

use App\Http\Controllers\Controller;
use App\Models\Shed\ShedReceive;
use App\Models\Shed\BatchAssign;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Level;
use App\Models\Master\Batch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BatchAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        
        $batchAssigns = BatchAssign::with(['shedReceive', 'flock', 'company', 'shed'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($batch) {
                return [
                    'id' => $batch->id,
                    'job_no' => $batch->job_no,
                    'flock_no' => $batch->flock_no,
                    'flock_name' => $batch->flock->name ?? '',
                    'company_name' => $batch->company->name ?? '',
                    'shed_name' => $batch->shed->name ?? '',
                    'level' => $batch->level,
                    'batch_no' => $batch->batch_no,
                    'batch_female_qty' => $batch->batch_female_qty,
                    'batch_male_qty' => $batch->batch_male_qty,
                    'batch_total_qty' => $batch->batch_total_qty,
                    'created_at' => $batch->created_at->format('Y-m-d'),
                ];
            });
        
        
        
        return Inertia::render('shed/batch-assign/List', [
            'batchAssigns' => $batchAssigns
        ]);
        
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        
    // Get all Shed Receives with relations
    $shedReceives = ShedReceive::with(['flock:id,name', 'shed:id,name', 'company:id,name'])
        ->get()
        ->map(function($shed) {
            return [
                'id' => $shed->id,
                'transaction_no' => $shed->transaction_no,
                'flock_id' => $shed->flock_id,
                'flock' => $shed->flock?->name,
                'shed_id' => $shed->shed_id,
                'shed' => $shed->shed?->name,
                'company_id' => $shed->company_id,
                'company' => $shed->company?->name,
                'shed_female_qty' => $shed->shed_female_qty ?? 0,
                'shed_male_qty' => $shed->shed_male_qty ?? 0,
                'shed_total_qty' => $shed->shed_total_qty ?? 0,
                'receive_type'=> $shed->receive_type ?? '',
                'created_by'       => Auth::id(),
            ];
        });


      
    // Flocks (for batch assign form)
    $flocks = Flock::select('id', 'name')->get();

    // Companies (if needed in assign)
    $companies = Company::select('id', 'name')->get();

    // Levels from database
    $levels = Level::where('status', true)->select('id', 'name')->get();

    // Batches from database
    $batches = Batch::where('status', true)->select('id', 'name')->get();

    return Inertia::render('shed/batch-assign/Create', [
        'shedReceives' => $shedReceives,
        'flocks' => $flocks,
        'companies' => $companies,
        'levels' => $levels,
        'batches' => $batches,
    ]);
       
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $batches = $request->batches ?? [];

        $shedReceive = ShedReceive::findOrFail($request->shed_receive_id);
        
        foreach ($batches as $batch) {
            BatchAssign::create([
                'shed_receive_id'        => $shedReceive->id ?? null,
                'job_no'                 => $shedReceive->job_no ?? null,
                'transaction_no'         => $shedReceive->transaction_no ?? null,
                'flock_no'               => $shedReceive->flock_name ?? 0,
                'flock_id'               => $shedReceive->flock_id?? null,
                'company_id'             => $shedReceive->company_id ?? null,
                'shed_id'                => $shedReceive->shed_id ?? null,
                'level'                  => $batch['level'] ?? null,
                'batch_no'               => $batch['batch_no'] ?? 1,
                'batch_female_qty'       => $batch['batch_female_qty'] ?? 0,
                'batch_male_qty'         => $batch['batch_male_qty'] ?? 0,
                'batch_total_qty'        => ($batch['batch_female_qty'] ?? 0) + ($batch['batch_male_qty'] ?? 0),
                'batch_female_mortality' => $batch['batch_female_mortality'] ?? 0,
                'batch_male_mortality'   => $batch['batch_male_mortality'] ?? 0,
                'batch_total_mortality'  => ($batch['batch_female_mortality'] ?? 0) + ($batch['batch_male_mortality'] ?? 0),
                'batch_excess_male'      => $batch['batch_excess_male'] ?? null,
                'batch_excess_female'    => $batch['batch_excess_female'] ?? 0,
                'batch_sortage_male'     => $batch['batch_sortage_male'] ?? null,
                'batch_sortage_female'   => $batch['batch_sortage_female'] ?? 0,
                'percentage'             => $batch['percentage'] ?? 0,
                'created_by'             => Auth::id(),
            ]);
        }

        return redirect()
            ->route('batch-assign.index')
            ->with('success', 'Batch assign successfully done!');

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
