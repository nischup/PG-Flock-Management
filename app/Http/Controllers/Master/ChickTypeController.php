<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\ChickType;

class ChickTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $chickTypes = ChickType::orderBy('id', 'desc')->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'status' => $item->status,
            'created_at' => $item->created_at->format('d M Y'),
            'updated_at' => $item->updated_at->format('d M Y'),
        ];
    });

        return Inertia::render('library/chickType/List', [
            'chickTypes' => $chickTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('library/chickType/Create');
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

        ChickType::create([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $chickType = ChickType::findOrFail($id);

        return Inertia::render('library/chickType/Show', [
            'chickType' => $chickType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $chickType = ChickType::findOrFail($id);

        return Inertia::render('library/chickType/Edit', [
            'chickType' => $chickType
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

        $chickType = ChickType::findOrFail($id);

        $chickType->update([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $chickType = ChickType::findOrFail($id);
        $chickType->delete();

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type deleted successfully.');
    }
}
