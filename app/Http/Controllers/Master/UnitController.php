<?php

namespace App\Http\Controllers\Master;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Unit::query();

            // Search filter
            if ($search = $request->get('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            // Status filter
            if ($status = $request->get('status')) {
                $statusValue = $status === 'Active' ? 1 : 0;
                $query->where('status', $statusValue);
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
            $units = $query->latest()->paginate($perPage)->withQueryString()->through(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'status' => $unit->status ? 'Active' : 'Inactive',
                    'created_at' => $unit->created_at ? $unit->created_at->format('Y-m-d H:i') : null,
                ];
            });

            return Inertia::render('library/unit/List', [
                'units' => $units,
                'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'per_page']),
            ]);
        } catch (\Exception $e) {
            Log::error('Unit Index Error: '.$e->getMessage());

            return Inertia::render('library/unit/List', [
                'units' => [],
                'error' => 'Failed to load units.',
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            Unit::create([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('unit.index')->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Store Error: '.$e->getMessage());

            return redirect()->route('unit.index')->with('error', 'Failed to create unit.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            $unit = Unit::findOrFail($id);
            $unit->update([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('unit.index')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Update Error: '.$e->getMessage());

            return redirect()->route('unit.index')->with('error', 'Failed to update unit.');
        }
    }

    public function destroy(string $id)
    {
        try {
            Unit::findOrFail($id)->delete();

            return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Delete Error: '.$e->getMessage());

            return redirect()->route('unit.index')->with('error', 'Failed to delete unit.');
        }
    }

    // PDF Export
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = Unit::latest();

        // Apply filters
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $statusValue = $request->status === 'Active' ? 1 : 0;
            $query->where('status', $statusValue);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $units = $query->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'status' => $u->status ? 'Active' : 'Inactive',
                'created_at' => $u->created_at ? $u->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Unit List',
            'columns' => $columns,
            'rows' => $units,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('unit-list.pdf');
    }

    // Excel Export
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = Unit::latest();

        // Apply filters
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $statusValue = $request->status === 'Active' ? 1 : 0;
            $query->where('status', $statusValue);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $rows = $query->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'status' => $u->status ? 'Active' : 'Inactive',
                'created_at' => $u->created_at ? $u->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $headings = array_map(fn ($c) => $c['label'], $columns);

        $body = [];
        foreach ($rows as $i => $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = isset($col['callback']) && is_callable($col['callback'])
                    ? $col['callback']($row, $i)
                    : ($row[$col['key']] ?? '');
                $line[] = is_array($val) ? json_encode($val) : $val;
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'units-report.xlsx');
    }
}
