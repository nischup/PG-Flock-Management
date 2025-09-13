<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EggGrade extends Model
{
    protected $fillable = [
        'name',
        'type',
        'min_weight',
        'max_weight',
        'status',
    ];
}
