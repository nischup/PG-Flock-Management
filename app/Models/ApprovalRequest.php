<?php

namespace App\Models;

use App\Models\Master\ApprovalMatrixConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'module_name',
        'record_id',
        'approval_matrix_config_id',
        'status',
        'approval_data',
        'initiated_by',
        'completed_at',
    ];

    protected $casts = [
        'approval_data' => 'array',
        'completed_at' => 'datetime',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(ApprovalMatrixConfig::class, 'approval_matrix_config_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    public function getCurrentLayer()
    {
        $completedLayers = $this->actions()
            ->where('action', 'approve')
            ->pluck('approval_matrix_layer_id')
            ->toArray();

        return $this->config->activeLayers()
            ->whereNotIn('id', $completedLayers)
            ->orderBy('layer_order')
            ->first();
    }

    public function canUserApprove(User $user): bool
    {
        $currentLayer = $this->getCurrentLayer();

        if (! $currentLayer) {
            return false;
        }

        return $user->hasRole($currentLayer->role_name);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['approved', 'rejected', 'expired']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForModule($query, string $moduleName)
    {
        return $query->where('module_name', $moduleName);
    }
}
