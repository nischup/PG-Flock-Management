<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailySexingError extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'female_qty',          // Female sexing error quantity
        'male_qty',            // Male sexing error quantity
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
