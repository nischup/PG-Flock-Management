<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyWeight extends Model
{
     protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'female_weight',       // Female weight (nullable)
        'male_weight',         // Male weight (nullable)
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
