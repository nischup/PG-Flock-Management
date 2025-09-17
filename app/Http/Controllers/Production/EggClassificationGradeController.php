<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\EggGrade;
use App\Models\Production\EggClassification;
use App\Models\Production\EggClassificationGrade;
use Illuminate\Http\Request;

class EggClassificationGradeController extends Controller
{
    public function index()
    {
        // First try to get existing egg classifications
        $classifications = EggClassification::with('batchAssign.batch:id,name')
            ->select('id', 'batchassign_id', 'classification_date', 'total_eggs', 'commercial_eggs', 'hatching_eggs')
            ->orderBy('classification_date', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'classification_date' => $c->classification_date,
                    'total_eggs' => $c->total_eggs,
                    'commercial_egg' => $c->commercial_eggs,
                    'hatching_egg' => $c->hatching_eggs,
                    'transaction_no' => $c->batchAssign->transaction_no ?? null,
                    'batch_name' => $c->batchAssign->batch->name ?? null,
                ];
            });

        // If no classifications exist, show batch assignments that can be used to create classifications
        if ($classifications->isEmpty()) {
            $classifications = \App\Models\Shed\BatchAssign::with(['batch:id,name', 'flock:id,name', 'shed:id,name'])
                ->select('id', 'transaction_no', 'batch_no', 'flock_id', 'shed_id', 'batch_total_qty')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($ba) {
                    return [
                        'id' => $ba->id,
                        'classification_date' => now()->format('Y-m-d'),
                        'total_eggs' => 0, // Will be filled when classification is created
                        'commercial_egg' => 0,
                        'hatching_egg' => 0,
                        'transaction_no' => $ba->transaction_no ?? 'N/A',
                        'batch_name' => $ba->batch->name ?? 'N/A',
                        'flock_name' => $ba->flock->name ?? 'N/A',
                        'shed_name' => $ba->shed->name ?? 'N/A',
                        'is_batch_assign' => true, // Flag to indicate this is a batch assign, not a classification
                    ];
                });
        }

        $grades = EggGrade::select('id', 'name', 'type', 'min_weight', 'max_weight')->get();

        return inertia('production/egg-classification/Grade', [
            'classifications' => $classifications,
            'grades' => $grades,
        ]);

    }

    public function create() {}

    public function store(Request $request)
    {
        // Check if this is a batch assign (not an existing classification)
        $batchAssign = \App\Models\Shed\BatchAssign::find($request->classification_id);
        
        if ($batchAssign) {
            // Create a new egg classification first
            $classification = EggClassification::create([
                'batchassign_id' => $request->classification_id,
                'classification_date' => now()->format('Y-m-d'),
                'total_eggs' => array_sum(array_column($request->grades, 'quantity')),
                'hatching_eggs' => 0, // Will be calculated based on type
                'commercial_eggs' => 0, // Will be calculated based on type
                'rejected_eggs' => 0,
                'technical_eggs' => 0,
                'created_by' => auth()->id(),
            ]);
            
            $classificationId = $classification->id;
        } else {
            $classificationId = $request->classification_id;
        }

        // Save the grades
        foreach ($request->grades as $grade) {
            EggClassificationGrade::updateOrCreate(
                [
                    'classification_id' => $classificationId,
                    'egg_grade_id' => $grade['egg_grade_id'],
                ],
                [
                    'quantity' => $grade['quantity'],
                ]
            );
        }

        return redirect()->route('egg-classification-grades.index')->with('success', 'Egg Grading successfully Done.');
    }

    /**
     * Get batch egg data for grading
     */
    public function getBatchEggData($batchId)
    {
        $batch = \App\Models\Shed\BatchAssign::with(['flock', 'shed', 'batch'])
            ->find($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        // Get latest daily operation data for this batch
        $latestOperation = \App\Models\DailyOperation\DailyOperation::where('batchassign_id', $batchId)
            ->with(['eggCollections'])
            ->latest('operation_date')
            ->first();

        $totalEggs = 0;
        $hatchingEggs = 0;
        $commercialEggs = 0;

        if ($latestOperation && $latestOperation->eggCollections->isNotEmpty()) {
            $eggCollection = $latestOperation->eggCollections->first();
            $totalEggs = $eggCollection->quantity ?? 0;
            
            // Calculate hatching and commercial eggs based on classification
            // For now, we'll use a simple calculation - you can adjust this based on your business logic
            $hatchingEggs = round($totalEggs * 0.6); // 60% hatching eggs
            $commercialEggs = round($totalEggs * 0.4); // 40% commercial eggs
        }

        // Get existing egg classification if any
        $existingClassification = \App\Models\Production\EggClassification::where('batchassign_id', $batchId)
            ->latest('classification_date')
            ->first();

        if ($existingClassification) {
            $totalEggs = $existingClassification->total_eggs;
            $hatchingEggs = $existingClassification->hatching_eggs;
            $commercialEggs = $existingClassification->commercial_eggs;
        }

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'transaction_no' => $batch->transaction_no,
                'batch_name' => $batch->batch->name ?? 'N/A',
                'flock_name' => $batch->flock->name ?? 'N/A',
                'shed_name' => $batch->shed->name ?? 'N/A',
            ],
            'egg_data' => [
                'total_eggs' => $totalEggs,
                'hatching_eggs' => $hatchingEggs,
                'commercial_eggs' => $commercialEggs,
            ]
        ]);
    }
}
