<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Shed;
use Illuminate\Support\Facades\Log;

class ShedController extends Controller
{
    public function index()
    {
        try {
            // Get all sheds as array
            $sheds = Shed::orderBy('id', 'desc')->get()->map(function($shed) {
                return [
                    'id' => $shed->id,
                    'name' => $shed->name,
                    'status' => $shed->status,
                    'created_at' => $shed->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            return Inertia::render('library/shed/List', [
                'sheds' => $sheds,
            ]);
        } catch (\Exception $e) {
            Log::error('Shed index error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to load sheds.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $shed = Shed::create($request->all());

            // Return new shed as array
            $newShed = [
                'id' => $shed->id,
                'name' => $shed->name,
                'status' => $shed->status,
                'created_at' => $shed->created_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('shed.index')->with('success', 'Shed created successfully!')->with('newShed', $newShed);
        } catch (\Exception $e) {
            Log::error('Shed store error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create shed.');
        }
    }

    public function update(Request $request, Shed $shed)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $shed->update($request->all());

            $updatedShed = [
                'id' => $shed->id,
                'name' => $shed->name,
                'status' => $shed->status,
                'created_at' => $shed->created_at->format('Y-m-d H:i:s'),
            ];

            return redirect()->route('shed.index')->with('success', 'Shed updated successfully!')->with('updatedShed', $updatedShed);
        } catch (\Exception $e) {
            Log::error('Shed update error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to update shed.');
        }
    }

    public function destroy(Shed $shed)
    {
        try {
            $shed->delete();
            return redirect()->route('shed.index')->with('success', 'Shed deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Shed delete error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete shed.');
        }
    }
}
