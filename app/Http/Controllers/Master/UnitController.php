<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Unit;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $units = Unit::latest()->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'status' => $unit->status ? 'Active' : 'Inactive',
                    'created_at' => $unit->created_at ? $unit->created_at->format('Y-m-d') : null,
                ];
            });

            return Inertia::render('library/unit/List', [
                'units' => $units
            ]);
        } catch (\Exception $e) {
            Log::error('Unit Index Error: ' . $e->getMessage());
            return Inertia::render('library/unit/List', [
                'units' => [],
                'error' => 'Failed to load units.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            Unit::create([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('unit.index')->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Store Error: ' . $e->getMessage());
            return redirect()->route('unit.index')->with('error', 'Failed to create unit.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            $unit = Unit::findOrFail($id);
            $unit->update([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('unit.index')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Update Error: ' . $e->getMessage());
            return redirect()->route('unit.index')->with('error', 'Failed to update unit.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Unit::findOrFail($id)->delete();
            return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Delete Error: ' . $e->getMessage());
            return redirect()->route('unit.index')->with('error', 'Failed to delete unit.');
        }
    }
}
