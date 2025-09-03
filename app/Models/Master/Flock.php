<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Flock extends Model
{
    protected $fillable = [
        'code',
        'name',
        'parent_flock_id',
        'status',
    ];

    protected $casts = [
        'created_at'=> 'date',
    ];
}
