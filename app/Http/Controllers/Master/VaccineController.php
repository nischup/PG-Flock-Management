<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Vaccine;
use App\Models\Master\VaccineType;
use Illuminate\Support\Facades\Log;

class VaccineController extends Controller
{
    public function index(Request $request)
    {




        try {
            $vaccines = Vaccine::with('vaccineType')
                ->orderBy('id', 'desc')
                ->get();


            $vaccineTypes = VaccineType::where('status', 1)
                ->orderBy('name')
                ->get();

            $mappedVaccines = $vaccines->map(function ($v) {
                return [
                    'id' => $v->id,
                    'vaccine_type_id' => $v->vaccine_type_id,
                    'vaccine_type_name' => $v->vaccineType->name ?? '',
                    'name' => $v->name,
                    'applicator' => $v->applicator,
                    'dose' => $v->dose,
                    'note' => $v->note,
                    'status' => $v->status,
                    'created_at' => $v->created_at->format('d M Y'),
                    'updated_at' => $v->updated_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/vaccine/List', [
                'vaccines' => $mappedVaccines->toArray(),
                'vaccineTypes' => $vaccineTypes->map(function ($vt) {
                    return [
                        'id' => $vt->id,
                        'name' => $vt->name,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Vaccine Index Error: ' . $e->getMessage());

            return Inertia::render('library/vaccine/List', [
                'vaccines' => [],
                'vaccineTypes' => [],
                'error' => 'Failed to load vaccines.',
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vaccine_type_id' => 'required|exists:vaccine_types,id',
                'name' => 'required|string|max:200',
                'applicator' => 'nullable|string|max:200',
                'dose' => 'nullable|string|max:100',
                'note' => 'nullable|string',
                'status' => 'required|integer|in:0,1',
            ]);

            $vaccine = Vaccine::create($validated);
            $vaccine->load('vaccineType');

            if ($request->wantsJson()) {
                return response()->json([
                    'vaccine' => [
                        'id' => $vaccine->id,
                        'vaccine_type_id' => $vaccine->vaccine_type_id,
                        'vaccine_type_name' => $vaccine->vaccineType->name ?? '',
                        'name' => $vaccine->name,
                        'applicator' => $vaccine->applicator,
                        'dose' => $vaccine->dose,
                        'note' => $vaccine->note,
                        'status' => $vaccine->status,
                        'created_at' => $vaccine->created_at->format('d M Y'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Vaccine created successfully.');
        } catch (\Exception $e) {
            Log::error('Vaccine Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create vaccine.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $vaccine = Vaccine::findOrFail($id);

            $validated = $request->validate([
                'vaccine_type_id' => 'required|exists:vaccine_types,id',
                'name' => 'required|string|max:200',
                'applicator' => 'nullable|string|max:200',
                'dose' => 'nullable|string|max:100',
                'note' => 'nullable|string',
                'status' => 'required|integer|in:0,1',
            ]);

            $vaccine->update($validated);
            $vaccine->load('vaccineType');

            if ($request->wantsJson()) {
                return response()->json([
                    'vaccine' => [
                        'id' => $vaccine->id,
                        'vaccine_type_id' => $vaccine->vaccine_type_id,
                        'vaccine_type_name' => $vaccine->vaccineType->name ?? '',
                        'name' => $vaccine->name,
                        'applicator' => $vaccine->applicator,
                        'dose' => $vaccine->dose,
                        'note' => $vaccine->note,
                        'status' => $vaccine->status,
                        'updated_at' => $vaccine->updated_at->format('d M Y'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Vaccine updated successfully.');
        } catch (\Exception $e) {
            Log::error('Vaccine Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update vaccine.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $vaccine = Vaccine::findOrFail($id);
            $vaccine->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', 'Vaccine deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Vaccine Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete vaccine.');
        }
    }
}
