<?php

namespace App\Http\Controllers\Shed;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shed\BatchConfiguration;
use App\Models\Shed\BatchAssign;
use Inertia\Inertia;

class BatchConfigurationController extends Controller
{
    // List
    public function index()
    {
        $batchConfigs = BatchConfiguration::with('batchAssign.batch',
            'batchAssign.company',
            'batchAssign.shed',
            'batchAssign.project',)->get(); // returns a collection
       
        return Inertia::render('shed/batch-config/List', [
            'batchConfigs' => $batchConfigs, // must be collection/array
        ]);
    }

    // Create form
    public function create()
    {
       $batchAssigns = BatchAssign::with('batch')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'transaction_no' => $b->transaction_no,
                    'batch_name' => $b->batch->name ?? '', // batch name from relation
                ];
            });

        return Inertia::render('shed/batch-config/Create', [
            'batchAssigns' => $batchAssigns
        ]);
    }

    // Store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_assign_id' => 'required|exists:batch_assigns,id',
            'area_sqft' => 'required|numeric',
            'num_workers' => 'required|integer',
            'density_per_sqft' => 'nullable|numeric',
            'feeders' => 'nullable|integer',
            'drinkers' => 'nullable|integer',
            'temperature_target' => 'nullable|numeric',
            'humidity_target' => 'nullable|numeric',
            'note' => 'nullable|string',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        BatchConfiguration::create($validated);

        return redirect()->route('batch-config.index')->with('success', 'Batch configuration created successfully!');
    }

    // Edit page
    public function edit(BatchConfiguration $batchConfiguration,$id)
    {
       // Eager load batchAssign -> batch, company, shed, project
            $batchConfig = BatchConfiguration::with('batchAssign')->findOrFail($id);

            $batchAssigns = BatchAssign::with('batch') // if batchAssign has batch relation
                ->get()
                ->map(function($b) {
                    return [
                        'id' => $b->id,
                        'transaction_no' => $b->transaction_no,
                        'batch_name' => $b->batch->name ?? '', // fetch batch name from related batch
                    ];
                });

            return inertia('shed/batch-config/Edit', [
                'batchConfiguration' => $batchConfig, // make sure relationship exists
                'batchAssigns' => $batchAssigns,
            ]);
    }

    // Update data
    public function update(Request $request, BatchConfiguration $batchConfiguration)
    {
        $validated = $request->validate([
            'batch_assign_id' => 'required|exists:batch_assigns,id',
            'area_sqft' => 'required|numeric',
            'num_workers' => 'required|integer',
            'density_per_sqft' => 'nullable|numeric',
            'feeders' => 'nullable|integer',
            'drinkers' => 'nullable|integer',
            'temperature_target' => 'nullable|numeric',
            'humidity_target' => 'nullable|numeric',
            'note' => 'nullable|string',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $batchConfiguration->update($validated);

        return redirect()->route('batch-config.index')->with('success', 'Batch Configuration updated successfully!');
    }
}