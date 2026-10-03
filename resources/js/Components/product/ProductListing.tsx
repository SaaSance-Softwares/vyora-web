import { useState, useEffect, useRef, useCallback, Suspense } from 'react';
import { usePage } from '@inertiajs/react';
import { ProductCard } from '@/components/product/ProductCard';
import api from '@/lib/api';
import { PaginatedResponse, ProductList } from '@/types';
import { Filter, ChevronDown, ChevronUp, SlidersHorizontal, X } from 'lucide-react';
import { FaqAccordion } from '@/Components/sections/FaqAccordion';


interface FilterState {
    in_stock: boolean;
    sort: string;
    min_price: string;
    max_price: string;
    size: string[];
    color: string[];
    fit: string[];
    fabric: string[];
}

interface ProductListingProps {
    title: string;
    subtitle?: string;
    bannerImage?: string;
    initialFilters?: Partial<FilterState>;
    baseEndpoint?: string;
    queryKey?: string; // explicitly passed if we want to override
    queryValue?: string;
    description?: string;
    faqs?: any[];
}

const SORT_OPTIONS = [
    { label: 'Newest Arrivals', value: 'new' },
    { label: 'Featured', value: 'featured' },
    { label: 'Best Sellers', value: 'best_seller' },
    { label: 'Price: Low to High', value: 'price_low_high' },
    { label: 'Price: High to Low', value: 'price_high_low' },
    { label: 'A to Z', value: 'a_z' },
    { label: 'Z to A', value: 'z_a' },
];

