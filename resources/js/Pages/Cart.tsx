import { useState, useEffect } from 'react';
import { useCartStore, CartItem } from '@/store/cart';
import { useWishlistStore, WishlistItem } from '@/store/wishlist';
import { formatPrice } from '@/lib/utils';
import { Link, Head, usePage } from '@inertiajs/react';
import api from '@/lib/api';
import { Trash2, Plus, Minus, ArrowRight, ShoppingBag, Heart, Eye } from 'lucide-react';

/* ── Cart Item ────────────────────────────────────────────────────────────── */
function CartRow({ item, update, remove }: {
    item: CartItem;
    update: (id: number, q: number) => void;
    remove: (id: number) => void;
}) {
    return (
        <div className="flex gap-4 py-5 border-b border-gray-100 last:border-0">
            <Link href={`/product/${item.slug}`} className="relative w-20 h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 block">
                {item.image && <img src={item.image} alt={item.name} className="w-full h-full object-cover" />}
            </Link>

            <div className="flex-1 min-w-0">
                <div className="flex justify-between items-start gap-2">
                    <Link href={`/product/${item.slug}`}>
                        <h3 className="text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-snug line-clamp-2">{item.name}</h3>
                    </Link>
                    <button onClick={() => remove(item.skuId)} className="text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-0.5">
                        <Trash2 size={15} />
                    </button>
                </div>

                <div className="flex items-center gap-2 mt-2 flex-wrap">
                    {item.colorName && (
                        <span className="flex items-center gap-1.5 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            <span className="w-2.5 h-2.5 rounded-full border border-white shadow-sm" style={{ backgroundColor: item.colorHex || '#aaa' }} />
                            {item.colorName}
                        </span>
                    )}
                    {item.size && (
                        <span className="text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            Size {item.size}
                        </span>
                    )}
                </div>

                <div className="flex items-center justify-between mt-3">
                    <div className="flex items-center gap-2 border border-gray-200 rounded-lg">
                        <button onClick={() => update(item.skuId, Math.max(1, item.quantity - 1))} className="px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500">
                            <Minus size={12} strokeWidth={3} />
                        </button>
                        <span className="text-sm font-bold text-gray-900 w-7 text-center">{item.quantity}</span>
                        <button onClick={() => update(item.skuId, item.quantity + 1)} className="px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500">
                            <Plus size={12} strokeWidth={3} />
                        </button>
                    </div>
                    <div className="flex flex-col items-end">
                        <p className="text-sm font-bold text-gray-900">{formatPrice(item.price * item.quantity)}</p>
                        {item.mrp && item.mrp > item.price ? (
                            <p className="text-[10px] text-gray-400 line-through mt-0.5">{formatPrice(item.mrp * item.quantity)}</p>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}

/* ── Wishlist Item ────────────────────────────────────────────────────────── */
function WishlistRow({ item, remove }: { item: WishlistItem; remove: (id: number) => void; }) {
    const cart = useCartStore();

    const handleBuyNow = () => {
        if (!item.skuId) {
            // Needs size selection
            window.location.href = `/product/${item.slug}`;
            return;
        }
        
        cart.addItem({
            skuId: item.skuId,
            productId: item.productId,
            name: item.name,
            slug: item.slug,
            variant: item.variant || '',
            price: item.price,
            mrp: item.mrp,
            image: item.image || '',
            quantity: 1,
            colorName: item.colorName,
            colorHex: item.colorHex,
            sizeName: item.sizeName,
            size: item.size
        });
        
        remove(item.productId);
    };

    return (
        <div className="flex gap-4 py-5 border-b border-gray-100 last:border-0">
            <Link href={`/product/${item.slug}`} className="relative w-20 h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 block">
                {item.image && <img src={item.image} alt={item.name} className="w-full h-full object-cover" />}
            </Link>

            <div className="flex-1 min-w-0">
                <div className="flex justify-between items-start gap-2">
                    <Link href={`/product/${item.slug}`}>
                        <h3 className="text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-snug line-clamp-2">{item.name}</h3>
                    </Link>
                    <button onClick={() => remove(item.productId)} className="text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-0.5">
                        <Trash2 size={15} />
                    </button>
                </div>

                <div className="flex items-center gap-2 mt-2 flex-wrap">
                    {item.colorName && (
                        <span className="flex items-center gap-1.5 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            <span className="w-2.5 h-2.5 rounded-full border border-white shadow-sm" style={{ backgroundColor: item.colorHex || '#aaa' }} />
                            {item.colorName}
                        </span>
                    )}
                    {item.size && (
                        <span className="text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            Size {item.size}
                        </span>
                    )}
                </div>

                <div className="flex items-center justify-between mt-3">
                    <div className="flex flex-col">
                        <p className="text-sm font-bold text-gray-900">{formatPrice(item.price)}</p>
                        {item.mrp && item.mrp > item.price ? (
                            <p className="text-[10px] text-gray-400 line-through mt-0.5">{formatPrice(item.mrp)}</p>
                        ) : null}
                    </div>
                    
                    <div className="flex items-center gap-2">
                        <button onClick={handleBuyNow} className="text-[10px] font-black uppercase tracking-widest text-white bg-black border border-black px-3 py-1.5 rounded-lg hover:bg-gray-800 transition-all">
                            Buy Now
                        </button>
                        <Link href={`/product/${item.slug}`} className="text-gray-500 border border-gray-200 p-1.5 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all" title="View Product">
                            <Eye size={14} />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

/* ── Page ─────────────────────────────────────────────────────────────────── */
export default function CartPage() {
    const { settings: sharedSettings } = usePage<any>().props;
    const storeName = sharedSettings?.store_name || 'Store';
    const cart = useCartStore();
    const wishlist = useWishlistStore();
    const [mounted, setMounted] = useState(false);
    const [settings, setSettings] = useState<any>(null);

    useEffect(() => {
        setMounted(true);
        api.get('/api/settings').then(r => setSettings(r.data)).catch(() => { });
    }, []);

    if (!mounted) return <div className="min-h-[60vh]" />;

    if (cart.items.length === 0 && wishlist.items.length === 0) return (
        <div className="max-w-sm mx-auto px-4 py-28 text-center">
            <ShoppingBag className="w-12 h-12 text-gray-200 mx-auto mb-5" />
            <h1 className="text-xl font-bold text-gray-900 mb-2">Your bag is empty</h1>
            <p className="text-sm text-gray-400 mb-8">Add items to get started.</p>
            <Link href="/shop" className="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all">
                Browse Shop <ArrowRight size={15} />
            </Link>
        </div>
    );

    const mrpTotal = cart.items.reduce((s, i) => s + Math.max(Number(i.mrp) || 0, Number(i.price)) * i.quantity, 0);
    const subtotal = cart.items.reduce((s, i) => s + Number(i.price) * i.quantity, 0);
    const mrpDiscount = mrpTotal > subtotal ? mrpTotal - subtotal : 0;
    
    let taxAmount = 0;
    const isTaxEnabled = settings?.is_tax_enabled == '1';
    const taxLabel = settings?.tax_label || 'Tax';
    const taxInclusive = settings?.tax_inclusion === 'include';
    
    const taxBreakdown: Record<string, number> = {};
    let trueSubtotal = 0;

    cart.items.forEach(item => {
        const itemTotal = item.price * item.quantity;
        let itemTax = 0;
        let trueItemTotal = itemTotal;

        if (isTaxEnabled) {
            let taxRate = 0;
            if (item.tax_class && settings?.taxes) {
                const t = settings.taxes.find((t: any) => t.id === item.tax_class);
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

    const total = taxInclusive ? subtotal : trueSubtotal + taxAmount;

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16">
            <Head title="Cart" />
            <div className="mb-10">
                <h1 className="text-2xl font-bold text-gray-900">Your Cart</h1>
                <p className="text-sm text-gray-400 mt-1">
                    {cart.items.length} {cart.items.length === 1 ? 'item' : 'items'} in your bag
                    {wishlist.items.length > 0 && ` • ${wishlist.items.length} saved for later`}
                </p>
            </div>

            <div className="flex flex-col lg:flex-row gap-10 items-start">
                {/* ── LEFT ─────────────────────────────────────────────────── */}
                <div className="flex-1 space-y-10 min-w-0">
                    {/* Cart Items */}
                    {cart.items.length > 0 && (
                        <section>
                            <div className="bg-white border-y sm:border sm:rounded-2xl px-4 sm:px-5 divide-y divide-gray-50 -mx-4 sm:mx-0">
                                {cart.items.map(item => (
                                    <CartRow key={item.skuId} item={item} update={cart.updateQuantity} remove={cart.removeItem} />
                                ))}
                            </div>
                        </section>
                    )}

                    {/* Wishlist Items */}
                    {wishlist.items.length > 0 && (
                        <section>
                            <h2 className="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5 mt-2">
                                <Heart size={12} /> Saved for Later / Wishlist
                            </h2>
                            <div className="bg-white border-y sm:border sm:rounded-2xl px-4 sm:px-5 divide-y divide-gray-50 -mx-4 sm:mx-0">
                                {wishlist.items.map(item => (
                                    <WishlistRow key={item.productId} item={item} remove={wishlist.removeItem} />
                                ))}
                            </div>
                        </section>
                    )}
                </div>

                {/* ── RIGHT ────────────────────────────────────────────────── */}
                {cart.items.length > 0 && (
                    <div className="w-full lg:w-[340px] shrink-0 space-y-4">
                        <div className="bg-white border border-gray-100 rounded-2xl p-5">
                            <p className="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Order Summary</p>

                            <div className="space-y-2.5 text-sm">
                                <div className="flex justify-between text-gray-600">
                                    <span>MRP Total</span>
                                    <span className="font-medium text-gray-900">{formatPrice(mrpTotal)}</span>
                                </div>
                                {mrpDiscount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Discount on MRP</span>
                                        <span>−{formatPrice(mrpDiscount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between text-gray-600 font-medium">
                                    <span>Cart Subtotal</span>
                                    <span>{formatPrice(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-gray-500">
                                    <span>Shipping</span>
                                    <span>Calculated at checkout</span>
                                </div>
                                {isTaxEnabled && settings?.show_tax_in_cart_checkout !== '0' && (
                                    <>
                                        {Object.entries(taxBreakdown).map(([rate, amount]) => (
                                            <div key={rate} className="flex justify-between text-gray-500 text-xs">
                                                <span>{taxLabel} @ {rate}% {taxInclusive ? '(Included)' : '(Excluded)'}</span>
                                                <span>{formatPrice(amount)}</span>
                                            </div>
                                        ))}
                                    </>
                                )}
                                <div className="h-px bg-gray-100 my-1" />
                                <div className="flex justify-between font-bold text-gray-900 text-base">
                                    <span>Estimated Total</span>
                                    <span>{formatPrice(total)}</span>
                                </div>
                            </div>

                            <Link href="/checkout" className="mt-5 w-full bg-gray-900 text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-black transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                Proceed to Checkout
                                <ArrowRight size={16} />
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
