<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait CompanyShedFilter
{
    public function scopeVisibleFor(Builder $query)
    {
        $user = Auth::user();
        if (!$user) return $query;
        return $query
            ->when($user->company_id > 0, fn($q) => 
                $q->where('company_id', $user->company_id)
                  ->when($user->shed_id > 0, fn($q2) => 
                      $q2->where('shed_id', $user->shed_id)
                  )
            );
    }
}
