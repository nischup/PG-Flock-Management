<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Ps\PsFirmReceive;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\MovementAdjustment;
use Illuminate\Support\Facades\Auth;

class ProductionFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);
        $fromCompanyId = $request->get('from_company_id');
        $flockId = $request->get('flock_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query for bird transfers with status 1
        $query = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
            ->where('status', 1) // Only fetch transfers with status 1
            ->latest();

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_no', 'like', "%{$search}%")
                    ->orWhere('transaction_no', 'like', "%{$search}%")
                    ->orWhereHas('flock', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fromCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($fromCompanyId) {
            $query->where('from_company_id', $fromCompanyId);
        }

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        if ($dateFrom) {
            $query->whereDate('transfer_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transfer_date', '<=', $dateTo);
        }

        // Get paginated results
        $transfers = $query->paginate($perPage)->withQueryString();

        $companies = Company::all();
        $flocks = Flock::all();
        $sheds = Shed::all();

        return inertia('production/firm-receive/List', [
            'transferBirds' => $transfers,
            'companies' => $companies,
            'flocks' => $flocks,
            'sheds' => $sheds,
            'filters' => $request->only(['search', 'per_page', 'from_company_id', 'flock_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

       
       
       
        $companyInfo = Company::findOrFail($request->receive_company_id);
        $flockInfo = Flock::findOrFail($request->flock_id);
       
        $transferBird = BirdTransfer::find($request->transfer_bird_id);

        $job_no = $transferBird->job_no;

        // 2️⃣ Get ps_receive_id from ps_receives
        $psReceive = PsFirmReceive::where('job_no', $job_no)->first();

        

        $firmReceive = PsFirmReceive::create([
            'ps_receive_id' => $psReceive->ps_receive_id,
            'receive_type' => 'pcs', // indicate it's a transfer
            'source_type' => 'transfer',
            'job_no' => $psReceive->job_no,
            'source_id' => $request->transfer_bird_id,
            'flock_id' => $flockInfo->id,
            'flock_no' => $flockInfo->name, // if you have flock relationship
            'receiving_company_id' => $request->receive_company_id,
            'firm_female_qty' => $request->receive_female_qty,
            'firm_male_qty' => $request->receive_male_qty,
            'firm_total_qty' => $request->receive_total_qty,
            'remarks' => $request->note ?? null,
            'created_by' => Auth::id(),
            'status' => 1,
        ]);

        $insertId = $firmReceive->id;


        
        if ($request->total_shortage > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no' =>    $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  1,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  3,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->shortage_male ?? 0,
                'female_qty' =>  $request->shortage_female ?? 0,
                'total_qty'  =>  $request->total_shortage ?? 0,
                'date'       =>  date('Y-m-d'),
                'remarks'    => "Sortage when firm receive from tansfer",
            ]);
        }

        if ($request->total_excess > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no'   =>  $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  1,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  2,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->excess_male ?? 0,
                'female_qty' =>  $request->excess_female ?? 0,
                'total_qty'  =>  $request->total_excess ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Excess when firm receive from tansfer",
            ]);
        }

        if ($request->total_mortality > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no' =>    $flockInfo->name, // fetch from batch or pass from request
                'stage'      =>  1,                  // 5 = Bird Transfer stage
                'stage_id'   =>  $insertId,
                'type'       =>  1,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                'male_qty'   =>  $request->mortality_male ?? 0,
                'female_qty' =>  $request->mortality_female ?? 0,
                'total_qty'  =>  $request->total_mortality ?? 0,
                'date'       => date('Y-m-d'),
                'remarks'    => "Mortality when firm receive from tansfer",
            ]);
        }






        $transactionNo = "{$insertId}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['transaction_no' => $transactionNo]);

        return redirect()->route('production-farm-receive.index')->with('success', 'Bird Receive successfully.');
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

    public function downloadRowPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Load firm receive with relations
        $item = PsFirmReceive::with([
            'flock',
            'company',
            'psReceive.chickCounts',
        ])->findOrFail($id);

        // ✅ Map breed_type IDs to names
        $breeds = BreedType::pluck('name', 'id')->toArray();

        $breedtype = $item->psReceive?->breed_type ?? [];
        if (! is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype]; // wrap single ID
        }

        $breedAll = array_map(fn ($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);

        // Chick counts
        $psChickCounts = $item->psReceive?->chickCounts;

        // Calculations
        $physical_female = $item->firm_female_qty;
        $physical_male = $item->firm_male_qty;

        $box_f = ($psChickCounts->ps_female_rec_box ?? 0) - $physical_female;
        $box_m = ($psChickCounts->ps_male_rec_box ?? 0) - $physical_male;

        $box_shortage = $box_f + $box_m;

        $deviation_female = $physical_female - ($psChickCounts->ps_female_rec_box ?? 0);
        $deviation_male = $physical_male - ($psChickCounts->ps_male_rec_box ?? 0);

        // Prepare data for Blade view
        $data = [
            'job_no' => $item->job_no,
            'transaction_no' => $item->transaction_no,
            'pi_no' => $item->psReceive->pi_no ?? '-',
            'pi_date' => optional($item->psReceive->pi_date)->format('Y-m-d') ?? '-',
            'flock_name' => $item->flock->name ?? '-',
            'flock_id' => $item->flock_id,
            'company_name' => $item->company->name ?? '-',
            'company_id' => $item->receiving_company_id,
            'firm_male_qty' => $item->firm_male_qty,
            'firm_female_qty' => $item->firm_female_qty,
            'firm_total_qty' => $item->firm_total_qty,
            'remarks' => $item->remarks ?? '-',
            'receive_date' => $item->created_at->format('Y-m-d'),
            'created_by' => $item->created_by,
            'status' => $item->status,
            'receive_type' => $item->receive_type,
            'breed_type' => $breedName,
            'source_type' => $item->source_type ?? '-',
            'source_id' => $item->source_id ?? '-',

            // batches section
            'batches' => [
                [
                    'batch_no' => 'A1',
                    'breed_name' => $breedName, // ✅ include breed name
                    'challan_female' => $psChickCounts->ps_female_rec_box ?? 0,
                    'challan_male' => $psChickCounts->ps_male_rec_box ?? 0,
                    'challan_total' => $psChickCounts->ps_total_re_box_qty ?? 0,

                    'physical_female' => $physical_female,
                    'box_f' => $box_f,
                    'total_female' => $physical_female + $box_f,

                    'physical_male' => $physical_male,
                    'box_m' => $box_m,
                    'total_male' => $physical_male + $box_m,

                    'box_shortage' => $box_shortage,
                    'total' => $item->firm_total_qty,

                    'deviation_female' => $deviation_female,
                    'deviation_male' => $deviation_male,
                    'deviation_total' => $deviation_female + $deviation_male,

                    'remarks' => $item->remarks ?? 'OK',
                ],
            ],
            'generatedAt' => now(),
        ];
        // dd($data);

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // ✅ Use correct Blade path
        $pdf = Pdf::loadView('reports.production-reports.production-farm-report.bird-receive-row', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("production-firm-receive-{$item->id}.pdf")
            : $pdf->stream("production-firm-receive-{$item->id}.pdf");
    }

    /**
     * Export production farm receive data as PDF
     */
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Get filter parameters (same as index method)
        $search = $request->get('search');
        $fromCompanyId = $request->get('from_company_id');
        $flockId = $request->get('flock_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query for bird transfers with status 1
        $query = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
            ->where('status', 1)
            ->latest();

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_no', 'like', "%{$search}%")
                    ->orWhere('transaction_no', 'like', "%{$search}%")
                    ->orWhereHas('flock', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fromCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($fromCompanyId) {
            $query->where('from_company_id', $fromCompanyId);
        }

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        if ($dateFrom) {
            $query->whereDate('transfer_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transfer_date', '<=', $dateTo);
        }

        $transfers = $query->get();

        // Prepare data for PDF
        $data = [
            'transfers' => $transfers,
            'filters' => $request->only(['search', 'from_company_id', 'flock_id', 'date_from', 'date_to']),
            'generatedAt' => now(),
        ];

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.production-reports.production-farm-report.list', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('production-farm-receive-list.pdf');
    }

    /**
     * Export production farm receive data as Excel
     */
    public function exportExcel(Request $request)
    {
        // Get filter parameters (same as index method)
        $search = $request->get('search');
        $fromCompanyId = $request->get('from_company_id');
        $flockId = $request->get('flock_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query for bird transfers with status 1
        $query = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])
            ->where('status', 1)
            ->latest();

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_no', 'like', "%{$search}%")
                    ->orWhere('transaction_no', 'like', "%{$search}%")
                    ->orWhereHas('flock', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fromCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toCompany', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($fromCompanyId) {
            $query->where('from_company_id', $fromCompanyId);
        }

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        if ($dateFrom) {
            $query->whereDate('transfer_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transfer_date', '<=', $dateTo);
        }

        $transfers = $query->get();

        // Prepare data for Excel export
        $data = [];
        foreach ($transfers as $transfer) {
            $data[] = [
                'Job No' => $transfer->job_no,
                'Transaction No' => $transfer->transaction_no,
                'Flock Name' => $transfer->flock?->name ?? '-',
                'From Company' => $transfer->fromCompany?->name ?? '-',
                'To Company' => $transfer->toCompany?->name ?? '-',
                'From Shed' => $transfer->fromShed?->name ?? '-',
                'To Shed' => $transfer->toShed?->name ?? '-',
                'Transfer Date' => $transfer->transfer_date?->format('Y-m-d') ?? '-',
                'Status' => $transfer->status == 1 ? 'Active' : 'Inactive',
                'Created At' => $transfer->created_at->format('Y-m-d H:i:s'),
            ];
        }

        // Use the existing ArrayExport class
        $export = new \App\Exports\ArrayExport($data, 'Production Farm Receive List');

        return $export->download('production-farm-receive-list.xlsx');
    }

    /**
     * Download PDF report for individual BirdTransfer record
     */
    public function downloadTransferPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Load bird transfer with relations
        $transfer = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
        ])->findOrFail($id);

        // Map breed_type IDs to names
        $breeds = BreedType::pluck('name', 'id')->toArray();
        $breedtype = $transfer->breed_type ?? [];
        if (! is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype];
        }

        $breedAll = array_map(fn ($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);

        // Prepare data for Blade view
        $data = [
            'job_no' => $transfer->job_no,
            'transaction_no' => $transfer->transaction_no,
            'flock_name' => $transfer->flock->name ?? '-',
            'flock_id' => $transfer->flock_id,
            'from_company_name' => $transfer->fromCompany->name ?? '-',
            'to_company_name' => $transfer->toCompany->name ?? '-',
            'from_shed_name' => $transfer->fromShed->name ?? '-',
            'to_shed_name' => $transfer->toShed->name ?? '-',
            'transfer_date' => $transfer->transfer_date?->format('Y-m-d') ?? '-',
            'transfer_female_qty' => $transfer->transfer_female_qty,
            'transfer_male_qty' => $transfer->transfer_male_qty,
            'transfer_total_qty' => $transfer->transfer_total_qty,
            'medical_female_qty' => $transfer->medical_female_qty,
            'medical_male_qty' => $transfer->medical_male_qty,
            'medical_total_qty' => $transfer->medical_total_qty,
            'deviation_female_qty' => $transfer->deviation_female_qty,
            'deviation_male_qty' => $transfer->deviation_male_qty,
            'deviation_total_qty' => $transfer->deviation_total_qty,
            'breed_type' => $breedName,
            'country_of_origin' => $transfer->country_of_origin ?? '-',
            'lc_no' => $transfer->lc_no ?? '-',
            'transport_type' => $transfer->transport_type ?? '-',
            'status' => $transfer->status,
            'created_at' => $transfer->created_at->format('Y-m-d H:i:s'),
            'generatedAt' => now(),
        ];

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.production-reports.production-farm-report.bird-transfer-row', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("bird-transfer-{$transfer->id}.pdf")
            : $pdf->stream("bird-transfer-{$transfer->id}.pdf");
    }
}
