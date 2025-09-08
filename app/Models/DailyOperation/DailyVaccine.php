<?php

namespace App\Models\DailyOperation;
use  App\Models\Master\unit;
use  App\Models\Master\vaccine;
use  App\Models\vaccineSchedule;

use Illuminate\Database\Eloquent\Model;

class DailyVaccine extends Model
{
    protected $fillable = [
        'daily_operation_id',     // FK to daily_operations
        'vaccine_id',             // FK to vaccines table
        'vaccine_schedule_id',    // Optional FK to vaccine_schedules
        'dose',                   // Vaccine dose
        'unit_id',                // FK to units table (nullable)
        'file_path',              // Uploaded file path
        'note',                   // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function vaccineSchedule()
    {
        return $this->belongsTo(VaccineSchedule::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
