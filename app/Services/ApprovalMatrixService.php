<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\Master\ApprovalMatrixConfig;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalMatrixService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}
    /**
     * Initiate an approval request for a record
     */
    public function initiateApproval(string $module, int $recordId, array $data, User $initiator): ?ApprovalRequest
    {
        try {
            // Find active configuration for the module
            $config = ApprovalMatrixConfig::active()
                ->forModule($module)
                ->first();

            if (! $config) {
                Log::warning("No active approval matrix configuration found for module: {$module}");

                return null;
            }

            // Check if approval request already exists
            $existingRequest = ApprovalRequest::where('module_name', $module)
                ->where('record_id', $recordId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                Log::info("Approval request already exists for {$module}:{$recordId}");

                return $existingRequest;
            }

            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'module_name' => $module,
                'record_id' => $recordId,
                'approval_matrix_config_id' => $config->id,
                'status' => 'pending',
                'approval_data' => $data,
                'initiated_by' => $initiator->id,
            ]);

            // Send notifications for first layer
            $this->sendLayerNotifications($approvalRequest);

            Log::info("Approval request initiated for {$module}:{$recordId} by user {$initiator->id}");

            return $approvalRequest;
        } catch (\Exception $e) {
            Log::error("Failed to initiate approval for {$module}:{$recordId}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Approve a record
     */
    public function approve(string $module, int $recordId, int $userId, ?string $comments = null): array
    {
        try {
            $request = ApprovalRequest::where('module_name', $module)
                ->where('record_id', $recordId)
                ->first();

            if (!$request) {
                return [
                    'success' => false,
                    'message' => 'Approval request not found',
                ];
            }

            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            $success = $this->processApproval($request, $user, 'approve', $comments);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Record approved successfully',
                    'status' => $request->fresh()->status,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to approve record',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Failed to approve {$module}:{$recordId}: ".$e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while approving the record',
            ];
        }
    }

    /**
     * Reject a record
     */
    public function reject(string $module, int $recordId, int $userId, ?string $comments = null): array
    {
        try {
            $request = ApprovalRequest::where('module_name', $module)
                ->where('record_id', $recordId)
                ->first();

            if (!$request) {
                return [
                    'success' => false,
                    'message' => 'Approval request not found',
                ];
            }

            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            $success = $this->processApproval($request, $user, 'reject', $comments);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Record rejected successfully',
                    'status' => $request->fresh()->status,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to reject record',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Failed to reject {$module}:{$recordId}: ".$e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while rejecting the record',
            ];
        }
    }

    /**
     * Process an approval action
     */
    public function processApproval(ApprovalRequest $request, User $approver, string $action, ?string $comments = null): bool
    {
        try {
            DB::beginTransaction();

            // Get current layer
            $currentLayer = $request->getCurrentLayer();
            if (! $currentLayer) {
                Log::warning("No current layer found for approval request {$request->id}");
                DB::rollBack();

                return false;
            }

            // Check if user can approve at current layer
            if (! $request->canUserApprove($approver)) {
                Log::warning("User {$approver->id} cannot approve at current layer for request {$request->id}");
                DB::rollBack();

                return false;
            }

            // Record approval action
            ApprovalAction::create([
                'approval_request_id' => $request->id,
                'approval_matrix_layer_id' => $currentLayer->id,
                'user_id' => $approver->id,
                'action' => $action,
                'comments' => $comments,
                'action_at' => now(),
            ]);

            // Update request status based on action
            if ($action === 'reject') {
                $request->update([
                    'status' => 'rejected',
                    'completed_at' => now(),
                ]);
            } else {
                // Check if all required layers are complete
                if ($this->isApprovalComplete($request)) {
                    $request->update([
                        'status' => 'approved',
                        'completed_at' => now(),
                    ]);
                } else {
                    // Update status to in_progress if not all layers are complete
                    $request->update([
                        'status' => 'in_progress',
                    ]);
                }
            }

            // Send notifications for next layer or completion
            $this->sendLayerNotifications($request);

            DB::commit();
            Log::info("Approval action processed for request {$request->id} by user {$approver->id}");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process approval for request {$request->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Get approval status for a record
     */
    public function getApprovalStatus(string $module, int $recordId): ?array
    {
        try {
            $request = ApprovalRequest::where('module_name', $module)
                ->where('record_id', $recordId)
                ->with(['config.layers', 'actions.user', 'initiator'])
                ->first();

            if (! $request) {
                return null;
            }

            $layers = $request->config->layers->map(function ($layer) use ($request) {
                $action = $request->actions->where('approval_matrix_layer_id', $layer->id)->first();

                return [
                    'id' => $layer->id,
                    'layer_order' => $layer->layer_order,
                    'layer_name' => $layer->layer_name,
                    'role_name' => $layer->role_name,
                    'is_required' => $layer->is_required,
                    'can_override' => $layer->can_override,
                    'timeout_hours' => $layer->timeout_hours,
                    'status' => $action ? ($action->action === 'approve' ? 'completed' : 'rejected') : 'pending',
                    'approver_name' => $action ? $action->user->name : null,
                    'action_at' => $action ? $action->action_at : null,
                    'comments' => $action ? $action->comments : null,
                ];
            });

            $currentLayer = $request->getCurrentLayer();
            $currentUser = auth()->user();
            
            return [
                'id' => $request->id,
                'module_name' => $request->module_name,
                'record_id' => $request->record_id,
                'status' => $request->status,
                'initiated_by' => $request->initiator->name,
                'initiated_at' => $request->created_at,
                'completed_at' => $request->completed_at,
                'layers' => $layers,
                'actions' => $layers->toArray(),
                'current_layer' => $currentLayer ? $currentLayer->layer_order : 0,
                'total_layers' => $layers->count(),
                'can_approve' => $currentUser && $request->canUserApprove($currentUser),
                'can_reject' => $currentUser && $request->canUserApprove($currentUser),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get approval status for {$module}:{$recordId}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Check if user can approve at current stage
     */
    public function canUserApprove(string $module, int $recordId, User $user): bool
    {
        try {
            $request = ApprovalRequest::where('module_name', $module)
                ->where('record_id', $recordId)
                ->where('status', 'pending')
                ->first();

            if (! $request) {
                return false;
            }

            return $request->canUserApprove($user);
        } catch (\Exception $e) {
            Log::error("Failed to check user approval permission for {$module}:{$recordId}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(User $user): array
    {
        try {
            $userRoles = $user->roles->pluck('name')->toArray();

            $requests = ApprovalRequest::where('status', 'pending')
                ->with(['config.layers', 'initiator'])
                ->get()
                ->filter(function ($request) use ($userRoles) {
                    $currentLayer = $request->getCurrentLayer();

                    return $currentLayer && in_array($currentLayer->role_name, $userRoles);
                });

            return $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'module_name' => $request->module_name,
                    'record_id' => $request->record_id,
                    'initiated_by' => $request->initiator->name,
                    'initiated_at' => $request->created_at,
                    'current_layer' => $request->getCurrentLayer(),
                    'approval_data' => $request->approval_data,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get pending approvals for user {$user->id}: ".$e->getMessage());

            return [];
        }
    }

    /**
     * Check if approval is complete
     */
    private function isApprovalComplete(ApprovalRequest $request): bool
    {
        $requiredLayers = $request->config->activeLayers()
            ->where('is_required', true)
            ->get();

        $completedLayers = $request->actions()
            ->where('action', 'approve')
            ->pluck('approval_matrix_layer_id')
            ->toArray();

        foreach ($requiredLayers as $layer) {
            if (! in_array($layer->id, $completedLayers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send notifications for current layer
     */
    private function sendLayerNotifications(ApprovalRequest $request): void
    {
        try {
            $currentLayer = $request->getCurrentLayer();

            if (! $currentLayer) {
                return;
            }

            // Get users with the required role
            $users = User::role($currentLayer->role_name)->get();

            foreach ($users as $user) {
                $this->notificationService->sendApprovalNotification(
                    user: $user,
                    action: 'pending',
                    module: $request->module_name,
                    recordId: $request->record_id,
                    additionalData: [
                        'approval_request_id' => $request->id,
                        'layer_name' => $currentLayer->name,
                        'initiated_by' => $request->initiated_by,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to send layer notifications for request {$request->id}: ".$e->getMessage());
        }
    }

    /**
     * Cancel an approval request
     */
    public function cancelApproval(ApprovalRequest $request, User $user): bool
    {
        try {
            // Only the initiator or admin can cancel
            if ($request->initiated_by !== $user->id && ! $user->hasRole('admin')) {
                return false;
            }

            $request->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            Log::info("Approval request {$request->id} cancelled by user {$user->id}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to cancel approval request {$request->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Get approval statistics
     */
    public function getApprovalStatistics(?string $module = null): array
    {
        try {
            $query = ApprovalRequest::query();

            if ($module) {
                $query->where('module_name', $module);
            }

            $total = $query->count();
            $pending = $query->where('status', 'pending')->count();
            $approved = $query->where('status', 'approved')->count();
            $rejected = $query->where('status', 'rejected')->count();

            return [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get approval statistics: '.$e->getMessage());

            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'approval_rate' => 0,
            ];
        }
    }
}
