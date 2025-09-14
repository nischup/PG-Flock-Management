<?php

namespace App\Models\Master;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected static $factory = \Database\Factories\CompanyFactory::class;

    protected $fillable = [
        'name',
        'short_name',
        'company_type',
        'location',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'contact_person_designation',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
