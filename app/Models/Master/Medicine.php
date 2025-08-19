<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['name', 'status'];

    // Human-readable status accessor
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Deactivated';
    }
}
