<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Medicine;
use Illuminate\Support\Facades\Log;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $medicines = Medicine::orderBy('id', 'desc')->get()->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'status' => $medicine->status, // 0 or 1
                    'created_at' => $medicine->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $medicine->updated_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            return Inertia::render('library/medicine/List', [
                'medicines' => $medicines,
            ]);
        } catch (\Exception $e) {
            Log::error('Medicine index error: ' . $e->getMessage());

            return Inertia::render('library/medicine/List', [
                'medicines' => [],
                'error' => 'Failed to fetch medicines.',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'   => 'required|string|max:200',
                'status' => 'required|in:0,1',
            ]);

            $medicine = Medicine::create([
                'name' => $validated['name'],
                'status' => (int) $validated['status'],
            ]);

            $data = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'status' => $medicine->status,
                'created_at' => $medicine->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $medicine->updated_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('medicine.index')->with('success', 'Medicine created successfully')->with('medicine', $data);
        } catch (\Exception $e) {
            Log::error('Medicine store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create medicine.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medicine $medicine)
    {
        try {
            $validated = $request->validate([
                'name'   => 'required|string|max:200',
                'status' => 'required|in:0,1',
            ]);

            $medicine->update([
                'name' => $validated['name'],
                'status' => (int) $validated['status'],
            ]);

            $data = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'status' => $medicine->status,
                'created_at' => $medicine->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $medicine->updated_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('medicine.index')->with('success', 'Medicine updated successfully')->with('medicine', $data);
        } catch (\Exception $e) {
            Log::error('Medicine update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update medicine.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medicine $medicine)
    {
        try {
            $medicine->delete();
            return redirect()->route('medicine.index')->with('success', 'Medicine deleted successfully');
        } catch (\Exception $e) {
            Log::error('Medicine delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete medicine.');
        }
    }
}
