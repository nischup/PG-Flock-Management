<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\ApprovalMatrixConfig;
use App\Models\Master\ApprovalMatrixLayer;
use Spatie\Permission\Models\Role;

class ApprovalMatrixSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = [
            'security',
            'store_incharge',
            'audit',
            'project_incharge',
            'gm',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create PS Firm Receive approval matrix
        $psFirmReceiveConfig = ApprovalMatrixConfig::create([
            'name' => 'PS Firm Receive Approval',
            'module_name' => 'ps-firm-receive',
            'approval_type' => 'sequential',
            'description' => 'Approval workflow for PS Firm Receive operations',
            'is_active' => true,
        ]);

        // Create layers for PS Firm Receive
        $psFirmReceiveLayers = [
            [
                'layer_order' => 1,
                'layer_name' => 'Security',
                'role_name' => 'security',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 24,
                'description' => 'Security verification and initial approval',
                'is_active' => true,
            ],
            [
                'layer_order' => 2,
                'layer_name' => 'Store Incharge',
                'role_name' => 'store_incharge',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 48,
                'description' => 'Store management approval',
                'is_active' => true,
            ],
            [
                'layer_order' => 3,
                'layer_name' => 'Audit',
                'role_name' => 'audit',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 72,
                'description' => 'Audit verification and compliance check',
                'is_active' => true,
            ],
            [
                'layer_order' => 4,
                'layer_name' => 'Project Incharge',
                'role_name' => 'project_incharge',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 96,
                'description' => 'Project management approval',
                'is_active' => true,
            ],
            [
                'layer_order' => 5,
                'layer_name' => 'GM',
                'role_name' => 'gm',
                'is_required' => true,
                'can_override' => true,
                'timeout_hours' => 120,
                'description' => 'General Manager final approval',
                'is_active' => true,
            ],
        ];

        foreach ($psFirmReceiveLayers as $layerData) {
            $layerData['approval_matrix_config_id'] = $psFirmReceiveConfig->id;
            ApprovalMatrixLayer::create($layerData);
        }

        // Create PS Receive approval matrix
        $psReceiveConfig = ApprovalMatrixConfig::create([
            'name' => 'PS Receive Approval',
            'module_name' => 'ps-receive',
            'approval_type' => 'sequential',
            'description' => 'Approval workflow for PS Receive operations',
            'is_active' => true,
        ]);

        // Create layers for PS Receive (simplified version)
        $psReceiveLayers = [
            [
                'layer_order' => 1,
                'layer_name' => 'Store Incharge',
                'role_name' => 'store_incharge',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 24,
                'description' => 'Store management approval',
                'is_active' => true,
            ],
            [
                'layer_order' => 2,
                'layer_name' => 'Audit',
                'role_name' => 'audit',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 48,
                'description' => 'Audit verification',
                'is_active' => true,
            ],
            [
                'layer_order' => 3,
                'layer_name' => 'GM',
                'role_name' => 'gm',
                'is_required' => true,
                'can_override' => true,
                'timeout_hours' => 72,
                'description' => 'General Manager approval',
                'is_active' => true,
            ],
        ];

        foreach ($psReceiveLayers as $layerData) {
            $layerData['approval_matrix_config_id'] = $psReceiveConfig->id;
            ApprovalMatrixLayer::create($layerData);
        }

        // Create Batch Assignment approval matrix
        $batchAssignConfig = ApprovalMatrixConfig::create([
            'name' => 'Batch Assignment Approval',
            'module_name' => 'batch-assign',
            'approval_type' => 'sequential',
            'description' => 'Approval workflow for Batch Assignment operations',
            'is_active' => true,
        ]);

        // Create layers for Batch Assignment
        $batchAssignLayers = [
            [
                'layer_order' => 1,
                'layer_name' => 'Project Incharge',
                'role_name' => 'project_incharge',
                'is_required' => true,
                'can_override' => false,
                'timeout_hours' => 24,
                'description' => 'Project management approval',
                'is_active' => true,
            ],
            [
                'layer_order' => 2,
                'layer_name' => 'GM',
                'role_name' => 'gm',
                'is_required' => true,
                'can_override' => true,
                'timeout_hours' => 48,
                'description' => 'General Manager approval',
                'is_active' => true,
            ],
        ];

        foreach ($batchAssignLayers as $layerData) {
            $layerData['approval_matrix_config_id'] = $batchAssignConfig->id;
            ApprovalMatrixLayer::create($layerData);
        }

        $this->command->info('Approval matrix configurations and layers created successfully!');
    }
}