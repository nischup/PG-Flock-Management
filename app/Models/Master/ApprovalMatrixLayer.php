<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalMatrixLayer extends Model
{
    protected $fillable = [
        'approval_matrix_config_id',
        'layer_order',
        'layer_name',
        'role_name',
        'is_required',
        'can_override',
        'timeout_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'can_override' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(ApprovalMatrixConfig::class, 'approval_matrix_config_id');
    }

    public function approvalActions(): HasMany
    {
        return $this->hasMany(\App\Models\ApprovalAction::class, 'approval_matrix_layer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('layer_order');
    }

    public function scopeForRole($query, string $roleName)
    {
        return $query->where('role_name', $roleName);
    }
}
