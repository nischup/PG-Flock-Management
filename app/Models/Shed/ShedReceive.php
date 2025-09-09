<?php

namespace App\Models\Shed;

use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;

use Illuminate\Database\Eloquent\Model;

class ShedReceive extends Model
{
    protected $fillable = [
        'receive_id',
        'job_no',
        'transaction_no',
        'flock_id',
        'flock_name',
        'shed_id',
        'shed_female_qty',
        'company_id',
        'shed_male_qty',
        'shed_total_qty',
        'receive_type',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];


    public function flock() {
    return $this->belongsTo(Flock::class);
        }
        public function shed() {
            return $this->belongsTo(Shed::class);
        }
        public function company() {
            return $this->belongsTo(Company::class);
        }
}
