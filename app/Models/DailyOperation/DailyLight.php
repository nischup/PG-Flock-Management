<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyLight extends Model
{
   protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'hour',                // Light hours
        'minute',              // Light minutes
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
