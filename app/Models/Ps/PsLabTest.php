<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsLabTest extends Model
{
    protected $fillable = [
        'ps_receive_id',
        'lab_type',
        'female_qty',
        'male_qty',
        'total_qty',
        'notes',
        'status',
    ];
    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    
    public function psReceive()
    {
        return $this->belongsTo(PsReceive::class, 'ps_receive_id', 'id');
    }
}
