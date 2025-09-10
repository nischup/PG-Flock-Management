<?php

namespace App\Http\Controllers\Production;
use App\Models\Production\EggClassification;
use App\Models\EggGrade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EggClassificationGradeController extends Controller
{
    
     public function index()
    {

         $classifications = EggClassification::select('id', 'classification_date')
            ->orderBy('classification_date', 'desc')
            ->get();

        // Load all grades from seeded table
        $grades = EggGrade::select('id', 'name', 'type')->get();

        return inertia('production/egg-classification/Grade', [
            'classifications' => $classifications,
            'grades' => $grades,
        ]);


    }
    
    
    
    public function create()
    {
       
    }
}
