<?php

namespace App\Http\Controllers\Ps;
use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsReceiveAttachment;
use App\Models\Ps\PsChickCount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Ps\PsLabTest;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

use App\Models\Master\BreedType;

use App\Models\Master\Supplier;

use App\Models\Master\Company;

class PsReceiveController extends Controller
{
    public function index(Request $request)
    {
        $psReceives = PsReceive::query()
            ->with(['chickCounts'])
            ->when($request->search, fn($q) =>
                $q->where('pi_no', 'like', "%{$request->search}%")
                  ->orWhere('order_no', 'like', "%{$request->search}%")
            )
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('ps/ps-receive/PsReceive', [
            'psReceives' => $psReceives,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {

        return inertia('ps/ps-receive/Create', [
            'suppliers' => Supplier::all(),
            'breedTypes' => BreedType::all(),
            'companies' => Company::all(),
        ]);


    }

    public function store(Request $request)
    {

        // $request->validate([
        //     // Main PS Receive
        //     //'shipment_type_id'   => 'required|integer|exists:shipment_types,id',
        //     'shipment_type_id'   => 'required|integer',
        //     'pi_no'              => 'required|string|max:50',
        //     'pi_date'            => 'required|date',
        //     'order_no'           => 'nullable|string|max:50',
        //     'order_date'         => 'nullable|date',
        //     'lc_no'              => 'nullable|string|max:50',
        //     'lc_date'            => 'nullable|date',
        //     //'supplier_id'        => 'required|integer|exists:suppliers,id',
        //     'supplier_id'        => 'required|integer',
        //     'breed_type'         => 'required|integer',
        //     'country_of_origin'  => 'required|integer',
        //     'transport_type'     => 'required|integer',
        //     'remarks'            => 'nullable|string|max:500',

        //     // Chick Counts
        //     'ps_male_rec_box'    => 'required|numeric|min:0',
        //     'ps_male_qty'        => 'required|numeric|min:0',
        //     'ps_female_rec_box'  => 'required|numeric|min:0',
        //     'ps_female_qty'      => 'required|numeric|min:0',
        //     'ps_total_qty'       => 'required|numeric|min:0',
        //     'ps_total_re_box_qty'=> 'required|numeric|min:0',
        //     'ps_challan_box_qty' => 'required|numeric|min:0',
        //     'ps_gross_weight'    => 'required|numeric|min:0',
        //     'ps_net_weight'      => 'required|numeric|min:0', // net ≤ gross
        // ]);

        try {
            //DB::beginTransaction();

            // 1️⃣ Create main PS Receive

<<<<<<< HEAD
               dd($request->all()); 
=======

>>>>>>> b83b31ab2cba766ab15511924f6b7f51e390cc0e
            //dd($request);
            $psReceive = PsReceive::create([
                'shipment_type_id' => (int) $request->shipment_type_id,
                'pi_no' => $request->pi_no,
                'pi_date' => $request->pi_date,
                'order_no' => $request->order_no,
                'order_date' => $request->order_date,
                'lc_no' => $request->lc_no,
                'lc_date' => $request->lc_date,
                'supplier_id' => (int) ($request->supplier_id ?? 0),
                'breed_type' => collect($request->breed_type)->pluck('id')->map(fn($id) => (int) $id)->unique()->toArray(),
                'country_of_origin' => (int) ($request->country_of_origin ?? 0),
                'transport_type' => (int) ($request->transport_type ?? 0),
                'company_id' => (int) ($request->company_id ?? 0),
                'remarks' => $request->remarks,
                'transport_inside_temp' => $request->vehicle_inside_temp,
                'status' => 1,
                'created_by' =>Auth::id()
            ]);

            $psReceive->chickCounts()->create([
                'ps_male_rec_box'=>(int)$request->ps_male_rec_box,
                'ps_male_qty'=>(float)$request->ps_male_qty,
                'ps_female_rec_box'=>(int)$request->ps_female_rec_box,
                'ps_female_qty'=>(float)$request->ps_female_qty,
                'ps_total_qty'=>(float)$request->ps_total_qty,
                'ps_total_re_box_qty'=>(int)$request->ps_total_re_box_qty,
                'ps_challan_box_qty'=>(int)$request->ps_challan_box_qty,
                'ps_gross_weight'=>(float)$request->ps_gross_weight,
                'ps_net_weight'=>(float)$request->ps_net_weight,
            ]);

            $psReceive->labTransfers()->create([
                'ps_receive_id'           => $psReceive->id,
                'lab_type'                => 1, // Gov Lab
                'lab_send_female_qty'     => (int) $request->gov_lab_send_female_qty ?? 0,
                'lab_send_male_qty'       => (int) $request->gov_lab_send_male_qty ?? 0,
                'lab_send_total_qty'      => (int) $request->gov_lab_send_total_qty ?? 0,
                'lab_receive_female_qty'  => (int) $request->gov_lab_receive_female_qty ?? 0,
                'lab_receive_male_qty'    => (int) $request->gov_lab_receive_male_qty ?? 0,
                'lab_receive_total_qty'   => (int) $request->gov_lab_receive_total_qty ?? 0,
                'notes'                   => $request->notes ?? null,
                'status'                  => 1,
            ]);

            // insert for lab_type = 2 (Provita Lab)
            $psReceive->labTransfers()->create([
                'ps_receive_id'           => $psReceive->id,
                'lab_type'                => 2, // Provita Lab
                'lab_send_female_qty'     => (int) $request->provita_lab_send_female_qty ?? 0,
                'lab_send_male_qty'       => (int) $request->provita_lab_send_male_qty ?? 0,
                'lab_send_total_qty'      => (int) $request->provita_lab_send_total_qty ?? 0,
                'lab_receive_female_qty'  => (int) $request->provita_lab_receive_female_qty ?? 0,
                'lab_receive_male_qty'    => (int) $request->provita_lab_receive_male_qty ?? 0,
                'lab_receive_total_qty'   => (int) $request->provita_lab_receive_total_qty ?? 0,
                'notes'                   => $request->notes ?? null,
                'status'                  => 1,
            ]);


            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {
                    $path = $uploadedFile->store('ps_receive_files'); // storage/app/ps_receive_files
                    $psReceive->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $uploadedFile->getClientOriginalExtension(),
                    ]);
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

            //DB::commit();

            return redirect()->route('ps-receive.index')
                ->with('success', 'PS Receive created successfully.');
        } catch (\Throwable $e) {

            //DB::rollBack();
            Log::error('PS Receive create failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Failed to create PS Receive. Please try again.']);
        }
    }

    public function edit($id)
    {





        // Load the PsReceive entry with relationships
        $psReceive = PsReceive::with([
            'chickCounts',
            'labTransfers',
            'attachments'
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
                'updated_by' => Auth::id()
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

    public function storelab(Request $request){

            $psReceiveId = $request->ps_receive_id;
            $labType     = $request->lab_type;
            $femaleQty   = $request->female_qty;
            $maleQty     = $request->male_qty;
            $notes       = $request->notes;

            // Create or update the lab test
            $labTest = PsLabTest::updateOrCreate(
                ['ps_receive_id' => $psReceiveId],
                [
                    'lab_type'   => $labType,
                    'female_qty' => $femaleQty,
                    'male_qty'   => $maleQty,
                    'total_qty'  => $femaleQty + $maleQty, // auto-calculate total
                    'notes'      => $notes,
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
            'psReceive' => $data
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
                'pi_no'         => $r->pi_no,
                'shipment_type' => $r->shipment_type_id == 1 ? 'Local' : 'Foreign',
                'receive_date'  => $r->pi_date ? date('Y-m-d', strtotime($r->pi_date)) : '', // ✅ FIXED
                'supplier'      => $r->supplier->name ?? 'N/A',
                'total_qty'     => $r->chickCounts->ps_total_qty ?? 0,
                'total_box'     => $r->chickCounts->ps_total_re_box_qty ?? 0,
                'remarks'       => $r->remarks ?? '',
            ];
        })->toArray();

    $columns = [
        ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
        ['label' => 'PI No', 'key' => 'pi_no'],
        ['label' => 'Shipment Type', 'key' => 'shipment_type'],
        ['label' => 'Receive Date', 'key' => 'receive_date'],
        ['label' => 'Supplier', 'key' => 'supplier'],
        ['label' => 'Total Birds Qty', 'key' => 'total_qty'],
        ['label' => 'Total Box', 'key' => 'total_box'],
        ['label' => 'Remarks', 'key' => 'remarks'],
    ];

    $data = [
        'title'       => 'PS Receive Report',
        'columns'     => $columns,
        'rows'        => $psReceives,
        'filters'     => $request->all(),
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
        ->when($request->search, fn($q, $search) =>
            $q->where('pi_no', 'like', "%{$search}%")
              ->orWhere('order_no', 'like', "%{$search}%")
        )
        ->latest()
        ->get()
        ->map(fn($r) => [
            'pi_no'         => $r->pi_no,
            'shipment_type' => $r->shipment_type_id == 1 ? 'Local' : 'Foreign',
            'receive_date'  => $r->pi_date ? $r->pi_date->format('Y-m-d') : '', // using pi_date
            'supplier'      => $r->supplier->name ?? 'N/A',
            'total_qty'     => $r->chickCounts->ps_total_qty ?? 0,
            'total_box'     => $r->chickCounts->ps_total_re_box_qty ?? 0,
            'remarks'       => $r->remarks ?? '',
        ])->toArray();

    $columns = [
        ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=> $i+1],
        ['label'=>'PI No', 'key'=>'pi_no'],
        ['label'=>'Shipment Type', 'key'=>'shipment_type'],
        ['label'=>'Receive Date', 'key'=>'receive_date'],
        ['label'=>'Supplier', 'key'=>'supplier'],
        ['label'=>'Total Birds Qty', 'key'=>'total_qty'],
        ['label'=>'Total Box', 'key'=>'total_box'],
        ['label'=>'Remarks', 'key'=>'remarks'],
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
        'attachments'
    ])->findOrFail($id);

    $data = [
        'pi_no'          => $psReceive->pi_no,
        'shipment_type'  => $psReceive->shipment_type_id == 1 ? 'Local' : 'Foreign',
        'receive_date'   => $psReceive->pi_date?->format('Y-m-d') ?? '',
        'supplier'       => $psReceive->supplier->name ?? 'N/A',
        'remarks'        => $psReceive->remarks ?? '',
        'chick_counts'   => $psReceive->chickCounts,
        'lab_transfers'  => $psReceive->labTransfers,
        'attachments'    => $psReceive->attachments,
        'generatedAt'    => now(),
    ];

    Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
    $pdf = Pdf::loadView('reports.ps.receive-row', $data)
              ->setPaper('a4', 'portrait');

    return $pdf->stream("ps-receive-{$psReceive->pi_no}.pdf");
}


}
