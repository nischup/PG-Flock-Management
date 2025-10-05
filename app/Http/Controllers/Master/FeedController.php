<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Feed;
use App\Models\Master\FeedType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Feed::with('feedType');

            // Search filter
            if ($search = $request->get('search')) {
                $query->where('feed_name', 'like', "%{$search}%")
                      ->orWhereHas('feedType', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            }

            // Status filter
            if ($status = $request->get('status')) {
                $statusValue = $status === 'Active' ? 1 : 0;
                $query->where('status', $statusValue);
            }

            // Feed Type filter
            if ($feedType = $request->get('feed_type')) {
                $query->where('feed_type_id', $feedType);
            }

            // Date range filters
            if ($dateFrom = $request->get('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            
            if ($dateTo = $request->get('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Per page
            $perPage = $request->get('per_page', 10);
            
            // Get paginated results
            $feeds = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString()->through(function ($feed) {
                return [
                    'id' => $feed->id,
                    'feed_type_id' => $feed->feed_type_id,
                    'feed_type_name' => $feed->feedType->name ?? '',
                    'feed_name' => $feed->feed_name,
                    'status' => $feed->status ? 'Active' : 'Inactive',
                    'created_at' => $feed->created_at ? $feed->created_at->format('Y-m-d H:i') : null,
                ];
            });

            $feedTypes = FeedType::where('status', 1)->orderBy('name')->get()->map(function ($ft) {
                return [
                    'id' => $ft->id,
                    'name' => $ft->name,
                ];
            })->toArray();

            return Inertia::render('library/feed/List', [
                'feeds' => $feeds,
                'feedTypes' => $feedTypes,
                'filters' => $request->only(['search', 'status', 'feed_type', 'date_from', 'date_to', 'per_page']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Feed Index Error: ' . $e->getMessage());
            return Inertia::render('library/feed/List', [
                'feeds' => [],
                'feedTypes' => [],
                'error' => 'Failed to load feeds.',
            ]);
        }
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
                    'created_at' => $feed->created_at->format('d M Y'),
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
                    'created_at' => $feed->created_at->format('d M Y'),
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

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = Feed::with('feedType');
        
        // Apply filters
        if ($request->search) {
            $query->where('feed_name', 'like', "%{$request->search}%")
                  ->orWhereHas('feedType', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
        }
        
        if ($request->status) {
            $statusValue = $request->status === 'Active' ? 1 : 0;
            $query->where('status', $statusValue);
        }
        
        if ($request->feed_type) {
            $query->where('feed_type_id', $request->feed_type);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $feeds = $query->orderBy('id', 'desc')
            ->get()
            ->map(fn($f) => [
                'feed_type' => $f->feedType->name ?? '',
                'feed_name' => $f->feed_name,
                'status' => $f->status ? 'Active' : 'Inactive',
                'created_at' => $f->created_at ? $f->created_at->format('d-m-Y') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Feed Type', 'key' => 'feed_type'],
            ['label' => 'Feed Name', 'key' => 'feed_name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Feed List',
            'columns' => $columns,
            'rows' => $feeds,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('feed-list.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = Feed::with('feedType');
        
        // Apply filters
        if ($request->search) {
            $query->where('feed_name', 'like', "%{$request->search}%")
                  ->orWhereHas('feedType', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
        }
        
        if ($request->status) {
            $statusValue = $request->status === 'Active' ? 1 : 0;
            $query->where('status', $statusValue);
        }
        
        if ($request->feed_type) {
            $query->where('feed_type_id', $request->feed_type);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $rows = $query->orderBy('id', 'desc')
            ->get()
            ->map(fn($f) => [
                'feed_type' => $f->feedType->name ?? '',
                'feed_name' => $f->feed_name,
                'status' => $f->status ? 'Active' : 'Inactive',
                'created_at' => $f->created_at ? $f->created_at->format('d-m-Y') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Feed Type', 'key' => 'feed_type'],
            ['label' => 'Feed Name', 'key' => 'feed_name'],
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

        return Excel::download(new ArrayExport($headings, $body), 'feeds-report.xlsx');
    }
}
