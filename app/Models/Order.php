<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'shipped_at'   => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (!$order->uuid) {
                $order->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Relations                                                           */
    /* ------------------------------------------------------------------ */

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Subtotal = sum of all item totals (before shipping/tax/discount).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Whether the order has tracking info.
     */
    public function getHasTrackingAttribute(): bool
    {
        return !empty($this->tracking_url) || !empty($this->tracking_number);
    }

    /**
     * Human-readable order status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'shipped'    => 'Shipped',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            default      => ucfirst($this->status),
        };
    }

    /**
     * Timeline steps for display.
     */
    public function getTimelineAttribute(): array
    {
        $steps = [
            ['key' => 'pending',    'label' => 'Order Placed',   'icon' => 'shopping-bag'],
            ['key' => 'processing', 'label' => 'Confirmed',      'icon' => 'check-circle'],
            ['key' => 'shipped',    'label' => 'Shipped',        'icon' => 'truck'],
            ['key' => 'delivered',  'label' => 'Delivered',      'icon' => 'home'],
        ];

        $order = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIdx = array_search($this->status, $order);
        if ($currentIdx === false) $currentIdx = -1;

        foreach ($steps as $i => &$step) {
            $step['done']    = $i <= $currentIdx;
            $step['current'] = $i === $currentIdx;
        }

        return $steps;
    }
}
