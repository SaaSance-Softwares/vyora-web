<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            $request->strictValidate([
                'type' => 'required|string|in:direct,template',
                'amount' => 'required|numeric|min:1',
                'assigned_to' => 'required|exists:users,id',
                'name' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:500',
                'validity_days' => 'nullable|integer|min:1',
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
        $request->strictValidate([
            'type' => 'required|string|in:direct,template',
            'amount' => 'required|numeric|min:1',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'validity_days' => 'nullable|integer|min:1',
            'assigned_to' => 'nullable|exists:users,id',
            'background_image' => 'nullable|image|max:5120',
        ]);

        $bgImagePath = null;
        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            $dir = public_path('storage/gift-cards/backgrounds');
            if (! is_dir($dir)) { mkdir($dir, 0755, true); }
            $filename = 'gc_bg_' . time() . '_' . Str::random(8) . '.webp';
            $destPath = $dir . '/' . $filename;

            // Convert to WebP using GD
            $mime = $file->getMimeType();
            $src = match (true) {
                str_contains($mime, 'png')  => imagecreatefrompng($file->getRealPath()),
                str_contains($mime, 'gif')  => imagecreatefromgif($file->getRealPath()),
                str_contains($mime, 'webp') => imagecreatefromwebp($file->getRealPath()),
                default                     => imagecreatefromjpeg($file->getRealPath()),
            };
            imagewebp($src, $destPath, 85);
            imagedestroy($src);

            $bgImagePath = 'storage/gift-cards/backgrounds/' . $filename;
        }

        $this->service->createTemplate(
            amount: (float) $request->amount,
            createdBy: Auth::id(),
            name: $request->name,
            description: $request->description,
            validityDays: $request->validity_days ? (int) $request->validity_days : null,
            backgroundImage: $bgImagePath,
        );

        return redirect()->route('admin.online-store.gift-cards.index')
            ->with('success', 'Gift card denomination created successfully! It is now live on the storefront.');
    }

    public function edit(GiftCardTemplate $giftCard)
    {
        return view('admin.gift-cards.edit', compact('giftCard'));
    }

    public function update(Request $request, GiftCardTemplate $giftCard)
    {
        $request->strictValidate([
            'amount' => 'required|numeric|min:1',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'validity_days' => 'nullable|integer|min:1',
            'background_image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'amount' => (float) $request->amount,
            'name' => $request->name,
            'description' => $request->description,
            'validity_days' => $request->validity_days ? (int) $request->validity_days : null,
        ];

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            $dir = public_path('storage/gift-cards/backgrounds');
            if (! is_dir($dir)) { mkdir($dir, 0755, true); }
            $filename = 'gc_bg_' . time() . '_' . Str::random(8) . '.webp';
            $destPath = $dir . '/' . $filename;

            // Convert to WebP using GD
            $mime = $file->getMimeType();
            $src = match (true) {
                str_contains($mime, 'png')  => imagecreatefrompng($file->getRealPath()),
                str_contains($mime, 'gif')  => imagecreatefromgif($file->getRealPath()),
                str_contains($mime, 'webp') => imagecreatefromwebp($file->getRealPath()),
                default                     => imagecreatefromjpeg($file->getRealPath()),
            };
            imagewebp($src, $destPath, 85);
            imagedestroy($src);

            if ($giftCard->background_image && file_exists(public_path($giftCard->background_image))) {
                unlink(public_path($giftCard->background_image));
            }

            $data['background_image'] = 'storage/gift-cards/backgrounds/' . $filename;
        }

        $giftCard->update($data);

        return redirect()->route('admin.online-store.gift-cards.index')
            ->with('success', 'Gift card template updated successfully.');
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

        return view('admin.gift-cards.show-card', ['giftCard' => $card]);
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
