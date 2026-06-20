<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiftCardApiController extends Controller
{
    public function __construct(private GiftCardService $service) {}

    // ── Storefront ────────────────────────────────────────────────────────────

    /**
     * List active gift card templates for the storefront.
     * Unlimited purchases allowed per template.
     */
    public function getPurchasableOptions()
    {
        $templates = GiftCardTemplate::where('is_active', true)
            ->withCount('issuedCards as purchased_count')
            ->orderBy('amount')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->displayName(),
                'amount' => $t->amount,
                'description' => $t->description,
                'validity_days' => $t->validity_days,
                'purchased_count' => $t->purchased_count,
                'created_at' => $t->created_at,
            ]);

        return response()->json($templates);
    }

    // ── Wallet / My Cards ─────────────────────────────────────────────────────

    /**
     * Get authenticated user's gift cards (owned + received).
     */
    public function myCards(Request $request)
    {
        $userId = $request->user()->id;

        $cards = GiftCard::where('assigned_to', $userId)
            ->with(['creator:id,name', 'purchaser:id,name', 'template:id,name,amount'])
            ->latest()
            ->get()
            ->map(fn ($card) => [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'plain_code' => $card->plain_code,   // decrypted – safe since only owner sees this
                'share_token' => $card->share_token,
                'amount' => $card->amount,
                'used_amount' => $card->used_amount,
                'remaining_amount' => $card->remaining_amount,
                'status' => $card->status,
                'status_badge' => $card->status_badge,
                'type' => $card->type,
                'template_name' => $card->template?->displayName(),
                'created_at' => $card->created_at,
                'expires_at' => $card->expires_at,
                'purchased_by' => $card->purchaser?->name,
                'created_by' => $card->creator?->name,
                'is_redeemable' => $card->isRedeemable(),
            ]);

        return response()->json($cards);
    }

    /**
     * Wallet summary – total usable balance, gifted amount, active cards.
     */
    public function walletSummary(Request $request)
    {
        $userId = $request->user()->id;

        $ownCards = GiftCard::where('assigned_to', $userId)
            ->whereIn('status', ['active', 'partially_used'])
            ->get();

        $giftedCards = GiftCard::where('purchased_by', $userId)
            ->where('assigned_to', '!=', $userId)
            ->whereNotNull('assigned_to')
            ->get();

        return response()->json([
            'total_balance' => $ownCards->sum('remaining_amount'),
            'gifted_amount' => $giftedCards->sum('amount'),
            'active_cards' => $ownCards->count(),
        ]);
    }

    // ── Purchase Flow ─────────────────────────────────────────────────────────

    /**
     * Activate a newly purchased gift card after successful payment.
     * Creates a brand-new unique card record for the buyer.
     */
    public function activateAfterPurchase(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:gift_card_templates,id',
            'order_id' => 'nullable|integer',
        ]);

        $template = GiftCardTemplate::find($request->template_id);
        $userId = $request->user()->id;

        if (! $template->is_active) {
            return response()->json(['success' => false, 'message' => 'This gift card is no longer available.'], 422);
        }

        $card = $this->service->issueFromTemplate($template, $userId, $request->order_id);

        return response()->json([
            'success' => true,
            'message' => 'Gift card activated!',
            'card_number' => $card->card_number,
            'plain_code' => $card->plain_code,
            'share_token' => $card->share_token,
            'amount' => $card->amount,
        ]);
    }

    // ── Sharing ───────────────────────────────────────────────────────────────

    /**
     * Look up a registered user by phone or email (for in-app gifting).
     */
    public function lookupUser(Request $request)
    {
        $request->validate(['identifier' => 'required|string']);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first(['id', 'name', 'email', 'phone']);

        if (! $user) {
            return response()->json(['found' => false, 'message' => 'No user found with that email or phone.'], 404);
        }
        if ($user->id === $request->user()->id) {
            return response()->json(['found' => false, 'message' => 'You cannot assign a gift card to yourself.'], 422);
        }

        return response()->json(['found' => true, 'user' => $user]);
    }

    /**
     * Transfer/gift a card to a registered user (in-app).
     */
    public function assignCard(Request $request)
    {
        $request->validate([
            'gift_card_id' => 'required|exists:gift_cards,id',
            'recipient_id' => 'required|exists:users,id',
        ]);

        $card = GiftCard::find($request->gift_card_id);
        $userId = $request->user()->id;

        if ($card->assigned_to !== $userId && $card->purchased_by !== $userId) {
            return response()->json(['success' => false, 'message' => 'You do not own this gift card.'], 403);
        }

        $recipient = User::find($request->recipient_id);
        $error = $this->service->assignCard($card, $recipient, $userId);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return response()->json(['success' => true, 'message' => "Gift card sent to {$recipient->name} successfully."]);
    }

    /**
     * Resolve a share token – returns card info for the shareable link landing page.
     * No auth required; the token acts as the credential.
     */
    public function resolveShareToken(Request $request, string $token)
    {
        $card = GiftCard::where('share_token', $token)
            ->with(['purchaser:id,name', 'template:id,name,amount'])
            ->first();

        if (! $card) {
            return response()->json(['success' => false, 'message' => 'Invalid share link.'], 404);
        }

        return response()->json([
            'success' => true,
            'card_number' => $card->card_number,
            'plain_code' => $card->plain_code,   // code shown on the gift claim page
            'amount' => $card->amount,
            'remaining_amount' => $card->remaining_amount,
            'status' => $card->status,
            'is_redeemable' => $card->isRedeemable(),
            'template_name' => $card->template?->displayName() ?? "₹{$card->amount} Gift Card",
            'purchased_by' => $card->purchaser?->name ?? 'Dope Style',
            'expires_at' => $card->expires_at,
        ]);
    }

    // ── Redemption (Checkout) ─────────────────────────────────────────────────

    /**
     * Validate a gift card code (for checkout preview – does NOT deduct balance).
     */
    public function validateCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $plainCode = strtoupper(trim($request->code));

        $card = GiftCard::whereIn('status', ['active', 'partially_used'])->get()
            ->first(fn ($gc) => $gc->plain_code === $plainCode);

        if (! $card) {
            return response()->json(['success' => false, 'message' => 'Invalid or already used gift card code.'], 422);
        }
        if (! $card->isRedeemable()) {
            return response()->json(['success' => false, 'message' => 'This gift card has expired or is no longer valid.'], 422);
        }
        if ($card->assigned_to && $card->assigned_to !== $request->user()?->id) {
            $msg = $request->user()
                ? 'This gift card is not assigned to your account.'
                : 'This gift card is assigned to a specific account. Please login to use it.';

            return response()->json(['success' => false, 'message' => $msg], 403);
        }

        return response()->json([
            'success' => true,
            'card_number' => $card->card_number,
            'remaining_amount' => $card->remaining_amount,
            'message' => "Gift card valid! ₹{$card->remaining_amount} available.",
        ]);
    }
}
