import React, { useEffect, useState, useRef } from 'react';
import { Link } from '@inertiajs/react';
import { ProductCard } from "@/components/product/ProductCard";

export default function ProductHorizontalScroll({ data, isFluid, settings }: { data: any; isFluid?: boolean; settings?: any }) {
    const [products, setProducts] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const scrollContainerRef = useRef<HTMLDivElement>(null);

    const scroll = (direction: 'left' | 'right') => {
        if (scrollContainerRef.current) {
            const scrollAmount = window.innerWidth < 768 ? window.innerWidth * 0.7 : 300;
            scrollContainerRef.current.scrollBy({
                left: direction === 'left' ? -scrollAmount : scrollAmount,
                behavior: 'smooth'
            });
        }
    };

    useEffect(() => {
        let isMounted = true;
        const fetchProducts = async () => {
            try {
                const apiUrl = '/api';
                
                let fetchUrl = `${apiUrl}/products?collection=${data.collection || ''}&limit=${data.limit || 8}`;
                if (data.product_slugs && data.product_slugs.length > 0) {
                    fetchUrl = `${apiUrl}/products?slugs=${data.product_slugs.join(',')}`;
                }

                const res = await fetch(fetchUrl, { 
                    cache: 'no-store' 
                });
                const responseData = await res.json();
                
                if (isMounted) {
                    if (Array.isArray(responseData)) {
                        setProducts(responseData);
                    } else if (responseData && Array.isArray(responseData.data)) {
                        setProducts(responseData.data);
                    }
                    setLoading(false);
                }
            } catch (error) {
                console.error("Failed to fetch products for horizontal scroll output:", error);
                if (isMounted) setLoading(false);
            }
        };

        fetchProducts();
        
        return () => { isMounted = false; };
    }, [data.collection, data.limit, data.product_slugs?.join(',')]);

    if (loading) {
        return (
            <section className={`px-4 md:px-8${isFluid ? ' w-full' : ' max-w-7xl mx-auto'}`}>
                <div className="h-10 w-64 bg-gray-200 animate-pulse rounded-lg mb-8"></div>
                <div className="flex space-x-6 overflow-x-hidden">
                    <div className="h-80 w-64 bg-gray-100 animate-pulse rounded-2xl shrink-0"></div>
                    <div className="h-80 w-64 bg-gray-100 animate-pulse rounded-2xl shrink-0"></div>
                </div>
            </section>
        );
    }

    if (products.length === 0) return null;

    const getDesktopGridClass = (length: number) => {
        if (length === 1) return 'md:grid md:grid-cols-1 md:space-x-0 md:gap-6 md:overflow-visible';
        if (length === 2) return 'md:grid md:grid-cols-2 md:space-x-0 md:gap-6 md:overflow-visible';
        if (length === 3) return 'md:grid md:grid-cols-3 md:space-x-0 md:gap-6 md:overflow-visible';
        if (length === 4) return 'md:grid md:grid-cols-4 md:space-x-0 md:gap-6 md:overflow-visible';
        return '';
    };

    return (
        <section className={`px-4 md:px-8${isFluid ? ' w-full' : ' max-w-7xl mx-auto'}`}>
            {data.title && (
                <div className="flex justify-between items-end mb-8 md:mb-10 px-2">
                    <h2 className="text-3xl md:text-4xl font-bold tracking-tight" style={{ color: settings?.text_color || '#111827' }}>{data.title}</h2>
                    <Link 
                        href={data.collection && data.collection !== 'undefined' ? `/collections/${data.collection}` : '/shop'} 
                        className="hidden md:flex font-bold uppercase tracking-widest text-xs hover:opacity-70 transition-opacity"
                        style={{ color: settings?.primary_color || '#000' }}
                    >
                        View All
                    </Link>
                </div>
            )}
            
            <div className="relative group/carousel">
                {/* Floating Navigation Arrows (Desktop Only) */}
                <button 
                    onClick={() => scroll('left')} 
                    className={`hidden md:flex absolute -left-4 top-[35%] -translate-y-1/2 z-10 w-12 h-12 rounded-full bg-white border border-gray-200 shadow-xl items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 hover:bg-gray-50 text-gray-800 ${products.length <= 4 ? '!hidden' : ''}`}
                    aria-label="Scroll left"
                >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button 
                    onClick={() => scroll('right')} 
                    className={`hidden md:flex absolute -right-4 top-[35%] -translate-y-1/2 z-10 w-12 h-12 rounded-full bg-white border border-gray-200 shadow-xl items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 hover:bg-gray-50 text-gray-800 ${products.length <= 4 ? '!hidden' : ''}`}
                    aria-label="Scroll right"
                >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" /></svg>
                </button>

                <div 
                    ref={scrollContainerRef}
                className={`flex overflow-x-auto pb-4 space-x-6 snap-x snap-mandatory px-2 -mx-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] ${getDesktopGridClass(products.length)}`}
            >
                {products.map((product: any) => {
                    const rawImage = product.image || product.thumbnail || (product.media && product.media.length > 0 ? product.media[0].original_url : null);
                    const cleanImage = rawImage;

                    const rawHoverImage = product.hover_image || null;
                    const cleanHoverImage = rawHoverImage;

                    const mappedProduct: any = {
                        id: product.id,
                        name: product.name,
                        slug: product.slug,
                        brand: product.brand || null,
                        price: product.price || 0,
                        price_formatted: product.formatted_price || `$${Number(product.price).toFixed(2)}`,
                        mrp: product.compare_at_price || product.mrp || product.price || 0,
                        discount_percentage: product.discount_percentage || 0,
                        image: cleanImage,
                        hover_image: cleanHoverImage,
                        category: typeof product.category === 'object' ? product.category?.name : (product.category || 'Apparel'),
                        is_new: product.is_new || false,
                        colors: product.colors?.map((c: any) => ({ name: c.name, hex: c.hex || c.meta, meta: c.hex || c.meta })) || [],
                        coupon_price: product.coupon_price || null,
                    };

                    return (
                        <div 
                            key={product.id}
                            className={`snap-start shrink-0 w-[70vw] ${products.length <= 4 ? 'md:w-full' : 'md:w-[280px]'}`}
                        >
                            <ProductCard product={mappedProduct} />
                        </div>
                    );
                })}
            </div>
            {(data.title || data.collection) && (
                <div className="mt-8 md:hidden px-2">
                    {/* Mobile View All Button */}
                    <Link 
                        href={data.collection && data.collection !== 'undefined' ? `/collections/${data.collection}` : '/shop'} 
                        className="flex w-full justify-center px-6 py-3 border rounded-xl font-bold transition-all"
                        style={settings?.primary_color ? { borderColor: settings.primary_color, color: settings.primary_color } : { borderColor: '#d1d5db', color: '#374151' }}
                    >
                        View All
                    </Link>
                </div>
            )}
            </div>
        </section>
    );
}
