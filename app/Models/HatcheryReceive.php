<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HatcheryReceive extends Model
{
    use HasFactory;

    // Use prefixed table
    protected $table = 'htc_hatchery_receives';

    protected $fillable = [
        'receive_date',
        'hatchery_id',
        'store_receive_id',
        'egg_classification_grade_id',
        'flock_id',
        'company_id',
        'project_id',
        'shade_id',
        'batch_no',
        'job_no',
        'transaction_no',
        'quantity',
        'status',
        'receive_status',
    ];

    public function hatchery()
    {
        return $this->belongsTo(Hatchery::class);
    }

    public function storeReceive()
    {
        return $this->belongsTo(StoreReceive::class, 'store_receive_id');
    }

    // public function grade()
    // {
    //     return $this->belongsTo(EggClassificationGrade::class, 'egg_classification_grade_id');
    // }
}
