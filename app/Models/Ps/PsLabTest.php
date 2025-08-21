<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsLabTest extends Model
{
    protected $fillable = [
        'ps_receive_id',
        'lab_type',
        'lab_send_female_qty',
        'lab_send_male_qty',
        'lab_send_total_qty',
        'lab_receive_female_qty',
        'lab_receive_male_qty',
        'lab_receive_total_qty',
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
