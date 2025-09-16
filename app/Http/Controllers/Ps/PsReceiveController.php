<?php

namespace App\Http\Controllers\Ps;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePsReceiveRequest;
use App\Models\Country;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Supplier;
use App\Models\Ps\PsLabTest;
use App\Models\Ps\PsReceive;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PsReceiveController extends Controller
{
    public function index(Request $request)
    {
        $psReceives = PsReceive::query()
            ->with(['supplier', 'chickCounts', 'company', 'country', 'labTransfers'])
            ->when($request->search, fn ($q) => $q->where('pi_no', 'like', "%{$request->search}%")
                ->orWhere('order_no', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            )
            ->when($request->company_id, fn ($q) => $q->where('company_id', $request->company_id))
            ->when($request->shipment_type_id, fn ($q) => $q->where('shipment_type_id', $request->shipment_type_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('pi_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('pi_date', '<=', $request->date_to))
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('ps/ps-receive/PsReceive', [
            'psReceives' => $psReceives,
            'filters' => $request->only(['search', 'per_page', 'company_id', 'shipment_type_id', 'date_from', 'date_to']),
            'companies' => Company::select('id', 'name')->get(),
        ]);
    }

    public function create()
    {
        // Get all suppliers initially (will be filtered on frontend)
        return inertia('ps/ps-receive/Create', [
            'suppliers' => Supplier::all(),
            'breedTypes' => BreedType::all(),
            'companies' => Company::all(),
            'countries' => Country::all(),
        ]);
    }

    public function show($id)
    {
        $psReceive = PsReceive::with([
            'supplier', 
            'chickCounts', 
            'company', 
            'country', 
            'labTransfers'
        ])->findOrFail($id);

        // Get breed type names
        $breedTypeIds = is_array($psReceive->breed_type) ? $psReceive->breed_type : json_decode($psReceive->breed_type, true);
        $breedTypes = BreedType::whereIn('id', $breedTypeIds)->get();
        $breedTypeNames = $breedTypes->pluck('name')->toArray();

        return inertia('ps/ps-receive/Show', [
            'psReceive' => $psReceive,
            'breedTypeNames' => $breedTypeNames,
        ]);
    }

    public function getSuppliersByShipmentType(Request $request)
    {
        $shipmentTypeId = $request->input('shipment_type_id');

        // Map shipment type ID to supplier type
        $supplierType = $shipmentTypeId == 1 ? 'Local' : 'Foreign';

        $suppliers = Supplier::where('supplier_type', $supplierType)
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        return response()->json($suppliers);
    }

    public function store(StorePsReceiveRequest $request)
    {
        try {
            // Log the incoming request data for debugging
            Log::info('PS Receive Store Request', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            DB::beginTransaction();

            $psReceive = PsReceive::create([
                'shipment_type_id' => (int) $request->shipment_type_id,
                'pi_no' => $request->pi_no,
                'pi_date' => $request->pi_date,
                'order_no' => $request->order_no,
                'order_date' => $request->order_date,
                'lc_no' => $request->lc_no,
                'lc_date' => $request->lc_date,
                'supplier_id' => (int) ($request->supplier_id ?? 0),
                'breed_type' => $request->breed_type,
                'country_of_origin' => (int) ($request->country_of_origin ?? 0),
                'transport_type' => (int) ($request->transport_type ?? 0),
                'company_id' => (int) ($request->company_id ?? 0),
                'remarks' => $request->remarks,
                'transport_inside_temp' => $request->vehicle_inside_temp,
                'status' => 1,
                'created_by' => Auth::id(),
            ]);

            // Log audit for PsReceive creation
            AuditLogService::logCreated($psReceive, $request);

            Log::info('PS Receive created successfully', ['ps_receive_id' => $psReceive->id]);

            $chickCount = $psReceive->chickCounts()->create([
                'ps_male_rec_box' => (int) $request->ps_male_rec_box,
                'ps_male_qty' => (float) $request->ps_male_qty,
                'ps_female_rec_box' => (int) $request->ps_female_rec_box,
                'ps_female_qty' => (float) $request->ps_female_qty,
                'ps_total_qty' => (float) $request->ps_total_qty,
                'ps_total_re_box_qty' => (int) $request->ps_total_re_box_qty,
                'ps_challan_box_qty' => (int) $request->ps_challan_box_qty,
                'ps_gross_weight' => (float) $request->ps_gross_weight,
                'ps_net_weight' => (float) $request->ps_net_weight,
            ]);

            // Log audit for PsChickCount creation
            AuditLogService::logCreated($chickCount, $request);

            Log::info('PS Chick Count created successfully', ['chick_count_id' => $chickCount->id]);

            $govLabTransfer = $psReceive->labTransfers()->create([
                'ps_receive_id' => $psReceive->id,
                'lab_type' => 1, // Gov Lab
                'lab_send_female_qty' => (int) $request->gov_lab_send_female_qty ?? 0,
                'lab_send_male_qty' => (int) $request->gov_lab_send_male_qty ?? 0,
                'lab_send_total_qty' => (int) $request->gov_lab_send_total_qty ?? 0,
                'lab_receive_female_qty' => (int) $request->gov_lab_receive_female_qty ?? 0,
                'lab_receive_male_qty' => (int) $request->gov_lab_receive_male_qty ?? 0,
                'lab_receive_total_qty' => (int) $request->gov_lab_receive_total_qty ?? 0,
                'notes' => $request->notes ?? null,
                'status' => 1,
            ]);

            // Log audit for Gov Lab Transfer creation
            AuditLogService::logCreated($govLabTransfer, $request);

            // insert for lab_type = 2 (Provita Lab)
            $provitaLabTransfer = $psReceive->labTransfers()->create([
                'ps_receive_id' => $psReceive->id,
                'lab_type' => 2, // Provita Lab
                'lab_send_female_qty' => (int) $request->provita_lab_send_female_qty ?? 0,
                'lab_send_male_qty' => (int) $request->provita_lab_send_male_qty ?? 0,
                'lab_send_total_qty' => (int) $request->provita_lab_send_total_qty ?? 0,
                'lab_receive_female_qty' => (int) $request->provita_lab_receive_female_qty ?? 0,
                'lab_receive_male_qty' => (int) $request->provita_lab_receive_male_qty ?? 0,
                'lab_receive_total_qty' => (int) $request->provita_lab_receive_total_qty ?? 0,
                'notes' => $request->notes ?? null,
                'status' => 1,
            ]);

            // Log audit for Provita Lab Transfer creation
            AuditLogService::logCreated($provitaLabTransfer, $request);

            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {
                    $path = $uploadedFile->store('ps_receive_files'); // storage/app/ps_receive_files
                    $attachment = $psReceive->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $uploadedFile->getClientOriginalExtension(),
                    ]);

                    // Log audit for file attachment creation
                    AuditLogService::logCreated($attachment, $request);
                }
            }

            // if ($request->hasFile('labfile')) {
            //     foreach ($request->file('labfile') as $uploadedFile) {
            //         $path = $uploadedFile->store('ps_lab_files'); // storage/app/ps_receive_files
            //         $psReceive->attachments()->create([
            //             'file_path' => $path,
            //             'file_type' => $uploadedFile->getClientOriginalExtension(),
            //         ]);
            //     }
            // }

            DB::commit();

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PS Receive create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withErrors(['general' => 'Failed to create PS Receive: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {

        // Load the PsReceive entry with relationships
        $psReceive = PsReceive::with([
            'chickCounts',
            'labTransfers',
            'attachments',
        ])->findOrFail($id);

        // Load dropdown data
        $suppliers = Supplier::select('id', 'name')->get();
        $breedTypes = BreedType::select('id', 'name')->get();
        $companies = Company::select('id', 'name')->get();

        $psReceive->labTransfers = $psReceive->labTransfers->toArray();

        // Return Inertia response instead of JSON
        return Inertia::render('ps/ps-receive/Edit', [
            'psReceive' => $psReceive,
            'suppliers' => $suppliers,
            'breedTypes' => $breedTypes,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, PsReceive $psReceive)
    {
        // 1️⃣ Update main PS Receive
        $psReceive->update([
            'shipment_type_id' => (int) $request->shipment_type_id,
            'pi_no' => $request->pi_no,
            'pi_date' => $request->pi_date,
            'order_no' => $request->order_no,
            'order_date' => $request->order_date,
            'lc_no' => $request->lc_no,
            'lc_date' => $request->lc_date,
            'supplier_id' => (int) ($request->supplier_id ?? 0),
            'breed_type' => $request->breed_type ?? [],
            'country_of_origin' => (int) ($request->country_of_origin ?? 0),
            'transport_type' => (int) ($request->transport_type ?? 0),
            'remarks' => $request->remarks,
            'updated_by' => Auth::id(),
        ]);

        // 2️⃣ Update or create chick counts
        if ($psReceive->chickCounts) {
            // Update existing
            $psReceive->chickCounts->update([
                'ps_male_rec_box' => (float) $request->ps_male_rec_box,
                'ps_male_qty' => (float) $request->ps_male_qty,
                'ps_female_rec_box' => (float) $request->ps_female_rec_box,
                'ps_female_qty' => (float) $request->ps_female_qty,
                'ps_total_qty' => (float) $request->ps_total_qty,
                'ps_total_re_box_qty' => (float) $request->ps_total_re_box_qty,
                'ps_challan_box_qty' => (float) $request->ps_challan_box_qty,
                'ps_gross_weight' => (float) $request->ps_gross_weight,
                'ps_net_weight' => (float) $request->ps_net_weight,
            ]);
        } else {
            // Create if not exists
            $psReceive->chickCounts()->create([
                'ps_male_rec_box' => (float) $request->ps_male_rec_box,
                'ps_male_qty' => (float) $request->ps_male_qty,
                'ps_female_rec_box' => (float) $request->ps_female_rec_box,
                'ps_female_qty' => (float) $request->ps_female_qty,
                'ps_total_qty' => (float) $request->ps_total_qty,
                'ps_total_re_box_qty' => (float) $request->ps_total_re_box_qty,
                'ps_challan_box_qty' => (float) $request->ps_challan_box_qty,
                'ps_gross_weight' => (float) $request->ps_gross_weight,
                'ps_net_weight' => (float) $request->ps_net_weight,
            ]);
        }

        // 3️⃣ Handle file uploads
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $uploadedFile) {
                $path = $uploadedFile->store('ps_receive_files'); // storage/app/ps_receive_files
                $psReceive->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $uploadedFile->getClientOriginalExtension(),
                ]);
            }
        }

        return redirect()->route('ps-receive.index')
            ->with('success', 'PS Receive updated successfully.');
    }

    public function destroy(PsReceive $psReceive)
    {
        try {
            $psReceive->delete();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'PS Receive deleted successfully.']);
            }

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('PS Receive delete failed', ['error' => $e->getMessage()]);

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete PS Receive.'], 500);
            }

            return back()->withErrors(['general' => 'Failed to delete PS Receive.']);
        }
    }

    public function storelab(Request $request)
    {

        $psReceiveId = $request->ps_receive_id;
        $labType = $request->lab_type;
        $femaleQty = $request->female_qty;
        $maleQty = $request->male_qty;
        $notes = $request->notes;

        // Create or update the lab test
        $labTest = PsLabTest::updateOrCreate(
            ['ps_receive_id' => $psReceiveId],
            [
                'lab_type' => $labType,
                'female_qty' => $femaleQty,
                'male_qty' => $maleQty,
                'total_qty' => $femaleQty + $maleQty, // auto-calculate total
                'notes' => $notes,
            ]
        );

        return redirect()->route('ps-receive.index')
            ->with('success', 'Lab Test saved successfully!');
    }

    public function getdata($id)
    {
        $psReceive = PsReceive::with('chickCounts')->findOrFail($id);

        // Flatten data from parent + child for modal
        $data = [
            'id' => $psReceive->id,
            'pi_no' => $psReceive->pi_no,
            'pi_date' => $psReceive->pi_date,
            'order_no' => $psReceive->order_no,
            'order_date' => $psReceive->order_date,
            'lc_no' => $psReceive->lc_no,
            'lc_date' => $psReceive->lc_date,
            'supplier_id' => $psReceive->supplier_id,
            'breed_type' => $psReceive->breed_type,
            'country_of_origin' => $psReceive->country_of_origin,
            'transport_type' => $psReceive->transport_type,
            'remarks' => $psReceive->remarks,
            'status' => $psReceive->status,
            // Child fields
            'ps_male_rec_box' => $psReceive->chickCounts->ps_male_rec_box ?? 0,
            'ps_male_qty' => $psReceive->chickCounts->ps_male_qty ?? 0,
            'ps_female_rec_box' => $psReceive->chickCounts->ps_female_rec_box ?? 0,
            'ps_female_qty' => $psReceive->chickCounts->ps_female_qty ?? 0,
            'ps_total_qty' => $psReceive->chickCounts->ps_total_qty ?? 0,
            'ps_total_re_box_qty' => $psReceive->chickCounts->ps_total_re_box_qty ?? 0,
            'ps_challan_box_qty' => $psReceive->chickCounts->ps_challan_box_qty ?? 0,
            'ps_gross_weight' => $psReceive->chickCounts->ps_gross_weight ?? 0,
            'ps_net_weight' => $psReceive->chickCounts->ps_net_weight ?? 0,
        ];

        // Return Inertia response instead of JSON
        return Inertia::render('ps/ps-receive/PsReceive', [
            'psReceive' => $data,
        ]);
    }

    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $psReceives = PsReceive::with(['chickCounts', 'supplier'])
            ->when($request->search, function ($q, $search) {
                $q->where('pi_no', 'like', "%{$search}%")
                    ->orWhere('order_no', 'like', "%{$search}%");
            })
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'pi_no' => $r->pi_no,
                    'shipment_type' => $r->shipment_type_id == 1 ? 'Local' : 'Foreign',
                    'receive_date' => $r->pi_date ? date('Y-m-d', strtotime($r->pi_date)) : '', // ✅ FIXED
                    'supplier' => $r->supplier->name ?? 'N/A',
                    'total_qty' => $r->chickCounts->ps_total_qty ?? 0,
                    'total_box' => $r->chickCounts->ps_total_re_box_qty ?? 0,
                    'remarks' => $r->remarks ?? '',
                ];
            })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Shipment Type', 'key' => 'shipment_type'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
            ['label' => 'Supplier', 'key' => 'supplier'],
            ['label' => 'Total Birds Qty', 'key' => 'total_qty'],
            ['label' => 'Total Box', 'key' => 'total_box'],
            ['label' => 'Remarks', 'key' => 'remarks'],
        ];

        $data = [
            'title' => 'PS Receive Report',
            'columns' => $columns,
            'rows' => $psReceives,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'landscape'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('ps-receive-report.pdf');
    }

    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = PsReceive::with(['chickCounts', 'supplier'])
            ->when($request->search, fn ($q, $search) => $q->where('pi_no', 'like', "%{$search}%")
                ->orWhere('order_no', 'like', "%{$search}%")
            )
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'pi_no' => $r->pi_no,
                'shipment_type' => $r->shipment_type_id == 1 ? 'Local' : 'Foreign',
                'receive_date' => $r->pi_date ? $r->pi_date->format('Y-m-d') : '', // using pi_date
                'supplier' => $r->supplier->name ?? 'N/A',
                'total_qty' => $r->chickCounts->ps_total_qty ?? 0,
                'total_box' => $r->chickCounts->ps_total_re_box_qty ?? 0,
                'remarks' => $r->remarks ?? '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'PI No', 'key' => 'pi_no'],
            ['label' => 'Shipment Type', 'key' => 'shipment_type'],
            ['label' => 'Receive Date', 'key' => 'receive_date'],
            ['label' => 'Supplier', 'key' => 'supplier'],
            ['label' => 'Total Birds Qty', 'key' => 'total_qty'],
            ['label' => 'Total Box', 'key' => 'total_box'],
            ['label' => 'Remarks', 'key' => 'remarks'],
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

        return Excel::download(new ArrayExport($headings, $body), 'ps-receive-report.xlsx');
    }

    public function downloadRowPdf($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $psReceive = PsReceive::with([
            'chickCounts',
            'supplier',
            'labTransfers',
            'attachments',
            'company',
            'country',
        ])->findOrFail($id);

        // Map breed_type IDs to names
        $breedTypes = [];
        if (! empty($psReceive->breed_type) && is_array($psReceive->breed_type)) {
            $breedTypes = BreedType::whereIn('id', $psReceive->breed_type)
                ->pluck('name')
                ->toArray();
        }

        $data = [
            'pi_no' => $psReceive->pi_no,
            'shipment_type' => $psReceive->shipment_type_id == 1 ? 'Local' : 'Foreign',
            'receive_date' => $psReceive->pi_date?->format('Y-m-d') ?? '',
            'supplier' => $psReceive->supplier->name ?? 'N/A',
            'company' => $psReceive->company,
            'order_no' => $psReceive->order_no,
            'order_date' => $psReceive->order_date?->format('Y-m-d') ?? '',
            'breed_types' => $breedTypes,
            'remarks' => $psReceive->remarks ?? '',
            'transport_type' => $psReceive->transport_type,
            'vehicle_temp' => $psReceive->transport_inside_temp,
            'chick_counts' => $psReceive->chickCounts,
            'lab_transfers' => $psReceive->labTransfers,
            'attachments' => $psReceive->attachments,
            'lc_no' => $psReceive->lc_no,
            'lc_date' => $psReceive->lc_date,
            'country_of_origin' => $psReceive->country?->name ?? 'N/A',
            'generatedAt' => now(),
        ];

        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $pdf = Pdf::loadView('reports.ps.receive-row', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("ps-receive-{$psReceive->pi_no}.pdf");
    }
}
