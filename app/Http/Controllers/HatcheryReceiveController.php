<?php
namespace App\Http\Controllers;

use App\Models\HatcheryReceive;
use App\Models\Hatchery;
use App\Models\StoreReceive;
use App\Models\EggClassificationGrade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HatcheryReceiveController extends Controller
{
    public function index()
    {
        $receives = HatcheryReceive::with(['hatchery','storeReceive','grade'])
                        ->orderBy('id','desc')->get();
        // return Inertia::render('HatcheryReceives/Index', ['receives' => $receives]);
    }

    public function create()
    {
        $hatcheries = Hatchery::all();
        $storeReceives = StoreReceive::all();
        // $grades = EggClassificationGrade::all();
        // return Inertia::render('HatcheryReceives/Create', compact('hatcheries','storeReceives','grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receive_date' => 'required|date',
            'hatchery_id' => 'required|exists:hatcheries,id',
            'store_receive_id' => 'required|exists:store_receives,id',
            'egg_classification_grade_id' => 'required|exists:egg_classification_grades,id',
            'flock_id' => 'required|integer',
            'company_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|integer',
            'receive_status' => 'required|integer',
        ]);

        HatcheryReceive::create($request->all());

        // return redirect()->route('hatchery-receives.index')
        //                  ->with('success', 'Hatchery receive created successfully');
    }

    public function edit(HatcheryReceive $hatcheryReceive)
    {
        $hatcheries = Hatchery::all();
        $storeReceives = StoreReceive::all();
        // $grades = EggClassificationGrade::all();
        // return Inertia::render('HatcheryReceives/Edit', compact('hatcheryReceive','hatcheries','storeReceives','grades'));
    }

    public function update(Request $request, HatcheryReceive $hatcheryReceive)
    {
        $request->validate([
            'receive_date' => 'required|date',
            'hatchery_id' => 'required|exists:hatcheries,id',
            'store_receive_id' => 'required|exists:store_receives,id',
            'egg_classification_grade_id' => 'required|exists:egg_classification_grades,id',
            'flock_id' => 'required|integer',
            'company_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|integer',
            'receive_status' => 'required|integer',
        ]);

        $hatcheryReceive->update($request->all());

        // return redirect()->route('hatchery-receives.index')
        //                  ->with('success', 'Hatchery receive updated successfully');
    }

    public function destroy(HatcheryReceive $hatcheryReceive)
    {
        $hatcheryReceive->delete();
        // return redirect()->route('hatchery-receives.index')
        //                  ->with('success', 'Hatchery receive deleted successfully');
    }
}
