import { useEffect, useState } from 'react';
import api from '@/lib/api';
import { useAuthStore } from '@/store/auth';

import { Link, usePage, router } from '@inertiajs/react';


import { formatPrice } from '@/lib/utils';
import { Star, StarHalf } from 'lucide-react';

interface OrderItem {
    id: number;
    product_name: string;
    variant_name: string;
    image_url: string;
    quantity: number;
    price: string;
}

interface Order {
    uuid: string;
    order_number: string;
    total_amount: string;
    status: string;
    created_at: string;
    items_count: number;
    items: OrderItem[];
    tracking_url: string | null;
    has_tracking: boolean;
    courier_partner: string | null;
}

interface GiftCard {
    id: number;
    card_number: string;
    plain_code: string;
    amount: number;
    remaining_amount: number;
    status: string;
    status_badge: { label: string; class: string };
    template_name: string;
    created_at: string;
    expires_at: string | null;
    is_redeemable: boolean;
}

export default function MyOrdersPage() {
    const { user } = useAuthStore();
    const { settings: settings } = usePage().props as any;
    const [activeTab, setActiveTab] = useState<'orders' | 'giftcards'>('orders');
    
    // Orders state
    const [orders, setOrders] = useState<Order[]>([]);
    const [orderMeta, setOrderMeta] = useState<any>(null);
    const [orderPage, setOrderPage] = useState(1);
    
    // Gift Cards state
    const [giftCards, setGiftCards] = useState<GiftCard[]>([]);
    
    const [loading, setLoading] = useState(true);

    const [reviewOrder, setReviewOrder] = useState<any>(null);
    const [reviewModalItem, setReviewModalItem] = useState<any>(null);
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewComment, setReviewComment] = useState('');
        const [reviewLoading, setReviewLoading] = useState(false);
    const [reviewImages, setReviewImages] = useState<File[]>([]);
    const [reviewImageError, setReviewImageError] = useState<string>('');
    const [existingImages, setExistingImages] = useState<any[]>([]);
    const [deletedImages, setDeletedImages] = useState<number[]>([]);
    const [isConvertingHeic, setIsConvertingHeic] = useState(false);

    const handleReviewClick = (order: any, e: any) => {
        e.preventDefault();
        if (order.items && order.items.length === 1) {
            openReviewModal(order.items[0]);
        } else {
            setReviewOrder(order);
        }
    };

    const openReviewModal = (item: any) => {
        setReviewOrder(null);
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

    useEffect(() => {
        if (user) {
            fetchData();
        } else {
            setLoading(false);
        }
    }, [user, orderPage]);

    const fetchData = async () => {
        setLoading(true);
        try {
            const [ordersRes, gcRes] = await Promise.all([
                api.get(`/api/my-orders?page=${orderPage}`),
                api.get('/api/gift-cards/my-cards')
            ]);
            
            // Laravel Paginate returns data in .data.data
            setOrders(ordersRes.data.data);
            setOrderMeta(ordersRes.data);
            setGiftCards(gcRes.data);
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
            case 'processing': return 'bg-blue-100 text-blue-700 border-blue-200';
            case 'shipped': return 'bg-indigo-100 text-indigo-700 border-indigo-200';
            case 'delivered': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
            case 'cancelled': return 'bg-rose-100 text-rose-700 border-rose-200';
            default: return 'bg-slate-100 text-slate-700 border-slate-200';
        }
    };

    if (!user) {
        return (
            <div className="min-h-[70vh] flex flex-col items-center justify-center p-4">
                <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg className="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 className="text-2xl font-heading font-bold mb-2">Login Required</h2>
                <p className="text-gray-500 mb-8 text-center max-w-xs">Please sign in to your account to view and track your orders.</p>
                <Link 
                    href="/login" 
                    className="bg-primary text-white px-10 py-3 rounded-full font-bold uppercase tracking-wider text-xs shadow-lg shadow-primary/20 hover:scale-105 transition-transform active:scale-95"
                >
                    Sign In
                </Link>
            </div>
        );
    }

    if (loading && orders.length === 0) {
        return (
            <div className="max-w-7xl mx-auto px-4 py-20">
                <div className="flex items-center gap-3 mb-10">
                    <div className="w-8 h-8 bg-gray-200 animate-pulse rounded-md"></div>
                    <div className="w-48 h-8 bg-gray-200 animate-pulse rounded-md"></div>
                </div>
                {[1, 2, 3].map(i => (
                    <div key={i} className="mb-6 h-40 bg-white border border-gray-100 rounded-2xl animate-pulse"></div>
                ))}
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#fafafa]">
            <div className="max-w-7xl mx-auto px-4 py-16">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <h1 className="text-3xl md:text-5xl font-heading font-black tracking-tight text-gray-900">
                            Account <span className="text-primary">History</span>
                        </h1>
                        <p className="text-gray-500 mt-2">Manage your orders and digital assets.</p>
                    </div>
                    
                    {/* Tabs */}
                    <div className="flex p-1.5 bg-gray-100 rounded-2xl w-fit">
                        <button 
                            onClick={() => setActiveTab('orders')}
                            className={`px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all ${activeTab === 'orders' ? 'bg-white text-black shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            Orders ({orderMeta?.total || 0})
                        </button>
                        <button 
                            onClick={() => setActiveTab('giftcards')}
                            className={`px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all ${activeTab === 'giftcards' ? 'bg-white text-black shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            Gift Cards ({giftCards.length})
                        </button>
                    </div>
                </div>

                {activeTab === 'orders' ? (
                    <>
                        {orders.length === 0 ? (
                            <div className="text-center py-20 bg-white border border-dashed border-gray-200 rounded-[2.5rem] shadow-sm">
                                <div className="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100">
                                    <svg className="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 mb-2">No orders yet</h3>
                                <p className="text-gray-500 mb-8">Looks like you haven't made your first purchase.</p>
                                <Link href="/shop" className="inline-flex items-center gap-2 bg-black text-white px-8 py-3 rounded-full font-bold uppercase tracking-wider text-xs hover:bg-gray-800 transition-all group">
                                    Start Shopping
                                    <svg className="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                <div className="grid gap-6">
                                    {orders.map(order => (
                                        <div key={order.uuid} className="group bg-white border border-gray-100 rounded-[2rem] p-6 md:p-8 transition-all duration-300 hover:shadow-[0_20px_50px_rgb(0,0,0,0.04)] hover:-translate-y-1 relative overflow-hidden">
                                            <div className="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary/5 to-transparent rounded-bl-[5rem] -mr-10 -mt-10 group-hover:scale-110 transition-transform duration-500"></div>
                                            <div className="relative z-10 flex flex-col lg:flex-row gap-8 items-start lg:items-center">
                                                <div className="flex-1 w-full lg:w-auto">
                                                    <div className="flex items-center justify-between lg:justify-start gap-4 mb-4">
                                                        <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border ${getStatusColor(order.status)}`}>
                                                            {order.status}
                                                            {order.status === 'delivered' && (
                                                                <span className="ml-1 opacity-80 font-medium">
                                                                    (On {new Date(order.updated_at || order.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short' })})
                                                                </span>
                                                            )}
                                                        </span>
                                                        <span className="text-xs text-gray-400 font-medium">
                                                            {new Date(order.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}
                                                        </span>
                                                    </div>
                                                    <h3 className="text-xl font-heading font-black text-gray-900 mb-1 group-hover:text-primary transition-colors">#{order.order_number}</h3>
                                                    <p className="text-sm text-gray-500">
                                                        Total <span className="text-gray-900 font-bold ml-1">{formatPrice(order.total_amount)}</span>
                                                        <span className="mx-2 text-gray-200">|</span>
                                                        {order.items_count} {order.items_count === 1 ? 'Item' : 'Items'}
                                                    </p>
                                                </div>
                                                <div className="flex flex-wrap gap-2 lg:gap-3 w-full lg:w-auto overflow-hidden">
                                                    {order.items?.slice(0, 4).map((item, idx) => (
                                                        <div key={item.id} title={item.delivery_date ? `${order.status === 'delivered' ? 'Delivered on' : 'Delivered by'}: ${item.delivery_date}` : item.product_name} className="relative w-14 h-18 md:w-16 md:h-20 bg-gray-50 rounded-xl overflow-hidden border border-gray-100 shrink-0 shadow-sm transition-transform hover:scale-105">
                                                            {item.image_url ? (
                                                                <img src={item.image_url} alt={item.product_name} fill className="object-cover" unoptimized />
                                                            ) : (
                                                                <div className="flex items-center justify-center h-full text-[10px] text-gray-300">NA</div>
                                                            )}
                                                            {idx === 3 && order.items.length > 4 && (
                                                                <div className="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center text-white text-xs font-bold">+{order.items.length - 3}</div>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                                <div className="w-full lg:w-auto pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-50 flex gap-2">
                                                    {order.tracking_url && (
                                                        <a href={order.tracking_url} target="_blank" rel="noopener noreferrer"
                                                           className="flex items-center justify-center gap-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-4 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all border border-indigo-200">
                                                            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                                            Track
                                                        </a>
                                                    )}
                                                    {order.status === 'delivered' && (
                                                        <button onClick={(e) => handleReviewClick(order, e)} className="flex items-center justify-center gap-1.5 bg-yellow-50 text-yellow-700 hover:bg-yellow-500 hover:text-white px-4 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all border border-yellow-200">
                                                            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                                            Review
                                                        </button>
                                                    )}
                                                    <Link href={`/orders/${order.uuid}`} className="flex items-center justify-center gap-2 bg-gray-50 text-gray-900 hover:bg-primary hover:text-white px-6 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all">
                                                        Details
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" /></svg>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Pagination */}
                                {orderMeta && orderMeta.last_page > 1 && (
                                    <div className="flex items-center justify-center gap-2 mt-12">
                                        {Array.from({ length: orderMeta.last_page }, (_, i) => i + 1).map(p => (
                                            <button 
                                                key={p} 
                                                onClick={() => setOrderPage(p)}
                                                className={`w-10 h-10 rounded-xl text-xs font-bold transition-all ${orderPage === p ? 'bg-black text-white' : 'bg-white border border-gray-100 text-gray-500 hover:border-gray-300'}`}
                                            >
                                                {p}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </>
                ) : (
                    <>
                        {giftCards.length === 0 ? (
                            <div className="text-center py-20 bg-white border border-dashed border-gray-200 rounded-[2.5rem]">
                                <div className="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg className="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 mb-2">No Gift Cards</h3>
                                <p className="text-gray-500 mb-8">You don't have any active gift cards in your wallet.</p>
                                <Link href="/gift-cards" className="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-full font-bold uppercase tracking-wider text-xs">
                                    Browse Gift Cards
                                </Link>
                            </div>
                        ) : (
                            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                                {giftCards.map(card => (
                                    <div key={card.id} className="relative group perspective">
                                        <div className="bg-gradient-to-br from-gray-900 to-black p-8 rounded-[2.5rem] text-white overflow-hidden shadow-2xl transition-all duration-500 group-hover:rotate-y-12">
                                            {/* Design Patterns */}
                                            <div className="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20 blur-2xl"></div>
                                            <div className="absolute bottom-0 left-0 w-32 h-32 bg-primary/10 rounded-full -ml-16 -mb-16 blur-xl"></div>
                                            
                                            <div className="relative z-10 flex flex-col h-full">
                                                <div className="flex justify-between items-start mb-12">
                                                    <div>
                                                        <p className="text-[10px] font-black uppercase tracking-[0.3em] opacity-40 mb-1">Redemption Code</p>
                                                        <h4 className="text-lg font-mono font-bold tracking-wider">{card.plain_code}</h4>
                                                    </div>
                                                    <span className={`px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest ${card.status_badge.class}`}>
                                                        {card.status_badge.label}
                                                    </span>
                                                </div>

                                                <div className="mt-auto">
                                                    <div className="flex items-end justify-between">
                                                        <div>
                                                            <p className="text-[10px] font-black uppercase tracking-widest opacity-40 mb-1">Balance</p>
                                                            <p className="text-3xl font-heading font-black">{formatPrice(card.remaining_amount)}</p>
                                                        </div>
                                                        <div className="text-right">
                                                            <p className="text-[10px] font-black uppercase tracking-widest opacity-40 mb-1">Original Value</p>
                                                            <p className="text-sm font-bold opacity-80">{formatPrice(card.amount)}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="mt-8 pt-6 border-t border-white/10 flex justify-between items-center text-[10px] font-bold uppercase tracking-widest opacity-40">
                                                        <span>{card.card_number}</span>
                                                        <span>Valid until {card.expires_at ? new Date(card.expires_at).toLocaleDateString() : 'Infinite'}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </>
                )}
            </div>

            {/* Item Selection Modal (for multi-item orders) */}
            {reviewOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[80vh] overflow-y-auto">
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-xl font-bold">Select Item to Review</h3>
                            <button onClick={() => setReviewOrder(null)} className="text-gray-400 hover:text-gray-900">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div className="space-y-4">
                            {reviewOrder.items.map((item: any) => (
                                <div key={item.id} className="flex items-center gap-4 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div className="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                        {item.image_url ? (
                                            <img src={item.image_url} alt={item.product_name} className="w-full h-full object-cover" />
                                        ) : (
                                            <div className="flex items-center justify-center h-full text-[10px] text-gray-400">NA</div>
                                        )}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <h4 className="font-bold text-gray-900 text-sm truncate">{item.product_name}</h4>
                                        <p className="text-xs text-gray-500 mt-1">{item.variant_name}</p>
                                    </div>
                                    <button 
                                        onClick={() => openReviewModal(item)}
                                        className="shrink-0 px-4 py-2 text-xs font-bold uppercase bg-black text-white rounded-lg hover:bg-gray-800"
                                    >
                                        {item.review ? 'Edit' : 'Review'}
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* Review Form Modal */}
            {reviewModalItem && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 className="text-xl font-bold mb-4">{reviewModalItem.review ? 'Edit Review' : 'Write a Review'}</h3>
                        <div className="flex gap-4 items-center mb-6 border-b pb-4">
                            <div className="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden">
                                {reviewModalItem.image_url ? (
                                    <img src={reviewModalItem.image_url} className="w-full h-full object-cover" />
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
