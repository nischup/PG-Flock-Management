<?php

namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\Shed\ShedReceive;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Ps\PsFirmReceive;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MovementAdjustment;
use Illuminate\Support\Facades\Auth;

class ProductionShedReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       $search = $request->search;

        // Fetch shed receives with related flock, shed, and company
        $shedReceives = ShedReceive::with(['flock', 'shed', 'company']) 
        
            ->where('receive_type', 'chicks')
            ->when($search, function ($query, $search) {
                $query->whereHas('flock', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('shed', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhere('job_no', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        // Return to Inertia page
        return inertia('production/shed-receive/List', [
            'psReceives' => $shedReceives,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $firmReceives = PsFirmReceive::with(['flock', 'company'])
            ->where('receive_type', 'pcs')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fr) {
                return [
                    'id' => $fr->id,
                    'transaction_no' => $fr->transaction_no,
                    'flock_id' => $fr->flock_id,
                    'flock_name' => $fr->flock?->name ?? 'N/A',
                    'receiving_company_id' => $fr->receiving_company_id,
                    'firm_female_qty' => $fr->firm_female_qty,
                    'firm_male_qty' => $fr->firm_male_qty,
                    'firm_total_qty' => $fr->firm_total_qty,
                    'remarks' => $fr->remarks,
                ];
            });

        // Fetch all flocks
        $flocks = Flock::select('id', 'name')->get();

        // Fetch all companies
        $companies = Company::select('id', 'name')->get();
        $sheds = Shed::select('id', 'name')->get();
        // Render the Inertia page

        return Inertia::render('production/shed-receive/Create', [
            'firmReceives' => $firmReceives,
            'flocks' => $flocks,
            'companies' => $companies,
            'sheds'=>$sheds,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $firmReceive = PsFirmReceive::findOrFail($request->job_id);
        


        $shedReceive = ShedReceive::create([
            'receive_id'       => $request->job_id,   // firm receive reference
            'job_no'           => $firmReceive->job_no,
            'transaction_no'   => $firmReceive->transaction_no,
            'company_id'       => $firmReceive->receiving_company_id,
            'flock_id'         => $request->flock_id,
            'flock_name'       => $request->flock_name,
            'shed_id'          => $request->shed_id,
            'shed_female_qty'  => $request->shed_female_qty,
            'shed_male_qty'    => $request->shed_male_qty,
            'shed_total_qty'   => $request->shed_total_qty,
            'receive_type'     => "chicks",
            'remarks'          => $request->remarks,
            'created_by'       => Auth::id(),
            'status'           => $request->status ?? 1,
        ]);





        if ($request->shed_sortage_box_qty > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $request->flock_id,
                'flock_no' =>    $request->flock_no, // fetch from batch or pass from request
                'stage'      =>  3,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $shedReceive->id,
                'type'       =>  3,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->shed_sortage_male_box ?? 0,
                'female_qty' =>  $request->shed_sortage_female_box ?? 0,
                'total_qty'  =>  $request->shed_sortage_box_qty ?? 0,
                'date'       =>  date('Y-m-d'),
                'remarks'    => "Sortage when shed receive",
            ]);
        }

        if ($request->shed_excess_box_qty > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $firmReceive->flock_id,
                'flock_no'   =>  $firmReceive->flock_no, // fetch from batch or pass from request
                'stage'      =>  3,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $shedReceive->id,
                'type'       =>  2,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->shed_excess_male_box ?? 0,
                'female_qty' =>  $request->shed_excess_female_box ?? 0,
                'total_qty'  =>  $request->shed_excess_box_qty ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Excess when shed receive",
            ]);
        }

        if ($request->shed_total_mortality > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $firmReceive->flock_id,
                'flock_no' =>    $firmReceive->flock_no, // fetch from batch or pass from request
                'stage'      =>  3,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $shedReceive->id,
                'type'       =>  1,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->shed_male_mortality ?? 0,
                'female_qty' =>  $request->shed_female_mortality ?? 0,
                'total_qty'  =>  $request->shed_total_mortality ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Mortality when shed receive",
            ]);
        }
























        return redirect()
            ->route('production-shed-receive.index')
            ->with('success', 'Production Shed Receive created successfully!');
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
