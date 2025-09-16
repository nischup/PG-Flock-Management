<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreedType extends Model
{
    use HasFactory;

    protected $table = 'breed_types';

    protected $fillable = [
        'name',
        'status',
    ];
}
