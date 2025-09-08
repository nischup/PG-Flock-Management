<?php

namespace App\Models\DailyOperation;

use Illuminate\Database\Eloquent\Model;

class DailyDestroy extends Model
{
    protected $fillable = [
        'daily_operation_id',       // FK to daily_operations
        'female_qty',               // Destroyed female quantity
        'male_qty',                 // Destroyed male quantity
        'female_destroy_reason',    // Optional reason for female destruction
        'male_destroy_reason',      // Optional reason for male destruction
        'note',                     // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }
}
