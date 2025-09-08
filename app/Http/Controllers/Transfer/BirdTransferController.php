<?php

namespace App\Http\Controllers\Transfer;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Master\Company;
use App\Models\Shed\BatchAssign;
use App\Models\Ps\PsFirmReceive;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BirdTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('transfer/bird-transfer/List');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(BatchAssign $batchAssign,$batchId)
    {
       
       
    // Get batch info
        $batchAssign = BatchAssign::with(['flock', 'company'])->findOrFail($batchId);

        // Calculate actual birds after mortality and errors
        $femaleQty = $batchAssign->batch_female_qty
                     - ($batchAssign->batch_female_mortality ?? 0)
                     - ($batchAssign->batch_excess_female ?? 0)
                     - ($batchAssign->batch_sortage_female ?? 0);

        $maleQty = $batchAssign->batch_male_qty
                   - ($batchAssign->batch_male_mortality ?? 0)
                   - ($batchAssign->batch_excess_male ?? 0)
                   - ($batchAssign->batch_sortage_male ?? 0);

        $totalQty = $femaleQty + $maleQty;

        // Pass flocks, companies, and batch info
        return Inertia::render('transfer/bird-transfer/Create', [
            'batchAssign' => $batchAssign,
            'flocks' => Flock::all(),
            'companies' => Company::all(),
            'sheds' => Shed::all(),
            'actualQty' => [
                'female' => $femaleQty,
                'male' => $maleQty,
                'total' => $totalQty,
            ],
        ]);

    
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
       
        $batch = BatchAssign::findOrFail($request->batch_assign_id);

        // Auto calculations
        $transferTotal = ($request->transfer_female_qty ?? 0) + ($request->transfer_male_qty ?? 0);
        $medicalTotal  = ($request->medical_female_qty ?? 0) + ($request->medical_male_qty ?? 0);

        $currentFemale = $batch->batch_female_qty - $batch->batch_female_mortality;
        $currentMale   = $batch->batch_male_qty - $batch->batch_male_mortality;

        $deviationFemale = $currentFemale - ($request->transfer_female_qty ?? 0) - ($request->medical_female_qty ?? 0);
        $deviationMale   = $currentMale - ($request->transfer_male_qty ?? 0) - ($request->medical_male_qty ?? 0);
        $deviationTotal  = $deviationFemale + $deviationMale;

        // Save transfer
        $transfer = BirdTransfer::create([
            'batch_assign_id'       => $batch->id,
            'job_no'                => $batch->job_no,
            'flock_no'              => $batch->flock_no,
            'flock_id'              => $batch->flock_id,
            'from_company_id'       => $request->from_company_id,
            'to_company_id'         => $request->to_company_id,
            'from_shed_id'          => $request->from_shed_id,
            'to_shed_id'            => $request->to_shed_id,
            'transfer_date'         => $request->transfer_date,

            'transfer_female_qty'   => $request->transfer_female_qty ?? 0,
            'transfer_male_qty'     => $request->transfer_male_qty ?? 0,
            'transfer_total_qty'    => $transferTotal,

            'medical_female_qty'    => $request->medical_female_qty ?? 0,
            'medical_male_qty'      => $request->medical_male_qty ?? 0,
            'medical_total_qty'     => $medicalTotal,

            'deviation_female_qty'  => $deviationFemale,
            'deviation_male_qty'    => $deviationMale,
            'deviation_total_qty'   => $deviationTotal,

            'created_by'            => Auth::id(),
            'status'                => 1,
        ]);

        // PsFirmReceive::create([
        //     'ps_receive_id'        => $transfer->id, // link to transfer
        //     'job_no'               =>  null,
        //     'receive_type'         => 'chicks', // indicate it's a transfer
        //     'source_type'          => 'transfer',
        //     'source_id'            => $transfer->id,
        //     'flock_id'             => $request->flock_id,
        //     'flock_name'           => $transfer->flock_no ?? '', // if you have flock relationship
        //     'receiving_company_id' => $request->to_company_id,
        //     'firm_female_qty'      => $request->transfer_female_qty,
        //     'firm_male_qty'        => $request->transfer_male_qty,
        //     'firm_total_qty'       => $request->transfer_male_qty + $request->transfer_female_qty,
        //     'remarks'              => $request->transfer_note ?? null,
        //     'created_by'           => Auth::id(),
        //     'status'               => 1,
        // ]);

        return redirect()->route('batch-assign.index')->with('success', 'Bird transfer recorded successfully.');
        
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
