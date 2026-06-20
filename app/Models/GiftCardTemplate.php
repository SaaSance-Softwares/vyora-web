<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'description',
        'is_active',
        'validity_days',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
        'validity_days' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All gift cards issued from this template.
     */
    public function issuedCards()
    {
        return $this->hasMany(GiftCard::class, 'template_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function displayName(): string
    {
        return $this->name ?? '₹'.number_format($this->amount, 0).' Gift Card';
    }

    public function purchasedCount(): int
    {
        return $this->issuedCards()->whereNotNull('purchased_by')->count();
    }
}
