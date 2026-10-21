<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'customer_name',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class, 'conversation_id');
    }

    /**
     * Check whether a string is a real customer name (not empty, not just digits/phone, not a generic placeholder).
     */
    public static function isValidCustomerName(?string $name): bool
    {
        if (! $name) {
            return false;
        }

        $trimmed = trim($name);
        if ($trimmed === '') {
            return false;
        }

        // Reject if it only contains digits, spaces, or phone symbols
        if (preg_match('/^[0-9+\-\s().]+$/', $trimmed)) {
            return false;
        }

        // Reject generic placeholders
        $placeholders = [
            'pos customer',
            'walk-in customer',
            'walk-in',
            'walk in',
            'guest',
            'customer',
            'unknown',
        ];

        if (in_array(strtolower($trimmed), $placeholders, true)) {
            return false;
        }

        return true;
    }

    /**
     * Look up a single phone number across User, Order, and Address records.
     */
    public static function resolveCustomerNameByPhone(?string $rawPhone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $rawPhone);
        if (strlen($digits) < 6) {
            return null;
        }

        $last10 = strlen($digits) >= 10 ? substr($digits, -10) : $digits;

        // 1. Check Users
        $users = User::whereNotNull('phone')
            ->whereNotNull('name')
            ->where(function ($q) use ($digits, $last10) {
                $q->where('phone', $digits)
                    ->orWhere('phone', 'like', '%' . $last10);
            })
            ->orderBy('id', 'desc')
            ->get(['name', 'phone']);

        foreach ($users as $user) {
            if (self::isValidCustomerName($user->name)) {
                return trim($user->name);
            }
        }

        // 2. Check Orders (POS / Guest customer_name)
        $orders = Order::whereNotNull('customer_phone')
            ->whereNotNull('customer_name')
            ->where(function ($q) use ($digits, $last10) {
                $q->where('customer_phone', $digits)
                    ->orWhere('customer_phone', 'like', '%' . $last10);
            })
            ->orderBy('id', 'desc')
            ->get(['customer_name', 'customer_phone']);

        foreach ($orders as $order) {
            if (self::isValidCustomerName($order->customer_name)) {
                return trim($order->customer_name);
            }
        }

        // 3. Check Addresses
        $addresses = Address::whereNotNull('phone')
            ->whereNotNull('name')
            ->where(function ($q) use ($digits, $last10) {
                $q->where('phone', $digits)
                    ->orWhere('phone', 'like', '%' . $last10);
            })
            ->orderBy('id', 'desc')
            ->get(['name', 'phone']);

        foreach ($addresses as $address) {
            if (self::isValidCustomerName($address->name)) {
                return trim($address->name);
            }
        }

        return null;
    }

    /**
     * Enrich a collection of WhatsappConversation models with customer names associated with their phone numbers.
     */
    public static function enrichConversations($conversations)
    {
        if ($conversations->isEmpty()) {
            return $conversations;
        }

        // Build lookup map of phone (full digits & last 10 digits) => customer name
        $phoneMap = [];

        $registerPhoneName = function (?string $rawPhone, ?string $rawName) use (&$phoneMap) {
            if (! $rawPhone || ! self::isValidCustomerName($rawName)) {
                return;
            }
            $digits = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($digits) < 6) {
                return;
            }
            $cleanName = trim($rawName);
            $phoneMap[$digits] = $cleanName;
            if (strlen($digits) >= 10) {
                $phoneMap[substr($digits, -10)] = $cleanName;
            }
        };

        // Load in increasing priority order so higher priority overwrites lower priority:
        // Priority 3 (lowest): Addresses
        Address::whereNotNull('phone')
            ->whereNotNull('name')
            ->orderBy('id', 'asc')
            ->get(['name', 'phone'])
            ->each(fn ($a) => $registerPhoneName($a->phone, $a->name));

        // Priority 2: Orders (customer_phone / customer_name)
        Order::whereNotNull('customer_phone')
            ->whereNotNull('customer_name')
            ->orderBy('id', 'asc')
            ->get(['customer_name', 'customer_phone'])
            ->each(fn ($o) => $registerPhoneName($o->customer_phone, $o->customer_name));

        // Priority 1 (highest): Registered Users
        User::whereNotNull('phone')
            ->whereNotNull('name')
            ->orderBy('id', 'asc')
            ->get(['name', 'phone'])
            ->each(fn ($u) => $registerPhoneName($u->phone, $u->name));

        foreach ($conversations as $conv) {
            $phoneDigits = preg_replace('/[^0-9]/', '', (string) $conv->phone_number);
            $nameDigits = preg_replace('/[^0-9]/', '', (string) $conv->customer_name);

            // Handle edge case where phone_number only had country code (e.g. "91") and customer_name had the 10-digit number
            if (strlen($phoneDigits) < 10 && strlen($nameDigits) >= 10 && ! self::isValidCustomerName($conv->customer_name)) {
                $combinedPhone = ($phoneDigits !== '' && ! str_starts_with($nameDigits, $phoneDigits))
                    ? ($phoneDigits . $nameDigits)
                    : $nameDigits;
                $conv->phone_number = $combinedPhone;
                $phoneDigits = $combinedPhone;
            }

            $last10 = strlen($phoneDigits) >= 10 ? substr($phoneDigits, -10) : $phoneDigits;
            $matchedName = $phoneMap[$phoneDigits] ?? $phoneMap[$last10] ?? null;

            if ($matchedName) {
                if ($conv->customer_name !== $matchedName) {
                    $conv->customer_name = $matchedName;
                    // Persist resolved customer name if not already stored
                    try {
                        self::where('id', $conv->id)->update(['customer_name' => $matchedName]);
                    } catch (\Throwable $e) {
                        // Ignore write errors on read path
                    }
                }
            } elseif (! self::isValidCustomerName($conv->customer_name)) {
                // Clear numeric/placeholder customer_name for clean UI display
                $conv->customer_name = null;
            }
        }

        return $conversations;
    }
}
