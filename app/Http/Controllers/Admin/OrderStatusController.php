<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\SmsTemplate;
use App\Models\EmailTemplate;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index()
    {
        $isQikinkEnabled = \App\Models\ThemeSetting::where('group', 'integration.qikink')->where('key', 'enabled')->value('value') === '1';
        
        $query = OrderStatus::orderBy('sort_order');
        if (!$isQikinkEnabled) {
            $query->where(function($q) {
                $q->whereNull('fulfillment_type')
                  ->orWhere('fulfillment_type', '!=', 'QikInk')
                  ->orWhere('is_system', true);
            });
        }
        $statuses = $query->get();

        return view('admin.order-statuses.index', compact('statuses'));
    }

    public function create()
    {
        $smsTemplates = SmsTemplate::where('status', true)->get();
        $emailTemplates = EmailTemplate::where('status', true)->get();
        $whatsappTemplates = WhatsappTemplate::where('status', 'APPROVED')->get();

        return view('admin.order-statuses.create', compact('smsTemplates', 'emailTemplates', 'whatsappTemplates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'integer',
            'sms_template_id' => 'nullable|exists:sms_templates,id',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'whatsapp_template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        OrderStatus::create($request->all());

        return redirect()->route('admin.order-statuses.index')->with('success', 'Order Status created successfully.');
    }

    public function edit(OrderStatus $orderStatus)
    {
        $smsTemplates = SmsTemplate::where('status', true)->get();
        $emailTemplates = EmailTemplate::where('status', true)->get();
        $whatsappTemplates = WhatsappTemplate::where('status', 'APPROVED')->get();

        return view('admin.order-statuses.edit', compact('orderStatus', 'smsTemplates', 'emailTemplates', 'whatsappTemplates'));
    }

    public function update(Request $request, OrderStatus $orderStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name,' . $orderStatus->id,
            'color' => 'nullable|string|max:50',
            'sort_order' => 'integer',
            'sms_template_id' => 'nullable|exists:sms_templates,id',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'whatsapp_template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        $orderStatus->update($request->all());

        return redirect()->route('admin.order-statuses.index')->with('success', 'Order Status updated successfully.');
    }

    public function destroy(OrderStatus $orderStatus)
    {
        if ($orderStatus->is_system) {
            return back()->with('error', 'Cannot delete a system status.');
        }

        // Need to check if any order is using this status
        if (\App\Models\Order::where('order_status_id', $orderStatus->id)->exists()) {
            return back()->with('error', 'Cannot delete status because it is assigned to one or more orders.');
        }

        $orderStatus->delete();
        return redirect()->route('admin.order-statuses.index')->with('success', 'Order Status deleted successfully.');
    }
}
