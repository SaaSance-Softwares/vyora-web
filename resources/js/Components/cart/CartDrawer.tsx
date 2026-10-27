import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { ShoppingBag, X, Plus, Minus, Trash2, ArrowRight, Sparkles, Check, Loader2 } from 'lucide-react';
import { useCartStore, CartItem } from '@/store/cart';
import { useCartDrawerStore } from '@/store/cartDrawer';
import { useUIStore } from '@/store/ui';
import { MilestoneTimeline, MilestoneCoupon } from '@/Components/checkout/MilestoneTimeline';
import { formatPrice } from '@/lib/utils';
import { trackAddToCart } from '@/lib/tracking';
import api from '@/lib/api';

interface RecommendedProduct {
    id: number;
    name: string;
    slug: string;
    price: number;
    mrp?: number;
    image?: string;
    first_sku_id?: number;
    first_sku_code?: string;
    color_name?: string;
    color_hex?: string;
    size_name?: string;
    has_multiple_variants?: boolean;
    is_exchangeable?: boolean;
    exchange_days?: number | null;
    is_returnable?: boolean;
    return_days?: number | null;
}

export function CartDrawer() {
    const { isOpen, closeCartDrawer } = useCartDrawerStore();
    const cart = useCartStore();
    const { openQuickView } = useUIStore();
    const { settings: sharedSettings } = usePage<any>().props;

    const drawerEnabled = (sharedSettings?.cart_drawer_enabled ?? '1') !== '0';
    const showMilestones = (sharedSettings?.cart_drawer_show_milestones ?? '1') !== '0';
    const showRecommendations = (sharedSettings?.cart_drawer_show_recommendations ?? '1') !== '0';
    const drawerTitle = sharedSettings?.cart_drawer_title || 'Your Bag';
    const recommendationsTitle = sharedSettings?.cart_drawer_recommendations_title || 'Pairs Well With';

    const [milestones, setMilestones] = useState<MilestoneCoupon[]>([]);
    const [recommendedProducts, setRecommendedProducts] = useState<RecommendedProduct[]>([]);
    const [nextMilestoneData, setNextMilestoneData] = useState<any>(null);
    const [loadingRecommendations, setLoadingRecommendations] = useState(false);
    const [addingProductId, setAddingProductId] = useState<number | null>(null);
    const [applyingCoupon, setApplyingCoupon] = useState(false);

    const subtotal = cart.items.reduce((sum, item) => sum + (Number(item.price) * (item.quantity || 1)), 0);
    const totalItemsCount = cart.items.reduce((sum, item) => sum + (item.quantity || 1), 0);

    // Lock body scroll when drawer is open
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    // Handle ESC key to close
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && isOpen) {
                closeCartDrawer();
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, closeCartDrawer]);

    // Fetch Milestone coupons
    useEffect(() => {
        if (!showMilestones) return;
        api.get('/api/coupons/public')
            .then(res => {
                const fetchedMilestones: MilestoneCoupon[] = res.data.milestone_coupons || [];
                setMilestones(fetchedMilestones);
            })
            .catch(() => {});
    }, [showMilestones]);

    // Fetch Recommended Products
    const fetchRecommendations = useCallback(async () => {
        if (!showRecommendations) return;
        setLoadingRecommendations(true);
        try {
            const productIds = cart.items.map(i => i.productId).join(',');
            const res = await api.get('/api/cart/recommendations', {
                params: {
                    subtotal,
                    cart_product_ids: productIds,
                }
            });
            if (res.data?.products) {
                setRecommendedProducts(res.data.products);
                setNextMilestoneData(res.data.next_milestone || null);
            }
        } catch (e) {
            console.error('Failed to load cart recommendations', e);
        } finally {
            setLoadingRecommendations(false);
        }
    }, [showRecommendations, subtotal, cart.items]);

    // Re-fetch recommendations when drawer opens or cart changes
    useEffect(() => {
        if (isOpen) {
            fetchRecommendations();
        }
    }, [isOpen, fetchRecommendations]);

    // Apply Coupon (for milestone clicks)
    const handleApplyCoupon = async (code: string) => {
        setApplyingCoupon(true);
        try {
            const items = cart.items.map(i => ({
                product_id: i.productId,
                price: i.price,
                original_price: i.mrp || i.price,
                quantity: i.quantity
            }));
            const res = await api.post('/api/coupons/apply', {
                code,
                cart: { subtotal, items }
            });
            if (res.data.success) {
                cart.setAppliedCoupon({
                    code: res.data.data.coupon.code,
                    discountAmount: res.data.data.discount_amount
                });
            }
        } catch (e) {
            console.error('Failed to apply milestone coupon', e);
        } finally {
            setApplyingCoupon(false);
        }
    };

    // Quick add recommended product
    const handleQuickAdd = async (product: RecommendedProduct) => {
        setAddingProductId(product.id);
        try {
            if (product.has_multiple_variants) {
                // Fetch full details and open quick view modal
                const res = await api.get(`/api/products/${product.slug}`);
                const data = res.data.data || res.data;
                openQuickView(data, 'cart');
            } else {
                // Direct add single-variant SKU
                cart.addItem({
                    skuId: product.first_sku_id || product.id,
                    skuCode: product.first_sku_code,
                    productId: product.id,
                    name: product.name,
                    slug: product.slug,
                    variant: product.first_sku_code || '',
                    price: product.price,
                    mrp: product.mrp,
                    image: product.image || '',
                    quantity: 1,
                    colorName: product.color_name,
                    colorHex: product.color_hex,
                    sizeName: product.size_name,
                    exchangeDays: product.is_exchangeable ? product.exchange_days : null,
                    returnDays: product.is_returnable ? product.return_days : null,
                });
                trackAddToCart(product, 1, product.first_sku_code);
            }
        } catch (e) {
            console.error('Failed to quick-add product', e);
        } finally {
            setAddingProductId(null);
        }
    };

    const handleCheckout = () => {
        closeCartDrawer();
        router.visit('/checkout');
    };

    const handleViewCart = () => {
        closeCartDrawer();
        router.visit('/cart');
    };

    if (!drawerEnabled) return null;

    return (
        <div
            className={`fixed inset-0 z-50 transition-visibility duration-300 ${
                isOpen ? 'visible pointer-events-auto' : 'invisible pointer-events-none'
            }`}
            aria-modal="true"
            role="dialog"
        >
            {/* Backdrop Blur & Dim */}
            <div
                className={`fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-300 ease-out ${
                    isOpen ? 'opacity-100' : 'opacity-0'
                }`}
                onClick={closeCartDrawer}
            />

            {/* Slide-in Panel */}
            <div className="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
                <div
                    className={`w-screen max-w-md bg-white shadow-2xl flex flex-col h-full transform transition-transform duration-300 ease-in-out ${
                        isOpen ? 'translate-x-0' : 'translate-x-full'
                    }`}
                >
                    {/* Drawer Header */}
                    <div className="p-4 sm:px-6 border-b border-gray-100 flex items-center justify-between bg-white z-10 shrink-0">
                        <div className="flex items-center gap-2.5">
                            <div className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-900">
                                <ShoppingBag size={17} strokeWidth={2.2} />
                            </div>
                            <div>
                                <h2 className="text-base font-extrabold text-gray-900 tracking-tight leading-none">
                                    {drawerTitle}
                                </h2>
                                <span className="text-xs text-gray-400 font-medium">
                                    {totalItemsCount} {totalItemsCount === 1 ? 'item' : 'items'}
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={closeCartDrawer}
                            className="p-2 rounded-full text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                            aria-label="Close cart"
                        >
                            <X size={19} />
                        </button>
                    </div>

                    {/* Scrollable Body Content */}
                    <div className="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-5 divide-y divide-gray-100">

                        {/* 1. Milestone Timeline Section */}
                        {showMilestones && milestones.length > 0 && (
                            <div className="pt-1 first:pt-0">
                                <MilestoneTimeline
                                    subtotal={subtotal}
                                    milestones={milestones}
                                    appliedCoupon={cart.appliedCoupon}
                                    onApply={handleApplyCoupon}
                                    applying={applyingCoupon}
                                    showContinueShoppingLink={false}
                                />
                            </div>
                        )}

                        {/* 2. Cart Items List */}
                        <div className="pt-4 first:pt-0">
                            {cart.items.length === 0 ? (
                                <div className="py-12 text-center space-y-4">
                                    <div className="w-16 h-16 mx-auto rounded-full bg-gray-50 flex items-center justify-center text-gray-300">
                                        <ShoppingBag size={28} />
                                    </div>
                                    <div className="space-y-1">
                                        <p className="text-sm font-bold text-gray-900">Your bag is empty</p>
                                        <p className="text-xs text-gray-400">Add products to your bag to enjoy exclusive discounts.</p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={closeCartDrawer}
                                        className="inline-flex items-center px-4 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-xs"
                                    >
                                        Continue Shopping
                                    </button>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {cart.items.map((item) => (
                                        <div
                                            key={item.skuId}
                                            className="flex gap-3.5 p-3 rounded-2xl border border-gray-100 bg-white hover:border-gray-200 transition-colors group"
                                        >
                                            {/* Thumbnail */}
                                            <Link
                                                href={`/product/${item.slug}`}
                                                onClick={closeCartDrawer}
                                                className="w-16 h-20 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 relative block"
                                            >
                                                {item.image ? (
                                                    <img
                                                        src={item.image}
                                                        alt={item.name}
                                                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center text-gray-300 text-xs font-bold">
                                                        DOPE
                                                    </div>
                                                )}
                                            </Link>

                                            {/* Details */}
                                            <div className="flex-1 min-w-0 flex flex-col justify-between">
                                                <div>
                                                    <div className="flex justify-between items-start gap-2">
                                                        <Link
                                                            href={`/product/${item.slug}`}
                                                            onClick={closeCartDrawer}
                                                            className="text-xs font-bold text-gray-900 hover:text-gray-600 transition-colors line-clamp-2 leading-snug"
                                                        >
                                                            {item.name}
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => cart.removeItem(item.skuId)}
                                                            className="text-gray-300 hover:text-red-500 transition-colors p-0.5 shrink-0"
                                                            title="Remove item"
                                                        >
                                                            <Trash2 size={14} />
                                                        </button>
                                                    </div>

                                                    {/* Attributes / Variants */}
                                                    <div className="flex items-center gap-1.5 mt-1 flex-wrap">
                                                        {item.colorName && (
                                                            <span className="inline-flex items-center gap-1 text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">
                                                                <span
                                                                    className="w-2 h-2 rounded-full border border-white shadow-xs"
                                                                    style={{ backgroundColor: item.colorHex || '#000' }}
                                                                />
                                                                {item.colorName}
                                                            </span>
                                                        )}
                                                        {item.sizeName && (
                                                            <span className="text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">
                                                                Size {item.sizeName}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>

                                                {/* Stepper & Price */}
                                                <div className="flex items-center justify-between mt-2 pt-1 border-t border-gray-50">
                                                    <div className="flex items-center border border-gray-200 rounded-lg bg-gray-50/60 overflow-hidden">
                                                        <button
                                                            type="button"
                                                            onClick={() => cart.updateQuantity(item.skuId, Math.max(1, item.quantity - 1))}
                                                            className="px-2 py-1 text-gray-500 hover:bg-gray-200 transition-colors"
                                                            disabled={item.quantity <= 1}
                                                        >
                                                            <Minus size={11} strokeWidth={2.5} />
                                                        </button>
                                                        <span className="text-xs font-extrabold text-gray-900 w-6 text-center select-none">
                                                            {item.quantity}
                                                        </span>
                                                        <button
                                                            type="button"
                                                            onClick={() => cart.updateQuantity(item.skuId, item.quantity + 1)}
                                                            className="px-2 py-1 text-gray-500 hover:bg-gray-200 transition-colors"
                                                        >
                                                            <Plus size={11} strokeWidth={2.5} />
                                                        </button>
                                                    </div>

                                                    <div className="text-right">
                                                        <span className="text-xs font-extrabold text-gray-900">
                                                            {formatPrice(item.price * item.quantity)}
                                                        </span>
                                                        {item.mrp && item.mrp > item.price ? (
                                                            <div className="text-[10px] text-gray-400 line-through">
                                                                {formatPrice(item.mrp * item.quantity)}
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* 3. Recommended Products Section */}
                        {showRecommendations && recommendedProducts.length > 0 && (
                            <div className="pt-4 first:pt-0 space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-1.5">
                                        <Sparkles size={14} className="text-amber-500 shrink-0" />
                                        <h3 className="text-xs font-bold text-gray-900 uppercase tracking-wider">
                                            {recommendationsTitle}
                                        </h3>
                                    </div>

                                    {nextMilestoneData && nextMilestoneData.gap > 0 && (
                                        <span className="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                            Add {formatPrice(nextMilestoneData.gap)} for tier unlock!
                                        </span>
                                    )}
                                </div>

                                {/* Recommendation Cards List */}
                                <div className="space-y-2.5">
                                    {recommendedProducts.map((p) => {
                                        const isAdding = addingProductId === p.id;
                                        return (
                                            <div
                                                key={p.id}
                                                className="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white hover:border-gray-300 transition-all shadow-2xs group"
                                            >
                                                {/* Image */}
                                                <Link
                                                    href={`/product/${p.slug}`}
                                                    onClick={closeCartDrawer}
                                                    className="w-12 h-14 bg-white rounded-lg overflow-hidden shrink-0 border border-gray-100 relative block"
                                                >
                                                    {p.image ? (
                                                        <img
                                                            src={p.image}
                                                            alt={p.name}
                                                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-[9px] text-gray-300 font-bold">
                                                            DOPE
                                                        </div>
                                                    )}
                                                </Link>

                                                {/* Title and Price */}
                                                <div className="flex-1 min-w-0">
                                                    <Link
                                                        href={`/product/${p.slug}`}
                                                        onClick={closeCartDrawer}
                                                        className="text-xs font-bold text-gray-900 hover:text-gray-600 transition-colors line-clamp-1 block truncate leading-tight"
                                                    >
                                                        {p.name}
                                                    </Link>
                                                    <div className="flex items-center gap-1.5 mt-0.5">
                                                        <span className="text-xs font-extrabold text-gray-900">
                                                            {formatPrice(p.price)}
                                                        </span>
                                                        {p.mrp && p.mrp > p.price ? (
                                                            <span className="text-[10px] text-gray-400 line-through">
                                                                {formatPrice(p.mrp)}
                                                            </span>
                                                        ) : null}
                                                    </div>
                                                </div>

                                                {/* 1-Click Add Button */}
                                                <button
                                                    type="button"
                                                    onClick={() => handleQuickAdd(p)}
                                                    disabled={isAdding}
                                                    className="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-extrabold text-white bg-black hover:bg-gray-800 active:scale-95 transition-all shadow-xs shrink-0 disabled:opacity-50"
                                                >
                                                    {isAdding ? (
                                                        <Loader2 size={12} className="animate-spin" />
                                                    ) : (
                                                        <>
                                                            <Plus size={12} strokeWidth={3} />
                                                            <span>Add</span>
                                                        </>
                                                    )}
                                                </button>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Sticky Drawer Footer */}
                    {cart.items.length > 0 && (
                        <div className="p-4 sm:px-6 border-t border-gray-100 bg-white space-y-3 shrink-0 shadow-[0_-4px_16px_rgba(0,0,0,0.04)]">
                            {/* Totals */}
                            <div className="space-y-1.5">
                                <div className="flex items-center justify-between text-xs">
                                    <span className="text-gray-500 font-medium">Subtotal</span>
                                    <span className="text-base font-extrabold text-gray-900">
                                        {formatPrice(subtotal)}
                                    </span>
                                </div>

                                {cart.appliedCoupon && (
                                    <div className="flex items-center justify-between text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                        <span className="flex items-center gap-1">
                                            <Sparkles size={12} />
                                            <span>Coupon ({cart.appliedCoupon.code})</span>
                                        </span>
                                        <span>-{formatPrice(cart.appliedCoupon.discountAmount)}</span>
                                    </div>
                                )}
                            </div>

                            {/* Primary Checkout Button */}
                            <button
                                type="button"
                                onClick={handleCheckout}
                                className="w-full py-3.5 px-4 bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md hover:bg-gray-900 active:scale-[0.99] transition-all flex items-center justify-center gap-2 group"
                            >
                                <span>Proceed to Checkout</span>
                                <ArrowRight size={15} className="group-hover:translate-x-1 transition-transform" />
                            </button>

                            {/* Secondary Link to Cart Page */}
                            <div className="flex items-center justify-between pt-1">
                                <button
                                    type="button"
                                    onClick={handleViewCart}
                                    className="text-xs font-bold text-gray-500 hover:text-black underline underline-offset-4 transition-colors"
                                >
                                    View Full Cart Details
                                </button>
                                <span className="text-[10px] text-gray-400 font-medium">
                                    🔒 100% Secure Checkout
                                </span>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
