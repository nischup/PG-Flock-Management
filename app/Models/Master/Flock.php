<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flock extends Model
{
    use HasFactory;

    protected static $factory = \Database\Factories\FlockFactory::class;

    protected $fillable = [
        'code',
        'name',
        'parent_flock_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];
    public function psReceive()
    {
        return $this->hasOne(\App\Models\Ps\PsReceive::class, 'flock_id', 'id');
    }
}
