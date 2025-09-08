<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyHumidity extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'today_humidity',      // Today's humidity value
        'std_humidity',        // Standard humidity value
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
