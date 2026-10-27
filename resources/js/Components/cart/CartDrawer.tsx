import React, { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { ShoppingBag, X, Plus, Minus, Trash2, ArrowRight, Sparkles, Check, Loader2, ArrowLeft, Ruler, ChevronDown } from 'lucide-react';
import { useCartStore, CartItem } from '@/store/cart';
import { useCartDrawerStore } from '@/store/cartDrawer';
import { MilestoneTimeline, MilestoneCoupon } from '@/Components/checkout/MilestoneTimeline';
import { formatPrice } from '@/lib/utils';
import { trackAddToCart } from '@/lib/tracking';
import { ProductDetail, Variant } from '@/types';
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

    // In-Drawer Option Selector & Size Chart states
    const [selectedProduct, setSelectedProduct] = useState<ProductDetail | null>(null);
    const [selectedColor, setSelectedColor] = useState<string | null>(null);
    const [selectedSize, setSelectedSize] = useState<string | null>(null);
    const [showSizeChart, setShowSizeChart] = useState(false);
    const [sizeChartTab, setSizeChartTab] = useState<'chart' | 'measure'>('chart');
    const [addedSuccess, setAddedSuccess] = useState(false);

    const subtotal = cart.items.reduce((sum, item) => sum + (Number(item.price) * (item.quantity || 1)), 0);
    const totalItemsCount = cart.items.reduce((sum, item) => sum + (item.quantity || 1), 0);

    const [isBreakdownOpen, setIsBreakdownOpen] = useState(false);

    // Summary calculations (matching Cart.tsx)
    const mrpTotal = cart.items.reduce((s, i) => s + Math.max(Number(i.mrp) || 0, Number(i.price)) * (i.quantity || 1), 0);
    const mrpDiscount = mrpTotal > subtotal ? mrpTotal - subtotal : 0;
    const discount = cart.appliedCoupon?.discountAmount || 0;

    let shippingLabel = 'Calculated at checkout';
    if (sharedSettings?.shipping_rules?.prepaid) {
        const p = sharedSettings.shipping_rules.prepaid;
        if (p.type === 'free') shippingLabel = 'Free';
        else if (p.type === 'included') shippingLabel = 'Included';
        else if (p.type === 'flat' && parseFloat(p.fee) === 0) shippingLabel = 'Free';
    }

    const isTaxEnabled = sharedSettings?.is_tax_enabled == '1';
    const taxLabel = sharedSettings?.tax_label || 'Tax';
    const taxInclusive = sharedSettings?.tax_inclusion === 'include';

    const taxBreakdown: Record<string, number> = {};
    let trueSubtotal = 0;
    let taxAmount = 0;

    cart.items.forEach(item => {
        const itemTotal = Number(item.price) * (item.quantity || 1);
        let itemTax = 0;
        let trueItemTotal = itemTotal;

        if (isTaxEnabled) {
            let taxRate = 0;
            if (item.tax_class && sharedSettings?.taxes) {
                const t = sharedSettings.taxes.find((tax: any) => tax.id === item.tax_class);
                if (t) taxRate = parseFloat(t.rate);
            }
            if (taxRate > 0) {
                if (taxInclusive) {
                    trueItemTotal = itemTotal / (1 + (taxRate / 100));
                    itemTax = itemTotal - trueItemTotal;
                } else {
                    trueItemTotal = itemTotal;
                    itemTax = itemTotal * (taxRate / 100);
                }
                const rateKey = taxRate.toString();
                taxBreakdown[rateKey] = (taxBreakdown[rateKey] || 0) + itemTax;
                taxAmount += itemTax;
            }
        }
        trueSubtotal += trueItemTotal;
    });

    const estimatedTotal = Math.max(0, (taxInclusive ? subtotal : trueSubtotal + taxAmount) - discount);

    const handleClose = useCallback(() => {
        setSelectedProduct(null);
        setShowSizeChart(false);
        setAddedSuccess(false);
        closeCartDrawer();
    }, [closeCartDrawer]);

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
                if (showSizeChart) {
                    setShowSizeChart(false);
                } else if (selectedProduct) {
                    setSelectedProduct(null);
                } else {
                    handleClose();
                }
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, showSizeChart, selectedProduct, handleClose]);

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
            const res = await api.get(`/api/products/${product.slug}`);
            const data: ProductDetail = res.data.data || res.data;

            // If product has multiple variants or options, open in-drawer selector
            const hasMultipleVariants = data.variants && data.variants.length > 1;
            const hasAttributes = data.variants?.some(v => v.attributes && v.attributes.length > 0);

            if (hasMultipleVariants || hasAttributes || data.size_chart) {
                setSelectedProduct(data);
                setShowSizeChart(false);
                setAddedSuccess(false);

                // Auto-select initial color
                const firstColor = data.variants?.flatMap(v => v.attributes || [])
                    .find(a => a.name?.toLowerCase() === 'color')?.value || null;
                setSelectedColor(firstColor);

                // Auto-select initial in-stock size
                const availableSizes = Array.from(new Set(
                    data.variants?.flatMap(v => v.attributes || [])
                        .filter(a => a.name?.toLowerCase() === 'size')
                        .map(a => a.value)
                ));

                const initialSize = availableSizes.find(sizeName => {
                    const match = data.variants.find(v => {
                        const matchCol = !firstColor || v.attributes?.some(a => a.name?.toLowerCase() === 'color' && a.value === firstColor);
                        const matchSz = v.attributes?.some(a => a.name?.toLowerCase() === 'size' && a.value === sizeName);
                        return matchCol && matchSz;
                    });
                    return match ? (match.stock === undefined || match.stock === null || match.stock > 0) : true;
                }) || availableSizes[0] || null;

                setSelectedSize(initialSize);
            } else {
                // Single variant, direct add
                const v = data.variants?.[0];
                const colorAttr = v?.attributes?.find(a => a.name?.toLowerCase() === 'color');
                const sizeAttr = v?.attributes?.find(a => a.name?.toLowerCase() === 'size');
                const colorImg = colorAttr ? data.images?.find((img: any) => img.color_id?.toString() === colorAttr.id?.toString()) : null;

                cart.addItem({
                    skuId: v?.id || data.id,
                    skuCode: v?.code || undefined,
                    productId: data.id,
                    name: data.name,
                    slug: data.slug,
                    variant: v?.code || '',
                    price: v?.price || data.price,
                    mrp: v?.mrp || data.mrp,
                    image: colorImg?.url || data.image || data.images?.[0]?.url || '',
                    quantity: 1,
                    colorName: colorAttr?.value || undefined,
                    colorHex: colorAttr?.meta || undefined,
                    sizeName: sizeAttr?.value || undefined,
                    size: sizeAttr?.value || undefined,
                    deliveryDate: data.delivery_timeline?.formatted_date || undefined,
                    exchangeDays: data.is_exchangeable ? data.exchange_days : null,
                    returnDays: data.is_returnable ? data.return_days : null,
                });
                trackAddToCart(data, 1, v?.code);
            }
        } catch (e) {
            console.error('Failed to prepare product options', e);
        } finally {
            setAddingProductId(null);
        }
    };

    // Calculate options for selectedProduct
    const colors = useMemo(() => {
        if (!selectedProduct?.variants) return [];
        const map = new Map<string, { id?: string | number; name: string; hex?: string }>();
        selectedProduct.variants.forEach(v => {
            const c = v.attributes?.find(a => a.name?.toLowerCase() === 'color');
            if (c && c.value && !map.has(c.value)) {
                map.set(c.value, { id: c.id, name: c.value, hex: c.meta || undefined });
            }
        });
        return Array.from(map.values());
    }, [selectedProduct]);

    const sizes = useMemo(() => {
        if (!selectedProduct?.variants) return [];
        const set = new Set<string>();
        selectedProduct.variants.forEach(v => {
            const s = v.attributes?.find(a => a.name?.toLowerCase() === 'size');
            if (s && s.value) set.add(s.value);
        });
        return Array.from(set);
    }, [selectedProduct]);

    const currentVariant = useMemo(() => {
        if (!selectedProduct?.variants?.length) return null;
        return selectedProduct.variants.find(v => {
            const matchColor = !selectedColor || v.attributes?.some(a => a.name?.toLowerCase() === 'color' && a.value === selectedColor);
            const matchSize = !selectedSize || v.attributes?.some(a => a.name?.toLowerCase() === 'size' && a.value === selectedSize);
            return matchColor && matchSize;
        }) || selectedProduct.variants[0];
    }, [selectedProduct, selectedColor, selectedSize]);

    // Active image for active color
    const activePreviewImage = useMemo(() => {
        if (!selectedProduct) return '';
        if (selectedColor) {
            const colorObj = colors.find(c => c.name === selectedColor);
            if (colorObj) {
                const img = selectedProduct.images?.find(i => i.color_id?.toString() === colorObj.id?.toString());
                if (img?.url) return img.url;
            }
        }
        return selectedProduct.image || selectedProduct.images?.[0]?.url || '';
    }, [selectedProduct, selectedColor, colors]);

    // Check size stock for active color
    const isSizeInStock = (sizeName: string) => {
        if (!selectedProduct?.variants?.length) return true;
        const v = selectedProduct.variants.find(v => {
            const matchColor = !selectedColor || v.attributes?.some(a => a.name?.toLowerCase() === 'color' && a.value === selectedColor);
            const matchSize = v.attributes?.some(a => a.name?.toLowerCase() === 'size' && a.value === sizeName);
            return matchColor && matchSize;
        });
        return v ? (v.stock === undefined || v.stock === null || v.stock > 0) : false;
    };

    // Parse Size Chart
    const sizeChartInfo = useMemo(() => {
        if (!selectedProduct?.size_chart) return null;
        let data: any = selectedProduct.size_chart.measurements;
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch {}
        }
        const headers: string[] = data?.headers || [];
        const rows: any[] = data?.rows || [];
        return {
            name: selectedProduct.size_chart.name || 'Size Guide',
            description: selectedProduct.size_chart.description,
            headers,
            rows,
        };
    }, [selectedProduct]);

    // Add selected variant to cart
    const handleAddVariantToCart = () => {
        if (!selectedProduct || !currentVariant) return;

        const colorObj = colors.find(c => c.name === selectedColor);
        const colorImg = colorObj ? selectedProduct.images?.find((img: any) => img.color_id?.toString() === colorObj.id?.toString()) : null;

        let variantLabel = '';
        if (selectedColor && selectedSize) variantLabel = `${selectedColor} - ${selectedSize}`;
        else if (selectedColor) variantLabel = selectedColor;
        else if (selectedSize) variantLabel = selectedSize;
        else if (currentVariant.code) variantLabel = currentVariant.code;

        cart.addItem({
            skuId: currentVariant.id,
            skuCode: currentVariant.code || undefined,
            productId: selectedProduct.id,
            name: selectedProduct.name,
            slug: selectedProduct.slug,
            variant: variantLabel,
            price: currentVariant.price || selectedProduct.price,
            mrp: currentVariant.mrp || selectedProduct.mrp || currentVariant.price,
            image: colorImg?.url || activePreviewImage || '',
            quantity: 1,
            colorName: selectedColor || undefined,
            colorHex: colorObj?.hex || undefined,
            sizeName: selectedSize || undefined,
            size: selectedSize || undefined,
            deliveryDate: selectedProduct.delivery_timeline?.formatted_date || undefined,
            exchangeDays: selectedProduct.is_exchangeable ? selectedProduct.exchange_days : null,
            returnDays: selectedProduct.is_returnable ? selectedProduct.return_days : null,
        });

        trackAddToCart(selectedProduct, 1, currentVariant.code);

        setAddedSuccess(true);
        setTimeout(() => {
            setSelectedProduct(null);
            setShowSizeChart(false);
            setAddedSuccess(false);
        }, 500);
    };

    const handleCheckout = () => {
        handleClose();
        router.visit('/checkout');
    };

    const handleViewCart = () => {
        handleClose();
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
                onClick={handleClose}
            />

            {/* Slide-in Panel Container */}
            <div className="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
                <div
                    className={`w-screen max-w-md bg-white shadow-2xl flex flex-col h-full transform transition-transform duration-300 ease-in-out relative overflow-hidden ${
                        isOpen ? 'translate-x-0' : 'translate-x-full'
                    }`}
                >

                    {/* ═══════════════════════════════════════════════════════════
                        SUBVIEW 1: IN-DRAWER SIZE CHART
                    ═════════════════════════════════════════════════════════════ */}
                    {showSizeChart && (
                        <div className="absolute inset-0 z-30 bg-white flex flex-col h-full animate-in fade-in slide-in-from-right-4 duration-200">
                            {/* Header */}
                            <div className="p-4 sm:px-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                                <button
                                    type="button"
                                    onClick={() => setShowSizeChart(false)}
                                    className="flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-black transition-colors"
                                >
                                    <ArrowLeft size={16} />
                                    <span>Back to Options</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={handleClose}
                                    className="p-1.5 rounded-full text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                >
                                    <X size={18} />
                                </button>
                            </div>

                            {/* Tabs */}
                            <div className="flex border-b border-gray-100 shrink-0 bg-gray-50/50">
                                <button
                                    type="button"
                                    onClick={() => setSizeChartTab('chart')}
                                    className={`flex-1 py-3 text-xs font-bold transition-colors ${
                                        sizeChartTab === 'chart'
                                            ? 'border-b-2 border-black text-black bg-white'
                                            : 'text-gray-400 hover:text-gray-700'
                                    }`}
                                >
                                    Size Guide Table
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setSizeChartTab('measure')}
                                    className={`flex-1 py-3 text-xs font-bold transition-colors ${
                                        sizeChartTab === 'measure'
                                            ? 'border-b-2 border-black text-black bg-white'
                                            : 'text-gray-400 hover:text-gray-700'
                                    }`}
                                >
                                    How to Measure
                                </button>
                            </div>

                            {/* Body */}
                            <div className="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                                {sizeChartTab === 'chart' ? (
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-sm font-extrabold text-gray-900">
                                                {sizeChartInfo?.name || 'Size Guide'}
                                            </h3>
                                            <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                                All units in Inches
                                            </span>
                                        </div>

                                        {sizeChartInfo && sizeChartInfo.rows.length > 0 ? (
                                            <div className="overflow-x-auto rounded-xl border border-gray-200 shadow-2xs">
                                                <table className="w-full text-xs text-center border-collapse">
                                                    <thead>
                                                        <tr className="bg-gray-100/80 text-gray-700 font-bold uppercase text-[10px] tracking-wider">
                                                            <th className="py-2.5 px-3 text-left">Size</th>
                                                            {sizeChartInfo.headers.map((h: string) => (
                                                                <th key={h} className="py-2.5 px-3">{h}</th>
                                                            ))}
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-100 bg-white">
                                                        {sizeChartInfo.rows.map((row: any) => {
                                                            const rowCode = row.size_code || row.size_name || '';
                                                            const isSelected = selectedSize?.toUpperCase() === rowCode.toUpperCase();
                                                            return (
                                                                <tr
                                                                    key={rowCode}
                                                                    onClick={() => {
                                                                        setSelectedSize(rowCode);
                                                                        setShowSizeChart(false);
                                                                    }}
                                                                    className={`cursor-pointer transition-colors ${
                                                                        isSelected
                                                                            ? 'bg-black text-white font-bold'
                                                                            : 'hover:bg-gray-50 text-gray-800'
                                                                    }`}
                                                                >
                                                                    <td className="py-3 px-3 text-left font-bold whitespace-nowrap">
                                                                        <span className="flex items-center gap-1.5">
                                                                            <span
                                                                                className={`w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 ${
                                                                                    isSelected ? 'border-white bg-white' : 'border-gray-300'
                                                                                }`}
                                                                            >
                                                                                {isSelected && <span className="w-1.5 h-1.5 rounded-full bg-black"></span>}
                                                                            </span>
                                                                            {row.size_name || rowCode}
                                                                        </span>
                                                                    </td>
                                                                    {sizeChartInfo.headers.map((h: string) => (
                                                                        <td key={h} className="py-3 px-3">
                                                                            {row.measurements?.[h] ?? row[h] ?? '-'}
                                                                        </td>
                                                                    ))}
                                                                </tr>
                                                            );
                                                        })}
                                                    </tbody>
                                                </table>
                                            </div>
                                        ) : (
                                            <div className="py-8 text-center text-xs text-gray-400 italic">
                                                Standard regular fit. Order your standard size for optimal fit.
                                            </div>
                                        )}

                                        <p className="text-[11px] text-gray-500 italic">
                                            💡 Tap any row above to quickly select that size and return.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="space-y-4 text-xs text-gray-600 leading-relaxed">
                                        <div className="p-3 bg-gray-50 rounded-xl space-y-2 border border-gray-200">
                                            <h4 className="font-bold text-gray-900 text-xs">1. Chest / Bust</h4>
                                            <p className="text-[11px]">Measure around the fullest part of your chest, keeping the tape horizontal under your arms.</p>
                                        </div>
                                        <div className="p-3 bg-gray-50 rounded-xl space-y-2 border border-gray-200">
                                            <h4 className="font-bold text-gray-900 text-xs">2. Length</h4>
                                            <p className="text-[11px]">Measure from the highest point of the shoulder down to the bottom hem of the garment.</p>
                                        </div>
                                        <div className="p-3 bg-gray-50 rounded-xl space-y-2 border border-gray-200">
                                            <h4 className="font-bold text-gray-900 text-xs">3. Shoulders</h4>
                                            <p className="text-[11px]">Measure horizontally across the back from one shoulder edge point to the other.</p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Footer */}
                            <div className="p-4 sm:px-6 border-t border-gray-100 bg-white shrink-0">
                                <button
                                    type="button"
                                    onClick={() => setShowSizeChart(false)}
                                    className="w-full py-3 bg-black text-white text-xs font-bold rounded-xl shadow-md hover:bg-gray-800 transition-colors"
                                >
                                    Done & Return to Product
                                </button>
                            </div>
                        </div>
                    )}

                    {/* ═══════════════════════════════════════════════════════════
                        SUBVIEW 2: IN-DRAWER VARIANT & COLOR/SIZE SELECTOR
                    ═════════════════════════════════════════════════════════════ */}
                    {selectedProduct && (
                        <div className="absolute inset-0 z-20 bg-white flex flex-col h-full animate-in fade-in slide-in-from-bottom-3 duration-200">
                            {/* Header */}
                            <div className="p-4 sm:px-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                                <button
                                    type="button"
                                    onClick={() => setSelectedProduct(null)}
                                    className="flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-black transition-colors"
                                >
                                    <ArrowLeft size={16} />
                                    <span>Back to Bag</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={handleClose}
                                    className="p-1.5 rounded-full text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                >
                                    <X size={18} />
                                </button>
                            </div>

                            {/* Body */}
                            <div className="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
                                {/* Product Summary Card */}
                                <div className="flex gap-4 p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div className="w-18 h-22 bg-white rounded-xl overflow-hidden shrink-0 border border-gray-200">
                                        {activePreviewImage ? (
                                            <img
                                                src={activePreviewImage}
                                                alt={selectedProduct.name}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-xs text-gray-300 font-bold">
                                                DOPE
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex-1 min-w-0 flex flex-col justify-center">
                                        <h3 className="text-sm font-extrabold text-gray-900 leading-snug line-clamp-2">
                                            {selectedProduct.name}
                                        </h3>
                                        <div className="flex items-baseline gap-2 mt-1.5">
                                            <span className="text-base font-extrabold text-gray-900">
                                                {formatPrice(currentVariant?.price || selectedProduct.price)}
                                            </span>
                                            {(currentVariant?.mrp || selectedProduct.mrp) &&
                                            (currentVariant?.mrp || selectedProduct.mrp) > (currentVariant?.price || selectedProduct.price) ? (
                                                <span className="text-xs text-gray-400 line-through">
                                                    {formatPrice(currentVariant?.mrp || selectedProduct.mrp)}
                                                </span>
                                            ) : null}
                                        </div>

                                        {selectedProduct.delivery_timeline?.formatted_date && (
                                            <p className="text-[10px] text-emerald-700 font-medium mt-1">
                                                🚚 Delivery by {selectedProduct.delivery_timeline.formatted_date}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Colors Section */}
                                {colors.length > 0 && (
                                    <div className="space-y-2.5">
                                        <div className="flex items-center justify-between text-xs">
                                            <span className="font-bold text-gray-900">
                                                Color: <span className="font-normal text-gray-600">{selectedColor || 'Choose'}</span>
                                            </span>
                                        </div>

                                        <div className="flex flex-wrap gap-2.5">
                                            {colors.map((c) => {
                                                const isSelected = selectedColor === c.name;
                                                return (
                                                    <button
                                                        key={c.name}
                                                        type="button"
                                                        onClick={() => setSelectedColor(c.name)}
                                                        className={`flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all ${
                                                            isSelected
                                                                ? 'border-black bg-black text-white shadow-xs'
                                                                : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400'
                                                        }`}
                                                    >
                                                        <span
                                                            className={`w-3.5 h-3.5 rounded-full border shadow-2xs ${
                                                                isSelected ? 'border-white' : 'border-gray-300'
                                                            }`}
                                                            style={{ backgroundColor: c.hex || '#000' }}
                                                        />
                                                        <span>{c.name}</span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {/* Sizes Section */}
                                {sizes.length > 0 && (
                                    <div className="space-y-2.5">
                                        <div className="flex items-center justify-between text-xs">
                                            <span className="font-bold text-gray-900">
                                                Size: <span className="font-normal text-gray-600">{selectedSize || 'Select a size'}</span>
                                            </span>

                                            {/* Size Chart Button */}
                                            {selectedProduct.size_chart && (
                                                <button
                                                    type="button"
                                                    onClick={() => setShowSizeChart(true)}
                                                    className="inline-flex items-center gap-1.5 text-xs font-bold text-gray-900 hover:text-black underline underline-offset-2 transition-colors py-0.5 px-1 rounded hover:bg-gray-100"
                                                >
                                                    <Ruler size={13} className="text-gray-500" />
                                                    <span>Size Chart</span>
                                                </button>
                                            )}
                                        </div>

                                        <div className="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                            {sizes.map((s) => {
                                                const isSelected = selectedSize === s;
                                                const inStock = isSizeInStock(s);
                                                return (
                                                    <button
                                                        key={s}
                                                        type="button"
                                                        disabled={!inStock}
                                                        onClick={() => setSelectedSize(s)}
                                                        className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all border text-center ${
                                                            isSelected
                                                                ? 'border-black bg-black text-white shadow-xs'
                                                                : inStock
                                                                ? 'border-gray-200 bg-white text-gray-900 hover:border-black'
                                                                : 'border-gray-100 bg-gray-50 text-gray-300 line-through cursor-not-allowed'
                                                        }`}
                                                    >
                                                        {s}
                                                    </button>
                                                );
                                            })}
                                        </div>

                                        {currentVariant && currentVariant.stock !== undefined && currentVariant.stock > 0 && currentVariant.stock <= 5 && (
                                            <p className="text-[11px] font-bold text-amber-600">
                                                ⚡ Only {currentVariant.stock} left in stock - order soon!
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>

                            {/* Sticky Footer Add to Bag Action */}
                            <div className="p-4 sm:px-6 border-t border-gray-100 bg-white shrink-0 shadow-[0_-4px_16px_rgba(0,0,0,0.04)]">
                                <button
                                    type="button"
                                    onClick={handleAddVariantToCart}
                                    disabled={addedSuccess || (sizes.length > 0 && !selectedSize)}
                                    className={`w-full py-3.5 px-4 text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 ${
                                        addedSuccess
                                            ? 'bg-emerald-600 text-white'
                                            : sizes.length > 0 && !selectedSize
                                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                            : 'bg-black text-white hover:bg-gray-900 active:scale-[0.99]'
                                    }`}
                                >
                                    {addedSuccess ? (
                                        <>
                                            <Check size={16} strokeWidth={3} />
                                            <span>Added to Bag!</span>
                                        </>
                                    ) : sizes.length > 0 && !selectedSize ? (
                                        <span>Please Select a Size</span>
                                    ) : (
                                        <>
                                            <span>+ Add to Bag</span>
                                            <span>•</span>
                                            <span>{formatPrice(currentVariant?.price || selectedProduct.price)}</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* ═══════════════════════════════════════════════════════════
                        SUBVIEW 3: MAIN CART BAG VIEW
                    ═════════════════════════════════════════════════════════════ */}
                    <div className="flex flex-col h-full">
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
                                onClick={handleClose}
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
                                            onClick={handleClose}
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
                                                    onClick={handleClose}
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
                                                                onClick={handleClose}
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
                                                    <div
                                                        onClick={() => handleQuickAdd(p)}
                                                        className="w-12 h-14 bg-white rounded-lg overflow-hidden shrink-0 border border-gray-100 relative cursor-pointer"
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
                                                    </div>

                                                    {/* Title and Price */}
                                                    <div
                                                        onClick={() => handleQuickAdd(p)}
                                                        className="flex-1 min-w-0 cursor-pointer"
                                                    >
                                                        <span className="text-xs font-bold text-gray-900 hover:text-gray-600 transition-colors line-clamp-1 block truncate leading-tight">
                                                            {p.name}
                                                        </span>
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

                                                    {/* 1-Click / Select Options Button */}
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
                                {/* Estimate Accordion */}
                                <div 
                                    onClick={() => setIsBreakdownOpen(!isBreakdownOpen)}
                                    className="border border-gray-100 rounded-xl bg-gray-50/70 p-3 space-y-2 cursor-pointer hover:bg-gray-100/70 transition-colors select-none"
                                    role="button"
                                    tabIndex={0}
                                    aria-expanded={isBreakdownOpen}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter' || e.key === ' ') {
                                            e.preventDefault();
                                            setIsBreakdownOpen(!isBreakdownOpen);
                                        }
                                    }}
                                >
                                    <div
                                        className="w-full flex items-center justify-between text-left group cursor-pointer"
                                    >
                                        <div className="flex items-center gap-1.5 cursor-pointer">
                                            <span className="text-xs font-bold text-gray-800 group-hover:text-black transition-colors cursor-pointer">
                                                Estimate
                                            </span>
                                            <ChevronDown
                                                size={14}
                                                className={`text-gray-500 transition-transform duration-200 transform ${
                                                    isBreakdownOpen ? 'rotate-180 text-black' : ''
                                                }`}
                                            />
                                            {mrpDiscount > 0 && !isBreakdownOpen && (
                                                <span className="text-[10px] font-extrabold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded ml-1">
                                                    Save {formatPrice(mrpDiscount + discount)}
                                                </span>
                                            )}
                                        </div>

                                        <span className="text-sm font-black text-gray-900 tracking-tight cursor-pointer">
                                            {formatPrice(estimatedTotal)}
                                        </span>
                                    </div>

                                    {/* Expandable Breakdown List */}
                                    {isBreakdownOpen && (
                                        <div 
                                            onClick={(e) => e.stopPropagation()} 
                                            className="pt-2 border-t border-gray-200/60 space-y-2 text-xs animate-in fade-in slide-in-from-top-1 duration-150 cursor-default"
                                        >
                                            {/* MRP Total */}
                                            <div className="flex justify-between text-gray-500">
                                                <span>MRP Total</span>
                                                <span className="font-medium text-gray-800">{formatPrice(mrpTotal)}</span>
                                            </div>

                                            {/* Discount on MRP */}
                                            {mrpDiscount > 0 && (
                                                <div className="flex justify-between text-emerald-600 font-medium">
                                                    <span>Discount on MRP</span>
                                                    <span>−{formatPrice(mrpDiscount)}</span>
                                                </div>
                                            )}

                                            {/* Cart Subtotal */}
                                            <div className="flex justify-between text-gray-600 font-medium">
                                                <span>Cart Subtotal</span>
                                                <span>{formatPrice(subtotal)}</span>
                                            </div>

                                            {/* Coupon Discount */}
                                            {cart.appliedCoupon && (
                                                <div className="flex justify-between items-center text-emerald-600 font-medium">
                                                    <div className="flex items-center gap-1.5">
                                                        <span>Coupon ({cart.appliedCoupon.code})</span>
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                cart.setAppliedCoupon(null);
                                                            }}
                                                            className="text-gray-400 hover:text-red-500 p-0.5 rounded transition-colors cursor-pointer"
                                                            title="Remove coupon"
                                                        >
                                                            <X size={12} />
                                                        </button>
                                                    </div>
                                                    <span>−{formatPrice(discount)}</span>
                                                </div>
                                            )}

                                            {/* Shipping */}
                                            <div className="flex justify-between text-gray-500">
                                                <span>Shipping</span>
                                                <span className="font-medium text-gray-700">{shippingLabel}</span>
                                            </div>

                                            {/* Tax Breakdown */}
                                            {isTaxEnabled && sharedSettings?.show_tax_in_cart_checkout !== '0' && (
                                                <>
                                                    {Object.entries(taxBreakdown).map(([rate, amount]) => (
                                                        <div key={rate} className="flex justify-between text-gray-400 text-[11px]">
                                                            <span>{taxLabel} @ {rate}% {taxInclusive ? '(Included)' : '(Excluded)'}</span>
                                                            <span>{formatPrice(amount)}</span>
                                                        </div>
                                                    ))}
                                                </>
                                            )}

                                            <div className="h-px bg-gray-200/80 my-1" />

                                            {/* Final Estimate Row in Breakdown */}
                                            <div className="flex justify-between font-bold text-gray-900 text-xs pt-0.5">
                                                <span>Estimated Total</span>
                                                <span className="font-black text-sm">{formatPrice(estimatedTotal)}</span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Primary Checkout Button */}
                                <button
                                    type="button"
                                    onClick={handleCheckout}
                                    className="w-full py-3.5 px-4 bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md hover:bg-gray-900 active:scale-[0.99] transition-all flex items-center justify-center gap-2 group cursor-pointer"
                                >
                                    <span>Proceed to Checkout</span>
                                    <ArrowRight size={15} className="group-hover:translate-x-1 transition-transform" />
                                </button>

                                {/* Secondary Link to Cart Page */}
                                <div className="flex items-center justify-between pt-1">
                                    <button
                                        type="button"
                                        onClick={handleViewCart}
                                        className="text-xs font-bold text-gray-500 hover:text-black underline underline-offset-4 transition-colors cursor-pointer"
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
        </div>
    );
}
