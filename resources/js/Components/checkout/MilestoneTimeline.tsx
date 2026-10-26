import React from 'react';
import { Sparkles, Check, Lock, Gift, ArrowRight } from 'lucide-react';

export interface MilestoneCoupon {
    id: number;
    code: string;
    name?: string;
    type: 'percentage' | 'fixed' | 'free_shipping' | 'bogo';
    discount_amount: number;
    min_cart_value: number;
    max_discount_amount?: number | null;
}

interface MilestoneTimelineProps {
    subtotal: number;
    milestones: MilestoneCoupon[];
    appliedCoupon: { code: string; discountAmount: number } | null;
    onApply: (code: string) => Promise<void> | void;
    applying?: boolean;
    showContinueShoppingLink?: boolean;
}

export function MilestoneTimeline({
    subtotal,
    milestones,
    appliedCoupon,
    onApply,
    applying = false,
    showContinueShoppingLink = false,
}: MilestoneTimelineProps) {
    // Only consider valid milestones with min_cart_value > 0 and sort ascending
    const validMilestones = React.useMemo(() => {
        return (milestones || [])
            .filter(m => Number(m.min_cart_value) > 0)
            .sort((a, b) => Number(a.min_cart_value) - Number(b.min_cart_value));
    }, [milestones]);

    if (!validMilestones || validMilestones.length === 0) {
        return null;
    }

    const n = validMilestones.length;
    const maxMilestoneValue = Number(validMilestones[n - 1].min_cart_value);

    // Identify unlocked & next milestone
    const unlockedMilestones = validMilestones.filter(m => subtotal >= Number(m.min_cart_value));
    const bestUnlocked = unlockedMilestones.length > 0 ? unlockedMilestones[unlockedMilestones.length - 1] : null;
    const nextMilestone = validMilestones.find(m => subtotal < Number(m.min_cart_value)) || null;

    // Helper: format discount label
    const formatDiscount = (m: MilestoneCoupon) => {
        if (m.type === 'percentage') {
            return `${Number(m.discount_amount)}% OFF`;
        }
        if (m.type === 'fixed') {
            return `₹${Number(m.discount_amount).toLocaleString('en-IN')} OFF`;
        }
        if (m.type === 'free_shipping') {
            return 'FREE SHIP';
        }
        return 'SPECIAL OFFER';
    };

    // Calculate progress percentage along the timeline
    // Each checkpoint is positioned evenly across the bar for optimal mobile & desktop rendering
    // Progress fill advances smoothly between milestones
    const calculateProgress = () => {
        if (subtotal <= 0) return 0;
        if (subtotal >= maxMilestoneValue) return 100;

        // Position nodes at fixed percentage steps: e.g. for 3 nodes: [20%, 55%, 90%] or proportional
        // Using piecewise linear interpolation between node positions
        const nodePositions = validMilestones.map((_, idx) => {
            if (n === 1) return 100;
            const margin = 12; // left/right margin in %
            const span = 100 - (margin * 2);
            return margin + (idx / (n - 1)) * span;
        });

        // If subtotal is below the first milestone
        const firstMin = Number(validMilestones[0].min_cart_value);
        if (subtotal < firstMin) {
            const firstPos = nodePositions[0];
            return Math.max(4, (subtotal / firstMin) * firstPos);
        }

        // Interpolate between the two bounding milestones
        for (let i = 0; i < n - 1; i++) {
            const lowVal = Number(validMilestones[i].min_cart_value);
            const highVal = Number(validMilestones[i + 1].min_cart_value);
            if (subtotal >= lowVal && subtotal <= highVal) {
                const lowPos = nodePositions[i];
                const highPos = nodePositions[i + 1];
                const ratio = (subtotal - lowVal) / (highVal - lowVal);
                return lowPos + ratio * (highPos - lowPos);
            }
        }

        return 100;
    };

    const progressPercent = calculateProgress();

    // Node position getter
    const getNodePosition = (idx: number) => {
        if (n === 1) return 100;
        const margin = 12;
        const span = 100 - (margin * 2);
        return margin + (idx / (n - 1)) * span;
    };

    // Shortfall message
    const diffToNext = nextMilestone ? Math.max(0, Math.ceil(Number(nextMilestone.min_cart_value) - subtotal)) : 0;

    const isApplied = (code: string) => {
        return appliedCoupon?.code?.toUpperCase() === code?.toUpperCase();
    };

    return (
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50/80 via-orange-50/40 to-emerald-50/50 border border-amber-200/70 p-4 sm:p-5 shadow-sm transition-all mb-4">
            {/* Top Header / Dynamic Encouragement message */}
            <div className="flex items-start justify-between gap-3 mb-4">
                <div className="space-y-1">
                    <div className="flex items-center gap-1.5">
                        <span className="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white shadow-xs">
                            <Sparkles size={11} className="animate-pulse" />
                        </span>
                        <h4 className="text-xs font-black uppercase tracking-wider text-amber-950">
                            Savings Milestone
                        </h4>
                    </div>
                    <p className="text-xs text-gray-700 font-medium">
                        {nextMilestone ? (
                            <>
                                {bestUnlocked ? (
                                    <>
                                        <span className="font-bold text-emerald-700">🎉 {formatDiscount(bestUnlocked)} unlocked! </span>
                                        Add <strong className="font-black text-gray-900">₹{diffToNext.toLocaleString('en-IN')}</strong> more to unlock <span className="font-black text-amber-700">{formatDiscount(nextMilestone)}</span>!
                                    </>
                                ) : (
                                    <>
                                        Add <strong className="font-black text-gray-900">₹{diffToNext.toLocaleString('en-IN')}</strong> more to unlock <span className="font-black text-amber-700">{formatDiscount(nextMilestone)}</span>!
                                    </>
                                )}
                            </>
                        ) : (
                            <span className="font-black text-emerald-700 flex items-center gap-1">
                                🏆 Awesome! Maximum savings unlocked ({bestUnlocked ? formatDiscount(bestUnlocked) : 'Best offer'})!
                            </span>
                        )}
                    </p>
                </div>

                {/* Optional Continue Shopping Link (for Cart Drawer or Cart Page) */}
                {showContinueShoppingLink && nextMilestone && (
                    <a
                        href="/shop"
                        className="hidden sm:inline-flex items-center gap-1 text-[11px] font-bold text-amber-900 hover:text-black underline underline-offset-2 shrink-0 transition-colors"
                    >
                        Add Items <ArrowRight size={11} />
                    </a>
                )}
            </div>

            {/* Progress Bar & Checkpoints */}
            <div className="relative pt-2 pb-7 px-1">
                {/* Background Track */}
                <div className="h-2 w-full bg-amber-200/50 rounded-full overflow-hidden">
                    {/* Fill */}
                    <div
                        className="h-full bg-gradient-to-r from-amber-500 via-orange-500 to-emerald-500 rounded-full transition-all duration-700 ease-out shadow-xs"
                        style={{ width: `${Math.min(100, Math.max(0, progressPercent))}%` }}
                    />
                </div>

                {/* Milestone Checkpoint Nodes */}
                {validMilestones.map((m, idx) => {
                    const pos = getNodePosition(idx);
                    const unlocked = subtotal >= Number(m.min_cart_value);
                    const isNext = nextMilestone?.id === m.id;
                    const applied = isApplied(m.code);

                    return (
                        <div
                            key={m.id || idx}
                            className="absolute -top-1.5 flex flex-col items-center -translate-x-1/2 select-none group"
                            style={{ left: `${pos}%` }}
                        >
                            {/* Circle Node Icon */}
                            <button
                                type="button"
                                disabled={!unlocked || applying}
                                onClick={() => unlocked && onApply(m.code)}
                                title={unlocked ? (applied ? 'Coupon Applied' : `Click to apply ${m.code}`) : `Spend ₹${Number(m.min_cart_value).toLocaleString('en-IN')} to unlock`}
                                className={`relative flex h-6 w-6 items-center justify-center rounded-full border-2 transition-all duration-300 shadow-sm ${
                                    applied
                                        ? 'bg-emerald-600 border-white text-white ring-2 ring-emerald-500 scale-110'
                                        : unlocked
                                        ? 'bg-emerald-500 border-white text-white hover:scale-115 hover:bg-emerald-600 cursor-pointer ring-1 ring-emerald-300'
                                        : isNext
                                        ? 'bg-white border-amber-500 text-amber-600 ring-2 ring-amber-200 animate-pulse'
                                        : 'bg-white border-gray-300 text-gray-400'
                                }`}
                            >
                                {applied ? (
                                    <Check size={12} strokeWidth={3.5} />
                                ) : unlocked ? (
                                    <Gift size={11} strokeWidth={2.5} />
                                ) : (
                                    <Lock size={10} strokeWidth={2.5} />
                                )}
                            </button>

                            {/* Node Labels */}
                            <div className="absolute top-8 flex flex-col items-center pointer-events-none whitespace-nowrap">
                                <span className={`text-[10px] font-black leading-tight ${
                                    unlocked ? 'text-emerald-700' : isNext ? 'text-amber-900 font-extrabold' : 'text-gray-400'
                                }`}>
                                    ₹{Number(m.min_cart_value).toLocaleString('en-IN')}
                                </span>
                                <span className={`text-[9px] font-extrabold tracking-tight px-1.5 py-0.5 rounded-full mt-0.5 transition-colors ${
                                    applied
                                        ? 'bg-emerald-600 text-white shadow-xs'
                                        : unlocked
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : isNext
                                        ? 'bg-amber-100 text-amber-900'
                                        : 'bg-gray-100 text-gray-500'
                                }`}>
                                    {applied ? '✓ APPLIED' : formatDiscount(m)}
                                </span>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Confirmation Banner when a Milestone is Unlocked (Auto-Applied) */}
            {bestUnlocked && (
                <div className="mt-6 pt-3 border-t border-amber-200/60 flex flex-wrap items-center justify-between gap-2">
                    <div className="text-[11px] text-gray-700 font-medium">
                        Unlocked <strong className="font-bold text-gray-900">{formatDiscount(bestUnlocked)}</strong> with code <code className="font-mono font-bold text-emerald-900 bg-emerald-100/80 px-1.5 py-0.5 rounded">{bestUnlocked.code}</code>
                    </div>
                    {isApplied(bestUnlocked.code) ? (
                        <div className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider rounded-lg shadow-xs">
                            <Check size={12} strokeWidth={3} />
                            <span>Applied</span>
                        </div>
                    ) : (
                        <div className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-lg animate-pulse">
                            <Sparkles size={12} />
                            <span>{applying ? 'Applying…' : 'Auto-applying…'}</span>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
