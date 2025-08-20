<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Feed;
use App\Models\Master\FeedType;

class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::orderBy('id', 'desc')->get();
        $feedTypes = FeedType::where('status', 1)->orderBy('name')->get();

        return Inertia::render('library/feed/List', [
            'feeds' => $feeds->map(function ($feed) {
                return [
                    'id' => $feed->id,
                    'feed_type_id' => $feed->feed_type_id,
                    'feed_type_name' => $feed->feedType->name ?? '',
                    'feed_name' => $feed->feed_name,
                    'status' => $feed->status,
                    'created_at' => $feed->created_at->format('Y-m-d'),
                ];
            })->toArray(),
            'feedTypes' => $feedTypes->map(function ($ft) {
                return [
                    'id' => $ft->id,
                    'name' => $ft->name,
                ];
            })->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feed_type_id' => 'required',
            'feed_name' => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        $feed = Feed::create($validated);
        $feed->load('feedType');

        if ($request->wantsJson()) {
            return response()->json([
                'feed' => [
                    'id' => $feed->id,
                    'feed_type_id' => $feed->feed_type_id,
                    'feed_type_name' => $feed->feedType->name ?? '',
                    'feed_name' => $feed->feed_name,
                    'status' => $feed->status,
                    'created_at' => $feed->created_at->format('Y-m-d'),
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Feed created successfully.');
    }

    public function update(Request $request, $id)
    {
        $feed = Feed::findOrFail($id);

        $validated = $request->validate([
            'feed_type_id' => 'required',
            'feed_name' => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        $feed->update($validated);
        $feed->load('feedType');

        if ($request->wantsJson()) {
            return response()->json([
                'feed' => [
                    'id' => $feed->id,
                    'feed_type_id' => $feed->feed_type_id,
                    'feed_type_name' => $feed->feedType->name ?? '',
                    'feed_name' => $feed->feed_name,
                    'status' => $feed->status,
                    'created_at' => $feed->created_at->format('Y-m-d'),
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Feed updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $feed = Feed::findOrFail($id);
        $feed->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Feed deleted successfully.');
    }
}
