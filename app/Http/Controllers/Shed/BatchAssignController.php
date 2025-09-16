<?php

namespace App\Http\Controllers\Shed;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\Batch;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Level;
use App\Models\Shed\BatchAssign;
use App\Models\Shed\ShedReceive;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BatchAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BatchAssign::with(['shedReceive', 'flock:id,code,name', 'company', 'shed', 'batch:id,name']);

        // Apply search filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('job_no', 'like', '%'.$request->search.'%')
                    ->orWhere('batch_no', 'like', '%'.$request->search.'%')
                    ->orWhereHas('batch', function ($bq) use ($request) {
                        $bq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('flock', function ($fq) use ($request) {
                        $fq->where('name', 'like', '%'.$request->search.'%')
                           ->orWhere('code', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('shed', function ($sq) use ($request) {
                        $sq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('company', function ($cq) use ($request) {
                        $cq->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        // Apply individual filters
        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->flock_id) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->shed_id) {
            $query->where('shed_id', $request->shed_id);
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        // Apply date filters
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $batchAssigns = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString()
            ->through(function ($batch) {
                return [
                    'id' => $batch->id,
                    'job_no' => $batch->job_no,
                    'flock_no' => $batch->flock_no,
                    'flock_id' => $batch->flock_id,
                    'flock_name' => $batch->flock->code ?? '',
                    'company_id' => $batch->company_id,
                    'company_name' => $batch->company->name ?? '',
                    'shed_id' => $batch->shed_id,
                    'shed_name' => $batch->shed->name ?? '',
                    'level' => $batch->level,
                    'stage' => $batch->stage,
                    'batch_no' => $batch->batch_no,
                    'batch_name' => $batch->batch?->name ?? '',
                    'batch_female_qty' => $batch->batch_female_qty,
                    'batch_male_qty' => $batch->batch_male_qty,
                    'batch_total_qty' => $batch->batch_total_qty,
                    'percentage' => $batch->percentage,
                    'created_at' => $batch->created_at,
                    'flock' => $batch->flock ? ['id' => $batch->flock->id, 'name' => $batch->flock->code] : null,
                    'company' => $batch->company ? ['id' => $batch->company->id, 'name' => $batch->company->name] : null,
                    'shed' => $batch->shed ? ['id' => $batch->shed->id, 'name' => $batch->shed->name] : null,
                    'batch' => $batch->batch ? ['id' => $batch->batch->id, 'name' => $batch->batch->name] : null,
                ];
            });

        return Inertia::render('shed/batch-assign/List', [
            'batchAssigns' => $batchAssigns,
            'filters' => $request->only(['search', 'per_page', 'company_id', 'flock_id', 'shed_id', 'level', 'date_from', 'date_to']),
            'companies' => Company::select('id', 'name')->get(),
            'flocks' => Flock::select('id', 'code', 'name')->get(),
            'sheds' => \App\Models\Shed::select('id', 'name')->get(),
            'levels' => Level::select('id', 'name')->get(),
            'batches' => Batch::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // Get all Shed Receives with relations
        $shedReceives = ShedReceive::with(['flock:id,code,name', 'shed:id,name', 'company:id,name'])
            ->get()
            ->map(function ($shed) {
                return [
                    'id' => $shed->id,
                    'transaction_no' => $shed->transaction_no,
                    'flock_id' => $shed->flock_id,
                    'flock' => $shed->flock?->code,
                    'shed_id' => $shed->shed_id,
                    'shed' => $shed->shed?->name,
                    'company_id' => $shed->company_id,
                    'company' => $shed->company?->name,
                    'shed_female_qty' => $shed->shed_female_qty ?? 0,
                    'shed_male_qty' => $shed->shed_male_qty ?? 0,
                    'shed_total_qty' => $shed->shed_total_qty ?? 0,
                    'receive_type' => $shed->receive_type ?? '',
                    'created_by' => Auth::id(),
                ];
            });

        // Flocks (for batch assign form)
        $flocks = Flock::select('id', 'name')->get();

        // Companies (if needed in assign)
        $companies = Company::select('id', 'name')->get();

        // Levels from database
        $levels = Level::where('status', true)->select('id', 'name')->get();

        // Batches from database
        $batches = Batch::where('status', true)->select('id', 'name')->get();

        return Inertia::render('shed/batch-assign/Create', [
            'shedReceives' => $shedReceives,
            'flocks' => $flocks,
            'companies' => $companies,
            'levels' => $levels,
            'batches' => $batches,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $batches = $request->batches ?? [];

        $shedReceive = ShedReceive::findOrFail($request->shed_receive_id);

        // check once using the Transaction model
        $exists = BirdTransfer::where('job_no', $shedReceive->job_no)->exists();

        foreach ($batches as $batch) {
            BatchAssign::create([
                'shed_receive_id' => $shedReceive->id ?? null,
                'job_no' => $shedReceive->job_no ?? null,
                'transaction_no' => $shedReceive->transaction_no ?? null,
                'flock_no' => $shedReceive->flock_no ?? 0,
                'flock_id' => $shedReceive->flock_id ?? null,
                'company_id' => $shedReceive->company_id ?? null,
                'shed_id' => $shedReceive->shed_id ?? null,
                'level' => $batch['level'] ?? null,
                'batch_no' => $batch['batch_no'] ?? 1,
                'batch_female_qty' => $batch['batch_female_qty'] ?? 0,
                'batch_male_qty' => $batch['batch_male_qty'] ?? 0,
                'batch_total_qty' => ($batch['batch_female_qty'] ?? 0) + ($batch['batch_male_qty'] ?? 0),
                'batch_female_mortality' => $batch['batch_female_mortality'] ?? 0,
                'batch_male_mortality' => $batch['batch_male_mortality'] ?? 0,
                'batch_total_mortality' => ($batch['batch_female_mortality'] ?? 0) + ($batch['batch_male_mortality'] ?? 0),
                'batch_excess_male' => $batch['batch_excess_male'] ?? null,
                'batch_excess_female' => $batch['batch_excess_female'] ?? 0,
                'batch_sortage_male' => $batch['batch_sortage_male'] ?? null,
                'batch_sortage_female' => $batch['batch_sortage_female'] ?? 0,
                'percentage' => $batch['percentage'] ?? 0,
                'stage' => $exists ? 3 : 1,
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()
            ->route('batch-assign.index')
            ->with('success', 'Batch assign successfully done!');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $batchAssign = BatchAssign::with(['shedReceive.flock', 'shedReceive.shed', 'shedReceive.company'])
            ->findOrFail($id);

        // Format the batch assign data for display
        $formattedBatchAssign = [
            'id' => $batchAssign->id,
            'shed_receive_id' => $batchAssign->shed_receive_id,
            'job_no' => $batchAssign->job_no,
            'transaction_no' => $batchAssign->transaction_no,
            'flock_no' => $batchAssign->flock_no,
            'flock_id' => $batchAssign->flock_id,
            'company_id' => $batchAssign->company_id,
            'shed_id' => $batchAssign->shed_id,
            'level' => $batchAssign->level,
            'batch_no' => $batchAssign->batch_no,
            'batch_female_qty' => $batchAssign->batch_female_qty,
            'batch_male_qty' => $batchAssign->batch_male_qty,
            'batch_total_qty' => $batchAssign->batch_total_qty,
            'batch_female_mortality' => $batchAssign->batch_female_mortality,
            'batch_male_mortality' => $batchAssign->batch_male_mortality,
            'batch_total_mortality' => $batchAssign->batch_total_mortality,
            'batch_excess_female' => $batchAssign->batch_excess_female,
            'batch_excess_male' => $batchAssign->batch_excess_male,
            'batch_total_excess' => $batchAssign->batch_total_excess,
            'batch_sortage_female' => $batchAssign->batch_sortage_female,
            'batch_sortage_male' => $batchAssign->batch_sortage_male,
            'batch_total_sortage' => $batchAssign->batch_total_sortage,
            'percentage' => $batchAssign->percentage,
            'created_at' => $batchAssign->created_at,
            'updated_at' => $batchAssign->updated_at,
            'shedReceive' => $batchAssign->shedReceive ? [
                'id' => $batchAssign->shedReceive->id,
                'transaction_no' => $batchAssign->shedReceive->transaction_no,
                'flock_id' => $batchAssign->shedReceive->flock_id,
                'flock' => $batchAssign->shedReceive->flock?->name,
                'shed_id' => $batchAssign->shedReceive->shed_id,
                'shed' => $batchAssign->shedReceive->shed?->name,
                'company_id' => $batchAssign->shedReceive->company_id,
                'company' => $batchAssign->shedReceive->company?->name,
                'shed_female_qty' => $batchAssign->shedReceive->shed_female_qty ?? 0,
                'shed_male_qty' => $batchAssign->shedReceive->shed_male_qty ?? 0,
                'shed_total_qty' => $batchAssign->shedReceive->shed_total_qty ?? 0,
            ] : null,
        ];

        return Inertia::render('shed/batch-assign/View', [
            'batchAssign' => $formattedBatchAssign,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $batchAssign = BatchAssign::with(['shedReceive.flock', 'shedReceive.shed', 'shedReceive.company'])
            ->findOrFail($id);

        // Get all Shed Receives with relations
        $shedReceives = ShedReceive::with(['flock:id,code,name', 'shed:id,name', 'company:id,name'])
            ->get()
            ->map(function ($shed) {
                return [
                    'id' => $shed->id,
                    'transaction_no' => $shed->transaction_no,
                    'flock_id' => $shed->flock_id,
                    'flock' => $shed->flock?->code,
                    'shed_id' => $shed->shed_id,
                    'shed' => $shed->shed?->name,
                    'company_id' => $shed->company_id,
                    'company' => $shed->company?->name,
                    'shed_female_qty' => $shed->shed_female_qty ?? 0,
                    'shed_male_qty' => $shed->shed_male_qty ?? 0,
                    'shed_total_qty' => $shed->shed_total_qty ?? 0,
                    'receive_type' => $shed->receive_type ?? '',
                    'created_by' => Auth::id(),
                ];
            });

        // Flocks (for batch assign form)
        $flocks = Flock::select('id', 'name')->get();

        // Companies (if needed in assign)
        $companies = Company::select('id', 'name')->get();

        // Levels from database
        $levels = Level::where('status', true)->select('id', 'name')->get();

        // Batches from database
        $batches = Batch::where('status', true)->select('id', 'name')->get();

        // Format the batch assign data for the form
        $formattedBatchAssign = [
            'id' => $batchAssign->id,
            'shed_receive_id' => $batchAssign->shed_receive_id,
            'job_no' => $batchAssign->job_no,
            'transaction_no' => $batchAssign->transaction_no,
            'flock_no' => $batchAssign->flock_no,
            'flock_id' => $batchAssign->flock_id,
            'company_id' => $batchAssign->company_id,
            'shed_id' => $batchAssign->shed_id,
            'level' => $batchAssign->level,
            'batch_no' => $batchAssign->batch_no,
            'batch_female_qty' => $batchAssign->batch_female_qty,
            'batch_male_qty' => $batchAssign->batch_male_qty,
            'batch_total_qty' => $batchAssign->batch_total_qty,
            'batch_female_mortality' => $batchAssign->batch_female_mortality,
            'batch_male_mortality' => $batchAssign->batch_male_mortality,
            'batch_total_mortality' => $batchAssign->batch_total_mortality,
            'batch_excess_female' => $batchAssign->batch_excess_female,
            'batch_excess_male' => $batchAssign->batch_excess_male,
            'batch_total_excess' => $batchAssign->batch_total_excess,
            'batch_sortage_female' => $batchAssign->batch_sortage_female,
            'batch_sortage_male' => $batchAssign->batch_sortage_male,
            'batch_total_sortage' => $batchAssign->batch_total_sortage,
            'percentage' => $batchAssign->percentage,
            'created_at' => $batchAssign->created_at,
            'updated_at' => $batchAssign->updated_at,
            'shedReceive' => $batchAssign->shedReceive ? [
                'id' => $batchAssign->shedReceive->id,
                'transaction_no' => $batchAssign->shedReceive->transaction_no,
                'flock_id' => $batchAssign->shedReceive->flock_id,
                'flock' => $batchAssign->shedReceive->flock?->name,
                'shed_id' => $batchAssign->shedReceive->shed_id,
                'shed' => $batchAssign->shedReceive->shed?->name,
                'company_id' => $batchAssign->shedReceive->company_id,
                'company' => $batchAssign->shedReceive->company?->name,
                'shed_female_qty' => $batchAssign->shedReceive->shed_female_qty ?? 0,
                'shed_male_qty' => $batchAssign->shedReceive->shed_male_qty ?? 0,
                'shed_total_qty' => $batchAssign->shedReceive->shed_total_qty ?? 0,
            ] : null,
        ];

        return Inertia::render('shed/batch-assign/Edit', [
            'batchAssign' => $formattedBatchAssign,
            'shedReceives' => $shedReceives,
            'flocks' => $flocks,
            'companies' => $companies,
            'levels' => $levels,
            'batches' => $batches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $batchAssign = BatchAssign::findOrFail($id);

        $shedReceive = ShedReceive::findOrFail($request->shed_receive_id);

        $batchAssign->update([
            'shed_receive_id' => $shedReceive->id ?? null,
            'job_no' => $shedReceive->job_no ?? null,
            'transaction_no' => $shedReceive->transaction_no ?? null,
            'flock_no' => $shedReceive->flock_name ?? 0,
            'flock_id' => $shedReceive->flock_id ?? null,
            'company_id' => $shedReceive->company_id ?? null,
            'shed_id' => $shedReceive->shed_id ?? null,
            'level' => $request->level ?? null,
            'batch_no' => $request->batch_no ?? 1,
            'batch_female_qty' => $request->batch_female_qty ?? 0,
            'batch_male_qty' => $request->batch_male_qty ?? 0,
            'batch_total_qty' => ($request->batch_female_qty ?? 0) + ($request->batch_male_qty ?? 0),
            'batch_female_mortality' => $request->batch_female_mortality ?? 0,
            'batch_male_mortality' => $request->batch_male_mortality ?? 0,
            'batch_total_mortality' => ($request->batch_female_mortality ?? 0) + ($request->batch_male_mortality ?? 0),
            'batch_excess_male' => $request->batch_excess_male ?? null,
            'batch_excess_female' => $request->batch_excess_female ?? 0,
            'batch_sortage_male' => $request->batch_sortage_male ?? null,
            'batch_sortage_female' => $request->batch_sortage_female ?? 0,
            'percentage' => $request->percentage ?? 0,
        ]);

        return redirect()
            ->route('batch-assign.index')
            ->with('success', 'Batch assignment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Export batch assignments to PDF
     */
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = BatchAssign::with(['shedReceive', 'flock', 'company', 'shed']);

        // Apply same filters as index method
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('job_no', 'like', '%'.$request->search.'%')
                    ->orWhere('batch_no', 'like', '%'.$request->search.'%')
                    ->orWhereHas('flock', function ($fq) use ($request) {
                        $fq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('shed', function ($sq) use ($request) {
                        $sq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('company', function ($cq) use ($request) {
                        $cq->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->flock_id) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->shed_id) {
            $query->where('shed_id', $request->shed_id);
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $batchAssigns = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'flock_name' => $batch->flock->name ?? '',
                    'shed_name' => $batch->shed->name ?? '',
                    'company_name' => $batch->company->name ?? '',
                    'level' => $batch->level,
                    'batch_no' => $batch->batch_no,
                    'batch_female_qty' => $batch->batch_female_qty,
                    'batch_male_qty' => $batch->batch_male_qty,
                    'batch_total_qty' => $batch->batch_total_qty,
                    'percentage' => $batch->percentage ?? 0,
                    'created_at' => $batch->created_at->format('d-M-Y'),
                ];
            })->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Shed Name', 'key' => 'shed_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Level', 'key' => 'level'],
            ['label' => 'Batch No', 'key' => 'batch_no'],
            ['label' => 'Male Qty', 'key' => 'batch_male_qty'],
            ['label' => 'Female Qty', 'key' => 'batch_female_qty'],
            ['label' => 'Total Qty', 'key' => 'batch_total_qty'],
            ['label' => 'Percentage', 'key' => 'percentage'],
            ['label' => 'Created Date', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Batch Assignment List',
            'columns' => $columns,
            'rows' => $batchAssigns,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('batch-assignment-list.pdf');
    }

    /**
     * Export batch assignments to Excel
     */
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $query = BatchAssign::with(['shedReceive', 'flock', 'company', 'shed']);

        // Apply same filters as index method
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('job_no', 'like', '%'.$request->search.'%')
                    ->orWhere('batch_no', 'like', '%'.$request->search.'%')
                    ->orWhereHas('flock', function ($fq) use ($request) {
                        $fq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('shed', function ($sq) use ($request) {
                        $sq->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('company', function ($cq) use ($request) {
                        $cq->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->flock_id) {
            $query->where('flock_id', $request->flock_id);
        }

        if ($request->shed_id) {
            $query->where('shed_id', $request->shed_id);
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $rows = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch, $index) {
                return [
                    'sn' => $index + 1,
                    'flock_name' => $batch->flock->name ?? '',
                    'shed_name' => $batch->shed->name ?? '',
                    'company_name' => $batch->company->name ?? '',
                    'level' => $batch->level,
                    'batch_no' => $batch->batch_no,
                    'batch_female_qty' => $batch->batch_female_qty,
                    'batch_male_qty' => $batch->batch_male_qty,
                    'batch_total_qty' => $batch->batch_total_qty,
                    'batch_female_mortality' => $batch->batch_female_mortality ?? 0,
                    'batch_male_mortality' => $batch->batch_male_mortality ?? 0,
                    'batch_total_mortality' => $batch->batch_total_mortality ?? 0,
                    'batch_excess_female' => $batch->batch_excess_female ?? 0,
                    'batch_excess_male' => $batch->batch_excess_male ?? 0,
                    'batch_sortage_female' => $batch->batch_sortage_female ?? 0,
                    'batch_sortage_male' => $batch->batch_sortage_male ?? 0,
                    'percentage' => $batch->percentage ?? 0,
                    'created_at' => $batch->created_at->format('Y-m-d H:i'),
                ];
            })->toArray();

        $columns = [
            ['label' => 'S/N', 'key' => 'sn'],
            ['label' => 'Flock Name', 'key' => 'flock_name'],
            ['label' => 'Shed Name', 'key' => 'shed_name'],
            ['label' => 'Company', 'key' => 'company_name'],
            ['label' => 'Level', 'key' => 'level'],
            ['label' => 'Batch No', 'key' => 'batch_no'],
            ['label' => 'Female Qty', 'key' => 'batch_female_qty'],
            ['label' => 'Male Qty', 'key' => 'batch_male_qty'],
            ['label' => 'Total Qty', 'key' => 'batch_total_qty'],
            ['label' => 'Female Mortality', 'key' => 'batch_female_mortality'],
            ['label' => 'Male Mortality', 'key' => 'batch_male_mortality'],
            ['label' => 'Total Mortality', 'key' => 'batch_total_mortality'],
            ['label' => 'Female Excess', 'key' => 'batch_excess_female'],
            ['label' => 'Male Excess', 'key' => 'batch_excess_male'],
            ['label' => 'Female Shortage', 'key' => 'batch_sortage_female'],
            ['label' => 'Male Shortage', 'key' => 'batch_sortage_male'],
            ['label' => 'Percentage', 'key' => 'percentage'],
            ['label' => 'Created Date', 'key' => 'created_at'],
        ];

        $headings = array_map(fn ($c) => $c['label'], $columns);

        $body = [];
        foreach ($rows as $i => $row) {
            $rowData = [];
            foreach ($columns as $col) {
                if (isset($col['callback']) && is_callable($col['callback'])) {
                    $rowData[] = $col['callback']($row, $i);
                } else {
                    $rowData[] = $row[$col['key']] ?? '';
                }
            }
            $body[] = $rowData;
        }

        $data = [
            'title' => 'Batch Assignment List',
            'headings' => $headings,
            'rows' => $body,
            'generatedAt' => now()->format('Y-m-d H:i'),
        ];

        return Excel::download(new ArrayExport($headings, $body), 'batch-assignment-list.xlsx');
    }

    /**
     * Download individual batch assignment PDF
     */
    public function downloadRowPdf(string $id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $batchAssign = BatchAssign::with(['shedReceive', 'flock', 'company', 'shed'])->findOrFail($id);

        $data = [
            'title' => 'Batch Assignment Details',
            'batchAssign' => $batchAssign,
            'flock_name' => $batchAssign->flock->name ?? '',
            'shed_name' => $batchAssign->shed->name ?? '',
            'company_name' => $batchAssign->company->name ?? '',
            'shed_receive_transaction' => $batchAssign->shedReceive->transaction_no ?? '',
            'generatedAt' => now(),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.batch-assign.row', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("batch-assignment-{$id}.pdf");
    }

    public function nextStage(BatchAssign $batchAssign)
    {
        // Increment stage safely (1 → 2 → 3)
        if ($batchAssign->stage < 3) {
            $batchAssign->stage++;

            $batchAssign->growing_start_date = date('Y-m-d');
            $batchAssign->save();
        }

        return redirect()->back()->with('success', 'Stage updated successfully.');
    }
}
