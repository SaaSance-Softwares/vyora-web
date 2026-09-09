<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You received a Gift Card</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f4f4f5;">
        <tr>
            <td align="center" style="padding:48px 16px;">

                {{-- Email Card --}}
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px;background-color:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e5e7eb;">

                    {{-- Logo Header --}}
                    <tr>
                        <td align="center" style="padding:32px 40px 24px;">
                            @if($logo)
                                <img src="{{ url($logo) }}" alt="{{ $storeName }}" style="max-height:36px;width:auto;display:block;margin:0 auto;">
                            @else
                                <p style="margin:0;font-size:12px;font-weight:900;letter-spacing:0.3em;text-transform:uppercase;color:#111827;">{{ $storeName }}</p>
                            @endif
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr>
                        <td style="padding:0 40px;"><div style="height:1px;background-color:#f3f4f6;"></div></td>
                    </tr>

                    {{-- Dark Gift Card Visual --}}
                    <tr>
                        <td style="padding:32px 40px 0;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#111827;border-radius:16px;overflow:hidden;">
                                <tr>
                                    <td style="padding:32px;text-align:center;">
                                        <p style="margin:0 0 6px;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#6b7280;">Gift Card Value</p>
                                        <p style="margin:0;font-size:52px;font-weight:900;letter-spacing:-2px;color:#ffffff;line-height:1;">&#8377;{{ number_format($card->amount, 0) }}</p>
                                        <p style="margin:12px 0 0;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:#374151;font-family:monospace;">Authenticated Digital Asset</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Headline --}}
                    <tr>
                        <td style="padding:28px 40px 8px;text-align:center;">
                            <p style="margin:0;font-size:22px;font-weight:900;color:#111827;letter-spacing:-0.5px;">You received a Gift Card! 🎁</p>
                            <p style="margin:8px 0 0;font-size:14px;color:#6b7280;">Use the code below at checkout to redeem your balance.</p>
                        </td>
                    </tr>

                    {{-- Code Block --}}
                    <tr>
                        <td style="padding:20px 40px;">
                            <p style="margin:0 0 8px;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#9ca3af;">Your Redemption Code</p>
                            <div style="background-color:#f9fafb;border:1.5px dashed #d1d5db;border-radius:12px;padding:20px;text-align:center;">
                                <p style="margin:0;font-family:monospace;font-size:22px;font-weight:700;letter-spacing:0.25em;color:#111827;word-break:break-all;">{{ $card->plain_code }}</p>
                            </div>
                            <p style="margin:10px 0 0;font-size:12px;color:#9ca3af;text-align:center;">Copy this code and paste it at checkout.</p>
                        </td>
                    </tr>

                    {{-- How to use steps --}}
                    <tr>
                        <td style="padding:0 40px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f9fafb;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 12px;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#9ca3af;">How to redeem</p>
                                        <table cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                            @foreach(['Add items to your cart on our store', 'At checkout, enter the code in the Gift Card field', 'Your balance will be applied instantly'] as $i => $step)
                                            <tr>
                                                <td style="padding:4px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0" role="presentation">
                                                        <tr>
                                                            <td style="padding-right:10px;vertical-align:top;">
                                                                <div style="width:20px;height:20px;background-color:#111827;border-radius:50%;text-align:center;line-height:20px;">
                                                                    <span style="color:#ffffff;font-size:9px;font-weight:900;">{{ $i + 1 }}</span>
                                                                </div>
                                                            </td>
                                                            <td style="font-size:13px;color:#4b5563;vertical-align:middle;">{{ $step }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr>
                        <td style="padding:0 40px;"><div style="height:1px;background-color:#f3f4f6;"></div></td>
                    </tr>

                    {{-- Footer inside card --}}
                    <tr>
                        <td style="padding:20px 40px;text-align:center;">
                            @if($card->expires_at)
                                <p style="margin:0 0 6px;font-size:11px;color:#9ca3af;">This gift card expires on <strong style="color:#6b7280;">{{ \Carbon\Carbon::parse($card->expires_at)->format('d M, Y') }}</strong></p>
                            @else
                                <p style="margin:0 0 6px;font-size:11px;color:#9ca3af;">This gift card has <strong style="color:#6b7280;">no expiry date</strong>.</p>
                            @endif
                        </td>
                    </tr>

                </table>

                {{-- Bottom footer --}}
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px;margin-top:24px;">
                    <tr>
                        <td style="text-align:center;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">&copy; {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
                            <p style="margin:4px 0 0;font-size:11px;color:#d1d5db;">This email was sent because someone shared a gift card with you.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
