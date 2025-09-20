<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirmLabTest extends Model
{
    protected $fillable = [
        'batch_assign_id',
        'lab_type',
        'firm_lab_send_female_qty',
        'firm_lab_send_male_qty',
        'firm_lab_send_total_qty',
        'firm_lab_receive_male_qty',
        'firm_lab_receive_female_qty',
        'firm_lab_receive_total_qty',
        'note',
        'remarks',
        'status',
    ];

    public function batchAssign()
    {
        return $this->belongsTo(\App\Models\Shed\BatchAssign::class);
    }
}
