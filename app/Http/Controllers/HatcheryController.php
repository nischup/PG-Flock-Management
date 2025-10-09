<?php

namespace App\Http\Controllers;

use App\Models\Hatchery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HatcheryController extends Controller
{
    // Display list of hatcheries
    public function index()
    {
        $hatcheries = Hatchery::orderBy('id','desc')->get();
        // return Inertia::render('Hatcheries/Index', ['hatcheries' => $hatcheries]);
    }

    // Show create form
    public function create()
    {
        // Inertia::render('Hatcheries/Create');
    }

    // Store new hatchery
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:hatcheries,name',
            'code' => 'nullable|unique:hatcheries,code',
            'location' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        Hatchery::create($request->all());

        // return redirect()->route('hatcheries.index')
        //                  ->with('success', 'Hatchery created successfully');
    }

    // Show edit form
    public function edit(Hatchery $hatchery)
    {
        //return Inertia::render('Hatcheries/Edit', compact('hatchery'));
    }

    // Update existing hatchery
    public function update(Request $request, Hatchery $hatchery)
    {
        $request->validate([
            'name' => 'required|unique:hatcheries,name,' . $hatchery->id,
            'code' => 'nullable|unique:hatcheries,code,' . $hatchery->id,
            'location' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $hatchery->update($request->all());

        return redirect()->route('hatcheries.index')
                         ->with('success', 'Hatchery updated successfully');
    }

    // Delete hatchery
    public function destroy(Hatchery $hatchery)
    {
        $hatchery->delete();
        // return redirect()->route('hatcheries.index')
        //                  ->with('success', 'Hatchery deleted successfully');
    }
}
