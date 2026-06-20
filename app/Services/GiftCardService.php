<?php

namespace App\Services;

use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\GiftCardTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiftCardService
{
    /**
     * Generate a unique, cryptographically-secure 16-char alphanumeric redemption code.
     * Stores it AES-encrypted. Returns the plain-text code for display.
     */
    public function generateCode(): string
    {
        do {
            $plain = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
            $exists = GiftCard::all()
                ->contains(fn ($gc) => $gc->plain_code === $plain);
        } while ($exists);

        return $plain;
    }

    /**
     * Generate a unique human-readable card number like GC-A1B2-C3D4.
     */
    public function generateCardNumber(): string
    {
        do {
            $number = 'GC-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (GiftCard::where('card_number', $number)->exists());

        return $number;
    }

    /**
     * Generate a unique share token (used in shareable gift links).
     */
    public function generateShareToken(): string
    {
        do {
            $token = Str::random(32);
        } while (GiftCard::where('share_token', $token)->exists());

        return $token;
    }

    // ── Template Management ───────────────────────────────────────────────────

    /**
     * Create a gift card template (admin sets a denomination, it shows on the storefront).
     * Unlimited users can purchase from a single template.
     */
    public function createTemplate(float $amount, int $createdBy, ?string $name = null, ?string $description = null, ?int $validityDays = null): GiftCardTemplate
    {
        return GiftCardTemplate::create([
            'name' => $name,
            'amount' => $amount,
            'description' => $description,
            'is_active' => true,
            'validity_days' => $validityDays,
            'created_by' => $createdBy,
        ]);
    }

    // ── Direct Cards (admin gift) ─────────────────────────────────────────────

    /**
     * Create a new gift card (admin direct assignment – no template needed).
     */
    public function createDirectCard(float $amount, int $createdBy, ?int $assignedTo = null): GiftCard
    {
        return DB::transaction(function () use ($amount, $createdBy, $assignedTo) {
            $plainCode = $this->generateCode();
            $cardNumber = $this->generateCardNumber();
            $shareToken = $this->generateShareToken();
            $status = $assignedTo ? 'active' : 'created';

            $card = GiftCard::create([
                'card_number' => $cardNumber,
                'code' => Crypt::encryptString($plainCode),
                'share_token' => $shareToken,
                'amount' => $amount,
                'used_amount' => 0,
                'remaining_amount' => $amount,
                'status' => $status,
                'type' => 'direct',
                'created_by' => $createdBy,
                'assigned_to' => $assignedTo,
            ]);

            GiftCardTransaction::create([
                'gift_card_id' => $card->id,
                'type' => 'creation',
                'amount_used' => 0,
                'description' => "Card created by admin. Value: ₹{$amount}",
                'performed_by' => $createdBy,
                'transaction_date' => now(),
            ]);

            if ($assignedTo) {
                GiftCardTransaction::create([
                    'gift_card_id' => $card->id,
                    'type' => 'assignment',
                    'amount_used' => 0,
                    'description' => "Card assigned to user #{$assignedTo} by admin",
                    'performed_by' => $createdBy,
                    'transaction_date' => now(),
                ]);
            }

            return $card;
        });
    }

    // ── Template-based Purchase ───────────────────────────────────────────────

    /**
     * Issue a brand-new unique gift card for a user purchasing from a template.
     * Each purchase = a new row in gift_cards with its own unique code.
     * Called after successful payment verification.
     */
    public function issueFromTemplate(GiftCardTemplate $template, int $purchasedBy, ?int $orderId = null): GiftCard
    {
        return DB::transaction(function () use ($template, $purchasedBy, $orderId) {
            $plainCode = $this->generateCode();
            $cardNumber = $this->generateCardNumber();
            $shareToken = $this->generateShareToken();

            // Calculate expiry if template has validity_days set
            $expiresAt = $template->validity_days
                ? now()->addDays($template->validity_days)
                : null;

            $card = GiftCard::create([
                'template_id' => $template->id,
                'card_number' => $cardNumber,
                'code' => Crypt::encryptString($plainCode),
                'share_token' => $shareToken,
                'amount' => $template->amount,
                'used_amount' => 0,
                'remaining_amount' => $template->amount,
                'status' => 'active',
                'type' => 'purchasable',
                'created_by' => null,         // system-generated on purchase
                'purchased_by' => $purchasedBy,
                'assigned_to' => $purchasedBy, // buyer owns it initially
                'purchase_order_id' => $orderId,
                'expires_at' => $expiresAt,
            ]);

            GiftCardTransaction::create([
                'gift_card_id' => $card->id,
                'order_id' => $orderId,
                'type' => 'purchase',
                'amount_used' => 0,
                'description' => "Gift card purchased by user #{$purchasedBy} from template #{$template->id} (₹{$template->amount})",
                'performed_by' => $purchasedBy,
                'transaction_date' => now(),
            ]);

            return $card;
        });
    }

    // ── Assignment / Gifting ─────────────────────────────────────────────────

    /**
     * Assign a gift card to another registered user (in-app transfer).
     * Returns error string or null on success.
     */
    public function assignCard(GiftCard $card, User $recipient, int $performedBy): ?string
    {
        if ($card->assigned_to && $card->assigned_to !== $performedBy) {
            return 'This card has already been assigned to another user.';
        }
        if (! in_array($card->status, ['active', 'assigned', 'partially_used'])) {
            return 'This card cannot be reassigned in its current state.';
        }

        DB::transaction(function () use ($card, $recipient, $performedBy) {
            $card->update([
                'assigned_to' => $recipient->id,
                'status' => in_array($card->status, ['active', 'partially_used']) ? $card->status : 'active',
            ]);

            GiftCardTransaction::create([
                'gift_card_id' => $card->id,
                'type' => 'assignment',
                'amount_used' => 0,
                'description' => "Card gifted to {$recipient->name} ({$recipient->email})",
                'performed_by' => $performedBy,
                'transaction_date' => now(),
            ]);
        });

        return null;
    }

    // ── Redemption ───────────────────────────────────────────────────────────

    /**
     * Validate a gift card code and redeem an amount from it.
     * Returns ['success' => bool, 'amount_deducted' => float, 'message' => string]
     */
    public function validateAndRedeem(string $plainCode, float $requestedAmount, ?int $performedBy = null, ?int $orderId = null): array
    {
        $card = GiftCard::whereIn('status', ['active', 'partially_used'])->get()
            ->first(fn ($gc) => $gc->plain_code === strtoupper($plainCode));

        if (! $card) {
            return ['success' => false, 'amount_deducted' => 0, 'message' => 'Invalid or already used gift card code.'];
        }

        if (! $card->isRedeemable()) {
            return ['success' => false, 'amount_deducted' => 0, 'message' => 'This gift card is no longer valid.'];
        }

        return DB::transaction(function () use ($card, $requestedAmount, $performedBy, $orderId) {
            $deduct = min($card->remaining_amount, $requestedAmount);
            $newUsed = $card->used_amount + $deduct;
            $newRemaining = $card->remaining_amount - $deduct;
            $newStatus = $newRemaining <= 0 ? 'used' : 'partially_used';

            $card->update([
                'used_amount' => $newUsed,
                'remaining_amount' => $newRemaining,
                'status' => $newStatus,
            ]);

            GiftCardTransaction::create([
                'gift_card_id' => $card->id,
                'order_id' => $orderId,
                'type' => 'redemption',
                'amount_used' => $deduct,
                'description' => "Redeemed ₹{$deduct} for order#{$orderId}",
                'performed_by' => $performedBy,
                'transaction_date' => now(),
            ]);

            return ['success' => true, 'amount_deducted' => $deduct, 'message' => "₹{$deduct} redeemed successfully.", 'card' => $card->fresh()];
        });
    }

    // ── Admin Actions ─────────────────────────────────────────────────────────

    /**
     * Admin: Withdraw remaining balance from a gift card.
     */
    public function withdrawCard(GiftCard $card, int $adminId): void
    {
        DB::transaction(function () use ($card, $adminId) {
            $withdrawn = $card->remaining_amount;

            GiftCardTransaction::create([
                'gift_card_id' => $card->id,
                'type' => 'withdrawal',
                'amount_used' => $withdrawn,
                'description' => "Remaining balance ₹{$withdrawn} withdrawn by admin",
                'performed_by' => $adminId,
                'transaction_date' => now(),
            ]);

            $card->update([
                'used_amount' => $card->amount,
                'remaining_amount' => 0,
                'status' => 'withdrawn',
            ]);
        });
    }
}
