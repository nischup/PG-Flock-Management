<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyMortality extends Model
{
    protected $fillable = [
        'daily_operation_id',       // FK to daily_operations
        'female_qty',               // Number of female chicks
        'male_qty',                 // Number of male chicks
        'female_mortality_reason',  // Optional reason for female mortality
        'male_mortality_reason',    // Optional reason for male mortality
        'note',                     // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
