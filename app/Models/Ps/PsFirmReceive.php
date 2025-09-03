<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsFirmReceive extends Model
{
   protected $fillable = [
        'ps_receive_id',
        'job_no',
        'receipt_type',
        'source_type',
        'source_id',
        'flock_id',
        'flock_name',
        'receiving_company_id',
        'firm_female_qty',
        'firm_male_qty',
        'firm_total_qty',
        'remarks',
        'created_by',
        'status',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];
}
