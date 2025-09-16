<?php

namespace App\Http\Controllers\Production;
use App\Models\BirdTransfer\BirdTransfer;
use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
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
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);
        $fromCompanyId = $request->get('from_company_id');
        $flockId = $request->get('flock_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query for bird transfers with status 1
        $query = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
        ->where('status', 1) // Only fetch transfers with status 1
        ->latest();

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_no', 'like', "%{$search}%")
                  ->orWhere('transaction_no', 'like', "%{$search}%")
                  ->orWhereHas('flock', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('fromCompany', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('toCompany', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($fromCompanyId) {
            $query->where('from_company_id', $fromCompanyId);
        }

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        if ($dateFrom) {
            $query->whereDate('transfer_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transfer_date', '<=', $dateTo);
        }

        // Get paginated results
        $transfers = $query->paginate($perPage)->withQueryString();

        $companies = Company::all();
        $flocks = Flock::all();
        $sheds = Shed::all();
        
        return inertia('production/firm-receive/List', [
            'transferBirds' => $transfers,
            'companies' => $companies,
            'flocks' => $flocks,
            'sheds' => $sheds,
            'filters' => $request->only(['search', 'per_page', 'from_company_id', 'flock_id', 'date_from', 'date_to']),
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
        
        
       // PsFirmReceive::where('job_no', $job_no)->update(['status' => 0]);
       
       
        // $jobNo = "{$request->transfer_bird_id}-{$companyInfo->short_name}-{$flockInfo->name}";
        
       
        
        $firmReceive = PsFirmReceive::create([
            'ps_receive_id'        => $psReceive->ps_receive_id ,
            'receive_type'         => 'pcs', // indicate it's a transfer
            'source_type'          => 'transfer',
            'job_no'               => $psReceive->job_no,
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

        $transactionNo = "{$insertId}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['transaction_no' => $transactionNo]);
        
        return redirect()->route('production-farm-receive.index')->with('success', 'Bird Receive successfully.');    
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
