<?php

namespace App\Http\Controllers\Ps;
use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            PsReceive::create($request->only([
                'pi_no', 'pi_date', 'order_no', 'order_date', 'lc_no', 'lc_date', 'shipment_type_id'
            ]));

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
