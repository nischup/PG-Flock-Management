<?php

namespace App\Models\Shed;

use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Shed\ShedReceive;

class BatchAssign extends Model
{
    protected $fillable = [
        'shed_receive_id',
        'job_no',
        'flock_no',
        'flock_id',
        'company_id',
        'shed_id',
        'level',
        'batch_no',
        'batch_female_qty',
        'batch_male_qty',
        'batch_total_qty',
        'batch_female_mortality',
        'batch_male_mortality',
        'batch_total_mortality',
        'batch_excess_male',
        'batch_excess_female',
        'batch_sortage_male',
        'batch_sortage_female',
        'percentage',
        'created_by',
        'updated_by',
    ];

    // Relationship to shed receive
    public function shedReceive()
    {
        return $this->belongsTo(ShedReceive::class);
    }

    // Optional: relationships to flock, company, shed
    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shed()
    {
        return $this->belongsTo(Shed::class);
    }
}
