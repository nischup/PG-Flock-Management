<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Disease;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;


// namespace App\Http\Controllers\Reports\Master;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Barryvdh\DomPDF\Facade\Pdf;
// use App\Models\Master\Vaccine;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\ArrayExport;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Disease::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $diseases = $query->latest()->get()->map(function ($disease) {
                return [
                    'id' => $disease->id,
                    'name' => $disease->name,
                    'status' => $disease->status ? 'Active' : 'Inactive',
                    'created_at' => $disease->created_at ? $disease->created_at->format('d M Y') : null,
                ];
            });

            return Inertia::render('library/disease/List', [
                'diseases' => $diseases->toArray(),
                'filters' => $request->only(['search', 'per_page', 'page']),
            ]);
        } catch (\Exception $e) {
            Log::error('Disease Index Error: ' . $e->getMessage());
            return Inertia::render('library/disease/List', [
                'diseases' => [],
                'filters' => $request->only(['search', 'per_page', 'page']),
                'error' => 'Failed to load diseases.',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            Disease::create([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('disease.index')->with('success', 'Disease created successfully.');
        } catch (\Exception $e) {
            Log::error('Disease Store Error: ' . $e->getMessage());
            return redirect()->route('disease.index')->with('error', 'Failed to create disease.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            $disease = Disease::findOrFail($id);
            $disease->update([
                'name' => $data['name'],
                'status' => $data['status'] === 'Active' ? 1 : 0,
            ]);

            return redirect()->route('disease.index')->with('success', 'Disease updated successfully.');
        } catch (\Exception $e) {
            Log::error('Disease Update Error: ' . $e->getMessage());
            return redirect()->route('disease.index')->with('error', 'Failed to update disease.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Disease::findOrFail($id)->delete();
            return redirect()->route('disease.index')->with('success', 'Disease deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Disease Delete Error: ' . $e->getMessage());
            return redirect()->route('disease.index')->with('error', 'Failed to delete disease.');
        }
    }

    /**
     * Toggle the status of a disease.
     */
    public function toggleStatus($id)
    {
        try {
            $disease = Disease::findOrFail($id);
            $disease->status = $disease->status ? 0 : 1;
            $disease->save();

            return redirect()->route('disease.index')->with('success', 'Disease status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Disease Toggle Status Error: ' . $e->getMessage());
            return redirect()->route('disease.index')->with('error', 'Failed to update disease status.');
        }
    }

    /**
     * Export PDF report
     */
    public function exportPdf(Request $request)
    {
        $query = Disease::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $diseases = $query->latest()->get()->map(function ($disease) {
            return [
                'name' => $disease->name,
                'status' => $disease->status ? 'Active' : 'Inactive',
                'created_at' => $disease->created_at ? $disease->created_at->format('d M Y') : '',
            ];
        })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Disease List',
            'columns' => $columns,
            'rows' => $diseases,
            'generatedAt' => now(),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('disease-list.pdf');
    }

    /**
     * Export Excel report using ArrayExport
     */
    public function exportExcel(Request $request)
    {
        $rows = Disease::latest()
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->get()
            ->map(fn($disease) => [
                'name' => $disease->name,
                'status' => $disease->status ? 'Active' : 'Inactive',
                'created_at' => $disease->created_at ? $disease->created_at->format('d M Y') : '',
            ])
            ->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $headings = array_map(fn($c) => $c['label'], $columns);

        $body = [];
        foreach ($rows as $i => $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = isset($col['callback']) ? $col['callback']($row, $i) : $row[$col['key']] ?? '';
                $line[] = $val;
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'disease-report.xlsx');
    }
}
