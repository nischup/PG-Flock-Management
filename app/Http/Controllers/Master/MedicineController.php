<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Medicine;
use Illuminate\Support\Facades\Log;

class MedicineController extends Controller
{
    public function index()
    {
        try {
            $medicines = Medicine::orderBy('id', 'desc')->get();

            return Inertia::render('library/medicine/List', [
                'medicines' => $medicines, // Vue will use status 0/1 directly
            ]);
        } catch (\Exception $e) {
            Log::error('Medicine index error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch medicines.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            Medicine::create([
                'name'   => $validated['name'],
                'status' => (int) $validated['status'],
            ]);

            return redirect()->route('medicine.index')->with('success', 'Medicine created successfully');
        } catch (\Exception $e) {
            Log::error('Medicine store error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create medicine.');
        }
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $medicine->update([
                'name'   => $validated['name'],
                'status' => (int) $validated['status'],
            ]);

            return redirect()->route('medicine.index')->with('success', 'Medicine updated successfully');
        } catch (\Exception $e) {
            Log::error('Medicine update error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to update medicine.');
        }
    }

    public function destroy(Medicine $medicine)
    {
        try {
            $medicine->delete();
            return redirect()->route('medicine.index')->with('success', 'Medicine deleted successfully');
        } catch (\Exception $e) {
            Log::error('Medicine delete error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete medicine.');
        }
    }
}
