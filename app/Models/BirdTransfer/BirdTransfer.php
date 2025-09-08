<?php

namespace App\Models\BirdTransfer;


use App\Models\Master\Flock;
use App\Models\Master\Company;
use App\Models\Master\Shed;
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

    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function fromCompany()
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany()
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function fromShed()
    {
        return $this->belongsTo(Shed::class, 'from_shed_id');
    }

    public function toShed()
    {
        return $this->belongsTo(Shed::class, 'to_shed_id');
    }
}
