<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    // ── Profile ─────────────────────────────────────────────────────────────

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->strictValidate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$user->id,
        ]);

        $user->update($validated);

        return response()->json(['success' => true, 'user' => $user]);
    }

    // ── Password ─────────────────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $request->strictValidate([
            'current_password' => 'required|string|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required|string|max:255',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        try {
            app(WhatsAppService::class)->sendEventWhatsApp('password_updated', $user);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp password updated: '.$e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordUpdatedEmail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send Password Updated Email: '.$e->getMessage());
        }

        return response()->json(['message' => 'Password updated successfully.']);
    }

    // ── Addresses ────────────────────────────────────────────────────────────

    public function listAddresses(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($addresses);
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->strictValidate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'nullable|string|max:100',
        ]);

        $userId = $request->user()->id;

        // First address becomes default automatically
        $isFirst = ! Address::where('user_id', $userId)->exists();

        $address = Address::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address_line1' => $validated['line1'],
            'address_line2' => $validated['line2'] ?? null,
            'city' => $validated['city'],
            'district' => $validated['district'] ?? null,
            'state' => $validated['state'],
            'zip_code' => $validated['pincode'],
            'country' => $validated['country'] ?? 'IN',
            'is_default' => $isFirst,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($request->user()->email)->send(new \App\Mail\AddressUpdatedEmail($request->user(), 'added'));
        } catch (\Exception $e) {
            \Log::error('Failed to send Address Updated Email (added): '.$e->getMessage());
        }

        return response()->json($address, 201);
    }

    public function updateAddress(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->strictValidate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $address->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address_line1' => $validated['line1'],
            'address_line2' => $validated['line2'] ?? null,
            'city' => $validated['city'],
            'district' => $validated['district'] ?? null,
            'state' => $validated['state'],
            'zip_code' => $validated['pincode'],
            'country' => $validated['country'] ?? 'IN',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($request->user()->email)->send(new \App\Mail\AddressUpdatedEmail($request->user(), 'updated'));
        } catch (\Exception $e) {
            \Log::error('Failed to send Address Updated Email (updated): '.$e->getMessage());
        }

        return response()->json($address);
    }

    public function deleteAddress(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Promote next address as default if the deleted one was default
        if ($wasDefault) {
            $next = Address::where('user_id', $request->user()->id)->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        try {
            \Illuminate\Support\Facades\Mail::to($request->user()->email)->send(new \App\Mail\AddressUpdatedEmail($request->user(), 'deleted'));
        } catch (\Exception $e) {
            \Log::error('Failed to send Address Updated Email (deleted): '.$e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function setDefaultAddress(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Unset all defaults for this user, then set the chosen one
        Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }
}
