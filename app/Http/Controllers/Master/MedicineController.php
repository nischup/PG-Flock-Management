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
    public function index(Request $request)
    {
        try {
            $query = Medicine::query();

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
            $medicines = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString()->through(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'status' => $medicine->status ? 'Active' : 'Inactive',
                    'created_at' => $medicine->created_at ? $medicine->created_at->format('Y-m-d H:i') : null,
                ];
            });

            return Inertia::render('library/medicine/List', [
                'medicines' => $medicines,
                'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'per_page']),
            ]);
        } catch (\Exception $e) {
            Log::error('Medicine index error: ' . $e->getMessage());
            return Inertia::render('library/medicine/List', [
                'medicines' => [],
                'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'per_page']),
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

        $query = Medicine::query();
        
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

        $rows = $query->orderBy('id', 'desc')->get()->map(fn($m) => [
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

        $query = Medicine::query();
        
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

        $rows = $query->orderBy('id', 'desc')->get()->map(fn($m) => [
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
