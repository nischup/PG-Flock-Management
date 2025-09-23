<?php

namespace App\Http\Controllers\Production;

use App\Models\Country;
use App\Models\Master\Shed;
use App\Models\Master\Flock;
use Illuminate\Http\Request;
use App\Models\Master\Company;
use App\Models\Master\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Master\BreedType;
use App\Models\Ps\PsFirmReceive;
use App\Models\MovementAdjustment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BirdTransfer\BirdTransfer;

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
            'toProject',
            'fromShed',
            'toShed',
        ])->visibleFor('toCompany')
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
        $projects = Project::all();
        return inertia('production/firm-receive/List', [
            'transferBirds' => $transfers,
            'companies' => $companies,
            'flocks' => $flocks,
            'sheds' => $sheds,
            'projects' => $projects,
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
            'project_id' => $request->project_id,
            'firm_female_qty' => $request->receive_female_qty,
            'firm_male_qty' => $request->receive_male_qty,
            'firm_total_qty' => $request->receive_total_qty,
            'remarks' => $request->note ?? null,
            'created_by' => Auth::id(),
            'status' => 1,
        ]);


        $insertId = $firmReceive->id;
        $transactionNo = "{$insertId}-{$companyInfo->short_name}-{$flockInfo->name}";

        // Save the job_no back to the record
        $firmReceive->update(['transaction_no' => $transactionNo]);

        if ($request->total_shortage > 0) {
            MovementAdjustment::create([
                'flock_id'   =>  $flockInfo->id,
                'flock_no' =>    $flockInfo->name,
                'job_no' => $psReceive->job_no,
                'transaction_no' => $transactionNo,
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
                'flock_no'   =>  $flockInfo->name, 
                'job_no' => $psReceive->job_no,
                'transaction_no' => $transactionNo,// fetch from batch or pass from request
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
                'flock_no' =>    $flockInfo->name,
                'job_no' => $psReceive->job_no,
                'transaction_no' => $transactionNo, // fetch from batch or pass from request
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

    public function downloadProductionReceivePdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Load BirdTransfer with relations including firmReceive (PsFirmReceive), psReceive (PsReceive), and batchAssign
        $item = BirdTransfer::with([
            'flock',
            'fromCompany',
            'toCompany',
            'fromShed',
            'toShed',
            'batchAssign.batch',          // Ensure batchAssign is loaded correctly
            'firmReceive.psReceive',      // Load PsFirmReceive and its related PsReceive
            'firmReceive.psReceive.company', // Load company from PsReceive model
            'firmReceive.psReceive.country', // Load country from PsReceive model
        ])->findOrFail($id);

        // Access the associated PsFirmReceive through the firmReceive relationship
        $firmReceive = $item->firmReceive;

        // dd($firmReceive);
        // Ensure PsFirmReceive is loaded properly
        if (!$firmReceive) {
            abort(404, 'Firm receive data not found.');
        }

        // Get breed type names
        $breeds = BreedType::pluck('name', 'id')->toArray();
        $breedtype = $item->breed_type ?? [];
        if (!is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype];
        }
        $breedAll = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName = implode(', ', $breedNames);
        $fromCompany = Company::find($item->from_company_id);
        $toCompany = Company::find($item->to_company_id);

        $country = Country::find($item->country_of_origin);
        $countryName = $country ? $country->name : 'N/A';
        // Prepare batch info
        $batchAssign = $item->batchAssign;
        $batches = [
            [
                'batch_no' => $batchAssign?->batch?->name ?? 'N/A',
                // Challan (sent quantities)
                'challan_female' => $item->transfer_female_qty,
                'challan_male' => $item->transfer_male_qty,
                'challan_total' => $item->transfer_total_qty,

                // Physical quantities from firmReceive
                'physical_female' => $firmReceive->firm_female_qty ?? 0,
                'physical_male' => $firmReceive->firm_male_qty ?? 0,
                'total' => $firmReceive->firm_total_qty ?? 0,

                // Additional counts (medical, deviation)
                'medical_female' => $firmReceive->medical_female_qty ?? 0,
                'medical_male' => $firmReceive->medical_male_qty ?? 0,

                'deviation_female' => $firmReceive->firm_female_qty - $item->transfer_female_qty,
                'deviation_male' => $firmReceive->firm_male_qty - $item->transfer_male_qty,
                'deviation_total' => $firmReceive->firm_total_qty - $item->transfer_total_qty,

                // Add the company names
                'from_company_name' => $fromCompany?->name ?? 'N/A', // Get company name from `from_company_id`
                'to_company_name' => $toCompany?->name ?? 'N/A', // Get company name from `to_company_id`

                // Add country name
                'country_of_origin' => $countryName, // Get the country name from `country_of_origin` ID

                // Remarks
                'remarks' => $firmReceive->remarks ?? 'N/A',
            ],
        ];

        // Prepare data for the Blade template
        $data = [
            'id' => $item->id,
            'job_no' => $item->job_no,
            'transaction_no' => $item->transaction_no,
            'flock_name' => $item->flock->name ?? '-',
            'from_company' => $fromCompany?->name ?? '-',
            'to_company' => $toCompany?->name ?? '-',
            'breed_type' => $breedName,
            'firm_female_qty' => $item->transfer_female_qty,
            'firm_male_qty' => $item->transfer_male_qty,
            'firm_total_qty' => $item->transfer_total_qty,
            'remarks' => $firmReceive->remarks ?? '-',
            'receive_date' => optional($item->transfer_date)->format('Y-m-d'),
            'invoice_numbers' => $firmReceive->psReceive?->order_no ?? 'N/A', // Invoice / Gate pass from PsReceive
            'batches' => $batches,
            'country_of_origin' => $countryName, // Add the country of origin to the data
            'generatedAt' => now(),
        ];
        // dd($batches);

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // Generate the PDF using the Blade view
        $pdf = Pdf::loadView('reports.production-reports.production-farm-report.bird-receive-row', $data)
            ->setPaper('a4', 'landscape');

        // Return the PDF response (download or stream)
        return request()->query('download')
            ? $pdf->download("production-receive-{$item->id}.pdf")
            : $pdf->stream("production-receive-{$item->id}.pdf");
    }

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

        $breedAll = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);
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
