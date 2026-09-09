import { useEffect, useState } from 'react';
import api from '@/lib/api';
import { useAuthStore } from '@/store/auth';

import { Link } from '@inertiajs/react';

import { formatPrice } from '@/lib/utils';
import { router, usePage } from '@inertiajs/react';

interface OrderItem {
    id: number;
    product_name: string;
    variant_name: string;
    image_url: string;
    quantity: number;
    price: string | number;
    total: string;
    sku?: {
        product?: {
            featured_image?: string;
        }
    }
}

interface Address {
    name: string;
    email: string;
    phone: string;
    address_line1: string;
    address_line2?: string;
    city: string;
    state: string;
    zip_code: string;
}

interface Order {
    uuid: string;
    order_number: string;
    total_amount: string | number;
    subtotal?: string | number;
    shipping_amount: string | number;
    tax_amount: string | number;
    discount_amount: string | number;
    status: string;
    payment_method: string;
    payment_status: string;
    created_at: string;
    items: OrderItem[];
    shipping_address: Address;
    tracking_url: string | null;
    tracking_number: string | null;
    courier_partner: string | null;
    has_tracking: boolean;
    tax_breakdown?: any;
}

export default function OrderDetailsPage({ uuid }: { uuid: string }) {
    const { user } = useAuthStore();
    const { settings: settings } = usePage().props as any;
    
    const [order, setOrder] = useState<Order | null>(null);
    const [loading, setLoading] = useState(true);

    const [actionModal, setActionModal] = useState<'cancel' | 'return' | 'exchange' | null>(null);
    const [actionLoading, setActionLoading] = useState(false);
    
    const getActionFee = (action: 'cancel' | 'return' | 'exchange') => {
        const method = order?.payment_method === 'cod' ? 'cod' : 'prepaid';
        const rules = settings?.shipping_rules?.[method];
        if (rules && rules[`${action}_fee`]) {
            const percent = parseFloat(rules[`${action}_fee`]);
            const baseValue = order?.items.reduce((acc: number, item: any) => acc + (parseFloat(item.price as string || "0") * item.quantity), 0) || 0;
            return (percent / 100) * baseValue;
        }
        return 0;
    };

    const handleActionSubmit = async () => {
        if (!actionModal || !order) return;
        setActionLoading(true);
        try {
            await api.post(`/api/my-orders/${order.uuid}/${actionModal}`);
            setActionModal(null);
            fetchOrder();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Failed to process request.');
        } finally {
            setActionLoading(false);
        }
    };

    useEffect(() => {
        if (!user) {
            router.visit('/login');
            return;
        }
        fetchOrder();
    }, [user, uuid]);

    const fetchOrder = async () => {
        try {
            const res = await api.get(`/api/my-orders/${uuid}`);
            setOrder(res.data);
        } catch (error) {
            console.error("Error fetching order:", error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusStyle = (status: string) => {
        switch (status?.toLowerCase()) {
            case 'pending': return 'bg-amber-100 text-amber-800 border-amber-200';
            case 'processing': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'shipped': return 'bg-purple-100 text-purple-800 border-purple-200';
            case 'delivered': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
            case 'cancelled': return 'bg-rose-100 text-rose-800 border-rose-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    if (loading) return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
        </div>
    );
    
    if (!order) return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50">
            <p className="text-gray-500 mb-4 text-lg">Order not found.</p>
            <Link href="/orders" className="text-black font-semibold hover:underline">Back to Orders</Link>
        </div>
    );

    const safeSubtotal = order.items.reduce((acc, item) => acc + (parseFloat(item.price as string || "0") * item.quantity), 0);

    const hasNonReturnableItems = order.items.some((item: any) => {
        return item.sku?.product?.is_returnable === 0 || item.sku?.product?.is_returnable === false;
    });

    return (
        <div className="min-h-screen bg-gray-50 py-12 md:py-20 font-sans">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* Header */}
                <div className="mb-8">
                    <Link href="/orders" className="inline-flex items-center text-sm text-gray-500 hover:text-black transition-colors mb-6 font-medium">
                        <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Orders
                    </Link>

                    <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
                                Order #{order.order_number}
                            </h1>
                            <p className="text-sm text-gray-500 mt-2 font-medium">
                                Placed on {new Date(order.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' })} at {new Date(order.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                            </p>
                        </div>
                        <div>
                            <span className={`inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border ${getStatusStyle(order.status)} shadow-sm`}>
                                {order.status}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Tracking Banner */}
                {(order.status === 'shipped' || (order.status === 'delivered' && order.tracking_number)) && (
                    <div className="mb-8 bg-gradient-to-r from-gray-900 to-black rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-xl relative overflow-hidden">
                        <div className="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl pointer-events-none"></div>
                        <div className="relative z-10">
                            <h3 className="text-lg font-bold text-white tracking-wide">Tracking Information</h3>
                            <p className="text-gray-300 mt-2 text-sm">
                                {order.courier_partner ? `${order.courier_partner} - ` : ''}
                                {order.tracking_number ? <span className="font-mono bg-white/10 px-2.5 py-1 rounded-md text-white border border-white/20">{order.tracking_number}</span> : 'Preparing tracking details...'}
                            </p>
                        </div>
                        {order.tracking_url && (
                            <a 
                                href={order.tracking_url} 
                                target="_blank" 
                                rel="noopener noreferrer"
                                className="relative z-10 inline-flex items-center justify-center px-6 py-3 bg-white text-black text-sm font-bold rounded-xl hover:bg-gray-100 transition-transform hover:scale-105 active:scale-95 shadow-lg w-full sm:w-auto"
                            >
                                Track Package
                            </a>
                        )}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {/* Items List */}
                    <div className="lg:col-span-7 xl:col-span-8 space-y-6">
                        <div className="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100">
                            <h2 className="text-xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Items Ordered</h2>
                            <div className="space-y-6">
                                {order.items.map((item) => {
                                    const imgUrl = item.image_url || item.sku?.product?.featured_image;
                                    return (
                                        <div key={item.id} className="flex gap-4 sm:gap-6 group">
                                            <div className="w-24 h-32 sm:w-28 sm:h-36 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 relative shadow-sm transition-transform group-hover:scale-[1.02]">
                                                {imgUrl ? (
                                                    <img 
                                                        src={imgUrl} 
                                                        alt={item.product_name} 
                                                        className="w-full h-full object-cover object-top"
                                                    />
                                                ) : (
                                                    <div className="flex items-center justify-center h-full text-xs text-gray-400 font-medium bg-gray-100">No Image</div>
                                                )}
                                            </div>
                                            <div className="flex-1 min-w-0 flex flex-col justify-center">
                                                <div className="flex justify-between items-start gap-4">
                                                    <div>
                                                        <h3 className="text-base font-bold text-gray-900 leading-tight">
                                                            {item.product_name}
                                                        </h3>
                                                        {item.variant_name && (
                                                            <p className="text-sm text-gray-500 mt-1 font-medium">{item.variant_name}</p>
                                                        )}
                                                        {item.delivery_date && (
                                                            <p className="text-xs text-green-700 mt-2 font-medium bg-green-50 inline-block px-2 py-1 rounded border border-green-100">
                                                                Delivered by: <span className="font-bold">{item.delivery_date}</span>
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                                
                                                <div className="mt-4 flex items-center gap-6">
                                                    <div className="text-sm font-medium bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 text-gray-700">
                                                        Qty: {item.quantity}
                                                    </div>
                                                    <div className="text-base font-bold text-gray-900">
                                                        {formatPrice(parseFloat(item.price as string || "0") * item.quantity)}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>

                    {/* Order Summary & Customer Details */}
                    <div className="lg:col-span-5 xl:col-span-4 space-y-6">
                        
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="p-6 sm:p-8">
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

                                    {order.tax_breakdown && Object.keys(order.tax_breakdown).length > 0 ? (
                                        Object.entries(order.tax_breakdown).map(([rate, amount]: any) => (
                                            <div key={rate} className="flex justify-between text-gray-500 text-xs">
                                                <span>Tax @ {rate}% {Math.abs((parseFloat(order.total_amount as string || "0") + parseFloat(order.discount_amount as string || "0")) - (safeSubtotal + parseFloat(order.shipping_amount as string || "0"))) < 0.1 ? '(Included)' : '(Excluded)'}</span>
                                                <span>{formatPrice(amount)}</span>
                                            </div>
                                        ))
                                    ) : (
                                        parseFloat(order.tax_amount as string || "0") > 0 && (
                                            <div className="flex justify-between text-gray-500 text-xs">
                                                <span>Tax {Math.abs((parseFloat(order.total_amount as string || "0") + parseFloat(order.discount_amount as string || "0")) - (safeSubtotal + parseFloat(order.shipping_amount as string || "0"))) < 0.1 ? '(Included)' : '(Excluded)'}</span>
                                                <span>{formatPrice(parseFloat(order.tax_amount as string || "0"))}</span>
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
                                        <span>{formatPrice(parseFloat(order.total_amount as string || "0"))}</span>
                                    </div>

                                    {parseFloat(order.upfront_amount as string || "0") > 0 && (
                                        <div className="mt-2 p-3 bg-orange-50 border border-orange-100 rounded-lg space-y-2">
                                            <div className="flex justify-between text-sm text-orange-800 font-semibold">
                                                <span>Upfront (Non Refundable)</span>
                                                <span>{formatPrice(parseFloat(order.upfront_amount as string || "0"))}</span>
                                            </div>
                                            <div className="flex justify-between text-xs text-orange-700 font-bold">
                                                <span>Due on Delivery</span>
                                                <span>{formatPrice(Math.max(0, parseFloat(order.total_amount as string || "0") - parseFloat(order.upfront_amount as string || "0")))}</span>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="p-6 sm:p-8">
                                <h2 className="text-lg font-bold text-gray-900 mb-6">Order Details</h2>
                                
                                <div className="space-y-6">
                                    <div>
                                        <h3 className="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3">Shipping Address</h3>
                                        <p className="text-sm text-gray-800 leading-relaxed font-medium">
                                            <span className="block text-gray-900 font-bold mb-1">{order.shipping_address.name}</span>
                                            {order.shipping_address.address_line1}<br />
                                            {order.shipping_address.address_line2 && <>{order.shipping_address.address_line2}<br /></>}
                                            {order.shipping_address.city}, {order.shipping_address.state} {order.shipping_address.zip_code}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 className="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3">Contact Details</h3>
                                        <div className="text-sm text-gray-800 font-medium space-y-1.5">
                                            <p className="flex items-center gap-2">
                                                <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                {order.shipping_address.email}
                                            </p>
                                            <p className="flex items-center gap-2">
                                                <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                {order.shipping_address.phone}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4 pt-6 border-t border-gray-100">
                                        <div>
                                            <h3 className="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Payment Method</h3>
                                            <p className="text-sm text-gray-900 font-bold">{order.payment_method}</p>
                                        </div>
                                        <div>
                                            <h3 className="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Payment Status</h3>
                                            <p className={`text-sm font-extrabold capitalize ${order.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'}`}>
                                                {order.payment_status}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Order Actions */}
                        {(order.status === 'pending' || order.status === 'processing' || order.status === 'delivered') && (
                            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div className="p-6 sm:p-8">
                                    <h2 className="text-lg font-bold text-gray-900 mb-4">Order Actions</h2>
                                    <div className="flex flex-col gap-3">
                                        {(order.status === 'pending' || order.status === 'processing') && (
                                            <button onClick={() => setActionModal('cancel')} className="w-full py-3 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl transition-colors">
                                                Cancel Order
                                            </button>
                                        )}
                                        {order.status === 'delivered' && (
                                            <>
                                                {!hasNonReturnableItems ? (
                                                    <button onClick={() => setActionModal('return')} className="w-full py-3 text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl transition-colors">
                                                        Return Order
                                                    </button>
                                                ) : (
                                                    <div className="bg-orange-50 border border-orange-100 p-3 rounded-xl mb-1">
                                                        <p className="text-xs text-orange-800 font-semibold text-center">
                                                            This order contains non-returnable items. You can only exchange this order.
                                                        </p>
                                                    </div>
                                                )}
                                                <button onClick={() => setActionModal('exchange')} className="w-full py-3 text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors">
                                                    Exchange Order
                                                </button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                    </div>
                </div>
                
                {/* Action Modal */}
                {actionModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                        <div className="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                            <div className="p-6">
                                <h3 className="text-xl font-bold text-gray-900 capitalize mb-2">{actionModal} Order</h3>
                                <p className="text-sm text-gray-600 mb-6">Are you sure you want to {actionModal} this order?</p>
                                
                                <div className="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                                    <div className="flex justify-between items-center text-sm font-medium">
                                        <span className="text-gray-700 capitalize">{actionModal} Fee</span>
                                        <span className="text-gray-900">{formatPrice(getActionFee(actionModal))}</span>
                                    </div>
                                    {order?.payment_method === 'cod' && actionModal === 'cancel' && (!settings?.shipping_rules?.cod?.upfront_refundable || settings?.shipping_rules?.cod?.upfront_refundable != '1') && (
                                        <p className="text-xs text-red-500 font-semibold mt-3 pt-3 border-t border-gray-200">Note: The upfront shipping amount is non-refundable.</p>
                                    )}
                                    {order?.payment_method === 'prepaid' && (
                                        <p className="text-xs text-gray-500 font-medium mt-3 pt-3 border-t border-gray-200">Note: The fee will be automatically deducted from your refund.</p>
                                    )}
                                </div>

                                <div className="flex gap-3">
                                    <button 
                                        onClick={() => setActionModal(null)} 
                                        className="flex-1 px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                                    >
                                        No, Keep It
                                    </button>
                                    <button 
                                        onClick={handleActionSubmit}
                                        disabled={actionLoading}
                                        className={`flex-1 px-4 py-2.5 text-sm font-bold text-white bg-black hover:bg-gray-900 rounded-xl transition-colors flex items-center justify-center ${actionLoading ? 'opacity-70 cursor-not-allowed' : ''}`}
                                    >
                                        {actionLoading ? <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> : 'Confirm'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
