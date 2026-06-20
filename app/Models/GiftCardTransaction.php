<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_card_id',
        'order_id',
        'type',
        'amount_used',
        'description',
        'performed_by',
        'transaction_date',
    ];

    protected $casts = [
        'amount_used'      => 'float',
        'transaction_date' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'creation'   => 'Card Created',
            'purchase'   => 'Purchased',
            'assignment' => 'Assigned',
            'redemption' => 'Redeemed',
            'withdrawal' => 'Withdrawn by Admin',
            default      => ucfirst($this->type),
        };
    }
}
