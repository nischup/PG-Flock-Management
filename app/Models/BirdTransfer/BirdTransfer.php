<?php

namespace App\Models\BirdTransfer;

use Illuminate\Database\Eloquent\Model;

class BirdTransfer extends Model
{
    protected $fillable = [
        'batch_assign_id',
        'job_no',
        'flock_no',
        'flock_id',
        'from_company_id',
        'to_company_id',
        'from_shed_id',
        'to_shed_id',
        'transfer_date',

        'transfer_female_qty',
        'transfer_male_qty',
        'transfer_total_qty',

        'medical_female_qty',
        'medical_male_qty',
        'medical_total_qty',

        'deviation_female_qty',
        'deviation_male_qty',
        'deviation_total_qty',

        'created_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];
}
