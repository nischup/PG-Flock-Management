<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\FirmLabTest;
use App\Models\Shed\BatchAssign;
use Inertia\Inertia;

class FirmLabTestController extends Controller
{
    // List page
    public function index()
    {
        $firmLabTests = FirmLabTest::with('batchAssign.batch', 'batchAssign.company', 'batchAssign.project', 'batchAssign.shed')->get();

        return Inertia::render('shed/firm-labtest/List', [
            'firmLabTests' => $firmLabTests,
        ]);
    }

    public function create()
    {
        $batchAssigns = BatchAssign::with('batch')->get()->map(function ($b) {
            return [
                    'id' => $b->id,
                    'transaction_no' => $b->transaction_no,
                    'batch_no' => $b->batch->name ?? '', // plain, not nested
                ];
            });

            return Inertia::render('shed/firm-labtest/Create', [
                'batchAssigns' => $batchAssigns,
            ]);
    }

    // Store data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_assign_id' => 'required|exists:batch_assigns,id',
            'firm_lab_send_female_qty' => 'required|integer',
            'firm_lab_send_male_qty' => 'required|integer',
            'firm_lab_send_total_qty' => 'required|integer',
            'firm_lab_receive_female_qty' => 'nullable|integer',
            'firm_lab_receive_male_qty' => 'nullable|integer',
            'firm_lab_receive_total_qty' => 'nullable|integer',
            'note' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        // lab type = 2 by default
        $validated['firm_lab_type'] = 2;

        FirmLabTest::create($validated);

        return redirect()->route('firm-lab-tests.index')->with('success', 'Firm Lab Test created successfully!');
    }

    // Edit form
    public function edit(FirmLabTest $firmLabTest)
    {
        // Fetch batch assigns
        $batchAssigns = BatchAssign::get()->map(function ($b) {
            return [
                'id' => $b->id,
                'transaction_no' => $b->transaction_no,
                'batch_no' => $b->batch->batch_no ?? '',
            ];
        });

        return Inertia::render('shed/firm-labtest/Edit', [
            'firmLabTest' => $firmLabTest,
            'batchAssigns' => $batchAssigns,
        ]);
    }


    // Update function
    public function update(Request $request, FirmLabTest $firmLabTest)
    {
        $request->validate([
            'batch_assign_id' => 'required|exists:batch_assigns,id',
            'firm_lab_send_female_qty' => 'required|numeric',
            'firm_lab_send_male_qty' => 'required|numeric',
            'firm_lab_send_total_qty' => 'required|numeric',
            'firm_lab_receive_female_qty' => 'required|numeric',
            'firm_lab_receive_male_qty' => 'required|numeric',
            'firm_lab_receive_total_qty' => 'required|numeric',
            'note' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $firmLabTest->update($request->all());

        return redirect()->route('firm-lab-tests.index')
                        ->with('success', 'Firm Lab Test updated successfully.');
    }

    // Destroy
    public function destroy(FirmLabTest $firmLabTest)
    {
        $firmLabTest->delete();

        return redirect()->route('firm-lab-tests.index')->with('success', 'Firm Lab Test deleted successfully!');
    }
}
