<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $table = 'diseases';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}
