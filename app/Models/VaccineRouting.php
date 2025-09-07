<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineRouting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
