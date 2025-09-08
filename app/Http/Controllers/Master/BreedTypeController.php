<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\BreedType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use Illuminate\Support\Facades\Log;

class BreedTypeController extends Controller
{
    public function index()
    {
        try {
            $breedTypes = BreedType::orderBy('id', 'desc')->get()->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->name,
                    'status'     => $item->status,
                    'created_at' => $item->created_at->format('d M Y'),
                    'updated_at' => $item->updated_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/breedType/List', [
                'breedTypes' => $breedTypes
            ]);
        } catch (\Exception $e) {
            Log::error('BreedType index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to load Breed Types.');
        }
    }

    public function create()
    {
        try {
            return Inertia::render('library/breedType/Create');
        } catch (\Exception $e) {
            Log::error('BreedType create error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to open create form.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        try {
            BreedType::create([
                'name'   => $request->name,
                'status' => $request->status ?? 1,
            ]);

            return redirect()->route('breed-type.index')
                ->with('success', 'Breed Type created successfully.');
        } catch (\Exception $e) {
            Log::error('BreedType store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create Breed Type.');
        }
    }

    public function show(string $id)
    {
        try {
            $breedType = BreedType::findOrFail($id);
            return Inertia::render('library/breedType/Show', [
                'breedType' => $breedType
            ]);
        } catch (\Exception $e) {
            Log::error('BreedType show error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to show Breed Type.');
        }
    }

    public function edit(string $id)
    {
        try {
            $breedType = BreedType::findOrFail($id);
            return Inertia::render('library/breedType/Edit', [
                'breedType' => $breedType
            ]);
        } catch (\Exception $e) {
            Log::error('BreedType edit error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to open edit form.');
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'nullable|integer',
        ]);

        try {
            $breedType = BreedType::findOrFail($id);
            $breedType->update([
                'name'   => $request->name,
                'status' => $request->status ?? 1,
            ]);

            return redirect()->route('breed-type.index')
                ->with('success', 'Breed Type updated successfully.');
        } catch (\Exception $e) {
            Log::error('BreedType update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to update Breed Type.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $breedType = BreedType::findOrFail($id);
            $breedType->delete();

            return redirect()->route('breed-type.index')
                ->with('success', 'Breed Type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('BreedType delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Breed Type.');
        }
    }

    // ------------------- PDF EXPORT -------------------
    public function exportPdf(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $rows = BreedType::orderBy('id', 'desc')->get()->map(function ($b) {
                return [
                    'name'       => $b->name,
                    'status'     => (int) $b->status,
                    'created_at' => $b->created_at?->format('d-M-Y') ?? '',
                ];
            })->toArray();

            $columns = [
                ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ];

            $data = [
                'title'       => 'Breed Types List',
                'columns'     => $columns,
                'rows'        => $rows,
                'filters'     => $request->all(),
                'generatedAt' => now(),
                'orientation' => $request->get('orientation', 'portrait'),
            ];

            Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
            $pdf = Pdf::loadView('reports.common.list', $data)
                ->setPaper('a4', $data['orientation']);

            return $pdf->stream('breed-types-list.pdf');
        } catch (\Exception $e) {
            Log::error('BreedType export PDF error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF.');
        }
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $rows = BreedType::orderBy('id', 'desc')->get()->map(function ($b) {
                return [
                    'name'       => $b->name,
                    'status'     => (int) $b->status,
                    'created_at' => $b->created_at?->format('d-M-Y') ?? '',
                ];
            })->toArray();

            $columns = [
                ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ];

            // Use array_map to build Excel body
            $body = array_map(function ($row, $i) use ($columns) {
                return array_map(function ($col) use ($row, $i) {
                    $callback = $col['callback'] ?? null;
                    return $callback && is_callable($callback) ? $callback($row, $i) : ($row[$col['key']] ?? '');
                }, $columns);
            }, $rows, array_keys($rows));

            $headings = array_map(fn($c) => $c['label'], $columns);

            return Excel::download(new ArrayExport($headings, $body), 'breed-types-report.xlsx');
        } catch (\Exception $e) {
            Log::error('BreedType export Excel error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel.');
        }
    }
}
