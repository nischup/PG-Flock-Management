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
        //     'shipment_type_id' => 'required|integer',
        //     'pi_no' => 'nullable|string|max:255',
        //     'pi_date' => 'nullable|date',
        //     'order_no' => 'nullable|string|max:255',
        //     'order_date' => 'nullable|date',
        //     'lc_no' => 'nullable|string|max:255',
        //     'lc_date' => 'nullable|date',
        //     'supplier_id' => 'nullable|integer',
        //     'breed_type' => 'nullable|integer',
        //     'country_of_origin' => 'nullable|integer',
        //     'transport_type' => 'nullable|integer',
        //     'remarks' => 'nullable|string',

        //     // Chick counts
        //     'ps_male_box' => 'required|integer|min:0',
        //     'ps_male_approximate_qty' => 'required|integer|min:0',
        //     'ps_male_totalqty' => 'required|numeric|min:0',
        //     'ps_male_challan_qty' => 'required|numeric|min:0',
        //     'ps_male_rate' => 'required|numeric|min:0',
        //     'ps_male_value_total' => 'required|numeric|min:0',
        //     'ps_female_box' => 'required|integer|min:0',
        //     'ps_female_approximate_qty' => 'required|integer|min:0',
        //     'ps_female_totalqty' => 'required|numeric|min:0',
        //     'ps_challan_qty' => 'required|numeric|min:0',
        //     'ps_female_rate' => 'required|numeric|min:0',
        //     'ps_female_value_total' => 'required|numeric|min:0',
        //     'ps_totalbox' => 'required|numeric|min:0',
        //     'ps_value_total' => 'required|numeric|min:0',

        //     // Files
        //     'file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
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

            // 2️⃣ Create chick counts
            $psReceive->chickCounts()->create([
                'ps_male_box' => (int) $request->ps_male_box,
                'ps_male_approximate_qty' => (int) $request->ps_male_approximate_qty,
                'ps_male_totalqty' => (float) $request->ps_male_totalqty,
                'ps_male_challan_qty' => (float) $request->ps_male_challan_qty,
                'ps_male_rate' => (float) $request->ps_male_rate,
                'ps_male_value_total' => (float) $request->ps_male_value_total,
                'ps_female_box' => (int) $request->ps_female_box,
                'ps_female_approximate_qty' => (int) $request->ps_female_approximate_qty,
                'ps_female_totalqty' => (float) $request->ps_female_totalqty,
                'ps_challan_qty' => (float) $request->ps_challan_qty,
                'ps_female_rate' => (float) $request->ps_female_rate,
                'ps_female_value_total' => (float) $request->ps_female_value_total,
                'ps_totalbox' => (float) $request->ps_totalbox,
                'ps_value_total' => (float) $request->ps_value_total,
            ]);

            // 3️⃣ Store uploaded files

            dd($request->hasFile('file'));
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

    public function edit(PsReceive $psReceive)
    {
        return Inertia::render('ps/ps-receive/Edit', [
            'psReceive' => $psReceive,
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
}
