<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\ApprovalMatrixConfig;
use App\Models\Master\ApprovalMatrixLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class ApprovalMatrixLayerController extends Controller
{
    // ------------------- LIST APPROVAL MATRIX LAYERS -------------------
    public function index(Request $request)
    {
        try {
            $configId = $request->get('config_id');

            if (! $configId) {
                return redirect()->back()->with('error', 'Configuration ID is required.');
            }

            $config = ApprovalMatrixConfig::findOrFail($configId);

            $layers = ApprovalMatrixLayer::where('approval_matrix_config_id', $configId)
                ->orderBy('layer_order')
                ->get()
                ->map(function ($layer) {
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
                        'created_at' => $layer->created_at->format('d M Y'),
                    ];
                });

            return Inertia::render('library/approval-matrix-layer/List', [
                'layers' => $layers,
                'config' => [
                    'id' => $config->id,
                    'name' => $config->name,
                    'module_name' => $config->module_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixLayer index error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch approval matrix layers.');
        }
    }

    // ------------------- SHOW CREATE FORM -------------------
    public function create(Request $request)
    {
        $configId = $request->get('config_id');

        if (! $configId) {
            return redirect()->back()->with('error', 'Configuration ID is required.');
        }

        $config = ApprovalMatrixConfig::findOrFail($configId);
        $roles = Role::all()->pluck('name', 'name');

        return Inertia::render('library/approval-matrix-layer/Create', [
            'config' => [
                'id' => $config->id,
                'name' => $config->name,
                'module_name' => $config->module_name,
            ],
            'roles' => $roles,
        ]);
    }

    // ------------------- CREATE APPROVAL MATRIX LAYER -------------------
    public function store(Request $request)
    {
        $data = $request->validate([
            'approval_matrix_config_id' => 'required|exists:approval_matrix_configs,id',
            'layer_order' => 'required|integer|min:1',
            'layer_name' => 'required|string|max:200',
            'role_name' => 'required|string|max:100',
            'is_required' => 'nullable|boolean',
            'can_override' => 'nullable|boolean',
            'timeout_hours' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            // Check if layer order already exists for this config
            $existingLayer = ApprovalMatrixLayer::where('approval_matrix_config_id', $data['approval_matrix_config_id'])
                ->where('layer_order', $data['layer_order'])
                ->first();

            if ($existingLayer) {
                return redirect()->back()->with('error', 'Layer order already exists for this configuration.');
            }

            ApprovalMatrixLayer::create($data);

            return redirect()->route('approval-matrix-layer.index', ['config_id' => $data['approval_matrix_config_id']])
                ->with('success', 'Approval matrix layer created successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixLayer store error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to create approval matrix layer.');
        }
    }

    // ------------------- SHOW EDIT FORM -------------------
    public function edit(string $id)
    {
        try {
            $layer = ApprovalMatrixLayer::with('config')->findOrFail($id);
            $roles = Role::all()->pluck('name', 'name');

            return Inertia::render('library/approval-matrix-layer/Edit', [
                'layer' => [
                    'id' => $layer->id,
                    'approval_matrix_config_id' => $layer->approval_matrix_config_id,
                    'layer_order' => $layer->layer_order,
                    'layer_name' => $layer->layer_name,
                    'role_name' => $layer->role_name,
                    'is_required' => (int) $layer->is_required,
                    'can_override' => (int) $layer->can_override,
                    'timeout_hours' => $layer->timeout_hours,
                    'description' => $layer->description,
                    'is_active' => (int) $layer->is_active,
                ],
                'config' => [
                    'id' => $layer->config->id,
                    'name' => $layer->config->name,
                    'module_name' => $layer->config->module_name,
                ],
                'roles' => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixLayer edit error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch approval matrix layer.');
        }
    }

    // ------------------- UPDATE APPROVAL MATRIX LAYER -------------------
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'layer_order' => 'required|integer|min:1',
            'layer_name' => 'required|string|max:200',
            'role_name' => 'required|string|max:100',
            'is_required' => 'nullable|boolean',
            'can_override' => 'nullable|boolean',
            'timeout_hours' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $layer = ApprovalMatrixLayer::findOrFail($id);

            // Check if layer order already exists for this config (excluding current layer)
            $existingLayer = ApprovalMatrixLayer::where('approval_matrix_config_id', $layer->approval_matrix_config_id)
                ->where('layer_order', $data['layer_order'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingLayer) {
                return redirect()->back()->with('error', 'Layer order already exists for this configuration.');
            }

            $layer->update($data);

            return redirect()->route('approval-matrix-layer.index', ['config_id' => $layer->approval_matrix_config_id])
                ->with('success', 'Approval matrix layer updated successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixLayer update error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update approval matrix layer.');
        }
    }

    // ------------------- DELETE APPROVAL MATRIX LAYER -------------------
    public function destroy(string $id)
    {
        try {
            $layer = ApprovalMatrixLayer::findOrFail($id);
            $configId = $layer->approval_matrix_config_id;
            $layer->delete();

            return redirect()->route('approval-matrix-layer.index', ['config_id' => $configId])
                ->with('success', 'Approval matrix layer deleted successfully.');
        } catch (\Exception $e) {
            Log::error('ApprovalMatrixLayer delete error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete approval matrix layer.');
        }
    }
}
