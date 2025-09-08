<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Medicine;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        try {
            $medicines = Medicine::latest()
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'status' => (int) $m->status, // 0 or 1
                'created_at' => $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : '',
            ])->toArray();

            return Inertia::render('library/medicine/List', [
                'medicines' => $medicines,
                'filters' => request()->all(),
            ]);
        } catch (\Exception $e) {
            Log::error('Medicine index error: ' . $e->getMessage());
            return Inertia::render('library/medicine/List', [
                'medicines' => [],
                'filters' => request()->all(),
                'error' => 'Failed to fetch medicines.',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        $medicine = Medicine::create($validated);

        return redirect()->route('medicine.index')
            ->with('success', 'Medicine created successfully');
    } catch (\Exception $e) {
        Log::error('Medicine store error: ' . $e->getMessage());
        return back()->with('error', 'Failed to create medicine.');
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medicine $medicine)
    {
        try {
            $validated = $request->validate([
                'name'   => 'required|string|max:200',
                'status' => 'required|in:0,1',
            ]);

            $medicine->update([
                'name' => $validated['name'],
                'status' => (int) $validated['status'],
            ]);

            $data = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'status' => $medicine->status,
                'created_at' => $medicine->created_at->format('d M Y'),
                'updated_at' => $medicine->updated_at->format('d M Y'),
            ];

            return redirect()->route('medicine.index')->with('success', 'Medicine updated successfully')->with('medicine', $data);
        } catch (\Exception $e) {
            Log::error('Medicine update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update medicine.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medicine $medicine)
    {
        try {
            $medicine->delete();
            return redirect()->route('medicine.index')->with('success', 'Medicine deleted successfully');
        } catch (\Exception $e) {
            Log::error('Medicine delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete medicine.');
        }
    }

     // 📌 PDF Export
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Medicine::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($m) => [
                'name' => $m->name,
                'status' => $m->status ? 'Active' : 'Inactive',
                'created_at' => $m->created_at ? $m->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=> $i+1],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Status', 'key'=>'status'],
            ['label'=>'Created At', 'key'=>'created_at'],
        ];

        $data = [
            'title' => 'Medicine List',
            'columns' => $columns,
            'rows' => $rows,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled'=>true, 'isRemoteEnabled'=>true, 'defaultFont'=>'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('medicine-list.pdf');
    }

    // 📌 Excel Export
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Medicine::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($m) => [
                'name' => $m->name,
                'status' => $m->status ? 'Active' : 'Inactive',
                'created_at' => $m->created_at ? $m->created_at->format('Y-m-d H:i') : '',
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

        return Excel::download(new ArrayExport($headings, $body), 'medicine-report.xlsx');
    }
}
