<?php

namespace App\Models\Ps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'remarks',
        'created_by',
        'updated_by',
        'status',
        'created_at',
    ];


    protected $casts = [
        'pi_date' => 'date',
        'order_date' => 'date',
        'lc_date' => 'date',
        'created_at'=> 'date',
    ];

    public function attachments()
    {
        return $this->hasMany(PsReceiveAttachment::class);
    }

    public function chickCounts()
    {
        return $this->hasOne(PsChickCount::class);
    }
}
