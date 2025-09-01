<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Master\Vaccine;

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
                'id' => $v->id,
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
            ['label'=>'ID', 'key'=>'id', 'nowrap'=>true],
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
}
