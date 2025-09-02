<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Unit;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Unit::query();

            if ($search = $request->get('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            $units = $query->latest()->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'status' => $unit->status ? 'Active' : 'Inactive', // FIXED
                    'created_at' => $unit->created_at ? $unit->created_at->format('Y-m-d H:i') : null,
                ];
            });

            return Inertia::render('library/unit/List', [
                'units' => $units,
                'filters' => $request->only(['search']),
            ]);
        } catch (\Exception $e) {
            Log::error('Unit Index Error: ' . $e->getMessage());
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
            Log::error('Unit Store Error: ' . $e->getMessage());
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
            Log::error('Unit Update Error: ' . $e->getMessage());
            return redirect()->route('unit.index')->with('error', 'Failed to update unit.');
        }
    }

    public function destroy(string $id)
    {
        try {
            Unit::findOrFail($id)->delete();
            return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Unit Delete Error: ' . $e->getMessage());
            return redirect()->route('unit.index')->with('error', 'Failed to delete unit.');
        }
    }

    // PDF Export
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $units = Unit::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'status' => $u->status ? 'Active' : 'Inactive', // FIXED
                'created_at' => $u->created_at ? $u->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=> $i+1],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Status', 'key'=>'status'],
            ['label'=>'Created At', 'key'=>'created_at'],
        ];

        $data = [
            'title' => 'Unit List',
            'columns' => $columns,
            'rows' => $units,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled'=>true, 'isRemoteEnabled'=>true, 'defaultFont'=>'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('unit-list.pdf');
    }

    // Excel Export
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Unit::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'status' => $u->status ? 'Active' : 'Inactive', // FIXED
                'created_at' => $u->created_at ? $u->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=> $i+1],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Status', 'key'=>'status'],
            ['label'=>'Created At', 'key'=>'created_at'],
        ];

        $headings = array_map(fn($c) => $c['label'], $columns);

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
