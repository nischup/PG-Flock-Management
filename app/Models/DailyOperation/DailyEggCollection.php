<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyEggCollection extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'quantity',            // Egg quantity
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
