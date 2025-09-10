<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsFirmReceive;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;


class PsFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $psFirmReceives = PsFirmReceive::with(['flock', 'company'])
            // ->where('source_type', 'psreceive')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas(
                        'psReceive',
                        fn($q2) =>
                        $q2->where('pi_no', 'like', "%{$search}%")
                    )
                        ->orWhereHas(
                            'flock',
                            fn($q3) =>
                            $q3->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->paginate($request->per_page ?? 10)
            ->withQueryString();




        return Inertia::render('ps/ps-firm-receive/List', [
            'psFirmReceives' => $psFirmReceives->through(fn($item) => [
                'id' => $item->id,
                'job_no' => $item->job_no,
                'flock_name' => $item->flock->name ?? '-',
                'company_name' => $item->company->name ?? '-',
                'pi_no' => $item->psReceive->pi_no ?? '-',
                'firm_male_qty' => $item->firm_male_qty,
                'firm_female_qty' => $item->firm_female_qty,
                'firm_total_qty' => $item->firm_total_qty,
                'remarks' => $item->remarks,
                'receive_date' => $item->created_at,
                'created_by' => $item->created_by,
                'status' => $item->status,
            ]),
            'filters' => $request->only(['search', 'per_page']),
        ]);

       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all PS Receives (you may filter by status if needed)
        $psReceives = PsReceive::with('chickCounts', 'labTransfers')
            ->get()
            ->map(function ($ps) {
                return [
                    'id' => $ps->id,
                    'pi_no' => $ps->pi_no,
                    'pi_date' => optional($ps->pi_date)->format('Y-m-d'),
                    'order_no' => $ps->order_no,
                    'order_date' => optional($ps->order_date)->format('Y-m-d'),
                    'lc_no' => $ps->lc_no,
                    'lc_date' => optional($ps->lc_date)->format('Y-m-d'),
                    'shipment_type_id' => $ps->shipment_type_id,
                    'supplier_id' => $ps->supplier_id,
                    'breed_type' => $ps->breed_type,
                    'country_of_origin' => $ps->country_of_origin,
                    'transport_type' => $ps->transport_type,
                    'company_id' => $ps->company_id,
                    'remarks' => $ps->remarks,
                    'created_at' => $ps->created_at->format('Y-m-d'),

                    // Chick counts
                    'total_chicks_qty' => $ps->chickCounts->ps_total_qty ?? 0,
                    'total_box_qty' => $ps->chickCounts->ps_total_re_box_qty ?? 0,
                    'ps_challan_box_qty' => $ps->chickCounts->ps_challan_box_qty ?? 0,
                    'male_box_qty' => $ps->chickCounts->ps_male_rec_box ?? 0,
                    'female_box_qty' => $ps->chickCounts->ps_female_rec_box ?? 0,
                    'male_chicks' => $ps->chickCounts->ps_male_qty ?? 0,
                    'female_chicks' => $ps->chickCounts->ps_female_qty ?? 0,
                    'gross_weight' => $ps->chickCounts->ps_female_qty ?? 0,
                    'net_weight' => $ps->chickCounts->ps_net_weight ?? 0,
                    'Note' => $ps->chickCounts->ps_gross_weight ?? 0,
                    'labTest' => $ps->labTransfers, // important
                ];
            });

        $flocks = Flock::select('id', 'name')->get();
        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('ps/ps-firm-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
            'flocks' => $flocks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        
        $companyInfo = Company::findOrFail($request->receiving_company_id);
        $flockInfo = Flock::findOrFail($request->flock_id);

        

        $firmReceive = PsFirmReceive::create([
            'ps_receive_id' => $request->ps_receive_id,
            'receive_type' => 'box',
            'source_type' => 'psreceive',
            'source_id' => $request->ps_receive_id,
            'flock_id' => $request->flock_id,
            'flock_name' => $flockInfo->_name,
            'receiving_company_id' => $request->receiving_company_id,
            'firm_female_qty' => $request->firm_female_box_qty,
            'firm_male_qty' => $request->firm_male_box_qty,
            'firm_total_qty' => $request->firm_total_box_qty,
            'remarks' => $request->remarks,
            'created_by' => Auth::id(),
            'status' => $request->status ?? 1,
        ]);

        $insertId = $firmReceive->id;

        $transactionNo = "{$insertId}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['transaction_no' => $transactionNo,'job_no'=>$transactionNo]);

        
        return redirect()
            ->route('ps-firm-receive.index')
            ->with('success', 'Firm Receive created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $psFirmReceives = PsFirmReceive::with(['flock', 'company', 'psReceive'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('psReceive', fn($q2) => $q2->where('pi_no', 'like', "%{$search}%"))
                    ->orWhereHas('flock', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'pi_no' => $r->psReceive->pi_no ?? '-',
                    'flock_name' => $r->flock->name ?? '-',
                    'company_name' => $r->company->name ?? '-',
                    'male_qty' => $r->firm_male_qty,
                    'female_qty' => $r->firm_female_qty,
                    'total_qty' => $r->firm_total_qty,
                    'remarks' => $r->remarks ?? '-',
                    'receive_date' => $r->created_at->format('Y-m-d'),
                ];
            })
            ->toArray();
        dd();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Male Qty', 'key' => 'male_qty'],
            ['label' => 'Female Qty', 'key' => 'female_qty'],
            ['label' => 'Total Qty', 'key' => 'total_qty'],
            ['label' => 'Remarks', 'key' => 'remarks'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
        ];
        dd($psFirmReceives);

        $data = [
            'title' => 'PS Firm Receive Report',
            'columns' => $columns,
            'rows' => $psFirmReceives,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'landscape'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('ps-firm-receive-report.pdf');
    }

    /**
     * Download all PS Firm Receives as Excel
     */
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = PsFirmReceive::with(['flock', 'company', 'psReceive'])
            ->when(
                $request->search,
                fn($q, $search) =>
                $q->whereHas('psReceive', fn($q2) => $q2->where('pi_no', 'like', "%{$search}%"))
                    ->orWhereHas('flock', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
            )
            ->latest()
            ->get()
            ->map(fn($r) => [
                'pi_no' => $r->psReceive->pi_no ?? '-',
                'flock_name' => $r->flock->name ?? '-',
                'company_name' => $r->company->name ?? '-',
                'male_qty' => $r->firm_male_qty,
                'female_qty' => $r->firm_female_qty,
                'total_qty' => $r->firm_total_qty,
                'remarks' => $r->remarks ?? '-',
                'receive_date' => $r->created_at->format('Y-m-d'),
            ])
            ->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Male Qty', 'key' => 'male_qty'],
            ['label' => 'Female Qty', 'key' => 'female_qty'],
            ['label' => 'Total Qty', 'key' => 'total_qty'],
            ['label' => 'Remarks', 'key' => 'remarks'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
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

        return Excel::download(new ArrayExport($headings, $body), 'ps-firm-receive-report.xlsx');
    }

    /**
     * Download single PS Firm Receive as PDF
     */
    public function downloadRowPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $item = PsFirmReceive::with(['flock', 'company', 'psReceive'])->findOrFail($id);

        $data = [
            'pi_no' => $item->psReceive->pi_no ?? '-',
            'flock_name' => $item->flock->name ?? '-',
            'company_name' => $item->company->name ?? '-',
            'male_qty' => $item->firm_male_qty,
            'female_qty' => $item->firm_female_qty,
            'total_qty' => $item->firm_total_qty,
            'remarks' => $item->remarks ?? '-',
            'receive_date' => $item->created_at->format('Y-m-d'),
            'generatedAt' => now(),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.ps.ps-firm-receive-row', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream("ps-firm-receive-{$item->psReceive->pi_no}.pdf");
    }
}
