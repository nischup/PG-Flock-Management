<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Vaccine;
use Illuminate\Support\Facades\Log;

class VaccineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $vaccines = Vaccine::orderBy('id', 'desc')->get()->map(function ($vaccine) {
                return [
                    'id' => $vaccine->id,
                    'name' => $vaccine->name,
                    'status' => $vaccine->status,
                    'created_at' => $vaccine->created_at->format('Y-m-d'),
                ];
            })->toArray();

            return Inertia::render('library/vaccine/List', [
                'vaccines' => $vaccines,
                'filters' => $request->only(['search', 'per_page', 'page']),
            ]);
        } catch (\Exception $e) {
            Log::error('Vaccine index error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to load vaccines.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $vaccine = Vaccine::create($request->all());

            $newVaccine = [
                'id' => $vaccine->id,
                'name' => $vaccine->name,
                'status' => $vaccine->status,
                'created_at' => $vaccine->created_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('vaccine.index')
                ->with('success', 'Vaccine created successfully!')
                ->with('newVaccine', $newVaccine);
        } catch (\Exception $e) {
            Log::error('Vaccine store error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create vaccine.');
        }
    }

    public function update(Request $request, Vaccine $vaccine)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $vaccine->update($request->all());

            $updatedVaccine = [
                'id' => $vaccine->id,
                'name' => $vaccine->name,
                'status' => $vaccine->status,
                'created_at' => $vaccine->created_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('vaccine.index')
                ->with('success', 'Vaccine updated successfully!')
                ->with('updatedVaccine', $updatedVaccine);
        } catch (\Exception $e) {
            Log::error('Vaccine update error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to update vaccine.');
        }
    }

    public function destroy(Vaccine $vaccine)
    {
        try {
            $vaccine->delete();
            return redirect()->route('vaccine.index')->with('success', 'Vaccine deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Vaccine delete error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete vaccine.');
        }
    }
}
