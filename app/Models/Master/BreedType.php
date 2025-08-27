<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BreedType extends Model
{
    protected $table = 'breed_types';

    protected $fillable = [
        'name',
        'status',
    ];
}
