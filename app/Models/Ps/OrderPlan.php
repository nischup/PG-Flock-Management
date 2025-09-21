<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class OrderPlan extends Model
{
    protected $fillable = [
        'order_from',
        'order_to',
        'cc',
        'subject',
        'message',
        'attachment',
        'created_by',
        'approve_by',
        'approve_date',
        'status',
    ];

    public function orderPlandetails()
    {
        return $this->hasMany(OrderPlanDetails::class);
    }
}
