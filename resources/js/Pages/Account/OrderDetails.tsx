import { useEffect, useState } from 'react';
import api from '@/lib/api';
import { useAuthStore } from '@/store/auth';

import { Link } from '@inertiajs/react';

import { formatPrice } from '@/lib/utils';
import { router, usePage } from '@inertiajs/react';
import { Star, StarHalf } from 'lucide-react';

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
    review?: {
        id: number;
        rating: number;
        comment: string;
        is_approved: boolean;
    };
    product_id: number;
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
    order_return_valid_till?: string | null;
    order_exchange_valid_till?: string | null;
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
    const [selectedActionItems, setSelectedActionItems] = useState<Record<number, number>>({});
    
    // Review Modal State
    const [reviewModalItem, setReviewModalItem] = useState<OrderItem | null>(null);
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewComment, setReviewComment] = useState('');
        const [reviewLoading, setReviewLoading] = useState(false);
    const [reviewImages, setReviewImages] = useState<File[]>([]);
    const [reviewImageError, setReviewImageError] = useState<string>('');
    const [existingImages, setExistingImages] = useState<any[]>([]);
    const [deletedImages, setDeletedImages] = useState<number[]>([]);
    const [isConvertingHeic, setIsConvertingHeic] = useState(false);

    const openReviewModal = (item: OrderItem) => {
        setReviewModalItem(item);
        setReviewImages([]);
        setReviewImageError('');
        setExistingImages([]);
        setDeletedImages([]);
        if (item.review) {
            if (item.review.images) {
                setExistingImages(item.review.images);
            }
            setReviewRating(item.review.rating);
            setReviewComment(item.review.comment || '');
        } else {
            setReviewRating(5);
            setReviewComment('');
        }
    };

    const handleImageSelection = async (e: React.ChangeEvent<HTMLInputElement>) => {
        setReviewImageError('');
        if (e.target.files) {
            let files = Array.from(e.target.files);
            
            // Handle HEIC/HEIF conversion
            const hasHeic = files.some(f => f.name.toLowerCase().endsWith('.heic') || f.name.toLowerCase().endsWith('.heif'));
            if (hasHeic) {
                setIsConvertingHeic(true);
                try {
                    files = await Promise.all(files.map(async (file) => {
                        if (file.name.toLowerCase().endsWith('.heic') || file.name.toLowerCase().endsWith('.heif')) {
                                                        // Dynamically import from CDN to bypass Vite worker mangling
                            const heicModule = await import('https://cdn.jsdelivr.net/npm/heic2any@0.0.4/+esm');
                            const convertFn = typeof heicModule.default === 'function' ? heicModule.default : heicModule;
                            
                            try {
                                const convertedBlob = await convertFn({
                                    blob: new Blob([file], { type: file.type || 'image/heic' }),
                                    toType: "image/webp",
                                    quality: 0.8
                                });
                                const blob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                                return new File([blob], file.name.replace(/\.heic|\.heif/i, '.webp'), {
                                    type: 'image/webp'
                                });
                            } catch (err) {
                                console.warn("Local HEIC conversion failed, falling back to server conversion:", err);
                                // Return the raw file UNMODIFIED so the server knows it's a real HEIC
                                return file; 
                            }
                        }
                        return file;
                    }));
                } catch (error) {
                    console.error("Unexpected HEIC error:", error);
                }
                setIsConvertingHeic(false);
            }

            // Check count
            if (existingImages.length + reviewImages.length + files.length > 5) {
                setReviewImageError('Maximum 5 images allowed per review.');
                return;
            }
            
            // Check size (8MB = 8 * 1024 * 1024 = 8388608 bytes)
            const oversized = files.find(f => f.size > 8388608);
            if (oversized) {
                setReviewImageError('One or more images exceed the 8MB maximum file size limit.');
                return;
            }
            
            setReviewImages(prev => [...prev, ...files]);
        }
    };
    
    const removeExistingImage = (id: number) => {
        setExistingImages(prev => prev.filter(img => img.id !== id));
        setDeletedImages(prev => [...prev, id]);
    };
    
    const removeImage = (index: number) => {
        setReviewImages(prev => prev.filter((_, i) => i !== index));
    };

        const submitReview = async (e: any) => {
        e.preventDefault();
        if (!user) {
            router.visit('/login');
            return;
        }
        if (!reviewModalItem) return;
        
        setReviewLoading(true);
        setReviewImageError('');
        
        try {
            const formData = new FormData();
            formData.append('rating', reviewRating.toString());
            if (reviewComment) formData.append('comment', reviewComment);
            if (reviewModalItem.order_id) formData.append('order_id', reviewModalItem.order_id.toString());
            
            if (reviewImages.length > 0) {
                reviewImages.forEach((img, i) => formData.append(`images[${i}]`, img));
            }

            let endpoint = '';

            if (reviewModalItem.review) {
                // Update
                formData.append('_method', 'put');
                if (deletedImages.length > 0) {
                    deletedImages.forEach((imgId, i) => formData.append(`deleted_images[${i}]`, imgId.toString()));
                }
                endpoint = `/api/reviews/${reviewModalItem.review.id}`;
            } else {
                // Store
                endpoint = `/api/products/${reviewModalItem.product_id}/reviews`;
            }

            await api.post(endpoint, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setReviewModalItem(null);
            if (typeof fetchOrder === 'function') fetchOrder();
            if (typeof fetchData === 'function') fetchData();
            
        } catch (err: any) {
            console.error("Review API Error:", err);
            // Handle validation errors (422) or custom errors (403)
            let errMsg = "Error submitting review. Please try again.";
            if (err.response?.data?.error) {
                errMsg = err.response.data.error;
            } else if (err.response?.data?.message) {
                errMsg = err.response.data.message;
            } else if (err.response?.data?.errors) {
                const firstError = Object.values(err.response.data.errors)[0];
                if (firstError) errMsg = String(firstError);
            }
            setReviewImageError(errMsg);
        } finally {
            setReviewLoading(false);
        }
    };
    const getActionFee = (action: string) => {
        if (!settings?.shipping_rules?.fees) return 0;
        return parseFloat(settings.shipping_rules.fees[`${action}_fee`] || '0');
    };

    const handleActionSubmit = async () => {
        if (!user) {
            router.visit('/login');
            return;
        }
        if (!actionModal || !order) return;
        setActionLoading(true);
        try {
            const payload: any = {};
            if (actionModal === 'return' || actionModal === 'exchange') {
                const items = Object.entries(selectedActionItems)
                    .filter(([_, qty]) => qty > 0)
                    .map(([id, qty]) => ({ id: Number(id), quantity: qty }));
                if (items.length > 0) {
                    payload.items = items;
                }
            }
            await api.post(`/api/my-orders/${order.uuid}/${actionModal}`, payload);
            setActionModal(null);
            fetchOrder();
        } catch (err: any) {
            if (err.response?.status === 401) {
                router.visit('/login');
            } else {
                alert(err.response?.data?.message || 'Failed to process request.');
            }
        } finally {
            setActionLoading(false);
        }
    };

    useEffect(() => {
        fetchOrder();
    }, [uuid]);

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
                                {order.status === 'delivered' && (
                                    <span className="ml-1 opacity-80 font-medium">
                                        (On {new Date(order.updated_at || order.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short' })})
                                    </span>
                                )}
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
                                                            <div className="flex flex-col gap-1 mt-2">
                                                                <p className="text-xs text-green-700 font-medium bg-green-50 inline-block px-2 py-1 rounded border border-green-100 w-fit">
                                                                    {order.status === 'delivered' ? 'Delivered on: ' : 'Delivered by: '} <span className="font-bold">{item.delivery_date}</span>
                                                                </p>
                                                                {(item as any).exchange_valid_till && (
                                                                    <p className="text-[11px] text-gray-500 font-medium">
                                                                        Exchange valid till: <span className="font-bold text-gray-700">{(item as any).exchange_valid_till}</span>
                                                                    </p>
                                                                )}
                                                                {(item as any).return_valid_till && (
                                                                    <p className="text-[11px] text-gray-500 font-medium">
                                                                        Return valid till: <span className="font-bold text-gray-700">{(item as any).return_valid_till}</span>
                                                                    </p>
                                                                )}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                
                                                <div className="mt-4 flex flex-wrap items-center gap-4">
                                                    <div className="flex items-center gap-6">
                                                        <div className="text-sm font-medium bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 text-gray-700">
                                                            Qty: {item.quantity}
                                                        </div>
                                                        <div className="text-base font-bold text-gray-900">
                                                            {formatPrice(parseFloat(item.price as string || "0") * item.quantity)}
                                                        </div>
                                                    </div>
                                                    {order.status === 'delivered' && (
                                                        <button 
                                                            onClick={() => openReviewModal(item)}
                                                            className="ml-auto text-xs font-bold uppercase tracking-wider px-4 py-2 border rounded-lg transition-colors hover:bg-gray-50"
                                                        >
                                                            {item.review ? 'Edit Review' : 'Write a Review'}
                                                        </button>
                                                    )}
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
                                        {order.shipping_address ? (
                                            <p className="text-sm text-gray-800 leading-relaxed font-medium">
                                                <span className="block text-gray-900 font-bold mb-1">{order.shipping_address.name}</span>
                                                {order.shipping_address.address_line1}<br />
                                                {order.shipping_address.address_line2 && <>{order.shipping_address.address_line2}<br /></>}
                                                {order.shipping_address.city}, {order.shipping_address.state} {order.shipping_address.zip_code}
                                            </p>
                                        ) : order.pos_location ? (
                                            <p className="text-sm text-gray-800 leading-relaxed font-medium">
                                                <span className="block text-gray-900 font-bold mb-1">In-Store Purchase</span>
                                                {order.pos_location.name}<br />
                                                {order.pos_location.address}<br />
                                                {order.pos_location.address_line_2 && <>{order.pos_location.address_line_2}<br /></>}
                                                {order.pos_location.city}, {order.pos_location.state} {order.pos_location.pincode}
                                            </p>
                                        ) : (
                                            <p className="text-sm text-gray-500 italic">No shipping address provided.</p>
                                        )}
                                    </div>

                                    <div>
                                        <h3 className="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3">Contact Details</h3>
                                        {order.shipping_address ? (
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
                                        ) : (order.customer_phone || order.customer_name) ? (
                                            <div className="text-sm text-gray-800 font-medium space-y-1.5">
                                                {order.customer_name && (
                                                    <p className="flex items-center gap-2 font-bold">
                                                        {order.customer_name}
                                                    </p>
                                                )}
                                                {order.customer_phone && (
                                                    <p className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                        {order.customer_phone}
                                                    </p>
                                                )}
                                            </div>
                                        ) : (
                                            <p className="text-sm text-gray-500 italic">No contact details provided.</p>
                                        )}
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
                                            <button onClick={() => { setSelectedActionItems({}); setActionModal('cancel'); }} className="w-full py-3 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl transition-colors">
                                                Cancel Order
                                            </button>
                                        )}
                                        {order.status === 'delivered' && (
                                            <>
                                                {order.order_exchange_valid_till && (
                                                    <p className="text-xs font-semibold text-gray-500 mb-1">
                                                        Exchange {order.can_exchange ? 'available till' : 'was available till'} <span className="text-gray-900">{order.order_exchange_valid_till}</span>
                                                    </p>
                                                )}
                                                {order.order_return_valid_till && (
                                                    <p className="text-xs font-semibold text-gray-500 mb-2">
                                                        Return {order.can_return ? 'available till' : 'was available till'} <span className="text-gray-900">{order.order_return_valid_till}</span>
                                                    </p>
                                                )}
                                                
                                                {(order.can_return || order.can_exchange) && (
                                                    <div className="space-y-2 mt-4">
                                                        {order.can_return && !hasNonReturnableItems ? (
                                                            <button onClick={() => { setSelectedActionItems({}); setActionModal('return'); }} className="w-full py-3 text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl transition-colors">
                                                                Return Order
                                                            </button>
                                                        ) : order.can_exchange ? (
                                                            <div className="bg-orange-50 border border-orange-100 p-3 rounded-xl mb-1">
                                                                <p className="text-xs text-orange-800 font-semibold text-center">
                                                                    This order contains non-returnable items. You can only exchange this order.
                                                                </p>
                                                            </div>
                                                        ) : null}
                                                        
                                                        {order.can_exchange && (
                                                            <button onClick={() => { setSelectedActionItems({}); setActionModal('exchange'); }} className="w-full py-3 text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors">
                                                                Exchange Order
                                                            </button>
                                                        )}
                                                    </div>
                                                )}
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
                                
                                {(actionModal === 'return' || actionModal === 'exchange') && order?.items && (
                                    <div className="mb-6 space-y-3 max-h-60 overflow-y-auto pr-2">
                                        <p className="text-xs font-bold text-gray-500 uppercase">Select items to {actionModal}:</p>
                                        {order.items.map(item => {
                                            const maxQty = item.quantity - (item.returned_quantity || 0);
                                            if (maxQty <= 0) return null;
                                            const selectedQty = selectedActionItems[item.id] || 0;
                                            
                                            return (
                                                <div key={item.id} className="flex items-center justify-between p-3 border border-gray-100 rounded-xl bg-gray-50">
                                                    <div className="flex items-center gap-3">
                                                        <input 
                                                            type="checkbox" 
                                                            checked={selectedQty > 0}
                                                            onChange={(e) => {
                                                                setSelectedActionItems(prev => ({ ...prev, [item.id]: e.target.checked ? maxQty : 0 }));
                                                            }}
                                                            className="w-4 h-4 text-black border-gray-300 rounded focus:ring-black"
                                                        />
                                                        <div className="flex flex-col">
                                                            <span className="text-sm font-bold text-gray-900 line-clamp-1">{item.product_name}</span>
                                                            {item.variant_name && <span className="text-xs text-gray-500">{item.variant_name}</span>}
                                                        </div>
                                                    </div>
                                                    {selectedQty > 0 && maxQty > 1 && (
                                                        <select 
                                                            value={selectedQty}
                                                            onChange={(e) => setSelectedActionItems(prev => ({ ...prev, [item.id]: Number(e.target.value) }))}
                                                            className="ml-2 text-sm border-gray-200 rounded-lg py-1 px-2 pr-8 focus:ring-0 focus:border-gray-300"
                                                        >
                                                            {Array.from({ length: maxQty }, (_, i) => i + 1).map(num => (
                                                                <option key={num} value={num}>{num}</option>
                                                            ))}
                                                        </select>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}

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
            
            {/* Review Modal */}
            {reviewModalItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 className="text-xl font-bold mb-4">{reviewModalItem.review ? 'Edit Review' : 'Write a Review'}</h3>
                        <div className="flex gap-4 items-center mb-6 border-b pb-4">
                            <div className="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden">
                                {(reviewModalItem.image_url || reviewModalItem.sku?.product?.featured_image) ? (
                                    <img src={reviewModalItem.image_url || reviewModalItem.sku?.product?.featured_image} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-[10px] text-gray-400">NA</div>
                                )}
                            </div>
                            <div className="flex-1">
                                <p className="font-bold text-gray-900 leading-tight">{reviewModalItem.product_name}</p>
                                {reviewModalItem.variant_name && <p className="text-xs text-gray-500 mt-1">{reviewModalItem.variant_name}</p>}
                            </div>
                        </div>
                        
                        <form onSubmit={submitReview}>
                            <div className="mb-4">
                                <label className="block text-sm font-bold text-gray-700 mb-2">Rating</label>
                                <div className="flex gap-2">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                    <div key={star} className="relative w-8 h-8 group transition-transform hover:scale-110">
                                        <Star className="w-8 h-8 text-gray-300 absolute inset-0 pointer-events-none" />
                                        {reviewRating >= star ? (
                                            <Star className="w-8 h-8 fill-yellow-400 text-yellow-400 absolute inset-0 pointer-events-none" />
                                        ) : reviewRating >= star - 0.5 ? (
                                            <StarHalf className="w-8 h-8 fill-yellow-400 text-yellow-400 absolute inset-0 pointer-events-none" />
                                        ) : null}
                                        <div className="absolute inset-0 flex">
                                            <button type="button" onClick={() => setReviewRating(star - 0.5)} className="w-1/2 h-full z-10 focus:outline-none cursor-pointer" />
                                            <button type="button" onClick={() => setReviewRating(star)} className="w-1/2 h-full z-10 focus:outline-none cursor-pointer" />
                                        </div>
                                    </div>
                                ))}
                                </div>
                            </div>
                            
                            <div className="mb-6">
                                <label className="block text-sm font-bold text-gray-700 mb-2">Review Comment (Optional)</label>
                                <textarea 
                                    rows={4} 
                                    value={reviewComment}
                                    onChange={(e) => setReviewComment(e.target.value)}
                                    className="w-full border border-gray-300 rounded-xl focus:ring-primary focus:border-primary text-sm p-3"
                                    placeholder="What did you like or dislike? What did you use this product for?"
                                ></textarea>
                            </div>
                            
                            <div className="mb-6">
                                <label className="block text-sm font-bold text-gray-700 mb-2">Photos (Max 5, up to 8MB each, supports HEIC)</label>
                                
                                <div className="flex flex-wrap gap-3 mb-3">
                                    {existingImages.map((img) => (
                                        <div key={`existing-${img.id}`} className="relative w-16 h-16 rounded-lg border border-gray-200 shadow-sm group">
                                                                                        {img.image_path.toLowerCase().endsWith('.heic') || img.image_path.toLowerCase().endsWith('.heif') ? (
                                                <div className="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    <span className="text-[8px] font-bold mt-0.5">HEIC</span>
                                                </div>
                                            ) : (
                                                <img src={`/${img.image_path}`} className="w-full h-full object-cover rounded-lg" onError={(e) => {
                                                    e.currentTarget.onerror = null;
                                                    e.currentTarget.parentElement!.innerHTML = '<div class="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-400"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><span class="text-[8px] font-bold mt-0.5">IMG</span></div>';
                                                }} />
                                            )}
                                            <button type="button" onClick={() => removeExistingImage(img.id)} className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors z-10">
                                                <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    ))}
                                    
                                    {reviewImages.map((file, i) => (
                                        <div key={`new-${i}`} className="relative w-16 h-16 rounded-lg border border-gray-200 shadow-sm group">
                                                                                        {file.name.toLowerCase().endsWith('.heic') || file.name.toLowerCase().endsWith('.heif') ? (
                                                <div className="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    <span className="text-[8px] font-bold mt-0.5">HEIC</span>
                                                </div>
                                            ) : (
                                                <img src={URL.createObjectURL(file)} className="w-full h-full object-cover rounded-lg" onError={(e) => {
                                                    e.currentTarget.onerror = null;
                                                    e.currentTarget.parentElement!.innerHTML = '<div class="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-400"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><span class="text-[8px] font-bold mt-0.5">IMG</span></div>';
                                                }} />
                                            )}
                                            <button type="button" onClick={() => removeImage(i)} className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors z-10">
                                                <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    ))}
                                    
                                    {(existingImages.length + reviewImages.length) < 5 && (
                                        <label className="w-16 h-16 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 hover:border-gray-500 hover:text-gray-600 transition-colors cursor-pointer bg-gray-50 relative">
                                            {isConvertingHeic ? (
                                                <div className="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                                            ) : (
                                                <>
                                                    <svg className="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
                                                    <input type="file" accept="image/*,.heic,.heif,image/heic,image/heif" multiple onChange={handleImageSelection} className="hidden" disabled={isConvertingHeic} />
                                                </>
                                            )}
                                        </label>
                                    )}
                                </div>
                                
                                {reviewImageError && (
                                    <p className="text-xs text-red-500 font-semibold">{reviewImageError}</p>
                                )}
                            </div>
                            
                            <div className="flex justify-end gap-3">
                                <button 
                                    type="button" 
                                    onClick={() => setReviewModalItem(null)} 
                                    className="px-4 py-2 font-bold text-gray-500 hover:text-black transition-colors"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    disabled={reviewLoading}
                                    className="bg-black text-white px-6 py-2 rounded-xl font-bold text-sm tracking-wide disabled:opacity-50"
                                >
                                    {reviewLoading ? 'Submitting...' : 'Submit Review'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
