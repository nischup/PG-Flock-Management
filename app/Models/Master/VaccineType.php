<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineType extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel's plural convention)
    protected $table = 'vaccine_types';

    // Mass assignable fields
    protected $fillable = [
        'name',
        'status',
    ];

    // Casts
    protected $casts = [
        'status' => 'integer', // ensures 0/1 as integer
    ];

    // Optional: Accessor for status text
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Deactivated';
    }
}
