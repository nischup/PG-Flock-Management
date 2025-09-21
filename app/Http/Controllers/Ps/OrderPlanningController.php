<?php

namespace App\Http\Controllers\Ps;


use App\Http\Controllers\Controller;

use App\Models\Ps\OrderPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlanningMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class OrderPlanningController extends Controller
{
    // List all order plans
    public function index()
    {
        $plans = OrderPlan::with('orderPlandetails')->latest()->paginate(10);

        return inertia('ps/order-planning/List', [
            'plans' => $plans
        ]);
    }

    // Show create form
    public function create()
    {
        return inertia('ps/order-planning/Create');
    }

    // Store new order plan with details
    
    public function store(Request $request)
    {
        
        
        
        
        
        $data = $request->validate([
        'order_from' => 'required|email',
        'order_to'   => 'required|email',
        'cc'         => 'nullable|string',
        'subject'    => 'nullable|string',
        'message'    => 'nullable|string',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
        'items'      => 'required|array|min:1',
        'items.*.order_volume'  => 'required|string',
        'items.*.shipping_date' => 'required|date',
        'items.*.supply_base'   => 'required|string',
    ]);

    $data['created_by'] = Auth::id();

    if ($request->hasFile('attachment')) {
        $data['attachment'] = $request->file('attachment')->store('order_attachments', 'public');
    }

    $orderPlan = OrderPlan::create($data);

    foreach ($data['items'] as $item) {
        $orderPlan->orderPlandetails()->create($item);
    }

    return redirect()->route('order-plans.index')->with('success', 'Order Plan created.');
    }


    // Show edit form
    public function edit(OrderPlan $orderPlan)
    {
        $orderPlan->load('orderPlandetails'); // load relationship

        return Inertia::render('ps/order-planning/Edit', [
            'plan' => [
                'id' => $orderPlan->id,
                'order_from' => $orderPlan->order_from,
                'order_to' => $orderPlan->order_to,
                'cc' => $orderPlan->cc,
                'subject' => $orderPlan->subject,
                'message' => $orderPlan->message,
                'attachment' => $orderPlan->attachment,
                'details' => $orderPlan->orderPlandetails->map(function($item) {
                    return [
                        'id' => $item->id,
                        'order_volume' => $item->order_volume,
                        'shipping_date' => $item->shipping_date,
                        'supply_base' => $item->supply_base,
                    ];
                })
            ]
        ]);
    }

    // Update existing order plan with details
    public function update(Request $request, OrderPlan $orderPlan)
    {
        $data = $request->validate([
        'order_from' => 'required|email',
        'order_to'   => 'required|email',
        'cc'         => 'nullable|string',
        'subject'    => 'nullable|string',
        'message'    => 'nullable|string',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
        'items'      => 'required|array|min:1',
        'items.*.id' => 'nullable|integer',
        'items.*.order_volume'  => 'required|string',
        'items.*.shipping_date' => 'required|date',
        'items.*.supply_base'   => 'required|string',
    ]);
    
    if ($request->hasFile('attachment')) {
        if ($orderPlan->attachment) {
            Storage::disk('public')->delete($orderPlan->attachment);
        }
        $data['attachment'] = $request->file('attachment')->store('order_attachments', 'public');
    }

    $orderPlan->update($data);

    // Update details
    foreach ($data['items'] as $item) {
        if (isset($item['id'])) {
            $orderPlan->orderPlandetails()->where('id', $item['id'])->update($item);
        } else {
            $orderPlan->orderPlandetails()->create($item);
        }
    }

    return redirect()->route('order-plans.index')->with('success', 'Order Plan updated.');
    }

    // Delete order plan and its details
    public function destroy(OrderPlan $orderPlan)
    {
        $orderPlan->delete();

        return redirect()->route('order-plans.index')->with('success', 'Order planning deleted!');
    }
}
