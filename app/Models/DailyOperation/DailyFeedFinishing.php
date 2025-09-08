<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyFeedFinishing extends Model
{
    protected $fillable = [
        'daily_operation_id',       // FK to daily_operations
        'female_finishing_time',    // Female finishing time (nullable)
        'male_finishing_time',      // Male finishing time (nullable)
        'note',                     // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
