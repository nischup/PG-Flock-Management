<?php

namespace App\Http\Controllers\Production;
use App\Models\Production\EggClassification;
use App\Models\Production\EggClassificationGrade;

use App\Models\EggGrade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EggClassificationGradeController extends Controller
{
    
     public function index()
    {

        
        
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

            

        $grades = EggGrade::select('id', 'name', 'type')->get();

        return inertia('production/egg-classification/Grade', [
            'classifications' => $classifications,
            'grades' => $grades,
        ]);



    }
    
    
    
    public function create()
    {
       
    }

    public function store(Request $request)
    {
        

        foreach ($request->grades as $grade) {
            EggClassificationGrade::updateOrCreate(
                [
                    'classification_id' => $request->classification_id,
                    'egg_grade_id' => $grade['egg_grade_id'],
                ],
                [
                    'quantity' => $grade['quantity'],
                ]
            );
        }

        return redirect()->route('egg-classification-grades.index')->with('success', 'Egg Grading successfully Done.');    
    }
}
