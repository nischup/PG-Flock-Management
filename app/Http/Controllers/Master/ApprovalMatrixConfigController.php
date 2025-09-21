<?php

namespace App\Http\Controllers\Master;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Master\ApprovalMatrixConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ApprovalMatrixConfigController extends Controller
{
    // ------------------- LIST APPROVAL MATRIX CONFIGS -------------------
    public function index(Request $request)
    {
        try {
            $configs = ApprovalMatrixConfig::with('layers')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'name' => $config->name,
                        'module_name' => $config->module_name,
                        'approval_type' => $config->approval_type,
                        'description' => $config->description,
                        'is_active' => (int) $config->is_active,
                        'layers_count' => $config->layers->count(),
                        'created_at' => $config->created_at->format('d M Y'),
                    ];
                });

            return Inertia::render('library/approval-matrix-config/List', [
                'configs' => $configs,
            ]);
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixConfig index error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch approval matrix configurations.');
        }
    }

    // ------------------- SHOW CREATE FORM -------------------
    public function create()
    {
        $modules = [
            'ps-receive' => 'PS Receive',
            'ps-firm-receive' => 'PS Firm Receive',
            'batch-assign' => 'Batch Assignment',
            'shed-receive' => 'Shed Receive',
            'bird-transfer' => 'Bird Transfer',
            'order-planning' => 'Order Planning',
        ];

        $approvalTypes = [
            'sequential' => 'Sequential (One by One)',
            'parallel' => 'Parallel (All at Once)',
            'conditional' => 'Conditional (Based on Rules)',
        ];

        return Inertia::render('library/approval-matrix-config/Create', [
            'modules' => $modules,
            'approvalTypes' => $approvalTypes,
        ]);
    }

    // ------------------- CREATE APPROVAL MATRIX CONFIG -------------------
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'module_name' => 'required|string|max:100',
            'approval_type' => 'required|string|in:sequential,parallel,conditional',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $config = ApprovalMatrixConfig::create($data);

            return redirect()->route('approval-matrix-config.index')
                ->with('success', 'Approval matrix configuration created successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixConfig store error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to create approval matrix configuration.');
        }
    }

    // ------------------- SHOW EDIT FORM -------------------
    public function edit(string $id)
    {
        try {
            $config = ApprovalMatrixConfig::with('layers')->findOrFail($id);

            $modules = [
                'ps-receive' => 'PS Receive',
                'ps-firm-receive' => 'PS Firm Receive',
                'batch-assign' => 'Batch Assignment',
                'shed-receive' => 'Shed Receive',
                'bird-transfer' => 'Bird Transfer',
                'order-planning' => 'Order Planning',
            ];

            $approvalTypes = [
                'sequential' => 'Sequential (One by One)',
                'parallel' => 'Parallel (All at Once)',
                'conditional' => 'Conditional (Based on Rules)',
            ];

            return Inertia::render('library/approval-matrix-config/Edit', [
                'config' => [
                    'id' => $config->id,
                    'name' => $config->name,
                    'module_name' => $config->module_name,
                    'approval_type' => $config->approval_type,
                    'description' => $config->description,
                    'is_active' => (int) $config->is_active,
                    'layers' => $config->layers->map(function ($layer) {
                        return [
                            'id' => $layer->id,
                            'layer_order' => $layer->layer_order,
                            'layer_name' => $layer->layer_name,
                            'role_name' => $layer->role_name,
                            'is_required' => (int) $layer->is_required,
                            'can_override' => (int) $layer->can_override,
                            'timeout_hours' => $layer->timeout_hours,
                            'description' => $layer->description,
                            'is_active' => (int) $layer->is_active,
                        ];
                    }),
                ],
                'modules' => $modules,
                'approvalTypes' => $approvalTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixConfig edit error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch approval matrix configuration.');
        }
    }

    // ------------------- UPDATE APPROVAL MATRIX CONFIG -------------------
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'module_name' => 'required|string|max:100',
            'approval_type' => 'required|string|in:sequential,parallel,conditional',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $config = ApprovalMatrixConfig::findOrFail($id);
            $config->update($data);

            return redirect()->route('approval-matrix-config.index')
                ->with('success', 'Approval matrix configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixConfig update error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update approval matrix configuration.');
        }
    }

    // ------------------- DELETE APPROVAL MATRIX CONFIG -------------------
    public function destroy(string $id)
    {
        try {
            $config = ApprovalMatrixConfig::findOrFail($id);
            $config->delete();

            return redirect()->route('approval-matrix-config.index')
                ->with('success', 'Approval matrix configuration deleted successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixConfig delete error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete approval matrix configuration.');
        }
    }

    // ------------------- PDF EXPORT -------------------
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $configs = ApprovalMatrixConfig::with('layers')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($c) => [
                'name' => $c->name,
                'module_name' => $c->module_name,
                'approval_type' => $c->approval_type,
                'description' => $c->description,
                'is_active' => $c->is_active ? 'Active' : 'Inactive',
                'layers_count' => $c->layers->count(),
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Module', 'key' => 'module_name'],
            ['label' => 'Type', 'key' => 'approval_type'],
            ['label' => 'Description', 'key' => 'description'],
            ['label' => 'Status', 'key' => 'is_active'],
            ['label' => 'Layers', 'key' => 'layers_count'],
            ['label' => 'Created', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Approval Matrix Configurations',
            'columns' => $columns,
            'rows' => $configs,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
            ->setPaper('a4', $data['orientation']);

        return $pdf->stream('approval-matrix-configs.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = ApprovalMatrixConfig::with('layers')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($c) => [
                'name' => $c->name,
                'module_name' => $c->module_name,
                'approval_type' => $c->approval_type,
                'description' => $c->description,
                'is_active' => $c->is_active ? 'Active' : 'Inactive',
                'layers_count' => $c->layers->count(),
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn ($r, $i) => $i + 1],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Module', 'key' => 'module_name'],
            ['label' => 'Type', 'key' => 'approval_type'],
            ['label' => 'Description', 'key' => 'description'],
            ['label' => 'Status', 'key' => 'is_active'],
            ['label' => 'Layers', 'key' => 'layers_count'],
            ['label' => 'Created', 'key' => 'created_at'],
        ];

        $headings = array_map(fn ($c) => $c['label'], $columns);

        $body = [];
        foreach ($rows as $i => $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = $col['callback'] ?? null;
                $line[] = $val && is_callable($val) ? $val($row, $i) : ($row[$col['key']] ?? '');
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'approval-matrix-configs.xlsx');
    }
}
