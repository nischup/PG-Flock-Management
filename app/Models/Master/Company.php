<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Company extends Model
{
    protected $fillable = [
        'name',
        'company_type',
        'location',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'contact_person_designation',
        'status'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
