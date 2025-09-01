<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Master\Vaccine;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class VaccineReportController extends Controller
{
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Fetch vaccines with optional search
        $vaccines = Vaccine::with('vaccineType')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            })
            ->orderBy($request->get('sort', 'id'), $request->get('direction', 'desc'))
            ->get()
            ->map(fn($v) => [
                // 'id' => $v->id,
                'name' => $v->name,
                'vaccine_type' => $v->vaccineType->name ?? '',
                'applicator' => $v->applicator,
                'dose' => $v->dose,
                'note' => $v->note,
                'status' => $v->status,
                'created_at' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'class'=>'right', 'callback'=>fn($r,$i)=>$i+1],
            // ['label'=>'ID', 'key'=>'id', 'nowrap'=>true],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Vaccine Type', 'key'=>'vaccine_type'],
            ['label'=>'Applicator', 'key'=>'applicator'],
            ['label'=>'Dose', 'key'=>'dose'],
            ['label'=>'Note', 'key'=>'note'],
            ['label'=>'Status', 'key'=>'status', 'callback'=>fn($r)=> $r['status'] ? 'Active' : 'Inactive', 'nowrap'=>true],
            ['label'=>'Created', 'key'=>'created_at', 'nowrap'=>true],
        ];

        $data = [
            'title' => 'Vaccine List',
            'columns' => $columns,
            'rows' => $vaccines,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled'=>true, 'isRemoteEnabled'=>true, 'defaultFont'=>'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('vaccine-list.pdf');
    }

    public function downloadExcel(Request $request)
{
    ini_set('memory_limit', '512M');
    set_time_limit(120);

    // Fetch rows exactly like PDF method (same mapping)
    $rows = Vaccine::with('vaccineType')
        ->when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%");
        })
        ->orderBy($request->get('sort', 'id'), $request->get('direction', 'desc'))
        ->get()
        ->map(fn($v) => [
            // 'id' => $v->id,
            'name' => $v->name,
            'vaccine_type' => $v->vaccineType->name ?? '',
            'applicator' => $v->applicator,
            'dose' => $v->dose,
            'note' => $v->note,
            'status' => $v->status,
            'created_at' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : '',
        ])->toArray();

    // same columns definition (keep same as PDF for parity)
    $columns = [
        ['label'=>'#', 'key'=>'index', 'class'=>'right', 'callback'=>fn($r,$i)=> $i+1],
        // ['label'=>'ID', 'key'=>'id', 'nowrap'=>true],
        ['label'=>'Name', 'key'=>'name'],
        ['label'=>'Vaccine Type', 'key'=>'vaccine_type'],
        ['label'=>'Applicator', 'key'=>'applicator'],
        ['label'=>'Dose', 'key'=>'dose'],
        ['label'=>'Note', 'key'=>'note'],
        ['label'=>'Status', 'key'=>'status', 'callback'=>fn($r)=> $r['status'] ? 'Active' : 'Inactive', 'nowrap'=>true],
        ['label'=>'Created', 'key'=>'created_at', 'nowrap'=>true],
    ];

    // Build headings and body arrays for Excel
    $headings = array_map(fn($c) => $c['label'], $columns);

    $body = [];
    foreach ($rows as $i => $row) {
        $line = [];
        foreach ($columns as $col) {
            if (!empty($col['callback']) && is_callable($col['callback'])) {
                // callbacks use $row and $index
                $val = $col['callback']($row, $i);
            } else {
                $val = $row[$col['key']] ?? '';
            }
            // make sure value is scalar
            if (is_array($val)) {
                $val = json_encode($val);
            }
            $line[] = $val;
        }
        $body[] = $line;
    }

    // Download Excel using ArrayExport
    return Excel::download(new ArrayExport($headings, $body), 'vaccines-report.xlsx');

    // --- OR use the view approach (uncomment to use) ---
    // $data = [
    //     'title' => 'Vaccine List',
    //     'columns' => $columns,
    //     'rows' => $rows,
    //     'generatedAt' => now(),
    // ];
    // return Excel::download(new \App\Exports\ViewExport('reports.common.list', $data), 'vaccines-report.xlsx');
}
}
