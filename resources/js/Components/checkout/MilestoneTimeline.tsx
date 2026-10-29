import React, { useRef, useState, useEffect, useCallback, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { Sparkles, Check, Lock, ArrowRight } from 'lucide-react';
import Confetti from 'react-confetti';

export interface MilestoneCoupon {
    id: number;
    code: string;
    name?: string;
    type: 'percentage' | 'fixed' | 'free_shipping' | 'bogo';
    discount_amount: number;
    min_cart_value: number;
    max_discount_amount?: number | null;
}

export interface MilestoneSettings {
    milestone_title?: string;
    milestone_bg_type?: 'single' | 'gradient';
    milestone_bg_color?: string;
    milestone_bg_from?: string;
    milestone_bg_to?: string;
    milestone_bg_direction?: string;
    milestone_border_color?: string;
    milestone_progress_color?: string;
    milestone_track_color?: string;
    milestone_unlocked_circle_color?: string;
    milestone_unlocked_icon_color?: string;
    milestone_target_circle_color?: string;
    milestone_target_circle_bg?: string;
    milestone_badge_bg?: string;
    milestone_badge_text_color?: string;
    milestone_badge_border_color?: string;
    milestone_target_badge_bg?: string;
    milestone_target_badge_text?: string;
}

interface MilestoneTimelineProps {
    subtotal: number;
    milestones: MilestoneCoupon[];
    appliedCoupon: { code: string; discountAmount: number } | null;
    onApply: (code: string) => Promise<void> | void;
    applying?: boolean;
    showContinueShoppingLink?: boolean;
    customSettings?: MilestoneSettings;
}

export function MilestoneTimeline({
    subtotal,
    milestones,
    appliedCoupon,
    onApply,
    applying = false,
    showContinueShoppingLink = false,
    customSettings,
}: MilestoneTimelineProps) {
    let pageProps: any = null;
    try {
        pageProps = usePage()?.props;
    } catch {
        // Fallback when rendered outside Inertia
    }

    const s: MilestoneSettings = customSettings || pageProps?.settings || {};

    const title = s.milestone_title || 'Savings Milestones';
    const bgType = s.milestone_bg_type || 'single';
    const bgColor = s.milestone_bg_color || '#ffffff';
    const bgFrom = s.milestone_bg_from || '#ffffff';
    const bgTo = s.milestone_bg_to || '#f9fafb';
    const bgDirection = s.milestone_bg_direction || 'to bottom right';
    const borderColor = s.milestone_border_color || '#f3f4f6';

    const containerBackground = bgType === 'gradient'
        ? `linear-gradient(${bgDirection}, ${bgFrom}, ${bgTo})`
        : bgColor;

    const progressColor = s.milestone_progress_color || '#10b981';
    const trackColor = s.milestone_track_color || '#f3f4f6';

    const unlockedCircleColor = s.milestone_unlocked_circle_color || '#059669';
    const unlockedIconColor = s.milestone_unlocked_icon_color || '#ffffff';

    const targetCircleColor = s.milestone_target_circle_color || '#10b981';
    const targetCircleBg = s.milestone_target_circle_bg || '#ffffff';

    const badgeBg = s.milestone_badge_bg || '#ecfdf5';
    const badgeTextColor = s.milestone_badge_text_color || '#047857';
    const badgeBorderColor = s.milestone_badge_border_color || '#a7f3d0';

    const targetBadgeBg = s.milestone_target_badge_bg || '#f9fafb';
    const targetBadgeText = s.milestone_target_badge_text || '#374151';

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
            return 'FREE SHIPPING';
        }
        return 'SPECIAL OFFER';
    };

    // Node position getter (ensures left & right labels never clip container edges)
    const getNodePosition = (idx: number) => {
        if (n === 1) return 50;
        const margin = n === 2 ? 22 : 14;
        const span = 100 - margin * 2;
        return margin + (idx / (n - 1)) * span;
    };

    // Calculate progress percentage along the timeline
    const calculateProgress = () => {
        if (subtotal <= 0) return 0;
        if (subtotal >= maxMilestoneValue) return 100;

        const nodePositions = validMilestones.map((_, idx) => getNodePosition(idx));

        // Before first milestone
        const firstMin = Number(validMilestones[0].min_cart_value);
        if (subtotal < firstMin) {
            const firstPos = nodePositions[0];
            return Math.max(0, (subtotal / firstMin) * firstPos);
        }

        // Interpolate between milestones
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
    const diffToNext = nextMilestone ? Math.max(0, Math.ceil(Number(nextMilestone.min_cart_value) - subtotal)) : 0;

    const isApplied = (code: string) => {
        return appliedCoupon?.code?.toUpperCase() === code?.toUpperCase();
    };

    const containerRef = useRef<HTMLDivElement>(null);
    const [containerSize, setContainerSize] = useState({ width: 0, height: 0 });
    const [showPopper, setShowPopper] = useState(false);
    const [justUnlocked, setJustUnlocked] = useState<MilestoneCoupon | null>(null);

    const prevBestIdRef = useRef<number | null>(null);
    const prevSubtotalRef = useRef<number>(subtotal);
    const isInitialRender = useRef<boolean>(true);

    const updateContainerSize = useCallback(() => {
        if (containerRef.current) {
            setContainerSize({
                width: containerRef.current.offsetWidth,
                height: containerRef.current.offsetHeight,
            });
        }
    }, []);

    useEffect(() => {
        updateContainerSize();
        window.addEventListener('resize', updateContainerSize);
        return () => window.removeEventListener('resize', updateContainerSize);
    }, [updateContainerSize]);

    // Detect when user crosses a new milestone upwards
    useEffect(() => {
        if (isInitialRender.current) {
            isInitialRender.current = false;
            prevBestIdRef.current = bestUnlocked ? bestUnlocked.id : null;
            prevSubtotalRef.current = subtotal;
            return;
        }

        if (bestUnlocked && subtotal > prevSubtotalRef.current) {
            const isNewHigherMilestone = !prevBestIdRef.current || bestUnlocked.id !== prevBestIdRef.current;
            if (isNewHigherMilestone) {
                updateContainerSize();
                setJustUnlocked(bestUnlocked);
                setShowPopper(true);

                const timer = setTimeout(() => {
                    setShowPopper(false);
                    setJustUnlocked(null);
                }, 4000);

                return () => clearTimeout(timer);
            }
        }

        prevBestIdRef.current = bestUnlocked ? bestUnlocked.id : null;
        prevSubtotalRef.current = subtotal;
    }, [bestUnlocked?.id, subtotal, updateContainerSize]);

    return (
        <div
            ref={containerRef}
            className={`relative overflow-hidden rounded-2xl p-4 sm:p-5 transition-all duration-300 mb-4 border ${
                justUnlocked ? 'ring-2 ring-emerald-400 shadow-md' : 'shadow-xs'
            }`}
            style={{
                background: containerBackground,
                borderColor: borderColor,
            }}
        >
            {/* Confetti & Party Poppers */}
            {showPopper && typeof window !== 'undefined' && containerSize.width > 0 && (
                <div className="absolute inset-0 pointer-events-none z-30 overflow-hidden rounded-2xl">
                    <Confetti
                        width={containerSize.width}
                        height={containerSize.height}
                        recycle={false}
                        numberOfPieces={220}
                        gravity={0.22}
                        initialVelocityY={16}
                        initialVelocityX={10}
                        friction={0.98}
                        colors={['#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#14b8a6', '#ef4444', '#fbbf24', '#6366f1']}
                    />
                </div>
            )}

            {showPopper && (
                <>
                    {/* Bottom-left & bottom-right primary party poppers */}
                    <div className="absolute -bottom-2 -left-2 text-2xl sm:text-4xl pointer-events-none z-30 select-none milestone-popper-left">
                        🎉
                    </div>
                    <div className="absolute -bottom-2 -right-2 text-2xl sm:text-4xl pointer-events-none z-30 select-none milestone-popper-right">
                        🎉
                    </div>

                    {/* Top-corner celebratory party poppers */}
                    <div className="absolute -top-1 -left-1 text-xl sm:text-2xl pointer-events-none z-30 select-none milestone-popper-top-left">
                        🎉
                    </div>
                    <div className="absolute -top-1 -right-1 text-xl sm:text-2xl pointer-events-none z-30 select-none milestone-popper-top-right">
                        🎉
                    </div>

                    {/* Mid confetti balls */}
                    <div className="absolute bottom-5 left-10 text-xl sm:text-2xl pointer-events-none z-30 select-none milestone-popper-float-1">
                        🎊
                    </div>
                    <div className="absolute bottom-5 right-10 text-xl sm:text-2xl pointer-events-none z-30 select-none milestone-popper-float-2">
                        🎊
                    </div>

                    {/* Festive sparkle stars */}
                    <div className="absolute top-2 left-1/4 text-lg pointer-events-none z-30 select-none milestone-spark-1">
                        ✨
                    </div>
                    <div className="absolute top-2 right-1/4 text-lg pointer-events-none z-30 select-none milestone-spark-2">
                        ✨
                    </div>
                </>
            )}

            {/* Celebration Banner when a milestone is crossed */}
            {justUnlocked && (
                <div className="mb-3 flex items-center justify-between gap-2 px-3 py-2 bg-gradient-to-r from-emerald-500/15 via-teal-500/15 to-emerald-500/15 border border-emerald-500/30 rounded-xl text-emerald-900 text-xs font-semibold shadow-xs">
                    <div className="flex items-center gap-2">
                        <span className="text-base animate-bounce">🎉</span>
                        <span>
                            <strong>Milestone Unlocked!</strong> You earned{' '}
                            <span className="font-bold text-emerald-700 underline underline-offset-2">{formatDiscount(justUnlocked)}</span>
                        </span>
                    </div>
                    <span className="text-sm shrink-0">✨</span>
                </div>
            )}

            {/* Header: Micro-badge & Add Items on top only once */}
            <div className="flex items-center justify-between gap-3 mb-2">
                <div className="flex items-center gap-1.5">
                    <span className="flex h-4 w-4 items-center justify-center rounded-full bg-gray-900 text-white">
                        <Sparkles size={9} />
                    </span>
                    <span className="text-[11px] font-bold text-gray-700 uppercase tracking-widest">
                        {title}
                    </span>
                </div>

                {nextMilestone && (
                    <a
                        href="/shop"
                        className="inline-flex items-center gap-1 text-[11px] font-bold text-gray-900 hover:text-black underline underline-offset-2 shrink-0 transition-colors"
                    >
                        Add Items <ArrowRight size={11} />
                    </a>
                )}
            </div>

            {/* Followed by: Dynamic Encouragement (Add ₹XXX more for XX% OFF) */}
            <div className="mb-4">
                {nextMilestone ? (
                    <p className="text-xs text-gray-600 font-medium leading-relaxed">
                        {bestUnlocked ? (
                            <>
                                <span className="font-semibold text-emerald-700">✓ {formatDiscount(bestUnlocked)} unlocked! </span>
                                Add <strong className="font-bold text-gray-900">₹{diffToNext.toLocaleString('en-IN')}</strong> more for <span className="font-bold text-gray-900">{formatDiscount(nextMilestone)}</span>
                            </>
                        ) : (
                            <>
                                Add <strong className="font-bold text-gray-900">₹{diffToNext.toLocaleString('en-IN')}</strong> more to unlock <span className="font-bold text-emerald-700">{formatDiscount(nextMilestone)}</span>
                            </>
                        )}
                    </p>
                ) : (
                    <p className="text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                        <Check size={13} strokeWidth={3} />
                        <span>Maximum savings unlocked ({bestUnlocked ? formatDiscount(bestUnlocked) : 'Best offer'})</span>
                    </p>
                )}
            </div>

            {/* Progress Track & Checkpoints: Perfectly center-aligned on vertical axis */}
            <div className="relative h-6 flex items-center mb-9 px-1">
                {/* Background Track - automatically vertically centered inside h-6 */}
                <div
                    className="h-1.5 w-full rounded-full overflow-hidden"
                    style={{ backgroundColor: trackColor }}
                >
                    <div
                        className="h-full rounded-full transition-all duration-500 ease-out"
                        style={{
                            width: `${Math.min(100, Math.max(0, progressPercent))}%`,
                            backgroundColor: progressColor,
                        }}
                    />
                </div>

                {/* Milestone Checkpoint Nodes: Centered exactly at h-6 centerline */}
                {validMilestones.map((m, idx) => {
                    const pos = getNodePosition(idx);
                    const unlocked = subtotal >= Number(m.min_cart_value);
                    const isNext = nextMilestone?.id === m.id;
                    const applied = isApplied(m.code);
                    const isJustUnlockedNode = justUnlocked?.id === m.id;

                    return (
                        <div
                            key={m.id || idx}
                            className="absolute top-0 h-6 flex items-center justify-center -translate-x-1/2 select-none"
                            style={{ left: `${pos}%` }}
                        >
                            {/* Checkpoint Dot: exactly centered on the track */}
                            <button
                                type="button"
                                disabled={!unlocked || applying}
                                onClick={() => unlocked && onApply(m.code)}
                                title={
                                    unlocked
                                        ? applied
                                            ? `${m.code} applied`
                                            : `Click to apply ${m.code}`
                                        : `Spend ₹${Number(m.min_cart_value).toLocaleString('en-IN')} to unlock`
                                }
                                style={{
                                    backgroundColor: (applied || unlocked)
                                        ? unlockedCircleColor
                                        : isNext
                                        ? targetCircleBg
                                        : '#ffffff',
                                    borderColor: (applied || unlocked)
                                        ? '#ffffff'
                                        : isNext
                                        ? targetCircleColor
                                        : '#e5e7eb',
                                    color: (applied || unlocked)
                                        ? unlockedIconColor
                                        : isNext
                                        ? targetCircleColor
                                        : '#9ca3af',
                                }}
                                className={`relative flex h-6 w-6 items-center justify-center rounded-full border-2 transition-all duration-200 shadow-xs ${
                                    unlocked ? 'cursor-pointer hover:scale-105' : 'cursor-default'
                                }`}
                            >
                                {isJustUnlockedNode && (
                                    <span className="absolute -inset-1.5 rounded-full bg-emerald-400 opacity-75 animate-ping pointer-events-none" />
                                )}
                                {applied || unlocked ? (
                                    <Check size={11} strokeWidth={3} />
                                ) : isNext ? (
                                    <span
                                        className="h-2 w-2 rounded-full"
                                        style={{ backgroundColor: targetCircleColor }}
                                    />
                                ) : (
                                    <Lock size={10} strokeWidth={2.5} />
                                )}
                            </button>

                            {/* Node Labels below the circle */}
                            <div 
                                onClick={() => unlocked && onApply(m.code)}
                                className={`absolute top-7 flex flex-col items-center whitespace-nowrap ${
                                    unlocked ? 'cursor-pointer group/label' : 'pointer-events-none'
                                }`}
                            >
                                <span
                                    className={`text-[11px] font-semibold leading-tight ${
                                        unlocked
                                            ? 'text-gray-900'
                                            : isNext
                                            ? 'text-gray-800'
                                            : 'text-gray-400'
                                    }`}
                                >
                                    ₹{Number(m.min_cart_value).toLocaleString('en-IN')}
                                </span>
                                <span
                                    style={{
                                        backgroundColor: (applied || unlocked)
                                            ? badgeBg
                                            : isNext
                                            ? targetBadgeBg
                                            : '#f9fafb',
                                        color: (applied || unlocked)
                                            ? badgeTextColor
                                            : isNext
                                            ? targetBadgeText
                                            : '#9ca3af',
                                        borderColor: (applied || unlocked)
                                            ? badgeBorderColor
                                            : '#e5e7eb',
                                    }}
                                    className="text-[9px] font-bold tracking-tight px-1.5 py-0.5 rounded mt-1 border transition-colors"
                                >
                                    {formatDiscount(m)}
                                </span>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Bottom Status: Clean applied confirmation or 1-click apply action */}
            {bestUnlocked && (
                <div className="mt-6 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    {appliedCoupon?.code?.toUpperCase() === bestUnlocked.code.toUpperCase() ? (
                        <>
                            <div className="flex items-center gap-2 text-gray-600">
                                <span
                                    className="flex h-1.5 w-1.5 rounded-full"
                                    style={{ backgroundColor: progressColor }}
                                />
                                <span>
                                    Code <span className="font-mono font-semibold text-gray-900">{bestUnlocked.code}</span> applied
                                </span>
                            </div>
                            <span
                                style={{
                                    backgroundColor: badgeBg,
                                    color: badgeTextColor,
                                    borderColor: badgeBorderColor,
                                }}
                                className="text-[11px] font-bold px-2 py-0.5 rounded-full border"
                            >
                                {formatDiscount(bestUnlocked)}
                            </span>
                        </>
                    ) : (
                        <>
                            <div className="flex items-center gap-2 text-gray-600">
                                <span
                                    className="flex h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"
                                />
                                <span>
                                    Code <span className="font-mono font-semibold text-gray-900">{bestUnlocked.code}</span> unlocked!
                                </span>
                            </div>
                            <button
                                type="button"
                                onClick={() => onApply(bestUnlocked.code)}
                                disabled={applying}
                                style={{
                                    backgroundColor: badgeBg,
                                    color: badgeTextColor,
                                    borderColor: badgeBorderColor,
                                }}
                                className="text-[11px] font-bold px-2.5 py-1 rounded-full border hover:opacity-80 active:scale-95 transition-all flex items-center gap-1 cursor-pointer"
                            >
                                {applying ? 'Applying...' : `Apply ${formatDiscount(bestUnlocked)} →`}
                            </button>
                        </>
                    )}
                </div>
            )}

            <style>{`
                @keyframes popperLeft {
                    0% { transform: scale(0.2) rotate(-35deg) translate(-10px, 15px); opacity: 0; }
                    50% { transform: scale(1.3) rotate(-10deg) translate(0, -6px); opacity: 1; }
                    75% { transform: scale(0.95) rotate(-15deg); opacity: 1; }
                    100% { transform: scale(1) rotate(-12deg); opacity: 1; }
                }
                @keyframes popperRight {
                    0% { transform: scaleX(-1) scale(0.2) rotate(-35deg) translate(-10px, 15px); opacity: 0; }
                    50% { transform: scaleX(-1) scale(1.3) rotate(-10deg) translate(0, -6px); opacity: 1; }
                    75% { transform: scaleX(-1) scale(0.95) rotate(-15deg); opacity: 1; }
                    100% { transform: scaleX(-1) scale(1) rotate(-12deg); opacity: 1; }
                }
                @keyframes popperPopFloat {
                    0% { transform: scale(0) translateY(12px); opacity: 0; }
                    40% { transform: scale(1.3) translateY(-8px); opacity: 1; }
                    70% { transform: scale(0.9) translateY(-2px); opacity: 1; }
                    100% { transform: scale(1) translateY(0); opacity: 1; }
                }
                @keyframes sparkShimmer {
                    0% { transform: scale(0) rotate(0deg); opacity: 0; }
                    50% { transform: scale(1.4) rotate(90deg); opacity: 1; }
                    100% { transform: scale(1) rotate(180deg); opacity: 0.9; }
                }
                .milestone-popper-left {
                    animation: popperLeft 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
                }
                .milestone-popper-right {
                    animation: popperRight 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
                }
                .milestone-popper-top-left {
                    animation: popperLeft 0.6s 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                }
                .milestone-popper-top-right {
                    animation: popperRight 0.6s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                }
                .milestone-popper-float-1 {
                    animation: popperPopFloat 0.7s 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                }
                .milestone-popper-float-2 {
                    animation: popperPopFloat 0.7s 0.18s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                }
                .milestone-spark-1 {
                    animation: sparkShimmer 0.8s 0.2s ease-out both;
                }
                .milestone-spark-2 {
                    animation: sparkShimmer 0.8s 0.28s ease-out both;
                }
            `}</style>
        </div>
    );
}
