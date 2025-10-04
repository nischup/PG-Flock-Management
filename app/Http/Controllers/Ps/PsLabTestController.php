<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsLabTest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PsLabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $labTests = PsLabTest::with('psReceive') // eager-load parent
                ->when($request->search, fn($q) =>
                    $q->whereHas('psReceive', fn($q2) =>
                        $q2->where('pi_no', 'like', "%{$request->search}%")
                        ->orWhere('order_no', 'like', "%{$request->search}%")
                    )
                )
                ->paginate($request->per_page ?? 10)
                ->withQueryString();

            
            return Inertia::render('ps/ps-lab-test/List', [
                'labTests' => $labTests,
                'filters' => $request->only(['search', 'per_page']),
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $psReceives = PsReceive::with('chickCounts', 'labTransfers')
        ->get()
        ->map(function ($ps) {
            return [
                'id' => $ps->id,
                'pi_no' => $ps->pi_no,
                'order_no' => $ps->order_no,
                'created_at' => $ps->created_at->format('Y-m-d'),
                'total_chicks_qty' => $ps->chickCounts->ps_total_qty ?? 0,
                'total_box_qty' => $ps->chickCounts->ps_total_re_box_qty ?? 0,
                'male_box_qty' => $ps->chickCounts->ps_male_rec_box ?? 0,
                'female_box_qty' => $ps->chickCounts->ps_female_rec_box ?? 0,
                'labTest' => $ps->labTransfers, // important
            ];
        });

        return Inertia::render('ps/ps-lab-test/Create', [
            'psReceives' => $psReceives,
        ]);

        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ps_receive_id' => 'required|exists:ps_receives,id',
            'female_qty' => 'required|numeric|min:0',
            'male_qty' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $labTest = PsLabTest::updateOrCreate(
            ['ps_receive_id' => $request->ps_receive_id],
            [
                'lab_type' => $request->lab_type,
                'female_qty' => $request->female_qty,
                'male_qty' => $request->male_qty,
                'total_qty' => $request->female_qty + $request->male_qty,
                'notes' => $request->notes,
            ]
        );

         return redirect()->route('ps-receive.index')->with('success', 'Lab Test saved successfully!');
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
            $labTest = PsLabTest::with('psReceive')->findOrFail($id);

            $labTestData = [
                'id' => $labTest->id,
                'ps_receive_id' => $labTest->ps_receive_id,
                'lab_type' => $labTest->lab_type,
                'lab_send_female_qty' => $labTest->lab_send_female_qty,
                'lab_send_male_qty' => $labTest->lab_send_male_qty,
                'lab_send_total_qty' => $labTest->lab_send_total_qty,
                'lab_receive_female_qty' => $labTest->lab_receive_female_qty,
                'lab_receive_male_qty' => $labTest->lab_receive_male_qty,
                'lab_receive_total_qty' => $labTest->lab_receive_total_qty,
                'mortality_qty' => $labTest->mortality_qty,
                'notes' => $labTest->notes,
                'status' => $labTest->status, // 1=receive, 2=complete
                'created_at' => $labTest->created_at->format('Y-m-d'),

                // bring PI info from ps_receive
                'ps_receive' => [
                    'id' => $labTest->psReceive->id,
                    'pi_no' => $labTest->psReceive->pi_no,
                    'order_no' => $labTest->psReceive->order_no,
                    'order_date' => $labTest->psReceive->order_date,
                    'pi_date' => $labTest->psReceive->pi_date,
                    'lc_no' => $labTest->psReceive->lc_no,
                    'lc_date' => $labTest->psReceive->lc_date,
                    'remarks' => $labTest->psReceive->remarks,
                ]
            ];


            
            return Inertia::render('ps/ps-lab-test/Edit', [
                'labTest' => $labTestData
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $labTest = PsLabTest::findOrFail($id);
        // Access request values directly
      
        //dd($request->lab_receive_total_qty);
        $labReceiveFemale = (int) $request->lab_receive_female_qty;
        $labReceiveMale = (int) $request->lab_receive_male_qty;
        $labReceiveTotal = (int) $request->lab_receive_total_qty; // <-- here
        $mortality = (int) $request->mortality_qty;
        $status = (int) $request->status;
        $notes = $request->notes;
       

        // Update the lab test
        $labTest->update([
            'lab_receive_female_qty' =>$labReceiveFemale,
            'lab_receive_male_qty' => $labReceiveMale,
            'lab_receive_total_qty' =>$labReceiveTotal,
            'mortality_qty' => $mortality ,
            'status' => (int) $status,
            'notes' => $notes,
        ]);

        return redirect()->route('ps-lab-test.index')->with('success', 'Lab Test updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function getData(Request $request)
    {
        $psReceiveId = 1;

        $psReceive = PsReceive::with('chickCounts')->findOrFail($psReceiveId);
        
            // Flatten data from parent + child for modal
            $data = [
                'id' => $psReceive->id,
                'pi_no' => $psReceive->pi_no,
                'pi_date' => $psReceive->pi_date,
                'order_no' => $psReceive->order_no,
                'order_date' => $psReceive->order_date,
                'lc_no' => $psReceive->lc_no,
                'lc_date' => $psReceive->lc_date,
                'supplier_id' => $psReceive->supplier_id,
                'breed_type' => $psReceive->breed_type,
                'country_of_origin' => $psReceive->country_of_origin,
                'transport_type' => $psReceive->transport_type,
                'remarks' => $psReceive->remarks,
                'status' => $psReceive->status,
                // Child fields
                'ps_male_rec_box' => $psReceive->chickCounts->ps_male_rec_box ?? 0,
                'ps_male_qty' => $psReceive->chickCounts->ps_male_qty ?? 0,
                'ps_female_rec_box' => $psReceive->chickCounts->ps_female_rec_box ?? 0,
                'ps_female_qty' => $psReceive->chickCounts->ps_female_qty ?? 0,
                'ps_total_qty' => $psReceive->chickCounts->ps_total_qty ?? 0,
                'ps_total_re_box_qty' => $psReceive->chickCounts->ps_total_re_box_qty ?? 0,
                'ps_challan_box_qty' => $psReceive->chickCounts->ps_challan_box_qty ?? 0,
                'ps_gross_weight' => $psReceive->chickCounts->ps_gross_weight ?? 0,
                'ps_net_weight' => $psReceive->chickCounts->ps_net_weight ?? 0,
            ];

        // Return Inertia render with labTestData
        return Inertia::render('ps/ps-receive/PsReceive', [
            'labTestData' => $data
        ]);
    }
}
