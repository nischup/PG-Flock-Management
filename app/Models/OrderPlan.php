<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPlan extends Model
{
    protected $fillable = [
        'order_from', 'order_to', 'cc'
    ];

    public function orderPlandetails()
    {
        return $this->hasMany(OrderPlanDetails::class);
    }
}
