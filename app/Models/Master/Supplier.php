<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'supplier_type',
        'address',
        'origin',
        'contact_person',
        'contact_person_email',
        'contact_person_mobile',
        'status',
    ];
}
