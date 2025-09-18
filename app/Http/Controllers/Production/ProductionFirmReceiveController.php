<?php

namespace App\Http\Controllers\Production;

use Inertia\Inertia;
use App\Models\Master\Shed;
use App\Models\Master\Flock;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use App\Models\Master\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Master\BreedType;
use App\Models\Ps\PsFirmReceive;
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


        // PsFirmReceive::where('job_no', $job_no)->update(['status' => 0]);


        // $jobNo = "{$request->transfer_bird_id}-{$companyInfo->short_name}-{$flockInfo->name}";



        $firmReceive = PsFirmReceive::create([
            'ps_receive_id'        => $psReceive->ps_receive_id,
            'receive_type'         => 'pcs', // indicate it's a transfer
            'source_type'          => 'transfer',
            'job_no'               => $psReceive->job_no,
            'source_id'            => $request->transfer_bird_id,
            'flock_id'             => $request->flock_id,
            'flock_name'           =>  $flockInfo->name ?? '', // if you have flock relationship
            'receiving_company_id' => $request->receive_company_id,
            'firm_female_qty'      => $request->receive_female_qty,
            'firm_male_qty'        => $request->receive_male_qty,
            'firm_total_qty'       => $request->receive_total_qty,
            'remarks'              => $request->note ?? null,
            'created_by'           => Auth::id(),
            'status'               => 1,
        ]);



        $insertId = $firmReceive->id;

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
        dd();
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
        if (!is_array($breedtype)) {
            $breedtype = is_null($breedtype) ? [] : [$breedtype]; // wrap single ID
        }

        $breedAll   = array_map(fn($id) => $breeds[$id] ?? null, $breedtype);
        $breedNames = array_filter($breedAll);
        $breedName  = implode(', ', $breedNames);

        // Chick counts
        $psChickCounts = $item->psReceive?->chickCounts;

        // Calculations
        $physical_female = $item->firm_female_qty;
        $physical_male   = $item->firm_male_qty;

        $box_f = ($psChickCounts->ps_female_rec_box ?? 0) - $physical_female;
        $box_m = ($psChickCounts->ps_male_rec_box ?? 0) - $physical_male;

        $box_shortage = $box_f + $box_m;

        $deviation_female = $physical_female - ($psChickCounts->ps_female_rec_box ?? 0);
        $deviation_male   = $physical_male - ($psChickCounts->ps_male_rec_box ?? 0);

        // Prepare data for Blade view
        $data = [
            'job_no'          => $item->job_no,
            'transaction_no'  => $item->transaction_no,
            'pi_no'           => $item->psReceive->pi_no ?? '-',
            'pi_date'         => optional($item->psReceive->pi_date)->format('Y-m-d') ?? '-',
            'flock_name'      => $item->flock->name ?? '-',
            'flock_id'        => $item->flock_id,
            'company_name'    => $item->company->name ?? '-',
            'company_id'      => $item->receiving_company_id,
            'firm_male_qty'   => $item->firm_male_qty,
            'firm_female_qty' => $item->firm_female_qty,
            'firm_total_qty'  => $item->firm_total_qty,
            'remarks'         => $item->remarks ?? '-',
            'receive_date'    => $item->created_at->format('Y-m-d'),
            'created_by'      => $item->created_by,
            'status'          => $item->status,
            'receive_type'    => $item->receive_type,
            'breed_type'      => $breedName,
            'source_type'     => $item->source_type ?? '-',
            'source_id'       => $item->source_id ?? '-',

            // batches section
            'batches' => [
                [
                    'batch_no'        => 'A1',
                    'breed_name'      => $breedName, // ✅ include breed name
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
                    'deviation_male'   => $deviation_male,
                    'deviation_total'  => $deviation_female + $deviation_male,

                    'remarks' => $item->remarks ?? 'OK',
                ],
            ],
            'generatedAt' => now(),
        ];
        // dd($data);

        // PDF options
        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);
        dd($data);

        // ✅ Use correct Blade path
        $pdf = Pdf::loadView('reports.production-reports.production-farm-report.bird-receive-row', $data)
            ->setPaper('a4', 'landscape');

        return request()->query('download')
            ? $pdf->download("production-firm-receive-{$item->id}.pdf")
            : $pdf->stream("production-firm-receive-{$item->id}.pdf");
    }
}
