<?php

namespace App\Http\Controllers\VaccineSchedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVaccineRoutingRequest;
use App\Models\VaccineRouting;
use Inertia\Inertia;

class VaccineRoutingController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routings = VaccineRouting::latest()->get();

        return Inertia::render('VaccineRouting/Index', [
            'routings' => $routings,
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
     * Remove the specified resource from storage.
     */
    public function destroy(VaccineRouting $vaccineRouting)
    {
        $vaccineRouting->delete();

        return redirect()->back()->with('success', 'Vaccine routing deleted successfully.');
    }
}
