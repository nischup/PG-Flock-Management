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
            $sheds = Shed::orderBy('id', 'desc')->get()->map(function ($shed) {
                return [
                    'id'         => $shed->id,
                    'name'       => $shed->name,
                    'status'     => (int) $shed->status, // keep as 0/1
                    'created_at' => $shed->created_at->format('Y-m-d'),
                ];
            });

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
            Shed::create([
                'name'   => $request->name,
                'status' => (int) $request->status,
            ]);

            return redirect()->route('shed.index')->with('success', 'Shed created successfully!');
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
            $shed->update([
                'name'   => $request->name,
                'status' => (int) $request->status,
            ]);

            return redirect()->route('shed.index')->with('success', 'Shed updated successfully!');
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
