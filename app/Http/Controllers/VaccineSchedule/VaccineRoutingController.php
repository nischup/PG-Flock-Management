<?php

// ============================================
// author Sabbir Ahmed
// Provita Group Gulshan 2 HO
// ============================================

namespace App\Http\Controllers\VaccineSchedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaccineRoutingRequest;
use App\Models\VaccineRouting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VaccineRoutingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $routings = VaccineRouting::latest()->get();

        return Inertia::render('VaccineRouting/Index', [
            'routings' => $routings,
            'filters' => $request->only(['search', 'per_page', 'page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVaccineRoutingRequest $request)
    {
        $routing = VaccineRouting::create($request->validated());

        return redirect()->back()->with('success', 'Vaccine routing created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VaccineRouting $vaccineRouting)
    {
        return Inertia::render('VaccineRouting/Show', [
            'routing' => $vaccineRouting,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreVaccineRoutingRequest $request, VaccineRouting $vaccineRouting)
    {
        $vaccineRouting->update($request->validated());

        return redirect()->back()->with('success', 'Vaccine routing updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VaccineRouting $vaccineRouting)
    {
        $vaccineRouting->delete();

        return redirect()->back()->with('success', 'Vaccine routing deleted successfully.');
    }
}
