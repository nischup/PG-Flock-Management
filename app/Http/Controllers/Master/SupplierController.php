<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'supplier_type' => $supplier->supplier_type,
                'address' => $supplier->address,
                'origin' => $supplier->origin,
                'contact_person' => $supplier->contact_person,
                'contact_person_email' => $supplier->contact_person_email,
                'contact_person_mobile' => $supplier->contact_person_mobile,
                'status' => $supplier->status,
                'created_at' => $supplier->created_at->format('d M Y'),
            ];
        });

        return Inertia::render('library/supplier/List', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'supplier_type'         => 'required|in:Local,Foreign',
            'address'               => 'nullable|string|max:500',
            'origin'                => 'nullable|string|max:255',
            'contact_person'        => 'nullable|string|max:255',
            'contact_person_email'  => 'nullable|email|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'status'                => 'required|in:0,1',
        ]);

        Supplier::create($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'                  => 'sometimes|required|string|max:200',
            'supplier_type'         => 'sometimes|required|in:Local,Foreign',
            'address'               => 'nullable|string|max:500',
            'origin'                => 'nullable|string|max:255',
            'contact_person'        => 'nullable|string|max:255',
            'contact_person_email'  => 'nullable|email|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'status'                => 'sometimes|required|in:0,1',
        ]);

        $supplier->update($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('supplier.index')->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Download PDF report of suppliers.
     */
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $suppliers = Supplier::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($s) => [
                'name' => $s->name,
                'type' => $s->supplier_type,
                'address' => $s->address,
                'contact' => $s->contact_person,
                'status' => $s->status ? 'Active' : 'Inactive',
                'created_at' => $s->created_at?->format('Y-m-d H:i'),
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Type', 'key' => 'type'],
            ['label' => 'Address', 'key' => 'address'],
            ['label' => 'Contact', 'key' => 'contact'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Supplier List',
            'columns' => $columns,
            'rows' => $suppliers,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('suppliers-list.pdf');
    }

    /**
     * Download Excel report of suppliers.
     */
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Supplier::latest()
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get()
            ->map(fn($s) => [
                'name' => $s->name,
                'type' => $s->supplier_type,
                'address' => $s->address,
                'contact' => $s->contact_person,
                'status' => $s->status ? 'Active' : 'Inactive',
                'created_at' => $s->created_at?->format('Y-m-d H:i'),
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Type', 'key' => 'type'],
            ['label' => 'Address', 'key' => 'address'],
            ['label' => 'Contact', 'key' => 'contact'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Created At', 'key' => 'created_at'],
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

        return Excel::download(new ArrayExport($headings, $body), 'suppliers-report.xlsx');
    }
}
