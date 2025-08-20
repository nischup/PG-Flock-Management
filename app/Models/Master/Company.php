<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Company extends Model
{
    protected $fillable = ['name', 'status', 'location']; // ✅ added location

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
