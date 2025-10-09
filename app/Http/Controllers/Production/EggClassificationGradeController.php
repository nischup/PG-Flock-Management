<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\EggGrade;
use App\Models\Production\EggClassification;
use App\Models\Production\EggClassificationGrade;
use App\Models\Production\EggClassificationGradeDetail;
use App\Models\Shed\BatchAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EggClassificationGradeController extends Controller
{
    public function index(Request $request)
    {
        $query = EggClassificationGrade::with([
            'grade',                           // Egg grade info
            'classification.batchAssign',
            'classification.batchAssign.batch',       // Classification + Batch info
        ])->whereHas('classification.batchAssign', function ($q) {
            $q->visibleFor(); // Scope from BatchAssign
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('classification.batchAssign', function ($subQ) use ($search) {
                    $subQ->where('transaction_no', 'like', "%{$search}%")
                        ->orWhereHas('batch', function ($batchQ) use ($search) {
                            $batchQ->where('name', 'like', "%{$search}%");
                        });
                })
                    ->orWhereHas('grade', function ($gradeQ) use ($search) {
                        $gradeQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereHas('classification', function ($q) use ($request) {
                $q->where('classification_date', '>=', $request->get('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('classification', function ($q) use ($request) {
                $q->where('classification_date', '<=', $request->get('date_to'));
            });
        }

        // Apply grade type filter
        if ($request->filled('grade_type')) {
            $query->whereHas('grade', function ($q) use ($request) {
                $q->where('type', $request->get('grade_type'));
            });
        }

        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $grades = $query->latest()->paginate($perPage);

        return inertia('production/egg-classification/GradeList', [
            'grades' => $grades,
            'filters' => $request->only(['search', 'per_page', 'date_from', 'date_to', 'grade_type']),
        ]);
    }

    public function create()
    {
        // First try to get existing egg classifications
        $classifications = EggClassification::with(['batchAssign.batch:id,name', 'batchAssign.company', 'batchAssign.project', 'batchAssign.flock:id,name,code', 'batchAssign.shed'])
            ->select('id', 'batchassign_id', 'classification_date', 'total_eggs', 'commercial_eggs', 'hatching_eggs')
            ->orderBy('classification_date', 'desc')
            ->get()
            ->map(function ($c) {
                $batch = $c->batchAssign;

                return [
                    'id' => $c->id,
                    'classification_date' => $c->classification_date,
                    'total_eggs' => $c->total_eggs,
                    'commercial_egg' => $c->commercial_eggs,
                    'hatching_egg' => $c->hatching_eggs,
                    'transaction_no' => $batch->transaction_no ?? null,
                    'batch_name' => $batch->batch->name ?? null,
                    'flock' => $batch->flock?->name ?? 'N/A',
                    'shed' => $batch->shed?->name ?? 'N/A',
                    'company' => $batch->company?->name ?? 'N/A',
                    'project' => $batch->project?->name ?? 'N/A',
                    'label' => sprintf(
                        '%s, %s, %s, %s, %s, %s',
                        $batch->company?->short_name ?? 'Unknown',
                        $batch->project?->name ?? 'Proj',
                        $batch->flock?->code ?? 'Flock',
                        $batch->shed?->name ?? 'Shed',
                        'Level '.$batch->level,
                        $batch->batch?->name ?? 'Batch'
                    ),
                ];
            });

        // If no classifications exist, show batch assignments that can be used to create classifications
        if ($classifications->isEmpty()) {
            $classifications = \App\Models\Shed\BatchAssign::with(['batch:id,name', 'flock:id,name,code', 'shed:id,name', 'company', 'project'])
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
                        'flock' => $ba->flock?->name ?? 'N/A',
                        'shed' => $ba->shed?->name ?? 'N/A',
                        'company' => $ba->company?->name ?? 'N/A',
                        'project' => $ba->project?->name ?? 'N/A',
                        'label' => sprintf(
                            '%s, %s, %s, %s, %s, %s',
                            $ba->company?->short_name ?? 'Unknown',
                            $ba->project?->name ?? 'Proj',
                            $ba->flock?->code ?? 'Flock',
                            $ba->shed?->name ?? 'Shed',
                            'Level '.$ba->level,
                            $ba->batch?->name ?? 'Batch'
                        ),
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

    public function store(Request $request)
    {
        
        $eggClassification = EggClassification::find($request->classification_id);
        $gradeRecord = EggClassificationGrade::updateOrCreate(
            [
                'classification_id' => $request->classification_id,
                'batchassign_id' => $eggClassification->batchassign_id,
                'flock_no' => $eggClassification->flock_no,
                'flock_id' => $eggClassification->flock_id,
                'batch_no' => $eggClassification->batch_no,
                'shed_id' => $eggClassification->shed_id,
                'company_id' => $eggClassification->company_id,
                'project_id' => $eggClassification->project_id,
                'job_no' => $eggClassification->job_no,
                'transaction_no' => $eggClassification->transaction_no,
            ],
        );
        foreach ($request->grades as $grade) {
            // 3️⃣ Insert or update corresponding grade detail
            EggClassificationGradeDetail::updateOrCreate(
                [
                    'egg_classification_grade_id' => $gradeRecord->id,
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
    public function getBatchEggData($classificationId)
    {
        // Get the latest egg classification for the given ID
        $existingClassification = EggClassification::where('id', $classificationId)
            ->latest('classification_date')
            ->first();

        // Initialize default values
        $totalEggs = 0;
        $hatchingEggs = 0;
        $commercialEggs = 0;
        $batchData = [
            'id' => null,
            'transaction_no' => 'N/A',
            'batch_name' => 'N/A',
            'flock_name' => 'N/A',
            'shed_name' => 'N/A',
        ];

        if ($existingClassification) {
            $totalEggs = $existingClassification->total_eggs ?? 0;
            $hatchingEggs = $existingClassification->hatching_eggs ?? 0;
            $commercialEggs = $existingClassification->commercial_eggs ?? 0;

            // Get related batch info
            if ($existingClassification->batchassing_id) {
                $batch = BatchAssign::with(['flock', 'shed', 'batch'])->find($existingClassification->batchassing_id);

                if ($batch) {
                    $batchData = [
                        'id' => $batch->id,
                        'transaction_no' => $batch->transaction_no ?? 'N/A',
                        'batch_name' => $batch->batch->name ?? 'N/A',
                        'flock_name' => $batch->flock->name ?? 'N/A',
                        'shed_name' => $batch->shed->name ?? 'N/A',
                    ];
                }
            }
        }

        return response()->json([
            'batch' => $batchData,
            'egg_data' => [
                'total_eggs' => $totalEggs,
                'hatching_eggs' => $hatchingEggs,
                'commercial_eggs' => $commercialEggs,
            ],
        ]);
    }

    public function edit($classificationId)
    {
        // Load the specific classification with batch info
        $classification = EggClassification::with('batchAssign.batch', 'batchAssign.flock', 'batchAssign.shed')
            ->findOrFail($classificationId);

        // Load all grades of this classification
        $grades = EggClassificationGrade::with('grade')
            ->where('classification_id', $classificationId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'egg_grade_id' => $item->egg_grade_id,
                    'name' => $item->grade->name ?? null,
                    'type' => $item->grade->type ?? null,
                    'min_weight' => $item->grade->min_weight ?? null,
                    'max_weight' => $item->grade->max_weight ?? null,
                    'quantity' => $item->quantity,
                ];
            });

        // Fetch all classifications for dropdown (same as create page)
        $classifications = EggClassification::with(['batchAssign.batch:id,name', 'batchAssign.company', 'batchAssign.project', 'batchAssign.flock:id,name,code', 'batchAssign.shed'])
            ->select('id', 'batchassign_id', 'classification_date', 'total_eggs', 'commercial_eggs', 'hatching_eggs')
            ->orderBy('classification_date', 'desc')
            ->get()
            ->map(function ($c) {
                $batch = $c->batchAssign;

                return [
                    'id' => $c->id,
                    'classification_date' => $c->classification_date,
                    'total_eggs' => $c->total_eggs,
                    'commercial_egg' => $c->commercial_eggs,
                    'hatching_egg' => $c->hatching_eggs,
                    'transaction_no' => $batch->transaction_no ?? null,
                    'batch_name' => $batch->batch->name ?? null,
                    'flock' => $batch->flock?->name ?? 'N/A',
                    'shed' => $batch->shed?->name ?? 'N/A',
                    'company' => $batch->company?->name ?? 'N/A',
                    'project' => $batch->project?->name ?? 'N/A',
                    'label' => sprintf(
                        '%s, %s, %s, %s, %s, %s',
                        $batch->company?->short_name ?? 'Unknown',
                        $batch->project?->name ?? 'Proj',
                        $batch->flock?->code ?? 'Flock',
                        $batch->shed?->name ?? 'Shed',
                        'Level '.$batch->level,
                        $batch->batch?->name ?? 'Batch'
                    ),
                ];
            });

        // If no classifications exist, show batch assignments for creating new
        if ($classifications->isEmpty()) {
            $classifications = \App\Models\Shed\BatchAssign::with(['batch:id,name', 'flock:id,name,code', 'shed:id,name', 'company', 'project'])
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($ba) {
                    return [
                        'id' => $ba->id,
                        'classification_date' => now()->format('Y-m-d'),
                        'total_eggs' => 0,
                        'commercial_egg' => 0,
                        'hatching_egg' => 0,
                        'transaction_no' => $ba->transaction_no ?? 'N/A',
                        'batch_name' => $ba->batch->name ?? 'N/A',
                        'flock_name' => $ba->flock->name ?? 'N/A',
                        'shed_name' => $ba->shed->name ?? 'N/A',
                        'flock' => $ba->flock?->name ?? 'N/A',
                        'shed' => $ba->shed?->name ?? 'N/A',
                        'company' => $ba->company?->name ?? 'N/A',
                        'project' => $ba->project?->name ?? 'N/A',
                        'label' => sprintf(
                            '%s, %s, %s, %s, %s, %s',
                            $ba->company?->short_name ?? 'Unknown',
                            $ba->project?->name ?? 'Proj',
                            $ba->flock?->code ?? 'Flock',
                            $ba->shed?->name ?? 'Shed',
                            'Level '.$ba->level,
                            $ba->batch?->name ?? 'Batch'
                        ),
                        'is_batch_assign' => true,
                    ];
                });
        }

        // Load all grades (for filtering by type)
        $allGrades = EggGrade::select('id', 'name', 'type', 'min_weight', 'max_weight')->get();

        return inertia('production/egg-classification/EditGrade', [
            'classification' => [
                'id' => $classification->id,
                'classification_date' => $classification->classification_date,
                'total_eggs' => $classification->total_eggs,
                'commercial_egg' => $classification->commercial_eggs,
                'hatching_egg' => $classification->hatching_eggs,
                'transaction_no' => $classification->batchAssign->transaction_no ?? null,
                'batch_name' => $classification->batchAssign->batch->name ?? null,
                'flock_name' => $classification->batchAssign->flock->name ?? null,
                'shed_name' => $classification->batchAssign->shed->name ?? null,
                'batchassign_id' => $classification->batchassign_id,
            ],
            'grades' => $grades,
            'classifications' => $classifications,
            'allGrades' => $allGrades,
        ]);
    }

    // Update function
    public function update(Request $request, $classificationId)
    {
        $classification = EggClassification::findOrFail($classificationId);

        // Update all grades quantities
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

        return redirect()->route('egg-classification-grades.index')
            ->with('success', 'Egg grades updated successfully.');
    }

    // test push//

}
