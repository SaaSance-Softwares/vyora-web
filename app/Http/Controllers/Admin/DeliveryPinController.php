<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPincode;
use Illuminate\Http\Request;

class DeliveryPinController extends Controller
{
    public function index()
    {
        $allowedPins = DeliveryPincode::where('type', 'allowed')->pluck('pincode')->implode(', ');
        $excludedPins = DeliveryPincode::where('type', 'excluded')->pluck('pincode')->implode(', ');

        return view('admin.delivery-pins.index', compact('allowedPins', 'excludedPins'));
    }

    public function update(Request $request)
    {
        // Wipe existing to rebuild (Simple approach for settings)
        DeliveryPincode::truncate();

        $this->savePins($request->input('allowed_pins', ''), 'allowed');
        $this->savePins($request->input('excluded_pins', ''), 'excluded');

        return redirect()->back()->with('success', 'Delivery PIN database updated successfully.');
    }

    private function savePins($input, $type)
    {
        if (empty($input)) return;

        // Split by comma, space or newline and filter empties
        $pins = collect(preg_split('/[\s,]+/', $input))
            ->map(fn($p) => trim($p))
            ->filter()
            ->unique();

        foreach ($pins as $pin) {
            DeliveryPincode::create([
                'pincode' => $pin,
                'type' => $type
            ]);
        }
    }
}
