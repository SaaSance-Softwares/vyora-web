<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'card_number',
        'code',
        'share_token',
        'amount',
        'used_amount',
        'remaining_amount',
        'status',
        'type',
        'created_by',
        'purchased_by',
        'assigned_to',
        'purchase_order_id',
        'expires_at',
    ];

    protected $casts = [
        'amount'           => 'float',
        'used_amount'      => 'float',
        'remaining_amount' => 'float',
        'expires_at'       => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template()
    {
        return $this->belongsTo(GiftCardTemplate::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function transactions()
    {
        return $this->hasMany(GiftCardTransaction::class)->latest('transaction_date');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Decrypt and return the plain redemption code.
     */
    public function getPlainCodeAttribute(): string
    {
        try {
            return Crypt::decryptString($this->code);
        } catch (\Exception $e) {
            return '***INVALID***';
        }
    }

    /**
     * Status badge helper for views.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'created'        => ['label' => 'Created',        'class' => 'bg-gray-100 text-gray-600'],
            'assigned'       => ['label' => 'Assigned',       'class' => 'bg-blue-100 text-blue-700'],
            'active'         => ['label' => 'Active',         'class' => 'bg-green-100 text-green-700'],
            'partially_used' => ['label' => 'Partially Used', 'class' => 'bg-yellow-100 text-yellow-700'],
            'used'           => ['label' => 'Used',           'class' => 'bg-gray-200 text-gray-500'],
            'withdrawn'      => ['label' => 'Withdrawn',      'class' => 'bg-red-100 text-red-600'],
            default          => ['label' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-500'],
        };
    }

    /**
     * Whether the card is redeemable.
     */
    public function isRedeemable(): bool
    {
        return in_array($this->status, ['active', 'partially_used'])
            && $this->remaining_amount > 0
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
