<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsChickCount extends Model
{
    protected $table = 'ps_chick_counts';

    protected $fillable = [
        'ps_receive_id',

        'ps_male_rec_box',
        'ps_male_qty',
        'ps_female_rec_box',
        'ps_female_qty',
        'ps_total_qty',
        'ps_total_re_box_qty',

        'ps_challan_box_qty',
        'ps_gross_weight',
        'ps_net_weight',
    ];


    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    // Belongs to one PS Receive
    public function receive()
    {
        return $this->belongsTo(PsReceive::class, 'ps_receive_id');
    }
}
