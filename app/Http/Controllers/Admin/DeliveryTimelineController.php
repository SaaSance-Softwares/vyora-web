<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTimeline;
use Illuminate\Http\Request;

class DeliveryTimelineController extends Controller
{
    public function index()
    {
        $timelines = DeliveryTimeline::orderBy('created_at', 'desc')->get();
        return view('admin.delivery-timelines.index', compact('timelines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'min_days' => 'required|integer|min:0',
            'max_days' => 'required|integer|gte:min_days',
            'internal_note' => 'nullable|string',
            'show_to_user' => 'nullable|string',
        ]);

        if (DeliveryTimeline::count() === 0) {
            $validated['is_default'] = true;
        }

        DeliveryTimeline::create($validated);

        return redirect()->route('admin.online-store.delivery-timelines.index')->with('success', 'Delivery timeline added successfully.');
    }

    public function edit(DeliveryTimeline $deliveryTimeline)
    {
        return view('admin.delivery-timelines.edit', compact('deliveryTimeline'));
    }

    public function update(Request $request, DeliveryTimeline $deliveryTimeline)
    {
        $validated = $request->validate([
            'min_days' => 'required|integer|min:0',
            'max_days' => 'required|integer|gte:min_days',
            'internal_note' => 'nullable|string',
            'show_to_user' => 'nullable|string',
        ]);

        $deliveryTimeline->update($validated);

        return redirect()->route('admin.online-store.delivery-timelines.index')->with('success', 'Delivery timeline updated successfully.');
    }

    public function destroy(DeliveryTimeline $deliveryTimeline)
    {
        if ($deliveryTimeline->is_default && DeliveryTimeline::count() > 1) {
            return redirect()->route('admin.online-store.delivery-timelines.index')->with('error', 'Cannot delete the default timeline unless it is the only one. Set another timeline as default first.');
        }

        $deliveryTimeline->delete();

        // If we deleted the only default timeline and there are others, make the newest one default
        if ($deliveryTimeline->is_default && DeliveryTimeline::count() > 0) {
            $newDefault = DeliveryTimeline::orderBy('created_at', 'desc')->first();
            $newDefault->update(['is_default' => true]);
        }

        return redirect()->route('admin.online-store.delivery-timelines.index')->with('success', 'Delivery timeline deleted successfully.');
    }

    public function setDefault(DeliveryTimeline $deliveryTimeline)
    {
        DeliveryTimeline::where('id', '!=', $deliveryTimeline->id)->update(['is_default' => false]);
        $deliveryTimeline->update(['is_default' => true]);

        return redirect()->route('admin.online-store.delivery-timelines.index')->with('success', 'Default delivery timeline updated.');
    }
}
