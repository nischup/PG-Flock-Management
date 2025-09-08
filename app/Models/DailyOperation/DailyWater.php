<?php

namespace App\Models\DailyOperation;
use  App\Models\Master\unit;
use Illuminate\Database\Eloquent\Model;

class DailyWater extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'quantity',            // Water quantity
        'unit_id',             // FK to units table (nullable)
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
