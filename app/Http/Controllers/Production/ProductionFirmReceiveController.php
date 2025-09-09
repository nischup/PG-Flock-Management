<?php

namespace App\Http\Controllers\Production;
use App\Models\BirdTransfer\BirdTransfer;
use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use Illuminate\Http\Request;
use App\Models\Ps\PsFirmReceive;
use App\Models\Ps\PsReceive;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProductionFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $transfers = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
        ->latest()
        ->get();

        $companies = Company::all();
        $flocks = Flock::all();
        
        return inertia('production/firm-receive/List', [
            'transferBirds' => $transfers,
            'companies' => $companies,
            'flocks' => $flocks,
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
        
        
        $companyInfo = Company::findOrFail($request->receive_company_id);
        $flockInfo = Flock::findOrFail($request->flock_id);


           
        $transferBird = BirdTransfer::find($request->transfer_bird_id);

            

        $job_no = $transferBird->job_no;

            // 2️⃣ Get ps_receive_id from ps_receives
        $psReceive = PsFirmReceive::where('job_no', $job_no)->first();
        
        
        
       
       
        // $jobNo = "{$request->transfer_bird_id}-{$companyInfo->short_name}-{$flockInfo->name}";
        
       
        
        $firmReceive = PsFirmReceive::create([
            'ps_receive_id'        => $psReceive->ps_receive_id ,
            'receive_type'         => 'chicks', // indicate it's a transfer
            'source_type'          => 'transfer',
            'source_id'            => $request->transfer_bird_id,
            'flock_id'             => $request->flock_id,
            'flock_name'           =>  $flockInfo->name ?? '', // if you have flock relationship
            'receiving_company_id' => $request->receive_company_id,
            'firm_female_qty'      => $request->receive_female_qty,
            'firm_male_qty'        => $request->receive_male_qty,
            'firm_total_qty'       => $request->receive_total_qty,
            'remarks'              => $request->note ?? null,
            'created_by'           => Auth::id(),
            'status'               => 1,
        ]);


      
        $insertId = $firmReceive->id;

        $jobNo = "{$psReceive->ps_receive_id}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['job_no' => $jobNo]);
        
        return redirect()->route('production-firm-receive.index')->with('success', 'Bird Receive successfully.');    
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
