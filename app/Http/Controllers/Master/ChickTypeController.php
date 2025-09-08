<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\ChickType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class ChickTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chickTypes = ChickType::orderBy('id', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'status' => $item->status,
                'created_at' => $item->created_at->format('d M Y'),
                'updated_at' => $item->updated_at->format('d M Y'),
            ];
        });

        return Inertia::render('library/chickType/List', [
            'chickTypes' => $chickTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('library/chickType/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        ChickType::create([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $chickType = ChickType::findOrFail($id);

        return Inertia::render('library/chickType/Show', [
            'chickType' => $chickType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $chickType = ChickType::findOrFail($id);

        return Inertia::render('library/chickType/Edit', [
            'chickType' => $chickType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        $chickType = ChickType::findOrFail($id);

        $chickType->update([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $chickType = ChickType::findOrFail($id);
        $chickType->delete();

        return redirect()->route('chick-type.index')
            ->with('success', 'Chick Type deleted successfully.');
    }
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = ChickType::orderBy('id', 'desc')->get()->map(fn($c) => [
            'name' => $c->name,
            'status' => $c->status,
            'created_at' => $c->created_at ? $c->created_at->format('d-m-Y') : '',
        ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Chick Type Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Chick Types Report',
            'columns' => $columns,
            'rows' => $rows,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('chick-types-report.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = ChickType::orderBy('id', 'desc')->get()->map(fn($c) => [
            'name' => $c->name,
            'status' => $c->status,
            'created_at' => $c->created_at ? $c->created_at->format('d-m-Y') : '',
        ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Chick Type Name', 'key' => 'name'],
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

        return Excel::download(new ArrayExport($headings, $body), 'chick-types-report.xlsx');
    }
}
