<?php

namespace App\Models\Ps;
use App\Models\Master\Flock;
use App\Models\Master\Company;
use Illuminate\Database\Eloquent\Model;

class PsFirmReceive extends Model
{
   protected $fillable = [
        'ps_receive_id',
        'job_no',
        'transaction_no',
        'receive_type',
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

    
    // Flock relationship
    public function flock()
    {
        return $this->belongsTo(Flock::class, 'flock_id');
    }

    // Company relationship
    public function company()
    {
        return $this->belongsTo(Company::class, 'receiving_company_id');
    }
}
