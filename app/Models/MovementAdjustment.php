<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementAdjustment extends Model
{
   protected $fillable = [
        'flock_id',    // FK to the flock
        'stage',       // Stage of adjustment (1-5)
        'stage_id',    // ID of the related stage record
        'type',        // Adjustment type (1=Mortality, 2=Excess, 3=Shortage)
        'male_qty',    // Number of male birds affected
        'female_qty',  // Number of female birds affected
        'total_qty',   // Total number of birds affected
        'date',        // Date of the adjustment
        'remarks',     // Optional notes
    ];
}
