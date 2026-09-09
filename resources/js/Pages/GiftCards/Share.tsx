import { useEffect, useState } from 'react';
import api from '@/lib/api';
import { Head, Link, usePage } from '@inertiajs/react';
import { Gift, Copy, CheckCircle, Clock, AlertCircle, ShoppingBag, MessageCircle, Mail } from 'lucide-react';

interface SharedCardInfo {
    success: boolean;
    card_number: string;
    plain_code: string;
    amount: number;
    remaining_amount: number;
    status: string;
    is_redeemable: boolean;
    template_name: string;
    purchased_by: string;
    expires_at: string | null;
    message?: string;
}

export default function GiftCardSharePage() {
    // In Inertia, route parameters are often passed as props, or we can parse window.location.
    // For simplicity, let's get token from props if provided, or from URL path directly.
    const { props } = usePage();
    const token = props.token;
    const settings = props.settings as any;

    const [info, setInfo] = useState<SharedCardInfo | null>(null);
    const [loading, setLoading] = useState(true);
    const [codeCopied, setCodeCopied] = useState(false);

    useEffect(() => {
        if (!token) return;
        api.get(`/api/gift-cards/share/${token}`)
            .then(r => setInfo(r.data))
            .catch(e => setInfo({ success: false, message: e.response?.data?.message || 'Invalid link.' } as any))
            .finally(() => setLoading(false));
    }, [token]);

    const copyCode = () => {
        if (!info?.plain_code) return;
        navigator.clipboard.writeText(info.plain_code);
        setCodeCopied(true);
        setTimeout(() => setCodeCopied(false), 2500);
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="w-16 h-16 rounded-[1.5rem] bg-white border border-gray-100 flex items-center justify-center shadow-sm animate-pulse">
                    <Gift className="w-8 h-8 text-gray-200" />
                </div>
            </div>
        );
    }

    if (!info || !info.success) {
        return (
            <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 text-center">
                <div className="w-20 h-20 rounded-[2rem] bg-white border border-red-100 flex items-center justify-center mb-6 shadow-sm">
                    <AlertCircle className="w-10 h-10 text-red-300" />
                </div>
                <h1 className="text-2xl font-black text-gray-900 mb-2">Invalid Gift Link</h1>
                <p className="text-gray-400 text-sm mb-8">{(info as any)?.message || 'This link is invalid or has expired.'}</p>
                <Link href="/gift-cards" className="bg-black text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all">
                    Browse Gift Cards
                </Link>
            </div>
        );
    }

    const isRedeemable = info.is_redeemable && info.status !== 'used' && info.status !== 'withdrawn';

    const getCardTheme = (amount: number) => {
        if (amount >= 5000) {
            return {
                bg: 'bg-gradient-to-br from-[#1a1a1a] via-black to-[#1a1a1a] text-[#ffd700] border border-[#ffd700]/30 shadow-[0_20px_50px_rgba(255,215,0,0.15)]',
                glow: 'bg-[#ffd700]/10',
                iconBg: 'bg-[#ffd700]/10 border-[#ffd700]/20 text-[#ffd700]',
                logo: 'brightness-0 invert opacity-80 mix-blend-plus-lighter',
                label: 'text-[#ffd700]/60',
                chip: 'bg-[#ffd700]/10 border-[#ffd700]/20',
            };
        }
        if (amount >= 3000) {
            return {
                bg: 'bg-gradient-to-br from-[#2c3e50] via-[#1a252f] to-[#111] text-[#fff] border border-[#34495e]',
                glow: 'bg-blue-400/10',
                iconBg: 'bg-white/10 border-white/20 text-white',
                logo: 'brightness-0 invert opacity-70',
                label: 'text-gray-400',
                chip: 'bg-white/10 border-white/10',
            };
        }
        if (amount >= 1000) {
            return {
                bg: 'bg-gradient-to-br from-[#434343] to-[#000000] text-white border border-gray-700',
                glow: 'bg-white/10',
                iconBg: 'bg-white/10 border-white/10 text-gray-200',
                logo: 'brightness-0 invert opacity-60',
                label: 'text-gray-400',
                chip: 'bg-white/10 border-white/10',
            };
        }
        return {
            bg: 'bg-gray-50 text-gray-900 border border-gray-200',
            glow: 'bg-black/5',
            iconBg: 'bg-white border border-gray-200 text-gray-900',
            logo: 'opacity-40',
            label: 'text-gray-400',
            chip: 'bg-gray-200/50 border-gray-200',
        };
    };


    
    const ogImage = settings?.favicon ? `/${settings.favicon}` : (settings?.main_logo ? `/${settings.main_logo}` : '/favicon.ico');
    
    return (
        <div className="min-h-screen bg-gray-50 pb-20">
            <Head>
                <title>{`Gift Card | ${settings?.store_name || 'Store'}`}</title>
                <meta property="og:title" content={`You received a Gift Card!`} />
                <meta property="og:description" content={`Open to view and redeem your gift card at ${settings?.store_name || 'our store'}.`} />
                <meta property="og:image" content={ogImage} />
            </Head>
            <div className="max-w-xl mx-auto px-4 pt-16">

                {/* From badge */}
                <div className="text-center mb-10">
                    <div className="inline-flex items-center gap-2 bg-white border border-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-[0.2em] px-5 py-2 rounded-full shadow-sm mb-4">
                        <Gift className="w-3 h-3 text-gray-400" />
                        A gift from {info.purchased_by}
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                        You've received a<br />
                        <span className="text-gray-400">gift card</span>
                    </h1>
                </div>

                {/* Card Visual */}
                <div className={`relative rounded-[2rem] p-8 shadow-2xl shadow-gray-200 mb-8 overflow-hidden ${getCardTheme(Number(info.amount)).bg}`}>
                    {/* Background pattern */}
                    <div className="absolute inset-0 opacity-10 bg-noise pointer-events-none mix-blend-overlay" />
                    <div className={`absolute -right-12 -top-12 w-48 h-48 rounded-full blur-[40px] pointer-events-none ${getCardTheme(Number(info.amount)).glow}`} />
                    <div className={`absolute -left-8 -bottom-8 w-36 h-36 rounded-full blur-[40px] pointer-events-none ${getCardTheme(Number(info.amount)).glow}`} />

                    <div className="relative z-10">
                        <div className="flex justify-between items-start mb-8">
                            <div className={`w-12 h-12 rounded-2xl flex items-center justify-center ${getCardTheme(Number(info.amount)).iconBg}`}>
                                <Gift className="w-6 h-6" />
                            </div>
                            {settings?.main_logo ? (
                                <img src={`/${settings.main_logo}`} alt="Logo" className={`h-5 w-auto object-contain ${getCardTheme(Number(info.amount)).logo}`} />
                            ) : (
                                <span className={`text-[10px] font-black tracking-[0.3em] uppercase ${getCardTheme(Number(info.amount)).label}`}>
                                    {settings?.store_name || 'VYORA'}
                                </span>
                            )}
                        </div>

                        <p className={`text-[10px] uppercase tracking-widest mb-1 ${getCardTheme(Number(info.amount)).label}`}>Gift Card Value</p>
                        <p className="text-5xl font-black tracking-tight mb-6">₹{Number(info.amount).toLocaleString()}</p>

                        {Number(info.remaining_amount) < Number(info.amount) && (
                            <p className="text-[11px] text-amber-400 mb-4 font-medium">
                                ₹{Number(info.remaining_amount).toLocaleString()} remaining (₹{(Number(info.amount) - Number(info.remaining_amount)).toLocaleString()} used)
                            </p>
                        )}

                        <div className={`flex items-center justify-between pt-4 border-t mt-6 ${Number(info.amount) >= 1000 ? 'border-white/10' : 'border-gray-200'}`}>
                            <div>
                                <p className={`text-[9px] uppercase tracking-widest mb-1 ${getCardTheme(Number(info.amount)).label}`}>{info.template_name}</p>
                                <p className={`font-mono text-[11px] tracking-[0.2em] ${getCardTheme(Number(info.amount)).label}`}>{info.card_number}</p>
                            </div>
                            <div className="text-right">
                                {info.expires_at ? (
                                    <div className={`flex items-center gap-1.5 text-[10px] ${getCardTheme(Number(info.amount)).label}`}>
                                        <Clock className="w-3 h-3" />
                                        <span>Exp {new Date(info.expires_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                                    </div>
                                ) : (
                                    <p className={`text-[10px] ${getCardTheme(Number(info.amount)).label}`}>No Expiry</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Code Section */}
                {isRedeemable ? (
                    <div className="bg-white border border-gray-100 rounded-[2rem] p-8 mb-6 shadow-sm">
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 text-center">Your Redemption Code</p>
                        <div className="flex items-center gap-3">
                            <div className="flex-1 bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4">
                                <p className="font-mono text-xl font-black tracking-[0.2em] text-gray-900 text-center">
                                    {info.plain_code}
                                </p>
                            </div>
                            <button
                                onClick={copyCode}
                                className={`w-14 h-14 rounded-2xl flex items-center justify-center transition-all shadow-sm ${
                                    codeCopied
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-gray-900 text-white hover:bg-gray-700'
                                }`}
                            >
                                {codeCopied ? <CheckCircle className="w-5 h-5" /> : <Copy className="w-5 h-5" />}
                            </button>
                        </div>
                        {codeCopied && (
                            <p className="text-center text-emerald-600 text-xs font-bold mt-3 animate-in fade-in">
                                ✓ Copied to clipboard
                            </p>
                        )}
                        <p className="text-[11px] text-gray-400 text-center mt-4 leading-relaxed">
                            Enter this code at checkout to redeem your ₹{Number(info.remaining_amount).toLocaleString()} balance.
                        </p>
                    </div>
                ) : (
                    <div className="bg-gray-100 border border-gray-200 rounded-[2rem] p-8 mb-6 text-center">
                        <AlertCircle className="w-10 h-10 text-gray-300 mx-auto mb-3" />
                        <p className="font-black text-gray-500 uppercase tracking-widest text-sm">
                            {info.status === 'used' ? 'This card has been fully redeemed' : 'This card is no longer active'}
                        </p>
                    </div>
                )}

                {/* CTAs */}
                {isRedeemable && (
                    <div className="space-y-3">
                        <Link
                            href="/shop"
                            className="w-full flex items-center justify-center gap-3 bg-black text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all shadow-xl shadow-gray-100"
                        >
                            <ShoppingBag className="w-4 h-4" /> Shop & Redeem Now
                        </Link>
                        <p className="text-center text-[11px] text-gray-400 font-medium">
                            You can also enter the code manually at checkout
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}
