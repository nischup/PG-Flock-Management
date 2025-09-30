<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\FirmLabTest;
use App\Models\Master\Batch;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Project;
use App\Models\Master\Shed;
use App\Models\MovementAdjustment;
use App\Models\Shed\BatchAssign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            'fromProject',
            'toProject',
            'fromShed',
            'toShed',
            'batchAssign.shed',
            'batchAssign.batch',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('transfer/bird-transfer/List', [
            'birdTransfers' => $birdTransfers,
            'companies' => Company::all(),
            'flocks' => Flock::all(),
            'sheds' => Shed::all(),
            'breedTypes' => \App\Models\Master\BreedType::all(),
            'countries' => \App\Models\Country::all(),
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
            'projects' => Project::all(),
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

            // Save transfer
            $transfer = BirdTransfer::create([
                'batch_assign_id' => $batch->id,
                'job_no' => $batch->job_no,
                'transaction_no' => $batch->transaction_no,
                'flock_no' => $batch->flock_no,
                'flock_id' => $batch->flock_id,
                'project_id' => $batch->project_id,
                'from_company_id' => $request->from_company_id,
                'to_company_id' => $request->to_company_id,
                'to_project_id' => $request->to_project_id,
                'from_shed_id' => $request->from_shed_id,
                'to_shed_id' => $request->to_shed_id,
                'transfer_date' => $request->transfer_date,

                'transfer_female_qty' => $request->transfer_female_qty ?? 0,
                'transfer_male_qty' => $request->transfer_male_qty ?? 0,
                'transfer_total_qty' => $request->transfer_total_qty,

                'medical_female_qty' => $request->medical_female_qty ?? 0,
                'medical_male_qty' => $request->medical_male_qty ?? 0,
                'medical_total_qty' => $request->medical_total_qty,

                'deviation_female_qty' => $request->deviation_male_qty,
                'deviation_male_qty' => $request->deviation_female_qty,
                'deviation_total_qty' => $request->deviation_total_qty,

                'shipment_type_id' => $ps->shipment_type_id,
                'lc_no' => $ps->lc_no,
                'breed_type' => $batch->breed_type,
                'country_of_origin' => $ps->country_of_origin,
                'transport_type' => $ps->transport_type,

                'created_by' => Auth::id(),
                'status' => 1,
            ]);

            if ($request->deviation_total_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $batch->flock_id,
                    'flock_no' => $batch->flock_no,
                    'transaction_no' => $batch->transaction_no,
                    'job_no' => $batch->job_no,  // fetch from batch or pass from request
                    'stage' => 5,                  // 5 = Bird Transfer stage
                    'stage_id' => $transfer->id,
                    'type' => 4,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                    'male_qty' => $request->deviation_male_qty ?? 0,
                    'female_qty' => $request->deviation_female_qty ?? 0,
                    'total_qty' => $request->deviation_total_qty ?? 0,
                    'date' => $request->transfer_date,
                    'remarks' => 'Deviation When Transfer',
                ]);
            }

            if ($request->medical_total_qty > 0) {
                FirmLabTest::create([
                    'batch_assign_id' => $batch->flock_id,
                    'firm_lab_send_female_qty' => $request->medical_female_qty ?? 0, // fetch from batch or pass from request
                    'firm_lab_send_male_qty' => $request->medical_male_qty ?? 0,                 // 5 = Bird Transfer stage
                    'firm_lab_send_total_qty' => $request->medical_total_qty,
                    'firm_lab_receive_female_qty' => 0,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                    'firm_lab_receive_male_qty' => 0,
                    'firm_lab_receive_total_qty' => 0,
                    'note' => '',
                    'remarks' => '',
                    'firm_lab_type' => 2,
                ]);
            }

            if ($batch->stage == 1) {
                $batch->stage = 2; // Growing

            } elseif ($batch->stage == 2) {
                $batch->status = 0; // Laying
                $batch->transfer_date = date('Y-m-d');
            }

            // 3. Save updated stage
            $batch->save();
        }

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

        // Load transfer with relations
        $item = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
            'batchAssign.batch',
        ])->findOrFail($id);

        $batchAssign = $item->batchAssign;

        // Get batch challan quantities
        $challanFemale = $batchAssign->batch_female_qty ?? 0;
        $challanMale = $batchAssign->batch_male_qty ?? 0;
        $challanTotal = $batchAssign->batch_total_qty ?? ($challanFemale + $challanMale);

        // ✅ Map breed_type IDs to names (same as PsFirmReceiveController)
        $breeds = \App\Models\Master\BreedType::pluck('name', 'id')->toArray();

        $breedtype = $item->breed_type ?? [];
        if (! is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype]; // wrap single ID into array
        }

        $breedAll = array_map(fn ($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);

        // Prepare batch data
        $batches = [
            [
                'batch_no' => $batchAssign?->batch?->name ?? 'N/A',
                'challan_female' => $challanFemale,
                'challan_male' => $challanMale,
                'challan_total' => $challanTotal,
                'physical_female' => $item->transfer_female_qty,
                'physical_male' => $item->transfer_male_qty,
                'total' => $item->transfer_total_qty,
                'breed_name' => $breedName,
                'lc_no' => $item->lc_no ?? 'N/A',
                'medical_female' => $item->medical_female_qty ?? 0,
                'medical_male' => $item->medical_male_qty ?? 0,
                'medical_total' => ($item->medical_female_qty ?? 0) + ($item->medical_male_qty ?? 0),
                'deviation_female' => $item->transfer_female_qty - $challanFemale,
                'deviation_male' => $item->transfer_male_qty - $challanMale,
                'deviation_total' => $item->transfer_total_qty - $challanTotal,
                'remarks' => $item->remarks ?? 'N/A',
            ],
        ];

        // Prepare data for PDF
        $data = [
            'job_no' => $item->job_no,
            'transaction_no' => $item->transaction_no,
            'flock_name' => $item->flock->name ?? '-',
            'from_company_name' => $item->fromCompany->name ?? '-',
            'to_company_name' => $item->toCompany->name ?? '-',
            'firm_male_qty' => $item->transfer_male_qty,
            'firm_female_qty' => $item->transfer_female_qty,
            'firm_total_qty' => $item->transfer_total_qty,
            'remarks' => $item->remarks ?? '-',
            'receive_date' => optional($item->transfer_date)->format('Y-m-d'),
            'source_type' => 'transfer',
            'source_id' => $item->id,
            'breed_type' => $breedName, // ✅ added here
            'batches' => $batches,
            'generatedAt' => now(),
        ];

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.bird-transfer.bird-transfer-row', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("bird-transfer-row-{$item->id}.pdf")
            : $pdf->stream("bird-transfer-row-{$item->id}.pdf");
    }
}
