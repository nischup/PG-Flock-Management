<?php

namespace App\Http\Controllers\Shed;
use App\Http\Controllers\Controller;
use App\Models\Shed\ShedReceive;
use App\Models\Master\Company;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShedReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $psReceives = PsReceive::with('chickCounts')
        ->get()
        ->map(function($ps) {
            return [
                'id' => $ps->id,
                'pi_no' => $ps->pi_no,
                'total_chicks_qty' => $ps->chickCounts->ps_total_qty ?? 0,
                'total_box_qty' => $ps->chickCounts->ps_total_re_box_qty ?? 0,
                'ps_challan_box_qty' => $ps->chickCounts->ps_challan_box_qty ?? 0,
                'male_box_qty' => $ps->chickCounts->ps_male_rec_box ?? 0,
                'female_box_qty' => $ps->chickCounts->ps_female_rec_box ?? 0,
            ];
        });


        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('shed/shed-receive/List', [
            'psReceives' => $psReceives,
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $psReceives = PsReceive::with('chickCounts')
        ->get()
        ->map(function($ps) {
            return [
                'id' => $ps->id,
                'pi_no' => $ps->pi_no,
                'total_chicks_qty' => $ps->chickCounts->ps_total_qty ?? 0,
                'total_box_qty' => $ps->chickCounts->ps_total_re_box_qty ?? 0,
                'ps_challan_box_qty' => $ps->chickCounts->ps_challan_box_qty ?? 0,
                'male_box_qty' => $ps->chickCounts->ps_male_rec_box ?? 0,
                'female_box_qty' => $ps->chickCounts->ps_female_rec_box ?? 0,
            ];
        });


        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('shed/shed-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
        ]);
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
    public function show(ShedReceive $shedReceive)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShedReceive $shedReceive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShedReceive $shedReceive)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShedReceive $shedReceive)
    {
        //
    }
}
