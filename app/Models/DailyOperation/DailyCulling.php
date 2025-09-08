<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyCulling extends Model
{
    protected $fillable = [
        'daily_operation_id',       // FK to daily_operations
        'female_qty',               // Cull female quantity
        'male_qty',                 // Cull male quantity
        'female_culling_reason',    // Optional reason for female culling
        'male_culling_reason',      // Optional reason for male culling
        'note',                     // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
