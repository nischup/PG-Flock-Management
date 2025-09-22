<?php


namespace App\Models\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait CompanyShedFilter
{
    public function scopeVisibleFor(Builder $query, ?string $companyColumn = null)
    {
        $user = Auth::user();
        if (!$user) return $query;

        $table = $query->getModel()->getTable();

        $companyColumn = $companyColumn ?? 'company_id';
        $projectColumn = 'project_id';
        $shedColumn = 'shed_id';

        if (Schema::hasColumn($table, $companyColumn) && $user->company_id > 0) {
            $query->where($companyColumn, $user->company_id);
        }

        if (Schema::hasColumn($table, $projectColumn) && $user->project_id > 0) {
            $query->where($projectColumn, $user->project_id);
        }

        if (Schema::hasColumn($table, $shedColumn) && $user->shed_id > 0) {
            $query->where($shedColumn, $user->shed_id);
        }

        return $query;
    }
}
