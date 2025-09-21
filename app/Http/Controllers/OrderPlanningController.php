<?php

namespace App\Http\Controllers;

use App\Models\OrderPlan;
use App\Models\OrderPlanDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlanningMail;

class OrderPlanController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_from' => 'required|email',
            'order_to'   => 'required|email',
            'cc'         => 'nullable|string',
            'items'      => 'required|array|min:1',
            'items.*.order_volume'   => 'required|string',
            'items.*.shipping_date'  => 'required|date',
            'items.*.supply_base'    => 'required|string',
        ]);

        // Save order plan
        $plan = OrderPlan::create([
            'order_from' => $data['order_from'],
            'order_to'   => $data['order_to'],
            'cc'         => $data['cc'],
        ]);

        // Save items
        foreach ($data['items'] as $item) {
            $plan->items()->create($item);
        }

        // Send email
        $to = $data['order_to'];
        $cc = array_filter(array_map('trim', explode(',', $data['cc'] ?? '')));
        // Mail::to($to)->cc($cc)->send(new OrderPlanningMail($plan));

        return back()->with('success', 'Order planning saved and email sent!');
    }

    public function index()
    {
        $plans = OrderPlan::with('items')->latest()->get();
        return inertia('OrderPlans/List', ['plans' => $plans]);
    }
}
