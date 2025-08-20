<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'status' => $unit->status ? 'Active' : 'Deactivated',
                'created_at' => $unit->created_at->format('Y-m-d'),
            ];
        });

        return Inertia::render('library/unit/List', [
            'units' => $units
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|string|in:Active,Deactivated',
        ]);

        Unit::create([
            'name' => $data['name'],
            'status' => $data['status'] === 'Active' ? 1 : 0,
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|string|in:Active,Deactivated',
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update([
            'name' => $data['name'],
            'status' => $data['status'] === 'Active' ? 1 : 0,
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Unit::findOrFail($id)->delete();
        return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
    }
}
