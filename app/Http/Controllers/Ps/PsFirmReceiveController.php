<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsFirmReceive;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


class PsFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $psFirmReceives = PsFirmReceive::with(['flock', 'company'])
    ->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('psReceive', fn($q2) => 
                $q2->where('pi_no', 'like', "%{$search}%")
            )
            ->orWhereHas('flock', fn($q3) => 
                $q3->where('name', 'like', "%{$search}%")
            );
        });
    })
    ->paginate($request->per_page ?? 10)
    ->withQueryString();


        

    return Inertia::render('ps/ps-firm-receive/List', [
        'psFirmReceives' => $psFirmReceives->through(fn($item) => [
            'id' => $item->id,
            'job_no' => $item->job_no,
            'flock_name' => $item->flock->name ?? '-',
            'company_name' => $item->company->name ?? '-',
            'pi_no' => $item->psReceive->pi_no ?? '-',
            'firm_male_qty' => $item->firm_male_qty,
            'firm_female_qty' => $item->firm_female_qty,
            'firm_total_qty' => $item->firm_total_qty,
            'remarks' => $item->remarks,
            'receive_date' => $item->created_at,
            'created_by' => $item->created_by,
            'status' => $item->status,
        ]),
        'filters' => $request->only(['search', 'per_page']),
    ]);

        return Inertia::render('ps/ps-firm-receive/List', [
            'psReceives' => $psReceives,
            'companies' => $companies,
            'flocks' => $flocks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         // Fetch all PS Receives (you may filter by status if needed)
        $psReceives = PsReceive::with('chickCounts', 'labTransfers')
        ->get()
        ->map(function($ps) {
            return [
                'id' => $ps->id,
                'pi_no' => $ps->pi_no,
                'pi_date' => optional($ps->pi_date)->format('Y-m-d'),
                'order_no' => $ps->order_no,
                'order_date' => optional($ps->order_date)->format('Y-m-d'),
                'lc_no' => $ps->lc_no,
                'lc_date' => optional($ps->lc_date)->format('Y-m-d'),
                'shipment_type_id' => $ps->shipment_type_id,
                'supplier_id' => $ps->supplier_id,
                'breed_type' => $ps->breed_type,
                'country_of_origin' => $ps->country_of_origin,
                'transport_type' => $ps->transport_type,
                'company_id' => $ps->company_id,
                'remarks' => $ps->remarks,
                'created_at' => $ps->created_at->format('Y-m-d'),

                // Chick counts
                'total_chicks_qty' => $ps->chickCounts->ps_total_qty ?? 0,
                'total_box_qty' => $ps->chickCounts->ps_total_re_box_qty ?? 0,
                'ps_challan_box_qty' => $ps->chickCounts->ps_challan_box_qty ?? 0,
                'male_box_qty' => $ps->chickCounts->ps_male_rec_box ?? 0,
                'female_box_qty' => $ps->chickCounts->ps_female_rec_box ?? 0,
                'male_chicks' => $ps->chickCounts->ps_male_qty ?? 0,
                'female_chicks' => $ps->chickCounts->ps_female_qty ?? 0,
                'gross_weight' => $ps->chickCounts->ps_female_qty ?? 0,
                'net_weight' => $ps->chickCounts->ps_net_weight ?? 0,
                'Note' => $ps->chickCounts->ps_gross_weight ?? 0,
                'labTest' => $ps->labTransfers, // important
            ];
        });

        $flocks = Flock::select('id', 'name')->get();
        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('ps/ps-firm-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
            'flocks' => $flocks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
       
     
        
       
        $jobNo = "PS{$request->ps_receive_id}-RC{$request->receiving_company_id}-F{$request->flock_id}";

        $firmReceive = PsFirmReceive::create([
            'ps_receive_id' => $request->ps_receive_id,
            'job_no' => $jobNo,
            'receipt_type' => 'box',
            'source_type' => 'psreceive',
            'source_id' => $request->ps_receive_id,
            'flock_id' => $request->flock_id,
            'flock_name' => $request->flock_name,
            'receiving_company_id' => $request->receiving_company_id,
            'firm_female_qty' => $request->firm_female_box_qty,
            'firm_male_qty' => $request->firm_male_box_qty,
            'firm_total_qty' => $request->firm_total_box_qty,
            'remarks' => $request->remarks,
            'created_by' => Auth::id(),
            'status' => $request->status ?? 1,
        ]);


        
        return redirect()->back()->with('success', 'Firm Receive created successfully!');
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
