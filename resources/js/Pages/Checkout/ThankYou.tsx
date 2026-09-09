import { Link, Head } from '@inertiajs/react';
import { CheckCircle, Package, ArrowRight, Truck } from "lucide-react";
import { useEffect, useState } from 'react';
import Confetti from 'react-confetti';
import { useWindowSize } from 'react-use';
import { trackPurchase } from '@/lib/tracking';

export default function ThankYouPage({ order }: { order: any }) {
    const { width, height } = useWindowSize();
    const [showConfetti, setShowConfetti] = useState(true);

    useEffect(() => {
        // Stop confetti after 5 seconds
        const timer = setTimeout(() => setShowConfetti(false), 5000);
        return () => clearTimeout(timer);
    }, []);

    useEffect(() => {
        if (order && order.id) {
            const firedKey = `purchase_fired_${order.id}`;
            if (!sessionStorage.getItem(firedKey)) {
                trackPurchase(
                    order.order_number || order.uuid,
                    parseFloat(order.total_amount || "0"),
                    order.items || []
                );
                sessionStorage.setItem(firedKey, 'true');
            }
        }
    }, [order]);

    // Helper to format currency
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(price);
    };

    if (!order) return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
        </div>
    );

    const safeSubtotal = order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.price || "0") * item.quantity), 0);
    const totalAmount = parseFloat(order.total_amount || "0");
    const shippingAmount = parseFloat(order.shipping_amount || "0");
    const discountAmount = parseFloat(order.discount_amount || "0");
    const taxAmount = parseFloat(order.tax_amount || "0");
    const isTaxIncluded = Math.abs((totalAmount + discountAmount) - (safeSubtotal + shippingAmount)) < 0.1;

    let taxBreakdown = {};
    try {
        taxBreakdown = typeof order.tax_breakdown === 'string' ? JSON.parse(order.tax_breakdown) : (order.tax_breakdown || {});
    } catch (e) {
        taxBreakdown = {};
    }

    return (
        <div className="min-h-screen bg-gray-50 font-sans py-12 md:py-20">
            <Head title="Order Confirmed | Dope Style" />
            
            {showConfetti && (
                <Confetti
                    width={width}
                    height={height}
                    recycle={false}
                    numberOfPieces={300}
                    gravity={0.15}
                    colors={['#000000', '#ffffff', '#4ade80', '#fbbf24', '#f87171']}
                    style={{ zIndex: 100 }}
                />
            )}

            <div className="max-w-3xl mx-auto px-4">
                
                {/* Header text outside the card for clean look */}
                <div className="text-center mb-8">
                    <CheckCircle className="w-16 h-16 text-emerald-500 mx-auto mb-4" />
                    <h1 className="text-3xl font-extrabold text-gray-900 mb-2">Order Confirmed!</h1>
                    <p className="text-gray-500 text-lg">Thank you for your purchase.</p>
                </div>

                <div className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    
                    <div className="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Order Number</p>
                            <p className="text-base font-mono font-bold text-gray-900">{order.order_number || order.uuid.split('-')[0]}</p>
                        </div>
                    </div>

                    <div className="p-6 md:p-8">
                        <h2 className="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <Package className="w-5 h-5 text-gray-400" />
                            Items Ordered
                        </h2>

                        {/* Product List */}
                        <div className="space-y-6 mb-8">
                            {order.items.map((item: any) => {
                                const imgUrl = item.image_url || item.product?.featured_image || item.sku?.product?.featured_image;
                                return (
                                    <div key={item.id} className="flex gap-4">
                                        <div className="w-20 h-28 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100">
                                            {imgUrl ? (
                                                <img 
                                                    src={imgUrl} 
                                                    alt={item.product_name || item.product?.name}
                                                    className="w-full h-full object-cover object-top"
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-[10px] text-gray-400 font-medium">No Image</div>
                                            )}
                                        </div>
                                        <div className="flex-1 min-w-0 flex flex-col py-1">
                                            <h3 className="text-sm font-bold text-gray-900 leading-tight mb-1">
                                                {item.product_name || item.product?.name}
                                            </h3>
                                            
                                            {(item.variant_name || item.sku?.color || item.sku?.size) && (
                                                <div className="text-sm text-gray-500 mb-2">
                                                    {item.variant_name || [
                                                        item.sku?.color?.name,
                                                        item.sku?.size?.name ? `Size: ${item.sku.size.name}` : ''
                                                    ].filter(Boolean).join(' | ')}
                                                </div>
                                            )}

                                            <div className="text-sm text-gray-500 flex items-center justify-between mt-auto">
                                                <span>Qty: {item.quantity}</span>
                                                <span className="font-bold text-gray-900">
                                                    {formatPrice(parseFloat(item.price || "0") * item.quantity)}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        <hr className="border-gray-100 mb-8" />

                        {/* Summary Section */}
                        <div className="space-y-4 max-w-sm ml-auto">
                            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 sm:p-8">
                            <h2 className="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Order Summary</h2>
                            <div className="space-y-2.5 text-sm">
                                <div className="flex justify-between text-gray-600">
                                    <span>MRP Total</span>
                                    <span className="font-medium text-gray-900">
                                        {formatPrice(order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.sku?.mrp || item.price || "0") * item.quantity), 0))}
                                    </span>
                                </div>
                                
                                {order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.sku?.mrp || item.price || "0") * item.quantity), 0) > safeSubtotal && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Discount on MRP</span>
                                        <span>−{formatPrice(order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.sku?.mrp || item.price || "0") * item.quantity), 0) - safeSubtotal)}</span>
                                    </div>
                                )}

                                <div className="flex justify-between text-gray-600 font-medium">
                                    <span>Cart Subtotal</span>
                                    <span>{formatPrice(safeSubtotal)}</span>
                                </div>

                                {(parseFloat(order.coupon_discount_amount as string || "0") > 0 || parseFloat(order.prepaid_discount_amount as string || "0") > 0 || parseFloat(order.gift_card_discount_amount as string || "0") > 0) ? (
                                    <>
                                        {parseFloat(order.coupon_discount_amount as string || "0") > 0 && (
                                            <div className="flex justify-between text-green-600">
                                                <span>Coupon Discount {order.coupon_code ? `(${order.coupon_code})` : ''}</span>
                                                <span>−{formatPrice(parseFloat(order.coupon_discount_amount as string || "0"))}</span>
                                            </div>
                                        )}
                                        {parseFloat(order.prepaid_discount_amount as string || "0") > 0 && (
                                            <div className="flex justify-between text-green-600">
                                                <span>Prepaid Discount</span>
                                                <span>−{formatPrice(parseFloat(order.prepaid_discount_amount as string || "0"))}</span>
                                            </div>
                                        )}
                                        {parseFloat(order.gift_card_discount_amount as string || "0") > 0 && (
                                            <div className="flex justify-between text-green-600">
                                                <span>Gift Card Applied</span>
                                                <span>−{formatPrice(parseFloat(order.gift_card_discount_amount as string || "0"))}</span>
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    parseFloat(order.discount_amount as string || "0") > 0 && (
                                        <div className="flex justify-between text-green-600">
                                            <span>Coupon Discount</span>
                                            <span>−{formatPrice(parseFloat(order.discount_amount as string || "0"))}</span>
                                        </div>
                                    )
                                )}

                                <div className="flex justify-between text-gray-500">
                                    <span>Shipping</span>
                                    <span>{parseFloat(order.shipping_amount as string || "0") === 0 ? 'Free' : formatPrice(parseFloat(order.shipping_amount as string || "0"))}</span>
                                </div>

                                {Object.keys(taxBreakdown).length > 0 ? (
                                    Object.entries(taxBreakdown).map(([rate, amount]: any) => (
                                        <div key={rate} className="flex justify-between text-gray-500 text-xs">
                                            <span>Tax @ {rate}% {isTaxIncluded ? '(Included)' : '(Excluded)'}</span>
                                            <span>{formatPrice(amount)}</span>
                                        </div>
                                    ))
                                ) : (
                                    taxAmount > 0 && (
                                        <div className="flex justify-between text-gray-500 text-xs">
                                            <span>Tax {isTaxIncluded ? '(Included)' : '(Excluded)'}</span>
                                            <span>{formatPrice(taxAmount)}</span>
                                        </div>
                                    )
                                )}

                                {(order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.sku?.mrp || item.price || "0") * item.quantity), 0) - safeSubtotal + parseFloat(order.discount_amount as string || "0")) > 0 && (
                                    <div className="flex justify-between text-green-600 font-medium pt-1">
                                        <span>Total Savings</span>
                                        <span>{formatPrice(order.items.reduce((acc: number, item: any) => acc + (parseFloat(item.sku?.mrp || item.price || "0") * item.quantity), 0) - safeSubtotal + parseFloat(order.discount_amount as string || "0"))}</span>
                                    </div>
                                )}

                                <div className="h-px bg-gray-100 my-1" />
                                <div className="flex justify-between font-bold text-gray-900 text-base">
                                    <span>Total</span>
                                    <span>{formatPrice(totalAmount)}</span>
                                </div>

                                {parseFloat(order.upfront_amount || "0") > 0 && (
                                    <div className="mt-2 p-3 bg-orange-50 border border-orange-100 rounded-lg space-y-2">
                                        <div className="flex justify-between text-sm text-orange-800 font-semibold">
                                            <span>Upfront (Non Refundable)</span>
                                            <span>{formatPrice(parseFloat(order.upfront_amount))}</span>
                                        </div>
                                        <div className="flex justify-between text-xs text-orange-700 font-bold">
                                            <span>Due on Delivery</span>
                                            <span>{formatPrice(totalAmount - parseFloat(order.upfront_amount))}</span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

                {/* Actions */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link 
                        href="/orders" 
                        className="w-full flex items-center justify-center gap-2 bg-white border border-gray-200 text-gray-900 font-bold py-3.5 px-6 rounded-xl hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        <Truck className="w-4 h-4" />
                        Track Order
                    </Link>
                    <Link 
                        href="/shop" 
                        className="w-full flex items-center justify-center gap-2 bg-black text-white font-bold py-3.5 px-6 rounded-xl hover:bg-gray-800 transition-colors shadow-sm"
                    >
                        Continue Shopping <ArrowRight className="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>
    );
}
