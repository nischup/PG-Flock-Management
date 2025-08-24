<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Master\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PsFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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


        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('ps/ps-firm-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
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
