<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\MovementAdjustment;
use App\Models\Ps\PsFirmReceive;
use App\Models\Shed\ShedReceive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductionShedReceiveController extends Controller
{
    /**
     * Calculate remaining boxes and update shed_receiving_status
     */
    private function updateShedReceivingStatus($firmReceiveId)
    {
        $firmReceive = PsFirmReceive::findOrFail($firmReceiveId);

        // Calculate total assigned boxes for this firm receive
        $totalAssigned = ShedReceive::where('receive_id', $firmReceiveId)
            ->sum('shed_total_qty');

        $remainingBoxes = $firmReceive->firm_total_qty - $totalAssigned;

        // Update status based on remaining boxes
        if ($remainingBoxes <= 0) {
            $firmReceive->update(['shed_receiving_status' => 2]); // Fully Assigned
        } else {
            $firmReceive->update(['shed_receiving_status' => 1]); // Partially Assigned
        }

        return [
            'total_boxes' => $firmReceive->firm_total_qty,
            'assigned_boxes' => $totalAssigned,
            'remaining_boxes' => max(0, $remainingBoxes),
            'status' => $firmReceive->fresh()->shed_receiving_status,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $companyId = $request->company_id;
        $flockId = $request->flock_id;
        $shedId = $request->shed_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Fetch shed receives with related flock, shed, company, and project
        $shedReceives = ShedReceive::with(['flock', 'shed', 'company', 'project'])
            ->visibleFor()
            ->where('receive_type', 'pcs')
            ->when($search, function ($query, $search) {
                $query->whereHas('flock', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('shed', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('job_no', 'like', "%{$search}%")
                    ->orWhere('transaction_no', 'like', "%{$search}%");
            })
            ->when($companyId, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($flockId, function ($query, $flockId) {
                $query->where('flock_id', $flockId);
            })
            ->when($shedId, function ($query, $shedId) {
                $query->where('shed_id', $shedId);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        // Fetch filter data
        $companies = Company::select('id', 'name', 'short_name')->orderBy('name')->get();
        $flocks = Flock::select('id', 'name', 'code')->orderBy('name')->get();
        $sheds = Shed::select('id', 'name')->orderBy('name')->get();

        // Return to Inertia page
        return inertia('production/shed-receive/List', [
            'shedReceives' => $shedReceives,
            'companies' => $companies,
            'flocks' => $flocks,
            'sheds' => $sheds,
            'filters' => $request->only(['search', 'per_page', 'company_id', 'flock_id', 'shed_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $firmReceives = PsFirmReceive::with(['flock', 'company', 'project'])
            ->whereIn('shed_receiving_status', [0, 1]) // 0 = Pending, 1 = Partially Assigned
            ->where('receive_type', 'pcs')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($fr) {
                // Calculate remaining boxes for this firm receive
                $assignedBoxes = ShedReceive::where('receive_id', $fr->id)->sum('shed_total_qty');
                $remainingBoxes = $fr->firm_total_qty - $assignedBoxes;

                // Calculate assigned female and male boxes
                $assignedFemale = ShedReceive::where('receive_id', $fr->id)->sum('shed_female_qty');
                $assignedMale = ShedReceive::where('receive_id', $fr->id)->sum('shed_male_qty');

                // Calculate remaining female and male boxes
                $remainingFemale = $fr->firm_female_qty - $assignedFemale;
                $remainingMale = $fr->firm_male_qty - $assignedMale;

                return [
                    'id' => $fr->id,
                    'transaction_no' => $fr->transaction_no,
                    'flock_id' => $fr->flock_id,
                    'flock_name' => $fr->flock?->name ?? 'N/A',
                    'flock_code' => $fr->flock?->code ?? 'N/A',
                    'receiving_company_id' => $fr->receiving_company_id,
                    'company_name' => $fr->company?->name ?? 'N/A',
                    'company_short_name' => $fr->company?->short_name ?? $fr->company?->name ?? 'N/A',
                    'project_id' => $fr->project_id,
                    'project_name' => $fr->project?->name ?? 'N/A',
                    'firm_female_qty' => $fr->firm_female_qty,
                    'firm_male_qty' => $fr->firm_male_qty,
                    'firm_total_qty' => $fr->firm_total_qty,
                    'assigned_qty' => $assignedBoxes,
                    'assigned_female_qty' => $assignedFemale,
                    'assigned_male_qty' => $assignedMale,
                    'remaining_qty' => $remainingBoxes,
                    'remaining_female_qty' => $remainingFemale,
                    'remaining_male_qty' => $remainingMale,
                    'status_text' => $fr->shed_receiving_status == 0 ? 'Pending' : 'Partially Assigned',
                    'remarks' => $fr->remarks,
                ];
            });

        // Fetch all flocks
        $flocks = Flock::select('id', 'code', 'name')->orderBy('id', 'desc')->get();

        // Fetch all companies
        $companies = Company::select('id', 'name')->get();
        $sheds = Shed::select('id', 'name')->get();
        // Render the Inertia page

        return Inertia::render('production/shed-receive/Create', [
            'firmReceives' => $firmReceives,
            'flocks' => $flocks,
            'companies' => $companies,
            'sheds' => $sheds,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $firmReceive = PsFirmReceive::findOrFail($request->job_id);
            $flock = Flock::findOrFail($request->flock_id);

        $shedReceive = ShedReceive::create([
            'receive_id'       => $request->job_id,   // firm receive reference
            'job_no'           => $firmReceive->job_no,
            'transaction_no'   => $firmReceive->transaction_no,
            'company_id'       => $firmReceive->receiving_company_id,
            'project_id'       => $firmReceive->project_id,
            'flock_id'         => $flock->id,
            'flock_no'         => $flock->name,
            'shed_id'          => $request->shed_id,
            'shed_female_qty'  => $request->shed_female_qty,
            'shed_male_qty'    => $request->shed_male_qty,
            'shed_total_qty'   => $request->shed_total_qty,
            'receive_type'     => "pcs",
            'remarks'          => $request->remarks,
            'created_by'       => Auth::id(),
            'status'           => $request->status ?? 1,
        ]);

            $remainingBoxes = $firmReceive->firm_total_qty - $currentAssigned;
            $requestedTotal = $request->shed_total_qty;

            // Validate that we don't assign more than remaining boxes
            if ($requestedTotal > $remainingBoxes) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'shed_total_qty' => "Cannot assign {$requestedTotal} pieces. Only {$remainingBoxes} pieces remaining from total {$firmReceive->firm_total_qty} pieces.",
                    ]);
            }

            $shedReceive = ShedReceive::create([
                'receive_id' => $request->job_id,   // firm receive reference
                'job_no' => $firmReceive->job_no,
                'transaction_no' => $firmReceive->transaction_no,
                'company_id' => $firmReceive->receiving_company_id,
                'project_id' => $firmReceive->project_id,
                'flock_id' => $flock->id,
                'flock_no' => $flock->code,
                'shed_id' => $request->shed_id,
                'shed_female_qty' => $request->shed_female_qty,
                'shed_male_qty' => $request->shed_male_qty,
                'shed_total_qty' => $request->shed_total_qty,
                'receive_type' => 'pcs',
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
                'status' => $request->status ?? 1,
            ]);

            // Update shed_receiving_status based on remaining boxes
            $assignmentInfo = $this->updateShedReceivingStatus($request->job_id);

            if ($request->shed_sortage_box_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $flock->id,
                    'flock_no' => $flock->code,
                    'job_no' => $firmReceive->job_no,
                    'transaction_no' => $firmReceive->transaction_no, // fetch from batch or pass from request
                    'stage' => 3,                  // 5 = Bird Transfer stage
                    'stage_id' => $shedReceive->id,
                    'type' => 3,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                    'male_qty' => $request->shed_sortage_male_box ?? 0,
                    'female_qty' => $request->shed_sortage_female_box ?? 0,
                    'total_qty' => $request->shed_sortage_box_qty ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Sortage when shed receive',
                ]);
            }

            if ($request->shed_excess_box_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $flock->id,
                    'flock_no' => $flock->name,
                    'job_no' => $firmReceive->job_no,
                    'transaction_no' => $firmReceive->transaction_no, // fetch from batch or pass from request
                    'stage' => 3,                  // 5 = Bird Transfer stage
                    'stage_id' => $shedReceive->id,
                    'type' => 2,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                    'male_qty' => $request->shed_excess_male_box ?? 0,
                    'female_qty' => $request->shed_excess_female_box ?? 0,
                    'total_qty' => $request->shed_excess_box_qty ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Excess when shed receive',
                ]);
            }

            if ($request->shed_total_mortality > 0) {
                MovementAdjustment::create([
                    'flock_id' => $flock->id,
                    'flock_no' => $flock->code,
                    'job_no' => $firmReceive->job_no,
                    'transaction_no' => $firmReceive->transaction_no, // fetch from batch or pass from request
                    'stage' => 3,                  // 5 = Bird Transfer stage
                    'stage_id' => $shedReceive->id,
                    'type' => 1,     // 1=Mortality,2=Excess,3=Shortage,4=Deviation
                    'male_qty' => $request->shed_male_mortality ?? 0,
                    'female_qty' => $request->shed_female_mortality ?? 0,
                    'total_qty' => $request->shed_total_mortality ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Mortality when shed receive',
                ]);
            }

            DB::commit();

            $statusMessage = $assignmentInfo['remaining_boxes'] > 0
                ? "Production Shed Receive created successfully! {$assignmentInfo['remaining_boxes']} pieces remaining to be assigned."
                : 'Production Shed Receive created successfully! All pieces have been assigned.';

            return redirect()
                ->route('production-shed-receive.index')
                ->with('success', $statusMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create production shed receive: '.$e->getMessage()]);
        }
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
}
