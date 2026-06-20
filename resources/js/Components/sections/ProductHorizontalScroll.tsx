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

    return (
        <section className={`px-4 md:px-8${isFluid ? ' w-full' : ' max-w-7xl mx-auto'}`}>
            {data.title && (
                <div className="flex justify-between items-end mb-8 md:mb-10">
                    <h2 className="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">{data.title}</h2>
                    {/* Desktop Controls */}
                    <div className="hidden md:flex items-center space-x-3">
                        <button 
                            onClick={() => scroll('left')} 
                            className="p-2.5 rounded-full bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group"
                            aria-label="Scroll left"
                        >
                            <svg className="w-5 h-5 text-gray-600 group-hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button 
                            onClick={() => scroll('right')} 
                            className="p-2.5 rounded-full bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group"
                            aria-label="Scroll right"
                        >
                            <svg className="w-5 h-5 text-gray-600 group-hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                        </button>
                        <Link 
                            href={data.collection && data.collection !== 'undefined' ? `/collections/${data.collection}` : '/shop'} 
                            className="font-semibold px-5 py-2.5 rounded-full transition-all ml-2 border"
                            style={settings?.primary_color ? { color: settings.primary_color, borderColor: `${settings.primary_color}33`, backgroundColor: `${settings.primary_color}0D` } : { color: '#4f46e5', borderColor: '#eef2ff', backgroundColor: '#eef2ff' }}
                        >
                            View All
                        </Link>
                    </div>
                </div>
            )}
            
            <div 
                ref={scrollContainerRef}
                className="flex overflow-x-auto pb-4 space-x-6 snap-x snap-mandatory px-2 -mx-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
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
                    };

                    return (
                        <div 
                            key={product.id}
                            className="snap-center shrink-0 w-[70vw] md:w-[280px]"
                        >
                            <ProductCard product={mappedProduct} />
                        </div>
                    );
                })}
            </div>
            {data.title && (
                <div className="mt-8 md:hidden">
                    {/* Mobile Controls */}
                    <div className="flex justify-between items-center mb-4 px-2">
                        <div className="flex space-x-3">
                            <button 
                                onClick={() => scroll('left')} 
                                className="p-2 rounded-full bg-white border border-gray-200 active:bg-gray-50 shadow-sm"
                            >
                                <svg className="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <button 
                                onClick={() => scroll('right')} 
                                className="p-2 rounded-full bg-white border border-gray-200 active:bg-gray-50 shadow-sm"
                            >
                                <svg className="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </div>
                    {/* Mobile View All Button */}
                    <Link 
                        href={data.collection && data.collection !== 'undefined' ? `/collections/${data.collection}` : '/shop'} 
                        className="inline-flex w-full justify-center px-6 py-3 border rounded-xl font-bold transition-all"
                        style={settings?.primary_color ? { borderColor: settings.primary_color, color: settings.primary_color } : { borderColor: '#d1d5db', color: '#374151' }}
                    >
                        View All
                    </Link>
                </div>
            )}
        </section>
    );
}
