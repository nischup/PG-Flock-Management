<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\Batch;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Ps\PsFirmReceive;
use App\Models\Shed\BatchAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MovementAdjustment;

class BirdTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $birdTransfers = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('transfer/bird-transfer/List', [
            'birdTransfers' => $birdTransfers,
            'companies' => Company::all(),
            'flocks' => Flock::all(),
            'sheds' => Shed::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(BatchAssign $batchAssign, $batchId)
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

        // Pass flocks, companies, batches, and batch info
        return Inertia::render('transfer/bird-transfer/Create', [
            'batchAssign' => $batchAssign,
            'flocks' => Flock::all(),
            'companies' => Company::all(),
            'sheds' => Shed::all(),
            'batches' => Batch::where('status', true)->get(),
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

        $batch = BatchAssign::with('shedReceive.firmReceive.psReceive')->find($request->batch_assign_id);

    if ($batch && $batch->shedReceive && $batch->shedReceive->firmReceive && $batch->shedReceive->firmReceive->psReceive) {
        $ps = $batch->shedReceive->firmReceive->psReceive;

        // Auto calculations
        $transferTotal = ($request->transfer_female_qty ?? 0) + ($request->transfer_male_qty ?? 0);
        $medicalTotal = ($request->medical_female_qty ?? 0) + ($request->medical_male_qty ?? 0);

        $currentFemale = $batch->batch_female_qty - $batch->batch_female_mortality;
        $currentMale = $batch->batch_male_qty - $batch->batch_male_mortality;

        $deviationFemale = $currentFemale - ($request->transfer_female_qty ?? 0) - ($request->medical_female_qty ?? 0);
        $deviationMale = $currentMale - ($request->transfer_male_qty ?? 0) - ($request->medical_male_qty ?? 0);
        $deviationTotal = $deviationFemale + $deviationMale;

        // Save transfer
        $transfer = BirdTransfer::create([
            'batch_assign_id' => $batch->id,
            'job_no' => $batch->job_no,
            'transaction_no' => $batch->transaction_no,
            'flock_no' => $batch->flock_no,
            'flock_id' => $batch->flock_id,
            'from_company_id' => $request->from_company_id,
            'to_company_id' => $request->to_company_id,
            'from_shed_id' => $request->from_shed_id,
            'to_shed_id' => $request->to_shed_id,
            'transfer_date' => $request->transfer_date,

            'transfer_female_qty' => $request->transfer_female_qty ?? 0,
            'transfer_male_qty' => $request->transfer_male_qty ?? 0,
            'transfer_total_qty' => $transferTotal,

            'medical_female_qty' => $request->medical_female_qty ?? 0,
            'medical_male_qty' => $request->medical_male_qty ?? 0,
            'medical_total_qty' => $medicalTotal,

            'deviation_female_qty' => $deviationFemale,
            'deviation_male_qty' => $deviationMale,
            'deviation_total_qty' => $deviationTotal,


            'shipment_type_id'=>$ps->shipment_type_id,
            'lc_no'=>$ps->lc_no,
            'breed_type'=>$ps->breed_type,
            'country_of_origin'=>$ps->country_of_origin,
            'transport_type'=>$ps->transport_type,


            'created_by' => Auth::id(),
            'status' => 1,
        ]);



        if ($deviationTotal>0) {
            MovementAdjustment::create([
                'flock_id'   =>  $batch->flock_id, 
                'flock_no' =>    $batch->flock_no, // fetch from batch or pass from request
                'stage'      => 5,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $transfer->id,
                'type'       =>  4,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $deviationMale ?? 0,
                'female_qty' =>  $deviationFemale ?? 0,
                'total_qty'  =>  $deviationTotal ?? 0,
                'date'       => $request->transfer_date,
                'remarks'    => "Deviation When Transfer",
            ]);
        }






        if ($batch->stage == 1) {
            $batch->stage = 2; // Growing
            
        } elseif ($batch->stage == 2) {
            $batch->status = 0; // Laying
             $batch->transfer_date = date("Y-m-d");
        }
        // Add your own logic for transition

        // 3. Save updated stage
        $batch->save();
    }
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
    public function downloadRowPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $item = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
            'batchAssign.batch',
        ])->findOrFail($id);

        $batchAssign = $item->batchAssign;

        $challanFemale = $batchAssign->batch_female_qty ?? 0;
        $challanMale   = $batchAssign->batch_male_qty ?? 0;
        $challanTotal  = $batchAssign->batch_total_qty ?? 0;

        $batches = [
            [
                'batch_no'         => $batchAssign?->batch?->name ?? 'N/A',
                'challan_female'   => $challanFemale,
                'challan_male'     => $challanMale,
                'challan_total'    => $challanTotal,
                'physical_female'  => $item->transfer_female_qty,
                'physical_male'    => $item->transfer_male_qty,
                'total'            => $item->transfer_total_qty,
                'breed_name'       => $item->breed_type ?? 'N/A', // corrected line
                'lc_no'            => $item->lc_no ?? 'N/A', // corrected line
                'medical_female'   => $item->medical_female_qty ?? 0,
                'medical_male'     => $item->medical_male_qty ?? 0,
                'medical_total'    => ($item->medical_female_qty ?? 0) + ($item->medical_male_qty ?? 0),
                'deviation_female' => $item->transfer_female_qty - $challanFemale,
                'deviation_male'   => $item->transfer_male_qty - $challanMale,
                'deviation_total'  => $item->transfer_total_qty - $challanTotal,
                'remarks'          => $item->remarks ?? 'N/A',
            ],
        ];
        //dd($batches);

        $data = [
            'job_no'           => $item->job_no,
            'transaction_no'   => $item->transaction_no,
            'flock_name'       => $item->flock->name ?? '-',
            'from_company_name' => $item->fromCompany->name ?? '-',
            'to_company_name'  => $item->toCompany->name ?? '-',
            'firm_male_qty'    => $item->transfer_male_qty,
            'firm_female_qty'  => $item->transfer_female_qty,
            'firm_total_qty'   => $item->transfer_total_qty,
            'remarks'          => $item->remarks ?? '-',
            'receive_date'     => optional($item->transfer_date)->format('Y-m-d'),
            'source_type'      => 'transfer',
            'source_id'        => $item->id,
            'batches'          => $batches,
            'generatedAt'      => now(),
        ];

        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.bird-transfer.bird-transfer-row', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("bird-transfer-row-{$item->id}.pdf")
            : $pdf->stream("bird-transfer-row-{$item->id}.pdf");
    }
}