function ProductListingInner({ title, subtitle, bannerImage, description, faqs, initialFilters, baseEndpoint = '/api/products', queryKey, queryValue }: ProductListingProps) {
    const { settings: settings } = usePage().props as any;
    const { url } = usePage();
  const searchParams = new URLSearchParams(url.substring(url.indexOf("?")));
    const primaryColor = settings?.tc_primary_color || '#000000';
    
    // Read dynamic query from URL if available, otherwise fallback to props from Server Component
    const activeQueryKey = queryKey || (searchParams.has('category') ? 'category' : searchParams.has('collection') ? 'collection' : undefined);
    const activeQueryValue = queryValue || (activeQueryKey ? searchParams.get(activeQueryKey) : undefined) || undefined;
    
    const [products, setProducts] = useState<ProductList[]>([]);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loading, setLoading] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);
    
    // Filters
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [filters, setFilters] = useState<FilterState>({
        in_stock: searchParams.has('in_stock') ? searchParams.get('in_stock') === '1' : (initialFilters?.in_stock || false),
        sort: searchParams.get('sort') || initialFilters?.sort || 'new',
        min_price: searchParams.get('min_price') || initialFilters?.min_price || '',
        max_price: searchParams.get('max_price') || initialFilters?.max_price || '',
        size: searchParams.get('size') ? searchParams.get('size')!.split(',') : (initialFilters?.size || []),
        color: searchParams.get('color') ? searchParams.get('color')!.split(',') : (initialFilters?.color || []),
        fit: searchParams.get('fit') ? searchParams.get('fit')!.split(',') : (initialFilters?.fit || []),
        fabric: searchParams.get('fabric') ? searchParams.get('fabric')!.split(',') : (initialFilters?.fabric || []),
    });

    const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
        sort: true, price: true, size: true, color: true, fit: true, fabric: true
    });

    const toggleSection = (section: string) => {
        setExpandedSections(prev => ({ ...prev, [section]: !prev[section] }));
    };

    const loader = useRef<HTMLDivElement>(null);

    const buildQueryString = (currentPage: number) => {
        const params = new URLSearchParams();
        params.append('page', currentPage.toString());
        if (activeQueryKey && activeQueryValue) params.append(activeQueryKey, activeQueryValue);
        
        if (filters.in_stock) params.append('in_stock', '1');
        if (filters.sort) params.append('sort', filters.sort);
        if (filters.min_price) params.append('min_price', filters.min_price);
        if (filters.max_price) params.append('max_price', filters.max_price);
        if (filters.size.length) params.append('size', filters.size.join(','));
        if (filters.color.length) params.append('color', filters.color.join(','));
        if (filters.fit.length) params.append('fit', filters.fit.join(','));
        if (filters.fabric.length) params.append('fabric', filters.fabric.join(','));
        
        // Cache buster to bypass strict Chromium XHR caching
        params.append('_t', Date.now().toString());

        
        return params.toString();
    };

    const fetchProducts = useCallback(async (currentPage: number, append = false) => {
        if (!append) setLoading(true);
        else setLoadingMore(true);

        const currentUrl = `${baseEndpoint}?${buildQueryString(currentPage)}`;
        console.log('Fetching products from:', currentUrl);

        try {
            const res = await api.get<PaginatedResponse<ProductList>>(currentUrl);
            const fetched = res.data.data;
            

            
            if (append) {
                setProducts(prev => [...prev, ...fetched]);
            } else {
                setProducts(fetched);
                // Also update filters if they come from the API (only on first load or filter change)
                if ((res.data as any).filters) {
                    const dynamicFilters = (res.data as any).filters;
                    if (dynamicFilters.fits) setAvailableFits(dynamicFilters.fits);
                    if (dynamicFilters.fabrics) setAvailableFabrics(dynamicFilters.fabrics);
                    if (dynamicFilters.colors) setAvailableColors(dynamicFilters.colors);
                    if (dynamicFilters.sizes) setAvailableSizes(dynamicFilters.sizes);
                }
            }
            
            setHasMore(res.data.meta.current_page < res.data.meta.last_page);
        } catch (error: any) {
            console.error('Failed to fetch products from ' + currentUrl, error);
            if (!append) setProducts([]);
            setHasMore(false);
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    }, [filters, baseEndpoint, activeQueryKey, activeQueryValue]);

    // Initial fetch when filters change
    useEffect(() => {
        setPage(1);
        setHasMore(true);
        fetchProducts(1, false);

        // Update browser URL silently for sharing/bookmarking
        const params = new URLSearchParams(window.location.search);
        
        // Remove existing filter params
        params.delete('in_stock');
        params.delete('sort');
        params.delete('min_price');
        params.delete('max_price');
        params.delete('size');
        params.delete('color');
        params.delete('fit');
        params.delete('fabric');
        
        // Add active params
        if (filters.in_stock) params.set('in_stock', '1');
        if (filters.sort && filters.sort !== 'new') params.set('sort', filters.sort);
        if (filters.min_price) params.set('min_price', filters.min_price);
        if (filters.max_price) params.set('max_price', filters.max_price);
        if (filters.size.length) params.set('size', filters.size.join(','));
        if (filters.color.length) params.set('color', filters.color.join(','));
        if (filters.fit.length) params.set('fit', filters.fit.join(','));
        if (filters.fabric.length) params.set('fabric', filters.fabric.join(','));
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);

    }, [filters, fetchProducts, activeQueryKey, activeQueryValue]);

    // Infinite scroll intersection observer
    useEffect(() => {
        const handleObserver = (entities: IntersectionObserverEntry[]) => {
            const target = entities[0];
            if (target.isIntersecting && hasMore && !loading && !loadingMore) {
                setPage(prev => {
                    const nextPage = prev + 1;
                    fetchProducts(nextPage, true);
                    return nextPage;
                });
            }
        };

        const option = { root: null, rootMargin: '200px', threshold: 0 };
        const observer = new IntersectionObserver(handleObserver, option);
        if (loader.current) observer.observe(loader.current);
        return () => observer.disconnect();
    }, [hasMore, loading, loadingMore, fetchProducts]);

    const handleFilterChange = (key: keyof FilterState, value: any) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const toggleArrayFilter = (key: keyof FilterState, value: string) => {
        setFilters(prev => {
            const current = prev[key] as string[];
            if (current.includes(value)) {
                return { ...prev, [key]: current.filter(item => item !== value) };
            }
            return { ...prev, [key]: [...current, value] };
        });
    };

    const clearFilters = () => {
        setFilters({
            in_stock: false,
            sort: 'new',
            min_price: '',
            max_price: '',
            size: [],
            color: [],
            fit: [],
            fabric: [],
        });
    };

    const [availableFits, setAvailableFits] = useState<{name: string, count: number}[]>([]);
    const [availableFabrics, setAvailableFabrics] = useState<{name: string, count: number}[]>([]);
    const [availableColors, setAvailableColors] = useState<{name: string, hex: string, count: number}[]>([]);
    const [availableSizes, setAvailableSizes] = useState<{name: string, count: number}[]>([]);

    // Dummy filter options for UI demonstration. In a real app, these should come from API.

    const displayTitle = title ? title : (activeQueryValue ? activeQueryValue.split('-').join(' ') : 'Products');

    return (
        <div className="max-w-7xl mx-auto px-4 py-8 relative">
            <div className="mb-6 flex flex-col gap-4">
                <div className="flex flex-col justify-center overflow-hidden relative">
                    {bannerImage && (
                        <div 
                            className="absolute inset-0 bg-cover bg-center z-0" 
                            style={{ backgroundImage: `url(${bannerImage})` }}
                        />
                    )}
                    <div className={`relative z-10 flex flex-col gap-2 ${bannerImage ? 'bg-gradient-to-r from-white via-white/80 to-transparent p-6 sm:p-8 w-full' : ''}`}>
                        <h1 className="text-3xl font-bold capitalize">{displayTitle}</h1>
                        {subtitle && <p className="text-gray-500 font-medium">{subtitle}</p>}
                    </div>
                </div>
                <div className="flex justify-end items-center gap-3 w-full border-t border-gray-100 pt-4">
                    <button 
                        onClick={() => setIsFilterOpen(true)}
                        className="flex-1 md:flex-none flex items-center justify-center gap-2 border px-4 py-2 rounded-xl lg:hidden font-medium bg-white shadow-sm"
                    >
                        <SlidersHorizontal size={18} /> Filters
                    </button>
                    <div className="hidden lg:flex items-center gap-2">
                        <span className="text-sm text-gray-500 font-medium">Sort By:</span>
                        <select 
                            value={filters.sort}
                            onChange={(e) => handleFilterChange('sort', e.target.value)}
                            className="border-none font-semibold text-sm focus:ring-0 cursor-pointer bg-transparent"
                            style={{ color: primaryColor }}
                        >
                            {SORT_OPTIONS.map(opt => (
                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            <div className="flex flex-col lg:flex-row gap-8 items-start">
                {/* Mobile Filter Overlay */}
                {isFilterOpen && (
                    <div className="fixed inset-0 bg-black/50 z-30 lg:hidden" onClick={() => setIsFilterOpen(false)} />
                )}

                {/* Sidebar Filters */}
                <div className={`
                    fixed lg:sticky top-[80px] pb-16 lg:pb-0 right-0 max-h-[calc(100vh-64px)] lg:max-h-[calc(100vh-100px)]
                    w-80 lg:w-64 bg-white z-40 lg:z-0 
                    transform transition-transform duration-300 ease-in-out
                    flex flex-col border-l lg:border-l-0 lg:border-r border-gray-100 pr-0 lg:pr-6
                    ${isFilterOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'}
                `}>
                    <div className="flex items-center justify-between p-4 lg:p-0 lg:pb-4 border-b lg:border-none shrink-0">
                        <h2 className="text-lg font-bold flex items-center gap-2"><Filter size={18} /> Filters</h2>
                        <div className="flex items-center gap-3">
                            <button onClick={clearFilters} className="text-sm font-bold text-gray-500 hover:text-black underline underline-offset-2">
                                Clear
                            </button>
                            <button onClick={() => setIsFilterOpen(false)} className="lg:hidden p-2 -mr-2 text-gray-500"><X size={20} /></button>
                        </div>
                    </div>

                    <div className="flex-1 overflow-y-auto p-4 lg:p-0 space-y-6 lg:mt-2 custom-scrollbar min-h-0">
                        
                        {/* Availability */}
                        <label className="flex items-center gap-3 cursor-pointer group">
                            <div className="relative flex items-center">
                                <input 
                                    type="checkbox" 
                                    checked={filters.in_stock}
                                    onChange={(e) => handleFilterChange('in_stock', e.target.checked)}
                                    className="peer sr-only"
                                />
                                <div className="w-5 h-5 border rounded border-gray-300 peer-checked:border-primary peer-checked:bg-primary transition-colors flex items-center justify-center">
                                    <svg className="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 placeholder-transition" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <span className="text-sm font-medium text-gray-700 group-hover:text-black">In Stock Only</span>
                        </label>

                        {/* Price Range */}
                        <div className="border-t border-gray-100 pt-5">
                            <button onClick={() => toggleSection('price')} className="flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4">
                                Price <ChevronDown size={16} className={`transition-transform ${expandedSections.price ? 'rotate-180' : ''}`} />
                            </button>
                            {expandedSections.price && (
                                <div className="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        placeholder="Min" 
                                        className="w-full border border-gray-200 rounded-md p-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                                        value={filters.min_price}
                                        onChange={(e) => handleFilterChange('min_price', e.target.value)}
                                    />
                                    <span className="text-gray-400">-</span>
                                    <input 
                                        type="number" 
                                        placeholder="Max" 
                                        className="w-full border border-gray-200 rounded-md p-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                                        value={filters.max_price}
                                        onChange={(e) => handleFilterChange('max_price', e.target.value)}
                                    />
                                </div>
                            )}
                        </div>

                        {/* Size */}
                        {availableSizes.length > 0 && (
                            <div className="border-t border-gray-100 pt-5">
                                <button onClick={() => toggleSection('size')} className="flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4">
                                    Size <ChevronDown size={16} className={`transition-transform ${expandedSections.size ? 'rotate-180' : ''}`} />
                                </button>
                                {expandedSections.size && (
                                    <div className="flex flex-wrap gap-2">
                                        {availableSizes.map(s => (
                                            <button 
                                                key={s.name}
                                                onClick={() => toggleArrayFilter('size', s.name)}
                                                className={`min-w-[40px] h-10 px-3 rounded-md border text-sm font-medium transition-colors flex items-center justify-center gap-1 ${filters.size.includes(s.name) ? 'bg-black text-white border-black' : 'border-gray-200 text-gray-700 hover:border-gray-300'}`}
                                            >
                                                <span>{s.name}</span>
                                                <span className={`text-xs opacity-60 ${filters.size.includes(s.name) ? 'text-gray-300' : 'text-gray-500'}`}>({s.count})</span>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Color */}
                        {availableColors.length > 0 && (
                            <div className="border-t border-gray-100 pt-5">
                                <button onClick={() => toggleSection('color')} className="flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4">
                                    Color <ChevronDown size={16} className={`transition-transform ${expandedSections.color ? 'rotate-180' : ''}`} />
                                </button>
                                {expandedSections.color && (
                                    <div className="space-y-2">
                                        {availableColors.map(c => (
                                            <label key={c.name} className="flex items-center justify-between cursor-pointer group w-full">
                                                <div className="flex items-center gap-3">
                                                    <input type="checkbox" checked={filters.color.includes(c.name)} onChange={() => toggleArrayFilter('color', c.name)} className="w-4 h-4 rounded border-gray-300 text-black focus:ring-black" />
                                                    <div className="flex items-center gap-2">
                                                        <span 
                                                            className="w-4 h-4 rounded-full border border-gray-200 shadow-sm" 
                                                            style={{ backgroundColor: c.hex || '#ffffff' }}
                                                        />
                                                        <span className="text-sm text-gray-600 group-hover:text-black">{c.name}</span>
                                                    </div>
                                                </div>
                                                <span className="text-gray-400 text-xs mr-3">{c.count}</span>
                                            </label>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Fit */}
                        {availableFits.length > 0 && (
                            <div className="border-t border-gray-100 pt-5">
                                <button onClick={() => toggleSection('fit')} className="flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4">
                                    Fit <ChevronDown size={16} className={`transition-transform ${expandedSections.fit ? 'rotate-180' : ''}`} />
                                </button>
                                {expandedSections.fit && (
                                    <div className="space-y-2">
                                        {availableFits.map(f => (
                                            <label key={f.name} className="flex items-center justify-between cursor-pointer group w-full">
                                                <div className="flex items-center gap-3">
                                                    <input type="checkbox" checked={filters.fit.includes(f.name)} onChange={() => toggleArrayFilter('fit', f.name)} className="w-4 h-4 rounded border-gray-300 text-black focus:ring-black" />
                                                    <span className="text-sm text-gray-600 group-hover:text-black">{f.name}</span>
                                                </div>
                                                <span className="text-gray-400 text-xs mr-3">{f.count}</span>
                                            </label>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Fabric */}
                        {availableFabrics.length > 0 && (
                            <div className="border-t border-gray-100 pt-5">
                                <button onClick={() => toggleSection('fabric')} className="flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 pb-2">
                                    Fabric <ChevronDown size={16} className={`transition-transform ${expandedSections.fabric ? 'rotate-180' : ''}`} />
                                </button>
                                {expandedSections.fabric && (
                                    <div className="space-y-2 pt-2">
                                        {availableFabrics.map(f => (
                                            <label key={f.name} className="flex items-center justify-between cursor-pointer group w-full">
                                                <div className="flex items-center gap-3">
                                                    <input type="checkbox" checked={filters.fabric.includes(f.name)} onChange={() => toggleArrayFilter('fabric', f.name)} className="w-4 h-4 rounded border-gray-300 text-black focus:ring-black" />
                                                    <span className="text-sm text-gray-600 group-hover:text-black">{f.name}</span>
                                                </div>
                                                <span className="text-gray-400 text-xs mr-3">{f.count}</span>
                                            </label>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Spacer block at the end to guarantee extra scrollable space so items aren't cut off */}
                        <div className="h-20 w-full shrink-0 lg:h-32"></div>
                    </div>

                    <div className="p-4 border-t border-gray-100 mt-auto bg-white z-10 w-full lg:hidden">
                        <button onClick={() => setIsFilterOpen(false)} className="w-full py-3 bg-black text-white text-sm font-bold rounded-lg shadow-sm">
                            Show Results
                        </button>
                    </div>
                </div>

                {/* Product Grid Area */}
                <div className="flex-1 w-full min-w-0">
                    {loading && products.length === 0 ? (
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-x-4 gap-y-10">
                            {[...Array(6)].map((_, i) => (
                                <div key={i} className="animate-pulse">
                                    <div className="bg-gray-100 aspect-[4/5] rounded-xl mb-4"></div>
                                    <div className="h-4 bg-gray-100 rounded w-3/4 mb-2"></div>
                                    <div className="h-4 bg-gray-100 rounded w-1/4"></div>
                                </div>
                            ))}
                        </div>
                    ) : products.length === 0 ? (
                        <div className="py-20 flex flex-col items-center justify-center text-center">
                            <div className="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                <Filter size={32} className="text-gray-300" />
                            </div>
                            <h3 className="text-xl font-bold mb-2 text-gray-900">No products found</h3>
                            <p className="text-gray-500 mb-6 max-w-sm">
                                We couldn't find any products matching your current filters. 
                            </p>

                            <button onClick={clearFilters} className="px-6 py-3 bg-black text-white font-bold rounded-xl shadow-sm hover:translate-y(-1px) transition-all">
                                Clear Filters
                            </button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-8 lg:gap-y-12">
                            {products.map((product) => (
                                <ProductCard key={product.id} product={product} activeCategory={activeQueryKey === 'category' ? activeQueryValue : undefined} />
                            ))}
                        </div>
                    )}

                    {/* Infinite Scroll Loader Trigger */}
                    {hasMore && products.length > 0 && (
                        <div ref={loader} className="w-full flex justify-center py-12 mt-4 text-gray-400">
                            {loadingMore ? (
                                <div className="flex items-center gap-2">
                                    <div className="w-2 h-2 bg-black rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                                    <div className="w-2 h-2 bg-black rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                                    <div className="w-2 h-2 bg-black rounded-full animate-bounce"></div>
                                </div>
                            ) : (
                                <span className="text-sm font-medium">Scroll to load more</span>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {(description && description !== '<p><br></p>') || (faqs && faqs.length > 0) ? (
                <div className="mt-16 pt-8 border-t border-gray-100">
                    {description && description !== '<p><br></p>' && (
                        <div className="prose prose-sm sm:prose-base max-w-none text-gray-600 mb-12" dangerouslySetInnerHTML={{ __html: description }} />
                    )}
                    {faqs && faqs.length > 0 && (
                        <FaqAccordion faqs={faqs} />
                    )}
                </div>
            ) : null}
        </div>
    );
}

export function ProductListing(props: ProductListingProps) {
    return (
        <Suspense fallback={
            <div className="flex justify-center items-center h-64">
                <div className="w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
            </div>
        }>
            <ProductListingInner {...props} />
        </Suspense>
    );
}
