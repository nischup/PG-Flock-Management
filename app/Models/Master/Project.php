<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Company;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',

        'location',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'contact_person_designation',
        'status',
    ];

    // Project belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
