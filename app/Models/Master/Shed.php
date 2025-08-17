<?php

namespace App\Models\Master;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Shed extends Model
{
   protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class, 'shed_id');
    }
}
