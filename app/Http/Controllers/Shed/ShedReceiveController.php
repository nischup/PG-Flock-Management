<?php

namespace App\Http\Controllers\Shed;
use App\Http\Controllers\Controller;
use App\Models\Shed\ShedReceive;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Ps\PsFirmReceive;
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
        $companies = Company::select('id', 'name')->where('status', '1')->get();

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
        
        $firmReceives = PsFirmReceive::with(['flock', 'company'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fr) {
                return [
                    'id' => $fr->id,
                    'job_no' => $fr->job_no,
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

        // Render the Inertia page

        return Inertia::render('shed/shed-receive/Create', [
             'firmReceives' => $firmReceives,
            'flocks' => $flocks,
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
