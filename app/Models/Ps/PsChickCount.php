<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsChickCount extends Model
{
    protected $table = 'ps_chick_counts';

    protected $fillable = [
        'ps_receive_id',

        'ps_male_box',
        'ps_male_approximate_qty',
        'ps_male_totalqty',
        'ps_male_challan_qty',
        'ps_male_rate',
        'ps_male_value_total',

        'ps_female_box',
        'ps_female_approximate_qty',
        'ps_female_totalqty',
        'ps_challan_qty',
        'ps_female_rate',
        'ps_female_value_total',

        'ps_totalbox',
        'ps_value_total',
    ];

    // Belongs to one PS Receive
    public function receive()
    {
        return $this->belongsTo(PsReceive::class, 'ps_receive_id');
    }
}
