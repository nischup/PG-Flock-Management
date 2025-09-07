<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\VaccineType;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class VaccineTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = VaccineType::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $vaccineTypes = $query->orderBy('id', 'desc')->get()->map(function ($vt) {
                return [
                    'id' => $vt->id,
                    'name' => $vt->name,
                    'status' => $vt->status,
                    'created_at' => $vt->created_at->format('d M Y'),
                    'updated_at' => $vt->updated_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/vaccineType/List', [
                'vaccineTypes' => $vaccineTypes,
                'filters' => $request->only(['search', 'per_page', 'page']),
            ]);
        } catch (\Exception $e) {
            Log::error('VaccineType Index Error: ' . $e->getMessage());
            return Inertia::render('library/vaccineType/List', [
                'vaccineTypes' => [],
                'filters' => $request->only(['search', 'per_page', 'page']),
                'error' => 'Failed to load vaccine types.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        $vaccineType = VaccineType::create($validated);

        // Proper Inertia response for useForm()
        return redirect()->back()->with('vaccineType', [
            'id' => $vaccineType->id,
            'name' => $vaccineType->name,
            'status' => $vaccineType->status,
            'created_at' => $vaccineType->created_at->format('d M Y'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $vaccineType = VaccineType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|integer|in:0,1',
        ]);

        $vaccineType->update($validated);

        return redirect()->back()->with('vaccineType', [
            'id' => $vaccineType->id,
            'name' => $vaccineType->name,
            'status' => $vaccineType->status,
            'created_at' => $vaccineType->created_at->format('d M Y'),
        ]);
    }

    public function destroy($id)
    {
        $vaccineType = VaccineType::findOrFail($id);
        $vaccineType->delete();

        return redirect()->back()->with('success', 'Vaccine Type deleted successfully!');
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $vaccineTypes = VaccineType::orderBy('id', 'desc')->get()->map(function ($vt) {
            return [
                'name' => $vt->name,
                'status' => (int) $vt->status,
                'created_at' => $vt->created_at ? $vt->created_at->format('d-M-Y') : '',
            ];
        })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Vaccine Type List',
            'columns' => $columns,
            'rows' => $vaccineTypes,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('vaccine-types.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = VaccineType::orderBy('id', 'desc')->get()->map(function ($vt) {
            return [
                'name' => $vt->name,
                'status' => (int) $vt->status,
                'created_at' => $vt->created_at ? $vt->created_at->format('d-M-Y') : '',
            ];
        })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
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

        return Excel::download(new ArrayExport($headings, $body), 'vaccine-types.xlsx');
    }
}
