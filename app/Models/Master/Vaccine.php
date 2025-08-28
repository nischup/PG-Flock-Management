<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaccine_type_id',
        'name',
        'description',
        'applicator',
        'dose',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function vaccineType()
    {
        return $this->belongsTo(VaccineType::class, 'vaccine_type_id');
    }
}
