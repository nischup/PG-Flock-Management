<?php

namespace App\Http\Controllers;

use App\Models\StoreReceive;
use App\Models\Store;
use App\Models\Production\EggClassificationGrade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreReceiveController extends Controller
{
    public function index()
    {
        $receives = StoreReceive::with(['store', 'grade'])->orderBy('id','desc')->get();
        //return Inertia::render('StoreReceives/Index', ['receives' => $receives]);
    }

    public function create()
    {
        $stores = Store::all();
        $grades = EggClassificationGrade::all();
        //return Inertia::render('StoreReceives/Create', compact('stores', 'grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receive_date' => 'required|date',
            'egg_classification_grade_id' => 'required|exists:egg_classification_grades,id',
            'flock_id' => 'required|integer',
            'company_id' => 'required|integer',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|integer',
            'transfer_status' => 'required|integer',
        ]);

        StoreReceive::create($request->all());

        //return redirect()->route('store-receives.index')
                         //->with('success', 'Store receive created successfully');
    }

    public function edit(StoreReceive $storeReceive)
    {
        $stores = Store::all();
        $grades = EggClassificationGrade::all();
       // return Inertia::render('StoreReceives/Edit', compact('storeReceive','stores','grades'));
    }

    public function update(Request $request, StoreReceive $storeReceive)
    {
        $request->validate([
            'receive_date' => 'required|date',
            'egg_classification_grade_id' => 'required|exists:egg_classification_grades,id',
            'flock_id' => 'required|integer',
            'company_id' => 'required|integer',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|integer',
            'transfer_status' => 'required|integer',
        ]);

        $storeReceive->update($request->all());

        //return redirect()->route('store-receives.index')
                        // ->with('success', 'Store receive updated successfully');
    }

    public function destroy(StoreReceive $storeReceive)
    {
        $storeReceive->delete();
        //return redirect()->route('store-receives.index')
                       //  ->with('success', 'Store receive deleted successfully');
    }
}
