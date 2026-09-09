import { useEffect, useState } from 'react';
import { useAuthStore } from '@/store/auth';
import api from '@/lib/api';
import { Link, usePage } from '@inertiajs/react';
import {
    Gift, Wallet, ArrowRight, CreditCard, Clock, CheckCircle,
    AlertCircle, X, Check, Copy, Share2, MessageCircle, Mail,
    ShieldCheck, Zap, History, LayoutGrid, ListFilter
} from 'lucide-react';

interface GiftCard {
    id: number;
    card_number: string;
    plain_code: string;
    share_token: string;
    amount: number;
    used_amount: number;
    remaining_amount: number;
    status: string;
    status_badge: { label: string; class: string };
    type: string;
    template_name: string | null;
    created_at: string;
    expires_at: string | null;
    purchased_by: string | null;
    created_by: string | null;
    is_redeemable: boolean;
}

interface WalletSummary {
    total_balance: number;
    gifted_amount: number;
    active_cards: number;
}

// ── Share Modal ─────────────────────────────────────────────────────────────
function ShareModal({ card, onClose, settings }: { card: GiftCard; onClose: () => void; settings: any }) {
    const [codeCopied, setCodeCopied] = useState(false);
    const [showEmailInput, setShowEmailInput] = useState(false);
    const [email, setEmail] = useState('');
    const [sendingEmail, setSendingEmail] = useState(false);
    const [emailSent, setEmailSent] = useState(false);
    const [emailError, setEmailError] = useState('');

    const shopUrl = typeof window !== 'undefined'
        ? `${window.location.origin}/shop`
        : '';

    const shareText = `🎁 I'm gifting you a ₹${card.amount.toLocaleString()} ${settings?.store_name || 'Store'} Gift Card!\n\nUse code: *${card.plain_code}* at checkout.\n\nShop now at: ${shopUrl}`;

    const copyCode = () => {
        navigator.clipboard.writeText(card.plain_code);
        setCodeCopied(true);
        setTimeout(() => setCodeCopied(false), 2000);
    };

    const shareWhatsApp = () => {
        window.open(`https://wa.me/?text=${encodeURIComponent(shareText)}`, '_blank');
    };

    const sendEmail = async () => {
        if (!email) { setEmailError('Please enter an email address'); return; }
        setSendingEmail(true);
        setEmailError('');
        try {
            await api.post('/api/gift-cards/share-email', { token: card.share_token, email });
            setEmailSent(true);
            setTimeout(() => { setShowEmailInput(false); setEmailSent(false); setEmail(''); }, 3000);
        } catch (e: any) {
            setEmailError(e.response?.data?.message || 'Failed to send email.');
        } finally {
            setSendingEmail(false);
        }
    };

    return (
        <div className="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300" onClick={onClose} />
            <div className="relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300">

                {/* Header */}
                <div className="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">Share Gift Card</h3>
                    <button onClick={onClose} className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                        <X className="w-4 h-4 text-gray-400" />
                    </button>
                </div>

                <div className="p-6 space-y-5">
                    {/* Code */}
                    <div>
                        <p className="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Redemption Code</p>
                        <div className="bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center justify-between">
                            <p className="font-mono text-sm font-bold tracking-[0.15em] text-gray-900">
                                {card.plain_code}
                            </p>
                            <button
                                onClick={copyCode}
                                className={`w-9 h-9 rounded-lg flex items-center justify-center transition-all ${codeCopied ? 'bg-emerald-500 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-100'}`}
                            >
                                {codeCopied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                            </button>
                        </div>
                    </div>

                    {/* Share buttons or email form */}
                    {!showEmailInput ? (
                        <div className="grid grid-cols-2 gap-3">
                            <button
                                onClick={shareWhatsApp}
                                className="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-700"
                            >
                                <MessageCircle className="w-3.5 h-3.5 text-emerald-500" />
                                WhatsApp
                            </button>
                            <button
                                onClick={() => setShowEmailInput(true)}
                                className="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-700"
                            >
                                <Mail className="w-3.5 h-3.5 text-blue-500" />
                                Email
                            </button>
                        </div>
                    ) : (
                        <div className="space-y-3 animate-in fade-in slide-in-from-bottom-2 duration-200">
                            {emailSent ? (
                                <div className="py-6 text-center">
                                    <div className="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <Check className="w-5 h-5" />
                                    </div>
                                    <p className="text-sm font-bold text-gray-900">Email sent!</p>
                                    <p className="text-xs text-gray-400 mt-1">Gift card delivered to {email}</p>
                                </div>
                            ) : (
                                <>
                                    <input
                                        type="email"
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        placeholder="Recipient's email address"
                                        className="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black/10 focus:border-gray-300"
                                        onKeyDown={(e) => e.key === 'Enter' && sendEmail()}
                                        autoFocus
                                    />
                                    {emailError && <p className="text-xs text-red-500">{emailError}</p>}
                                    <div className="grid grid-cols-2 gap-3">
                                        <button
                                            onClick={() => { setShowEmailInput(false); setEmailError(''); }}
                                            className="flex items-center justify-center py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-500"
                                        >
                                            Back
                                        </button>
                                        <button
                                            onClick={sendEmail}
                                            disabled={sendingEmail}
                                            className="flex items-center justify-center py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 disabled:opacity-40 transition-colors"
                                        >
                                            {sendingEmail ? 'Sending…' : 'Send'}
                                        </button>
                                    </div>
                                </>
                            )}
                        </div>
                    )}

                    {/* Footer note */}
                    {!showEmailInput && (
                        <p className="text-center text-[9px] text-gray-300 uppercase tracking-widest font-bold pt-1">
                            Secured · {settings?.store_name || 'Store'} Vault
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}


// ── Use Modal ─────────────────────────────────────────────────────────────
function UseModal({ card, onClose }: { card: GiftCard; onClose: () => void }) {
    const [codeCopied, setCodeCopied] = useState(false);

    const copyCode = () => {
        navigator.clipboard.writeText(card.plain_code);
        setCodeCopied(true);
        setTimeout(() => setCodeCopied(false), 2000);
    };

    return (
        <div className="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300" onClick={onClose} />
            <div className="relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300">

                {/* Header */}
                <div className="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">Use Gift Card</h3>
                    <button onClick={onClose} className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                        <X className="w-4 h-4 text-gray-400" />
                    </button>
                </div>

                <div className="p-6 space-y-5">
                    <p className="text-xs text-gray-400">Copy the code below and paste it at checkout to apply your balance.</p>

                    <div>
                        <p className="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Redemption Code</p>
                        <div className="bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center justify-between">
                            <p className="font-mono text-sm font-bold tracking-[0.15em] text-gray-900">
                                {card.plain_code}
                            </p>
                            <button
                                onClick={copyCode}
                                className={`w-9 h-9 rounded-lg flex items-center justify-center transition-all ${codeCopied ? 'bg-emerald-500 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-100'}`}
                            >
                                {codeCopied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                            </button>
                        </div>
                    </div>

                    <a
                        href="/shop"
                        className="flex items-center justify-center gap-2.5 w-full py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 transition-colors"
                    >
                        Shop & Redeem Now
                    </a>

                    <p className="text-center text-[9px] text-gray-300 uppercase tracking-widest font-bold">
                        ₹{Number(card.remaining_amount).toLocaleString()} available balance
                    </p>
                </div>
            </div>
        </div>
    );
}

// ── Assign Modal ─────────────────────────────────────────────────────────────
function AssignModal({ card, onClose, onSuccess }: { card: GiftCard; onClose: () => void; onSuccess: () => void }) {
    const [identifier, setIdentifier] = useState('');
    const [foundUser, setFoundUser] = useState<any>(null);
    const [searching, setSearching] = useState(false);
    const [assigning, setAssigning] = useState(false);
    const [confirmed, setConfirmed] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const lookup = async () => {
        if (!identifier.trim()) return;
        setSearching(true); setError(''); setFoundUser(null);
        try {
            const r = await api.post('/api/gift-cards/lookup-user', { identifier });
            setFoundUser(r.data.user);
        } catch (e: any) {
            setError(e.response?.data?.message || 'User not found.');
        } finally { setSearching(false); }
    };

    const assign = async () => {
        if (!confirmed || !foundUser) return;
        setAssigning(true); setError('');
        try {
            await api.post('/api/gift-cards/assign', { gift_card_id: card.id, recipient_id: foundUser.id });
            setSuccess(`Gift card sent to ${foundUser.name}!`);
            setTimeout(() => { onSuccess(); onClose(); }, 1500);
        } catch (e: any) {
            setError(e.response?.data?.message || 'Transaction failed.');
        } finally { setAssigning(false); }
    };

    return (
        <div className="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300" onClick={onClose} />
            <div className="relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300">

                {/* Header */}
                <div className="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">Transfer Gift Card</h3>
                    <button onClick={onClose} className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                        <X className="w-4 h-4 text-gray-400" />
                    </button>
                </div>

                {!success ? (
                    <div className="p-6 space-y-4">
                        {!foundUser ? (
                            <>
                                <p className="text-xs text-gray-400">Enter the recipient's email or phone to find their account.</p>
                                <div className="flex gap-2">
                                    <input
                                        value={identifier}
                                        onChange={e => setIdentifier(e.target.value)}
                                        onKeyDown={e => e.key === 'Enter' && lookup()}
                                        placeholder="Email or phone"
                                        className="flex-1 bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black/10 focus:border-gray-300"
                                    />
                                    <button
                                        onClick={lookup}
                                        disabled={searching || !identifier.trim()}
                                        className="px-5 py-3 bg-black text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-800 disabled:opacity-30 transition-colors"
                                    >
                                        {searching ? '…' : 'Find'}
                                    </button>
                                </div>
                                {error && (
                                    <p className="text-xs text-red-500 flex items-center gap-1.5">
                                        <AlertCircle className="w-3.5 h-3.5 shrink-0" />{error}
                                    </p>
                                )}
                            </>
                        ) : (
                            <>
                                {/* Recipient card */}
                                <div className="bg-gray-50 border border-gray-100 rounded-xl p-4">
                                    <p className="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Sending to</p>
                                    <p className="font-bold text-gray-900">{foundUser.name}</p>
                                    <p className="text-xs text-gray-500">{foundUser.email}</p>
                                </div>

                                {/* Warning */}
                                <div className="flex gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                                    <AlertCircle className="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                                    <p className="text-xs text-amber-700 leading-relaxed">
                                        This transfer is <strong>permanent</strong>. You will lose access to this gift card immediately.
                                    </p>
                                </div>

                                {/* Confirm checkbox */}
                                <label className="flex items-center gap-3 cursor-pointer">
                                    <div className={`w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0 ${confirmed ? 'bg-black border-black' : 'border-gray-200'}`}>
                                        {confirmed && <Check className="w-3 h-3 text-white" />}
                                    </div>
                                    <input type="checkbox" checked={confirmed} onChange={e => setConfirmed(e.target.checked)} className="hidden" />
                                    <span className="text-xs text-gray-600 font-bold">I confirm this transfer</span>
                                </label>

                                {error && <p className="text-xs text-red-500">{error}</p>}

                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        onClick={() => setFoundUser(null)}
                                        className="flex items-center justify-center py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-500"
                                    >
                                        Back
                                    </button>
                                    <button
                                        onClick={assign}
                                        disabled={!confirmed || assigning}
                                        className="flex items-center justify-center py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 disabled:opacity-40 transition-colors"
                                    >
                                        {assigning ? 'Sending…' : 'Transfer'}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                ) : (
                    <div className="p-8 text-center">
                        <div className="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <CheckCircle className="w-6 h-6" />
                        </div>
                        <p className="font-bold text-gray-900 mb-1">Transfer Complete</p>
                        <p className="text-xs text-gray-400">{success}</p>
                    </div>
                )}
            </div>
        </div>
    );
}

// ── Card Chip ────────────────────────────────────────────────────────────────
function GiftCardChip({ card, onShare, onAssign, onUse }: {
    card: GiftCard;
    onShare: (card: GiftCard) => void;
    onAssign: (card: GiftCard) => void;
    onUse: (card: GiftCard) => void;
}) {
    return (
        <div className={`bg-white border border-gray-200 rounded-2xl p-6 transition-all hover:shadow-sm flex flex-col justify-between ${card.is_redeemable ? 'opacity-100' : 'opacity-60 grayscale'}`}>
            <div className="flex justify-between items-start mb-6">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 shrink-0">
                        <Gift className="w-5 h-5 text-gray-400" />
                    </div>
                    <div>
                        <p className="text-sm font-bold text-gray-900">{card.template_name ?? 'Gift Card'}</p>
                        <p className="font-mono text-xs text-gray-500 mt-0.5">{card.card_number}</p>
                    </div>
                </div>
                <span className={`px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shrink-0 ${
                    card.status === 'active' ? 'bg-emerald-50 text-emerald-700' :
                    card.status === 'partially_used' ? 'bg-amber-50 text-amber-700' :
                    'bg-gray-100 text-gray-500'
                }`}>
                    {card.status_badge.label}
                </span>
            </div>

            <div className="mb-6">
                <p className="text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Available Balance</p>
                <div className="flex items-baseline gap-1">
                    <span className="text-lg font-bold text-gray-400">₹</span>
                    <p className="text-3xl font-bold text-gray-900">{card.remaining_amount.toLocaleString()}</p>
                </div>
                {card.used_amount > 0 && (
                    <div className="mt-4 flex items-center gap-3">
                        <div className="h-1.5 flex-1 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                className="h-full bg-gray-900 rounded-full" 
                                style={{ width: `${(card.remaining_amount / card.amount) * 100}%` }}
                            />
                        </div>
                        <p className="text-[10px] text-gray-500 font-bold uppercase tracking-widest whitespace-nowrap">
                            Used ₹{card.used_amount.toLocaleString()}
                        </p>
                    </div>
                )}
            </div>

            <div className="flex items-center justify-between pt-4 border-t border-gray-100 mt-auto">
                <div className="text-[11px] font-semibold text-gray-500 flex items-center gap-1.5">
                    <Clock className="w-3.5 h-3.5" />
                    {card.expires_at ? `Exp ${new Date(card.expires_at).toLocaleDateString()}` : 'No Expiry'}
                </div>
                {card.is_redeemable && (
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => onUse(card)}
                            className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-all border border-gray-200"
                        >
                            Use
                        </button>
                        <button
                            onClick={() => onShare(card)}
                            className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-all border border-gray-200"
                        >
                            Share
                        </button>
                        <button
                            onClick={() => onAssign(card)}
                            className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-black text-white px-4 py-2 rounded-xl hover:bg-gray-800 transition-all shadow-sm"
                        >
                            Transfer
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

// ── Page ─────────────────────────────────────────────────────────────────────
export default function MyGiftCardsPage() {
    const { props } = usePage();
    const settings = props.settings as any;
    const { user } = useAuthStore();
    const [mounted, setMounted] = useState(false);
    const [cards, setCards] = useState<GiftCard[]>([]);
    const [wallet, setWallet] = useState<WalletSummary | null>(null);
    const [loading, setLoading] = useState(true);
    const [shareCard, setShareCard] = useState<GiftCard | null>(null);
    const [assignCard, setAssignCard] = useState<GiftCard | null>(null);
    const [useCard, setUseCard] = useState<GiftCard | null>(null);

    const load = async () => {
        setLoading(true);
        try {
            const [cardsRes, walletRes] = await Promise.all([
                api.get('/api/gift-cards/my-cards'),
                api.get('/api/gift-cards/wallet'),
            ]);
            setCards(cardsRes.data);
            setWallet(walletRes.data);
        } catch { }
        finally { setLoading(false); }
    };

    useEffect(() => { setMounted(true); if (user) load(); }, [user]);

    if (!mounted) return null;

    if (!user) return (
        <div className="min-h-screen flex flex-col items-center justify-center px-4 bg-gray-50/50">
            <div className="w-24 h-24 bg-white rounded-[2.5rem] border border-gray-100 flex items-center justify-center mb-10 shadow-2xl shadow-gray-100 relative overflow-hidden group">
                <div className="absolute inset-0 bg-noise opacity-5 pointer-events-none" />
                <Gift className="w-10 h-10 text-gray-200 group-hover:scale-110 transition-transform" />
            </div>
            <h1 className="text-4xl font-black text-gray-900 mb-4 tracking-tight">Identity Required</h1>
            <p className="text-gray-400 text-base font-medium mb-10">Sign in to access your digital vault and assets.</p>
            <Link href="/login" className="bg-black text-white px-12 py-5 rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.3em] hover:bg-gray-800 transition-all flex items-center gap-4 shadow-2xl shadow-gray-200 active:scale-95">
                Authorize Access <ArrowRight className="w-5 h-5" />
            </Link>
        </div>
    );

    return (
        <div className="min-h-screen bg-white pb-20 selection:bg-black selection:text-white">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 py-12">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 tracking-tight mb-2">My Wallet</h1>
                        <p className="text-gray-500 text-sm">Manage your gift cards, balances, and transfers.</p>
                    </div>
                    <div className="shrink-0 flex items-center gap-4">
                        <Link href="/gift-cards"
                            className="inline-flex items-center gap-2 bg-black text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-gray-800 transition-all">
                            <Gift className="w-4 h-4" /> Buy Gift Card
                        </Link>
                    </div>
                </div>

                {/* Refined Wallet Summary */}
                {wallet && (
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12">
                        {[
                            { label: 'Total Balance', value: `₹${wallet.total_balance.toLocaleString()}`, icon: <Wallet className="w-4 h-4 text-gray-600" /> },
                            { label: 'Total Gifted', value: `₹${wallet.gifted_amount.toLocaleString()}`, icon: <History className="w-4 h-4 text-gray-600" /> },
                            { label: 'Active Cards', value: wallet.active_cards, icon: <LayoutGrid className="w-4 h-4 text-gray-600" /> },
                        ].map((stat, i) => (
                            <div key={i} className="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100">
                                        {stat.icon}
                                    </div>
                                    <p className="text-xs font-bold uppercase tracking-wider text-gray-500">{stat.label}</p>
                                </div>
                                <p className="text-3xl font-black text-gray-900">{stat.value}</p>
                            </div>
                        ))}
                    </div>
                )}

                {/* Cards Grid Container */}
                <div className="mb-12">
                    <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div className="flex items-center gap-3">
                            <h2 className="text-lg font-bold text-gray-900">Your Cards</h2>
                            <span className="bg-gray-100 text-xs font-bold px-2.5 py-0.5 rounded-md text-gray-500">
                                {cards.length}
                            </span>
                        </div>
                    </div>

                    {loading ? (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {[1, 2].map(i => <div key={i} className="h-64 rounded-2xl bg-gray-50 border border-gray-100 animate-pulse" />)}
                        </div>
                    ) : cards.length === 0 ? (
                        <div className="text-center py-20 bg-gray-50 border border-dashed border-gray-200 rounded-2xl">
                            <Gift className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <h3 className="text-lg font-bold text-gray-900 mb-2">No Gift Cards</h3>
                            <p className="text-gray-500 text-sm mb-6 max-w-xs mx-auto">You don't have any gift cards in your wallet yet.</p>
                            <Link href="/gift-cards" className="inline-flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all">
                                Buy a Gift Card <ArrowRight className="w-4 h-4" />
                            </Link>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {cards.map(card => (
                                <GiftCardChip
                                    key={card.id}
                                    card={card}
                                    onShare={setShareCard}
                                    onAssign={setAssignCard}
                                    onUse={setUseCard}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {shareCard && <ShareModal card={shareCard} onClose={() => setShareCard(null)} settings={settings} />}
            {assignCard && <AssignModal card={assignCard} onClose={() => setAssignCard(null)} onSuccess={load} />}
            {useCard && <UseModal card={useCard} onClose={() => setUseCard(null)} />}
        </div>
    );
}
