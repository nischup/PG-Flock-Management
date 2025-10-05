<?php

namespace App\Models\Ps;

use App\Models\Country;
use App\Models\Master\Company;
use App\Models\Master\Supplier;
use Illuminate\Database\Eloquent\Model;

class PsReceive extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = [
        'shipment_type_id',
        'pi_no',
        'pi_date',
        'order_no',
        'order_date',
        'lc_no',
        'lc_date',
        'supplier_id',
        'breed_type',
        'country_of_origin',
        'transport_type',
        'company_id',
        'transport_inside_temp',
        'remarks',
        'created_by',
        'updated_by',
        'status',
        'receiving_status',
        'created_at',
    ];

    protected $casts = [
        'pi_date' => 'date',
        'order_date' => 'date',
        'lc_date' => 'date',
        'created_at' => 'date',
        'breed_type' => 'array',
        'receiving_status' => 'integer',
    ];

    public function attachments()
    {
        return $this->hasMany(PsReceiveAttachment::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function chickCounts()
    {
        return $this->hasOne(PsChickCount::class);
    }

    public function labTransfers()
    {
        return $this->hasMany(PsLabTest::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_of_origin');
    }
}
