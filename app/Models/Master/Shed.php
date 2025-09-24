<?php

namespace App\Models\Master;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shed extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function users()
    {
        return $this->hasMany(User::class, 'shed_id');
    }
}
