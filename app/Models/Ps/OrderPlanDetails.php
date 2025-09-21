<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class OrderPlanDetails extends Model
{
    protected $fillable = [
        'order_plan_id',  // Foreign key
        'order_volume',
        'shipping_date',
        'supply_base',
    ];


    public function orderPlan()
    {
        return $this->belongsTo(OrderPlan::class);
    }
}
