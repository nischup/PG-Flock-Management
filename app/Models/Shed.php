<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shed extends Model
{
    use HasFactory;

    protected static $factory = \Database\Factories\ShedFactory::class;

    protected $fillable = [
        'name',
        'status',
    ];
}
