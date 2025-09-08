<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\FeedType;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

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

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = FeedType::orderBy('id', 'desc')
            ->get()
            ->map(fn($ft) => [
                'name' => $ft->name,
                'status' => $ft->status,
                'created_at' => $ft->created_at?->format('d-m-Y') ?? '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Feed Type Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Feed Type List',
            'columns' => $columns,
            'rows' => $rows,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('feed-type-list.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = FeedType::orderBy('id', 'desc')
            ->get()
            ->map(fn($ft) => [
                'name' => $ft->name,
                'status' => $ft->status,
                'created_at' => $ft->created_at?->format('d-m-Y') ?? '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Feed Type Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $headings = array_map(fn($c) => $c['label'], $columns);

        $body = [];
        foreach ($rows as $i => $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = $col['callback'] ?? null;
                $line[] = $val && is_callable($val) ? $val($row, $i) : ($row[$col['key']] ?? '');
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'feed-type-report.xlsx');
    }
}
