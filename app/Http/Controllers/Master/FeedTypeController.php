<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\FeedType;

class FeedTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FeedType::query();

        // Filtering (search)
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $feedTypes = $query->orderBy('id', 'desc')->get();

        return Inertia::render('library/feedType/List', [
            'feedTypes' => $feedTypes,
            'filters'   => $request->only(['search', 'per_page', 'page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        FeedType::create($validated);

        return redirect()->back()->with('success', 'Feed Type created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $feedType = FeedType::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        $feedType->update($validated);

        return redirect()->back()->with('success', 'Feed Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $feedType = FeedType::findOrFail($id);
        $feedType->delete();

        return redirect()->back()->with('success', 'Feed Type deleted successfully!');
    }
}
