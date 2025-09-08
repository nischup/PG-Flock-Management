<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyFeedingProgram extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'female_program',      // Female feeding program value (nullable)
        'male_program',        // Male feeding program value (nullable)
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
