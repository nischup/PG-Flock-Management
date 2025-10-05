<?php

namespace App\Http\Controllers\Shed;

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

class ShedReceiveController extends Controller
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
        // Fetch shed receives with comprehensive filtering
        $shedReceives = ShedReceive::with(['flock:id,name,code', 'shed:id,name', 'company:id,name', 'project:id,name'])
            ->visibleFor()
            ->where('receive_type', 'box')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_no', 'like', "%{$search}%")
                        ->orWhereHas('flock', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('shed', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->company_id, fn ($q) => $q->where('company_id', $request->company_id))
            ->when($request->flock_id, fn ($q) => $q->where('flock_id', $request->flock_id))
            ->when($request->shed_id, fn ($q) => $q->where('shed_id', $request->shed_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        // Return to Inertia page with comprehensive data
        return inertia('shed/shed-receive/List', [
            'shedReceives' => $shedReceives->through(fn ($item) => [
                'id' => $item->id,
                'job_no' => $item->job_no,
                'flock_id' => $item->flock_id,
                'flock_name' => $item->flock->name ?? $item->flock_name,
                'shed_id' => $item->shed_id,
                'shed_name' => $item->shed->name ?? 'N/A',
                'company_id' => $item->company_id,
                'company_name' => $item->company->name ?? 'N/A',
                'project_id' => $item->project_id,
                'project_name' => $item->project->name ?? 'N/A',
                'shed_female_qty' => $item->shed_female_qty,
                'shed_male_qty' => $item->shed_male_qty,
                'shed_total_qty' => $item->shed_total_qty,
                'receive_date' => $item->created_at,
                'remarks' => $item->remarks,
                'created_at' => $item->created_at,
                // Add relationship data for frontend dropdowns
                'flock' => $item->flock ? ['id' => $item->flock->id, 'name' => $item->flock->name, 'code' => $item->flock->code] : null,
                'shed' => $item->shed ? ['id' => $item->shed->id, 'name' => $item->shed->name] : null,
                'company' => $item->company ? ['id' => $item->company->id, 'name' => $item->company->name] : null,
                'project' => $item->project ? ['id' => $item->project->id, 'name' => $item->project->name] : null,
            ]),
            'filters' => $request->only(['search', 'per_page', 'company_id', 'flock_id', 'shed_id', 'date_from', 'date_to']),
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'flocks' => Flock::select('id', 'name', 'code')->orderBy('id', 'desc')->get(),
            'sheds' => Shed::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $firmReceives = PsFirmReceive::with(['flock', 'company', 'project'])
            ->whereIn('shed_receiving_status', [0, 1]) // 0 = Pending, 1 = Partially Assigned
            ->where('receive_type', 'box')
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

        return Inertia::render('shed/shed-receive/Create', [
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

            $firmReceive = PsFirmReceive::findOrFail($request->transaction_id);

            // Calculate current assigned boxes for this firm receive
            $currentAssigned = ShedReceive::where('receive_id', $request->transaction_id)
                ->sum('shed_total_qty');

            $remainingBoxes = $firmReceive->firm_total_qty - $currentAssigned;
            $requestedTotal = $request->shed_total_qty;

            // Validate that we don't assign more than remaining boxes
            if ($requestedTotal > $remainingBoxes) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'shed_total_qty' => "Cannot assign {$requestedTotal} boxes. Only {$remainingBoxes} boxes remaining from total {$firmReceive->firm_total_qty} boxes.",
                    ]);
            }

            $shedReceive = ShedReceive::create([
                'receive_id' => $request->transaction_id,   // firm receive reference
                'job_no' => $firmReceive->job_no,
                'company_id' => $firmReceive->receiving_company_id,
                'project_id' => $firmReceive->project_id,
                'transaction_no' => $firmReceive->transaction_no,
                'flock_id' => $firmReceive->flock_id,
                'flock_no' => $firmReceive->flock_no,
                'shed_id' => $request->shed_id,
                'shed_female_qty' => $request->shed_female_qty,
                'shed_male_qty' => $request->shed_male_qty,
                'shed_total_qty' => $request->shed_total_qty,
                'receive_type' => 'Box',
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
                'status' => $request->status ?? 1,
            ]);

            // Update shed_receiving_status based on remaining boxes
            $assignmentInfo = $this->updateShedReceivingStatus($request->transaction_id);

            if ($request->shed_sortage_box_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no, // fetch from batch or pass from request
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
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no,  // fetch from batch or pass from request
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
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no,  // fetch from batch or pass from request
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
                ? "Shed Receive created successfully! {$assignmentInfo['remaining_boxes']} boxes remaining to be assigned."
                : 'Shed Receive created successfully! All boxes have been assigned.';

            return redirect()
                ->route('shed-receive.index')
                ->with('success', $statusMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create shed receive: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShedReceive $shedReceive)
    {
        // Load the shed receive with all relationships
        $shedReceive = ShedReceive::with(['flock', 'company', 'shed', 'user'])
            ->findOrFail($shedReceive->id);

        // Get related firm receive data if available
        $firmReceive = null;
        if ($shedReceive->receive_id) {
            $firmReceive = PsFirmReceive::with(['flock', 'company'])
                ->find($shedReceive->receive_id);
        }

        return Inertia::render('shed/shed-receive/Show', [
            'shedReceive' => $shedReceive,
            'firmReceive' => $firmReceive,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShedReceive $shedReceive)
    {
        // Load the shed receive with relationships
        $shedReceive = ShedReceive::with(['flock', 'company', 'shed'])->findOrFail($shedReceive->id);

        // Fetch firm receives for dropdown (only those not fully assigned)
        $firmReceives = PsFirmReceive::with(['flock', 'company', 'project'])
            ->whereIn('shed_receiving_status', [0, 1]) // 0 = Pending, 1 = Partially Assigned
            ->where('receive_type', 'box')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fr) use ($shedReceive) {
                // Calculate remaining boxes for this firm receive
                $assignedBoxes = ShedReceive::where('receive_id', $fr->id)
                    ->where('id', '!=', $shedReceive->id) // Exclude current record for edit
                    ->sum('shed_total_qty');
                $remainingBoxes = $fr->firm_total_qty - $assignedBoxes;

                // Calculate assigned female and male boxes (excluding current record)
                $assignedFemale = ShedReceive::where('receive_id', $fr->id)
                    ->where('id', '!=', $shedReceive->id)
                    ->sum('shed_female_qty');
                $assignedMale = ShedReceive::where('receive_id', $fr->id)
                    ->where('id', '!=', $shedReceive->id)
                    ->sum('shed_male_qty');

                // Calculate remaining female and male boxes
                $remainingFemale = $fr->firm_female_qty - $assignedFemale;
                $remainingMale = $fr->firm_male_qty - $assignedMale;

                return [
                    'id' => $fr->id,
                    'job_no' => $fr->job_no,
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

        return Inertia::render('shed/shed-receive/Edit', [
            'shedReceive' => $shedReceive,
            'firmReceives' => $firmReceives,
            'flocks' => $flocks,
            'companies' => $companies,
            'sheds' => $sheds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShedReceive $shedReceive)
    {
        try {
            DB::beginTransaction();

            // Get the firm receive data
            $firmReceive = PsFirmReceive::findOrFail($request->transaction_id);

            // Calculate current assigned boxes for this firm receive (excluding current record)
            $currentAssigned = ShedReceive::where('receive_id', $request->transaction_id)
                ->where('id', '!=', $shedReceive->id)
                ->sum('shed_total_qty');

            $remainingBoxes = $firmReceive->firm_total_qty - $currentAssigned;
            $requestedTotal = $request->shed_total_qty;

            // Validate that we don't assign more than remaining boxes
            if ($requestedTotal > $remainingBoxes) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'shed_total_qty' => "Cannot assign {$requestedTotal} boxes. Only {$remainingBoxes} boxes remaining from total {$firmReceive->firm_total_qty} boxes.",
                    ]);
            }

            // Update the shed receive record
            $shedReceive->update([
                'receive_id' => $request->transaction_id,
                'job_no' => $firmReceive->job_no,
                'company_id' => $firmReceive->receiving_company_id,
                'project_id' => $firmReceive->project_id,
                'transaction_no' => $firmReceive->transaction_no,
                'flock_id' => $firmReceive->flock_id,
                'flock_no' => $firmReceive->flock_no,
                'shed_id' => $request->shed_id,
                'shed_female_qty' => $request->shed_female_qty,
                'shed_male_qty' => $request->shed_male_qty,
                'shed_total_qty' => $request->shed_total_qty,
                'remarks' => $request->remarks,
                'updated_by' => Auth::id(),
                'status' => $request->status ?? 1,
            ]);

            // Update shed_receiving_status based on remaining boxes
            $assignmentInfo = $this->updateShedReceivingStatus($request->transaction_id);

            // Delete existing movement adjustments for this shed receive
            MovementAdjustment::where('stage_id', $shedReceive->id)
                ->where('stage', 3)
                ->delete();

            // Create new movement adjustments if quantities are greater than 0
            if ($request->shed_sortage_box_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no,
                    'stage' => 3,
                    'stage_id' => $shedReceive->id,
                    'type' => 3, // Shortage
                    'male_qty' => $request->shed_sortage_male_box ?? 0,
                    'female_qty' => $request->shed_sortage_female_box ?? 0,
                    'total_qty' => $request->shed_sortage_box_qty ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Shortage when shed receive',
                ]);
            }

            if ($request->shed_excess_box_qty > 0) {
                MovementAdjustment::create([
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no,
                    'stage' => 3,
                    'stage_id' => $shedReceive->id,
                    'type' => 2, // Excess
                    'male_qty' => $request->shed_excess_male_box ?? 0,
                    'female_qty' => $request->shed_excess_female_box ?? 0,
                    'total_qty' => $request->shed_excess_box_qty ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Excess when shed receive',
                ]);
            }

            if ($request->shed_total_mortality > 0) {
                MovementAdjustment::create([
                    'flock_id' => $firmReceive->flock_id,
                    'flock_no' => $firmReceive->flock_no,
                    'transaction_no' => $firmReceive->transaction_no,
                    'job_no' => $firmReceive->job_no,
                    'stage' => 3,
                    'stage_id' => $shedReceive->id,
                    'type' => 1, // Mortality
                    'male_qty' => $request->shed_male_mortality ?? 0,
                    'female_qty' => $request->shed_female_mortality ?? 0,
                    'total_qty' => $request->shed_total_mortality ?? 0,
                    'date' => date('Y-m-d'),
                    'remarks' => 'Mortality when shed receive',
                ]);
            }

            DB::commit();

            $statusMessage = $assignmentInfo['remaining_boxes'] > 0
                ? "Shed Receive updated successfully! {$assignmentInfo['remaining_boxes']} boxes remaining to be assigned."
                : 'Shed Receive updated successfully! All boxes have been assigned.';

            return redirect()
                ->route('shed-receive.index')
                ->with('success', $statusMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update shed receive: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShedReceive $shedReceive)
    {
        //
    }
}
