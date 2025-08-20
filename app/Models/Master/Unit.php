<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'status'];

    // Accessor for status text
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? 'Active' : 'Deactivated';
    }
}
