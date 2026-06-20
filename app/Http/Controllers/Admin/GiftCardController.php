<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiftCardController extends Controller
{
    public function __construct(private GiftCardService $service) {}

    // ── Template Management ───────────────────────────────────────────────────

    public function index()
    {
        $templates = GiftCardTemplate::withCount('issuedCards')
            ->with('creator')
            ->latest()
            ->paginate(20);

        // Recent issued cards for the activity feed
        $recentCards = GiftCard::with(['purchaser', 'recipient', 'template'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.gift-cards.index', compact('templates', 'recentCards'));
    }

    public function create()
    {
        $users = User::select('id', 'name', 'email', 'phone')->orderBy('name')->get();

        return view('admin.gift-cards.create', compact('users'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'template');

        if ($type === 'direct') {
            // Admin-to-user direct gift
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'assigned_to' => 'required|exists:users,id',
            ]);

            $this->service->createDirectCard(
                amount: (float) $request->amount,
                createdBy: Auth::id(),
                assignedTo: (int) $request->assigned_to,
            );

            return redirect()->route('admin.online-store.gift-cards.index')
                ->with('success', 'Gift card created and assigned successfully!');
        }

        // Storefront template
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'validity_days' => 'nullable|integer|min:1',
        ]);

        $this->service->createTemplate(
            amount: (float) $request->amount,
            createdBy: Auth::id(),
            name: $request->name,
            description: $request->description,
            validityDays: $request->validity_days ? (int) $request->validity_days : null,
        );

        return redirect()->route('admin.online-store.gift-cards.index')
            ->with('success', 'Gift card denomination created successfully! It is now live on the storefront.');
    }

    public function show(GiftCardTemplate $giftCard)
    {
        // $giftCard here is a template (route model binding)
        $giftCard->load('creator');
        $issuedCards = GiftCard::where('template_id', $giftCard->id)
            ->with(['purchaser', 'recipient', 'transactions'])
            ->latest()
            ->paginate(20);

        return view('admin.gift-cards.show', compact('giftCard', 'issuedCards'));
    }

    public function toggleTemplate(GiftCardTemplate $giftCard)
    {
        $giftCard->update(['is_active' => ! $giftCard->is_active]);
        $state = $giftCard->is_active ? 'visible' : 'hidden';

        return back()->with('success', "Gift card template is now {$state} on the storefront.");
    }

    public function destroyTemplate(GiftCardTemplate $giftCard)
    {
        // Only allow deletion if no cards have been issued from this template
        if ($giftCard->issuedCards()->exists()) {
            return back()->with('error', 'Cannot delete a template that has issued cards.');
        }
        $giftCard->delete();

        return redirect()->route('admin.online-store.gift-cards.index')
            ->with('success', 'Template deleted.');
    }

    // ── Issued Card Actions ───────────────────────────────────────────────────

    public function showCard(GiftCard $card)
    {
        $card->load(['purchaser', 'recipient', 'transactions.performer', 'template']);

        return view('admin.gift-cards.show-card', compact('card'));
    }

    public function withdraw(Request $request, GiftCard $card)
    {
        if (! in_array($card->status, ['active', 'partially_used', 'assigned'])) {
            return back()->with('error', 'This card cannot be withdrawn in its current state.');
        }
        if ($card->remaining_amount <= 0) {
            return back()->with('error', 'No remaining balance to withdraw.');
        }

        $this->service->withdrawCard($card, Auth::id());

        return back()->with('success', "₹{$card->amount} gift card withdrawn successfully.");
    }
}
