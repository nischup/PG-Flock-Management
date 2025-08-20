<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['name', 'status'];

    // Always cast status to integer (0/1)
    protected $casts = [
        'status' => 'integer',
    ];

    // Optional accessor for display if you need text in blade
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Deactivated';
    }
}
