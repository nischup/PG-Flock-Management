<?php

namespace App\Http\Controllers\Ps;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Project;
use App\Models\Ps\PsFirmReceive;
use App\Models\Ps\PsReceive;
use App\Models\MovementAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PsFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $psFirmReceives = PsFirmReceive::with(['flock:id,name,code', 'company:id,name', 'psReceive:id,pi_no'])
            // ->where('source_type', 'psreceive')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_no', 'like', "%{$search}%")
                        ->orWhereHas('flock', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('psReceive', fn ($q2) => $q2->where('pi_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->company_id, fn ($q) => $q->where('receiving_company_id', $request->company_id))
            ->when($request->flock_id, fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('ps/ps-firm-receive/List', [
            'psFirmReceives' => $psFirmReceives->through(fn ($item) => [
                'id' => $item->id,
                'ps_receive_id' => $item->ps_receive_id,
                'job_no' => $item->job_no,
                'receipt_type' => $item->receipt_type,
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'flock_id' => $item->flock_id,
                'flock_name' => $item->flock->name ?? '-',
                'receiving_company_id' => $item->receiving_company_id,
                'company_name' => $item->company->name ?? '-',
                'firm_female_qty' => $item->firm_female_qty,
                'firm_male_qty' => $item->firm_male_qty,
                'firm_total_qty' => $item->firm_total_qty,
                'remarks' => $item->remarks,
                'created_by' => $item->created_by,
                'status' => $item->status,
                'receive_date' => $item->created_at,
                'psReceive' => $item->psReceive ? ['id' => $item->psReceive->id, 'pi_no' => $item->psReceive->pi_no, 'supplier' => $item->psReceive->supplier] : null,
                'company' => $item->company ? ['id' => $item->company->id, 'name' => $item->company->name] : null,
                'flock' => $item->flock ? ['id' => $item->flock->id, 'name' => $item->flock->name, 'code' => $item->flock->code] : null,
            ]),
            'filters' => $request->only(['search', 'per_page', 'company_id', 'flock_id', 'date_from', 'date_to']),
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'flocks' => Flock::select('id', 'name', 'code')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $breeds = BreedType::pluck('name', 'id')->toArray();

        // Fetch all PS Receives (you may filter by status if needed)
        $psReceives = PsReceive::with('chickCounts', 'labTransfers')
            ->get()
            ->map(function ($ps, $suppliers) {
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
                    'receive_type' => "box",
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





        $flocks = Flock::select('id', 'code', 'name', 'status')->get();
        // Fetch all companies
        $companies = Company::select('id', 'name')->get();
        $projects = Project::select('id', 'name', 'company_id')->get();    

        return Inertia::render('ps/ps-firm-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
            'flocks' => $flocks,
            'breeds' => $breeds,
            'projects' => $projects, // pass all projects
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
            'project_id' => $request->receiving_project_id,
            'flock_no' => $flockInfo->name,
            'receiving_company_id' => $request->receiving_company_id,
            'firm_female_qty' => $request->firm_female_box_qty,
            'firm_male_qty' => $request->firm_male_box_qty,
            'firm_total_qty' => $request->firm_total_box_qty,
            'remarks' => $request->remarks,
            'created_by' => Auth::id(),
            'status' => $request->status ?? 1,
        ]);

        $insertId = $firmReceive->id;

        if ($request->firm_sortage_box_qty > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no' =>    $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  2,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  3,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->firm_sortage_male_box ?? 0,
                'female_qty' =>  $request->firm_sortage_female_box ?? 0,
                'total_qty'  =>  $request->firm_sortage_box_qty ?? 0,
                'date'       =>  date('Y-m-d'),
                'remarks'    => "Sortage when firm receive",
            ]);
        }

        if ($request->firm_excess_box_qty > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no'   =>  $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  2,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  2,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->firm_excess_male_box ?? 0,
                'female_qty' =>  $request->firm_excess_female_box ?? 0,
                'total_qty'  =>  $request->firm_excess_box_qty ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Excess when firm receive",
            ]);
        }

        if ($request->firm_total_mortality > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no' =>    $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  2,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  1,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->firm_mortality_male ?? 0,
                'female_qty' =>  $request->firm_mortality_female ?? 0,
                'total_qty'  =>  $request->firm_total_mortality ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Mortality when firm receive",
            ]);
        }


        


        $transactionNo = "{$insertId}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['transaction_no' => $transactionNo, 'job_no' => $transactionNo]);

        return redirect()
            ->route('ps-firm-receive.index')
            ->with('success', 'Firm Receive created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $psFirmReceive = PsFirmReceive::with(['flock', 'company', 'psReceive'])
            ->findOrFail($id);

        return Inertia::render('ps/ps-firm-receive/Show', [
            'psFirmReceive' => [
                'id' => $psFirmReceive->id,
                'job_no' => $psFirmReceive->job_no,
                'transaction_no' => $psFirmReceive->transaction_no,
                'flock_name' => $psFirmReceive->flock->name ?? '-',
                'company_name' => $psFirmReceive->company->name ?? '-',
                'pi_no' => $psFirmReceive->psReceive->pi_no ?? '-',
                'firm_male_qty' => $psFirmReceive->firm_male_qty,
                'firm_female_qty' => $psFirmReceive->firm_female_qty,
                'firm_total_qty' => $psFirmReceive->firm_total_qty,
                'remarks' => $psFirmReceive->remarks,
                'receive_date' => $psFirmReceive->created_at,
                'created_by' => $psFirmReceive->created_by,
                'status' => $psFirmReceive->status,
                'receive_type' => $psFirmReceive->receive_type,
                'source_type' => $psFirmReceive->source_type,
                'source_id' => $psFirmReceive->source_id,
                'receiving_company_id' => $psFirmReceive->receiving_company_id,
                'flock_id' => $psFirmReceive->flock_id,
                'ps_receive_id' => $psFirmReceive->ps_receive_id,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $psFirmReceive = PsFirmReceive::with(['flock', 'company', 'psReceive'])
            ->findOrFail($id);

        // Fetch all PS Receives for dropdown
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
                    'labTest' => $ps->labTransfers,
                ];
            });

        $flocks = Flock::select('id', 'code', 'name', 'status')->get();
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('ps/ps-firm-receive/Edit', [
            'psFirmReceive' => [
                'id' => $psFirmReceive->id,
                'job_no' => $psFirmReceive->job_no,
                'transaction_no' => $psFirmReceive->transaction_no,
                'flock_id' => $psFirmReceive->flock_id,
                'flock_name' => $psFirmReceive->flock->name ?? '-',
                'receiving_company_id' => $psFirmReceive->receiving_company_id,
                'company_name' => $psFirmReceive->company->name ?? '-',
                'ps_receive_id' => $psFirmReceive->ps_receive_id,
                'pi_no' => $psFirmReceive->psReceive->pi_no ?? '-',
                'firm_male_qty' => $psFirmReceive->firm_male_qty,
                'firm_female_qty' => $psFirmReceive->firm_female_qty,
                'firm_total_qty' => $psFirmReceive->firm_total_qty,
                'remarks' => $psFirmReceive->remarks,
                'status' => $psFirmReceive->status,
                'receive_type' => $psFirmReceive->receive_type,
                'source_type' => $psFirmReceive->source_type,
                'source_id' => $psFirmReceive->source_id,
            ],
            'psReceives' => $psReceives,
            'companies' => $companies,
            'flocks' => $flocks,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $psFirmReceive = PsFirmReceive::findOrFail($id);

        $companyInfo = Company::findOrFail($request->receiving_company_id);
        $flockInfo = Flock::findOrFail($request->flock_id);

        $psFirmReceive->update([
            'ps_receive_id' => $request->ps_receive_id,
            'flock_id' => $request->flock_id,
            'flock_name' => $flockInfo->_name,
            'receiving_company_id' => $request->receiving_company_id,
            'firm_female_qty' => $request->firm_female_box_qty,
            'firm_male_qty' => $request->firm_male_box_qty,
            'firm_total_qty' => $request->firm_total_box_qty,
            'remarks' => $request->remarks,
            'status' => $request->status ?? 1,
        ]);

        $transactionNo = "{$psFirmReceive->id}-{$companyInfo->short_name}-{$flockInfo->name}";
        $psFirmReceive->update(['transaction_no' => $transactionNo, 'job_no' => $transactionNo]);

        return redirect()
            ->route('ps-firm-receive.index')
            ->with('success', 'PS Firm Receive updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $psFirmReceive = PsFirmReceive::findOrFail($id);
        $psFirmReceive->delete();

        return redirect()
            ->route('ps-firm-receive.index')
            ->with('success', 'PS Firm Receive deleted successfully!');
    }

    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $psFirmReceives = PsFirmReceive::with(['flock:id,name,code', 'company:id,name', 'psReceive:id,pi_no'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_no', 'like', "%{$search}%")
                        ->orWhereHas('flock', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('psReceive', fn ($q2) => $q2->where('pi_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->company_id, fn ($q) => $q->where('receiving_company_id', $request->company_id))
            ->when($request->flock_id, fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
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

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Male Qty', 'key' => 'male_qty'],
            ['label' => 'Female Qty', 'key' => 'female_qty'],
            ['label' => 'Total Qty', 'key' => 'total_qty'],
            ['label' => 'Remarks', 'key' => 'remarks'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
        ];

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

        $rows = PsFirmReceive::with(['flock:id,name,code', 'company:id,name', 'psReceive:id,pi_no'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_no', 'like', "%{$search}%")
                        ->orWhereHas('flock', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('psReceive', fn ($q2) => $q2->where('pi_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->company_id, fn ($q) => $q->where('receiving_company_id', $request->company_id))
            ->when($request->flock_id, fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->get()
            ->map(fn ($r) => [
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
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Male Qty', 'key' => 'male_qty'],
            ['label' => 'Female Qty', 'key' => 'female_qty'],
            ['label' => 'Total Qty', 'key' => 'total_qty'],
            ['label' => 'Remarks', 'key' => 'remarks'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
        ];

        $headings = array_map(fn ($c) => $c['label'], $columns);
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

        // Load PsFirmReceive with valid relationships
        $item = PsFirmReceive::with([
            'flock',
            'company',
            'psReceive.chickCounts',
        ])->findOrFail($id);

        // Get breed type from psReceive

            
        $breeds = BreedType::pluck('name', 'id')->toArray(); // [1 => 'Rhode Island', 2 => 'Leghorn', ...]
        $breedtype = $item->psReceive?->breed_type ?? [];
        // Map IDs to names
        $breedAll = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);

        // Remove nulls (optional)
        $breedNames = array_filter($breedAll);

        // Convert to comma-separated string if needed
        $breedName = implode(', ', $breedNames);
        
        

        $psChickCounts = $item->psReceive?->chickCounts;

        // Do calculations first
        $physical_female = $item->firm_female_qty;
        $physical_male   = $item->firm_male_qty;

        $box_f = ($psChickCounts->ps_female_rec_box ?? 0) - $physical_female;
        $box_m = ($psChickCounts->ps_male_rec_box ?? 0) - $physical_male;

        $box_shortage = $box_f + $box_m;

        $deviation_female = $physical_female - ($psChickCounts->ps_female_rec_box ?? 0);
        $deviation_male = $physical_male - ($psChickCounts->ps_male_rec_box ?? 0);

        // Prepare data for Blade view
        $data = [
            'job_no'         => $item->job_no,
            'transaction_no' => $item->transaction_no,
            'pi_no'          => $item->psReceive->pi_no ?? '-',
            'pi_date'        => optional($item->psReceive->pi_date)->format('Y-m-d') ?? '-',
            'flock_name'     => $item->flock->name ?? '-',
            'flock_id'       => $item->flock_id,
            'company_name'   => $item->company->name ?? '-',
            'company_id'     => $item->receiving_company_id,
            'firm_male_qty'  => $item->firm_male_qty,
            'firm_female_qty' => $item->firm_female_qty,
            'firm_total_qty' => $item->firm_total_qty,
            'remarks'        => $item->remarks ?? '-',
            'receive_date'   => $item->created_at->format('Y-m-d'),
            'created_by'     => $item->created_by,
            'status'         => $item->status,
            'receive_type'   => $item->receive_type,
            'breed_type'     => $breedName,
            'source_type'    => $item->source_type ?? '-',
            'source_id'      => $item->source_id ?? '-',

            // batches section
            'batches' => [
                [
                    'batch_no'        => 'A1',
                    'challan_female'  => $psChickCounts->ps_female_rec_box ?? 0,
                    'challan_male'    => $psChickCounts->ps_male_rec_box ?? 0,
                    'challan_total'   => $psChickCounts->ps_total_re_box_qty ?? 0,

                    'physical_female' => $physical_female,
                    'box_f'           => $box_f,
                    'total_female'    => $physical_female + $box_f,

                    'physical_male'   => $physical_male,
                    'box_m'           => $box_m,
                    'total_male'      => $physical_male + $box_m,

                    'box_shortage'    => $box_shortage,
                    'total'           => $item->firm_total_qty,

                    'deviation_female' => $deviation_female,
                    'deviation_male'  => $deviation_male,
                    'deviation_total' => $deviation_female + $deviation_male,

                    'remarks' => $item->remarks ?? 'OK',
                ],
            ],
            'generatedAt' => now(),
        ];

        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.ps.ps-firm-receive-row', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream("ps-firm-receive-{$item->psReceive->pi_no}.pdf");
    }
}
