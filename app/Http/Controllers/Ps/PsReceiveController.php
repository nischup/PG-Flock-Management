<?php

namespace App\Http\Controllers\Ps;
use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsReceiveAttachment;
use App\Models\Ps\PsChickCount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Ps\PsLabTest;

class PsReceiveController extends Controller
{
    public function index(Request $request)
    {
        $psReceives = PsReceive::query()
            ->when($request->search, fn($q) => 
                $q->where('pi_no', 'like', "%{$request->search}%")
                  ->orWhere('order_no', 'like', "%{$request->search}%")
            )
            ->paginate($request->per_page ?? 10)
            ->withQueryString();
        
        return Inertia::render('ps/ps-receive/PsReceive', [
            'psReceives' => $psReceives,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('ps/ps-receive/Create');
    }

    public function store(Request $request)
    {
            
        // $request->validate([
        //     // Main PS Receive
        //     //'shipment_type_id'   => 'required|integer|exists:shipment_types,id',
        //     'shipment_type_id'   => 'required|integer',
        //     'pi_no'              => 'required|string|max:50',
        //     'pi_date'            => 'required|date',
        //     'order_no'           => 'nullable|string|max:50',
        //     'order_date'         => 'nullable|date',
        //     'lc_no'              => 'nullable|string|max:50',
        //     'lc_date'            => 'nullable|date',
        //     //'supplier_id'        => 'required|integer|exists:suppliers,id',
        //     'supplier_id'        => 'required|integer',
        //     'breed_type'         => 'required|integer',
        //     'country_of_origin'  => 'required|integer',
        //     'transport_type'     => 'required|integer',
        //     'remarks'            => 'nullable|string|max:500',

        //     // Chick Counts
        //     'ps_male_rec_box'    => 'required|numeric|min:0',
        //     'ps_male_qty'        => 'required|numeric|min:0',
        //     'ps_female_rec_box'  => 'required|numeric|min:0',
        //     'ps_female_qty'      => 'required|numeric|min:0',
        //     'ps_total_qty'       => 'required|numeric|min:0',
        //     'ps_total_re_box_qty'=> 'required|numeric|min:0',
        //     'ps_challan_box_qty' => 'required|numeric|min:0',
        //     'ps_gross_weight'    => 'required|numeric|min:0',
        //     'ps_net_weight'      => 'required|numeric|min:0', // net ≤ gross
        // ]);

        try {
            DB::beginTransaction();

            // 1️⃣ Create main PS Receive
            $psReceive = PsReceive::create([
                'shipment_type_id' => (int) $request->shipment_type_id,
                'pi_no' => $request->pi_no,
                'pi_date' => $request->pi_date,
                'order_no' => $request->order_no,
                'order_date' => $request->order_date,
                'lc_no' => $request->lc_no,
                'lc_date' => $request->lc_date,
                'supplier_id' => (int) ($request->supplier_id ?? 0),
                'breed_type' => (int) ($request->breed_type ?? 0),
                'country_of_origin' => (int) ($request->country_of_origin ?? 0),
                'transport_type' => (int) ($request->transport_type ?? 0),
                'remarks' => $request->remarks,
                'status' => 1,
                'created_by' =>Auth::id()
            ]);

            $psReceive->chickCounts()->create([
                'ps_male_rec_box'=>(float)$request->ps_male_rec_box,
                'ps_male_qty'=>(float)$request->ps_male_qty,
                'ps_female_rec_box'=>(float)$request->ps_female_rec_box,
                'ps_female_qty'=>(float)$request->ps_female_qty,
                'ps_total_qty'=>(float)$request->ps_total_qty,
                'ps_total_re_box_qty'=>(float)$request->ps_total_re_box_qty,
                'ps_challan_box_qty'=>(float)$request->ps_challan_box_qty,
                'ps_gross_weight'=>(float)$request->ps_gross_weight,
                'ps_net_weight'=>(float)$request->ps_net_weight,
            ]);

           
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {
                    $path = $uploadedFile->store('ps_receive_files'); // storage/app/ps_receive_files
                    $psReceive->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $uploadedFile->getClientOriginalExtension(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive created successfully.');
        } catch (\Throwable $e) {
         
            DB::rollBack();
            Log::error('PS Receive create failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Failed to create PS Receive. Please try again.']);
        }
    }

    public function edit($id)
    {
        $psReceive = PsReceive::with('chickCounts')->findOrFail($id);
        
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

        // Return Inertia response instead of JSON
        return Inertia::render('ps/ps-receive/PsReceive', [
            'psReceive' => $data
        ]);
    }

    public function update(Request $request, PsReceive $psReceive)
    {
        $request->validate([
            'pi_no'        => 'required|string|max:255',
            'pi_date'      => 'nullable|date',
            'order_no'     => 'required|string|max:255',
            'order_date'   => 'nullable|date',
            'lc_no'        => 'nullable|string|max:255',
            'lc_date'      => 'nullable|date',
            'shipment_type_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $psReceive->update($request->only([
                'pi_no', 'pi_date', 'order_no', 'order_date', 'lc_no', 'lc_date', 'shipment_type_id'
            ]));

            DB::commit();

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PS Receive update failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Failed to update PS Receive. Please try again.']);
        }
    }

    public function destroy(PsReceive $psReceive)
    {
        try {
            $psReceive->delete();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'PS Receive deleted successfully.']);
            }

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('PS Receive delete failed', ['error' => $e->getMessage()]);

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete PS Receive.'], 500);
            }

            return back()->withErrors(['general' => 'Failed to delete PS Receive.']);
        }
    }

    public function storelab(){
        $labTest = PsLabTest::updateOrCreate(
            ['ps_receive_id' => 1],
            [
                'lab_type' => 1,
                'female_qty' => 1,
                'male_qty' => 1,
                'total_qty' => 1 + 1,   
                'notes' => "test",
            ]
        );

         return redirect()->route('ps-receive.index')->with('success', 'Lab Test saved successfully!');
    }
}
