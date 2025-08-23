<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\FeedType;
use Illuminate\Support\Facades\Log;

class FeedTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = FeedType::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $feedTypes = $query->orderBy('id', 'desc')->get()->map(function ($ft) {
                return [
                    'id' => $ft->id,
                    'name' => $ft->name,
                    'status' => $ft->status,
                    'created_at' => $ft->created_at->format('d M Y'),
                    'updated_at' => $ft->updated_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/feedType/List', [
                'feedTypes' => $feedTypes,
                'filters' => $request->only(['search', 'per_page', 'page']),
            ]);
        } catch (\Exception $e) {
            Log::error('FeedType Index Error: ' . $e->getMessage());
            return Inertia::render('library/feedType/List', [
                'feedTypes' => [],
                'filters' => $request->only(['search', 'per_page', 'page']),
                'error' => 'Failed to load feed types.',
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

            $feedType = FeedType::create($validated);

            return redirect()->back()->with('success', 'Feed Type created successfully!');
        } catch (\Exception $e) {
            Log::error('FeedType Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create feed type.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $feedType = FeedType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|integer|in:0,1',
            ]);

            $feedType->update($validated);

            return redirect()->back()->with('success', 'Feed Type updated successfully!');
        } catch (\Exception $e) {
            Log::error('FeedType Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update feed type.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $feedType = FeedType::findOrFail($id);
            $feedType->delete();

            return redirect()->back()->with('success', 'Feed Type deleted successfully!');
        } catch (\Exception $e) {
            Log::error('FeedType Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete feed type.');
        }
    }
}
