<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyTemperature extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'inside_temp',         // Actual inside temperature
        'std_inside_temp',     // Standard inside temperature
        'outside_temp',        // Actual outside temperature
        'std_outside_temp',    // Standard outside temperature
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
