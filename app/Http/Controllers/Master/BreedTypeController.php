<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\BreedType;

class BreedTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breedTypes = BreedType::orderBy('id', 'desc')->get()->map(function ($item) {
            return [
                'id'         => $item->id,
                'name'       => $item->name,
                'status'     => $item->status,
                'created_at' => $item->created_at->format('d M Y'),
                'updated_at' => $item->updated_at->format('d M Y'),
            ];
        });

        return Inertia::render('library/breedType/List', [
            'breedTypes' => $breedTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('library/breedType/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        BreedType::create([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('breed-type.index')
            ->with('success', 'Breed Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $breedType = BreedType::findOrFail($id);

        return Inertia::render('library/breedType/Show', [
            'breedType' => $breedType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $breedType = BreedType::findOrFail($id);

        return Inertia::render('library/breedType/Edit', [
            'breedType' => $breedType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        $breedType = BreedType::findOrFail($id);

        $breedType->update([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('breed-type.index')
            ->with('success', 'Breed Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $breedType = BreedType::findOrFail($id);
        $breedType->delete();

        return redirect()->route('breed-type.index')
            ->with('success', 'Breed Type deleted successfully.');
    }
}
