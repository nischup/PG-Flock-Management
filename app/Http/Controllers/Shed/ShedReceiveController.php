<?php

namespace App\Http\Controllers\Shed;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShedReceiveRequest;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Ps\PsFirmReceive;
use App\Models\Shed\ShedReceive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShedReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch shed receives with comprehensive filtering
        $shedReceives = ShedReceive::with(['flock:id,name,code', 'shed:id,name', 'company:id,name'])
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
            ]),
            'filters' => $request->only(['search', 'per_page', 'company_id', 'flock_id', 'shed_id', 'date_from', 'date_to']),
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'flocks' => Flock::select('id', 'name', 'code')->orderBy('name')->get(),
            'sheds' => Shed::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $firmReceives = PsFirmReceive::with(['flock', 'company'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fr) {
                return [
                    'id' => $fr->id,
                    'transaction_no' => $fr->transaction_no,
                    'flock_id' => $fr->flock_id,
                    'flock_name' => $fr->flock?->name ?? 'N/A',
                    'receiving_company_id' => $fr->receiving_company_id,
                    'firm_female_qty' => $fr->firm_female_qty,
                    'firm_male_qty' => $fr->firm_male_qty,
                    'firm_total_qty' => $fr->firm_total_qty,
                    'remarks' => $fr->remarks,
                ];
            });

        // Fetch all flocks
        $flocks = Flock::select('id', 'name')->get();

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
    public function store(StoreShedReceiveRequest $request)
    {

        $firmReceive = PsFirmReceive::findOrFail($request->transaction_id);

        $shedReceive = ShedReceive::create([
            'receive_id' => $request->transaction_id,   // firm receive reference
            'job_no' => $firmReceive->job_no,
            'company_id' => $firmReceive->receiving_company_id,
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

        return redirect()
            ->route('shed-receive.index')
            ->with('success', 'Shed Receive created successfully!');

    }

    /**
     * Display the specified resource.
     */
    public function show(ShedReceive $shedReceive)
    {
        // Load the shed receive with all relationships
        $shedReceive = ShedReceive::with(['flock', 'company', 'shed'])
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

        // Fetch firm receives for dropdown
        $firmReceives = PsFirmReceive::with(['flock', 'company'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fr) {
                return [
                    'id' => $fr->id,
                    'job_no' => $fr->job_no,
                    'transaction_no' => $fr->transaction_no,
                    'flock_id' => $fr->flock_id,
                    'flock_name' => $fr->flock?->name ?? 'N/A',
                    'receiving_company_id' => $fr->receiving_company_id,
                    'firm_female_qty' => $fr->firm_female_qty,
                    'firm_male_qty' => $fr->firm_male_qty,
                    'firm_total_qty' => $fr->firm_total_qty,
                    'remarks' => $fr->remarks,
                ];
            });

        // Fetch all flocks
        $flocks = Flock::select('id', 'name')->get();

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShedReceive $shedReceive)
    {
        //
    }
}
