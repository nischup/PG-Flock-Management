<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Shed;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class ShedController extends Controller
{
    public function index()
    {
        try {
            $sheds = Shed::orderBy('id', 'desc')->get()->map(function ($shed) {
                return [
                    'id' => $shed->id,
                    'name' => $shed->name,
                    'status' => (int) $shed->status,
                    'created_at' => $shed->created_at->format('d-M-Y'),
                ];
            });

            return Inertia::render('library/shed/List', [
                'sheds' => $sheds,
            ]);
        } catch (\Exception $e) {
            Log::error('Shed index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to load sheds.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            Shed::create([
                'name' => $request->name,
                'status' => (int) $request->status,
            ]);

            return redirect()->route('shed.index')->with('success', 'Shed created successfully!');
        } catch (\Exception $e) {
            Log::error('Shed store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create shed.');
        }
    }

    public function update(Request $request, Shed $shed)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        try {
            $shed->update([
                'name' => $request->name,
                'status' => (int) $request->status,
            ]);

            return redirect()->route('shed.index')->with('success', 'Shed updated successfully!');
        } catch (\Exception $e) {
            Log::error('Shed update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to update shed.');
        }
    }

    public function destroy(Shed $shed)
    {
        try {
            $shed->delete();
            return redirect()->route('shed.index')->with('success', 'Shed deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Shed delete error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete shed.');
        }
    }

    // ------------------- PDF EXPORT -------------------
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $sheds = Shed::orderBy('id', 'desc')->get()->map(function ($s) {
            return [
                'name' => $s->name,
                'status' => (int) $s->status,
                'created_at' => $s->created_at ? $s->created_at->format('d-M-Y') : '',
            ];
        })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Shed List',
            'columns' => $columns,
            'rows' => $sheds,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('shed-list.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Shed::orderBy('id', 'desc')->get()->map(function ($s) {
            return [
                'name' => $s->name,
                'status' => (int) $s->status,
                'created_at' => $s->created_at ? $s->created_at->format('d-M-Y') : '',
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

        return Excel::download(new ArrayExport($headings, $body), 'sheds-report.xlsx');
    }
}
