<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalMatrixConfig extends Model
{
    protected $fillable = [
        'name',
        'module_name',
        'approval_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function layers(): HasMany
    {
        return $this->hasMany(ApprovalMatrixLayer::class, 'approval_matrix_config_id')
            ->orderBy('layer_order');
    }

    public function activeLayers(): HasMany
    {
        return $this->layers()->where('is_active', true);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(\App\Models\ApprovalRequest::class, 'approval_matrix_config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $moduleName)
    {
        return $query->where('module_name', $moduleName);
    }
}
