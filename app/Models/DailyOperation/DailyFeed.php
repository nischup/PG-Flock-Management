<?php

namespace App\Models\DailyOperation;
use  App\Models\Master\Feed;
use  App\Models\Master\Unit;

use Illuminate\Database\Eloquent\Model;

class DailyFeed extends Model
{
    protected $fillable = [
        'daily_operation_id',  // FK to daily_operations
        'feed_type_id',        // FK to feeds table
        'quantity',            // Feed quantity
        'unit_id',             // FK to units table (nullable)
        'note',                // Additional note
    ];

    // Relationships
    public function dailyOperation()
    {
        return $this->belongsTo(DailyOperation::class);
    }

    public function feedType()
    {
        return $this->belongsTo(Feed::class, 'feed_type_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
