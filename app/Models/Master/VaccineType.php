<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class VaccineType extends Model
{
    protected $table = 'vaccine_types';

    protected $fillable = [
        'name',
        'status',
    ];
}
