<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\VaccineType;
use Illuminate\Support\Facades\Log;

class VaccineTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = VaccineType::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $vaccineTypes = $query->orderBy('id', 'desc')->get()->map(function ($vt) {
                return [
                    'id' => $vt->id,
                    'name' => $vt->name,
                    'status' => $vt->status,
                    'created_at' => $vt->created_at->format('d M Y'),
                    'updated_at' => $vt->updated_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/vaccineType/List', [
                'vaccineTypes' => $vaccineTypes,
                'filters' => $request->only(['search', 'per_page', 'page']),
            ]);
        } catch (\Exception $e) {
            Log::error('VaccineType Index Error: ' . $e->getMessage());
            return Inertia::render('library/vaccineType/List', [
                'vaccineTypes' => [],
                'filters' => $request->only(['search', 'per_page', 'page']),
                'error' => 'Failed to load vaccine types.',
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|integer|in:0,1',
            ]);

            VaccineType::create($validated);

            return redirect()->back()->with('success', 'Vaccine Type created successfully!');
        } catch (\Exception $e) {
            Log::error('VaccineType Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create vaccine type.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $vaccineType = VaccineType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|integer|in:0,1',
            ]);

            $vaccineType->update($validated);

            return redirect()->back()->with('success', 'Vaccine Type updated successfully!');
        } catch (\Exception $e) {
            Log::error('VaccineType Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update vaccine type.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $vaccineType = VaccineType::findOrFail($id);
            $vaccineType->delete();

            return redirect()->back()->with('success', 'Vaccine Type deleted successfully!');
        } catch (\Exception $e) {
            Log::error('VaccineType Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete vaccine type.');
        }
    }
}
