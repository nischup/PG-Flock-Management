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
        $medicines = Medicine::orderBy('id', 'desc')->get()->map(function($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'status' => $m->status == 1 ? 'Active' : 'Deactivated',
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('library/medicine/List', [
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            Medicine::create($request->all());
            return redirect()->route('medicine.index')->with('success', 'Medicine created successfully');
        } catch (\Exception $e) {
            Log::error('Medicine store error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create medicine.');
        }
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $medicine->update($request->all());
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
