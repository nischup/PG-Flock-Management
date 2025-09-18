<?php

namespace App\Models\Shed;
use App\Models\Shed\BatchAssign;
use Illuminate\Database\Eloquent\Model;

class BatchConfiguration extends Model
{
   protected $fillable = [
        'batch_assign_id',
        'area_sqft',
        'num_workers',
        'density_per_sqft',
        'feeders',
        'drinkers',
        'temperature_target',
        'humidity_target',
        'note',
        'effective_from',
        'effective_to',
    ];

    public function batchAssign()
    {
        return $this->belongsTo(BatchAssign::class);
        
    }
}
