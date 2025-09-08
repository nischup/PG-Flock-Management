<?php

namespace App\Models\DailyOperation;
use  App\Models\Master\unit;
use  App\Models\Master\Medicine;

use Illuminate\Database\Eloquent\Model;

class DailyMedicine extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'medicine_id',         // FK to medicines table
        'quantity',            // Medicine quantity
        'unit_id',             // FK to units table (nullable)
        'dose',                // Dose (e.g., "2 ml / day")
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
