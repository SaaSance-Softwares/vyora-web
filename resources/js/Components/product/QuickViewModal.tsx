import { useState, useMemo, useEffect } from 'react';
import { useUIStore } from '@/store/ui';
import { useCartStore } from '@/store/cart';
import { router, Link } from '@inertiajs/react';
import { formatPrice } from '@/lib/utils';
import { X, Ruler } from 'lucide-react';

export default function QuickViewModal() {
    const { quickViewProduct: product, quickViewAction: action, closeQuickView } = useUIStore();
    const cart = useCartStore();

    const [selectedColor, setSelectedColor] = useState<string | null>(null);
    const [selectedSize, setSelectedSize] = useState<string | null>(null);

    // Reset selection when product changes
    useEffect(() => {
        if (product) {
            setSelectedColor(null);
            setSelectedSize(null);
        }
    }, [product]);

    const colors = useMemo(() => {
        if (!product) return [];
        const all = new Map();
        product.variants?.forEach(v => {
            const c = v.attributes.find(a => a.name === 'Color');
            if (c && !all.has(c.value)) all.set(c.value, c);
        });
        return Array.from(all.values());
    }, [product]);

    const sizes = useMemo(() => {
        if (!product) return [];
        const all = new Set<string>();
        product.variants?.forEach(v => {
            const s = v.attributes.find(a => a.name === 'Size');
            if (s) all.add(s.value);
        });
        return Array.from(all);
    }, [product]);

    const currentVariant = useMemo(() => {
        if (!product) return null;
        
        if (colors.length > 0 && !selectedColor) return null;
        if (sizes.length > 0 && !selectedSize) return null;

        return product.variants.find(v => {
            const matchColor = colors.length === 0 || v.attributes.find(a => a.name === 'Color')?.value === selectedColor;
            const matchSize = sizes.length === 0 || v.attributes.find(a => a.name === 'Size')?.value === selectedSize;
            return matchColor && matchSize;
        });
    }, [product, selectedColor, selectedSize, colors, sizes]);

    const displayImage = useMemo(() => {
        if (!product) return '';
        if (selectedColor) {
            const colorObj = colors.find(c => c.value === selectedColor);
            if (colorObj) {
                const img = product.images?.find(i => i.color_id?.toString() === colorObj.id?.toString());
                if (img) return img.url;
            }
        }
        const primary = product.images?.find(i => i.is_primary);
        return primary?.url || product.images?.[0]?.url || product.image || '';
    }, [product, selectedColor, colors]);

    if (!product) return null;

    const handleAction = () => {
        if (!currentVariant) return alert('Please select all options.');
        const colorObj = colors.find(c => c.value === selectedColor);
        
        let variantLabel = '';
        if (selectedColor && selectedSize) variantLabel = `${selectedColor} - ${selectedSize}`;
        else if (selectedColor) variantLabel = selectedColor;
        else if (selectedSize) variantLabel = selectedSize;

        const colorImg = colorObj ? product.images?.find(img => img.color_id?.toString() === colorObj.id?.toString()) : null;

        cart.addItem({
            skuId: currentVariant.id,
            productId: product.id,
            name: product.name,
            slug: product.slug,
            variant: variantLabel,
            price: currentVariant.price,
            mrp: currentVariant.mrp,
            image: colorImg?.url || product.image || product.images?.[0]?.url || '',
            quantity: 1,
            colorName: selectedColor || undefined,
            colorHex: colorObj?.meta || undefined,
            sizeName: selectedSize || undefined,
            size: selectedSize || undefined,
        });
        
        closeQuickView();
        
        if (action === 'buy') {
            router.visit('/checkout');
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={closeQuickView} />
            <div className="relative bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div className="p-4 border-b flex justify-between items-center bg-gray-50 shrink-0">
                    <h2 className="font-bold text-lg truncate pr-4">{product.name}</h2>
                    <button onClick={closeQuickView} className="p-1.5 hover:bg-gray-200 rounded-full transition-colors"><X size={18} /></button>
                </div>
                
                <div className="p-6 overflow-y-auto flex-1 space-y-6 flex flex-col md:flex-row gap-6">
                    {/* Image Area */}
                    <div className="w-full md:w-2/5 shrink-0">
                        <div className="aspect-[3/4] bg-gray-100 rounded-xl overflow-hidden relative border border-gray-200">
                            {displayImage ? (
                                <img src={displayImage} alt={product.name} className="absolute inset-0 w-full h-full object-cover" />
                            ) : (
                                <div className="absolute inset-0 flex items-center justify-center text-gray-400">No Image</div>
                            )}
                        </div>
                    </div>

                    {/* Options Area */}
                    <div className="flex-1 space-y-6">
                        {/* Colors */}
                        {colors.length > 0 && (
                        <div>
                            <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Color</p>
                            <div className="flex flex-wrap gap-2">
                                {colors.map(c => (
                                    <button 
                                        key={c.value}
                                        onClick={() => setSelectedColor(c.value)}
                                        className={`w-10 h-10 rounded-full border-2 transition-all ${selectedColor === c.value ? 'border-black ring-2 ring-black/20 scale-110' : 'border-gray-200 hover:border-gray-400'}`}
                                        style={{ backgroundColor: c.meta || '#ccc' }}
                                        title={c.value}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                    
                    {/* Sizes */}
                    {sizes.length > 0 && (
                        <div>
                            <div className="flex items-center justify-between mb-3">
                                <p className="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                    Size
                                </p>
                                {product.size_chart && (
                                    <Link href={`/product/${product.slug}`} onClick={closeQuickView} className="text-[10px] font-bold text-gray-500 underline uppercase tracking-wider hover:text-black transition-colors underline-offset-4 flex items-center gap-1">
                                        <Ruler size={12} /> View Size Chart
                                    </Link>
                                )}
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {sizes.map(s => {
                                    // check stock
                                    const inStock = product.variants.some(v => 
                                        (!selectedColor || v.attributes.find(a => a.name === 'Color')?.value === selectedColor) &&
                                        v.attributes.find(a => a.name === 'Size')?.value === s &&
                                        v.stock > 0
                                    );
                                    
                                    return (
                                        <button 
                                            key={s}
                                            onClick={() => {
                                                if (inStock) setSelectedSize(s);
                                            }}
                                            disabled={!inStock}
                                            className={`min-w-[3rem] h-10 px-3 rounded-xl border font-bold text-sm transition-all
                                                ${selectedSize === s ? 'border-black bg-black text-white' : 
                                                  !inStock ? 'opacity-30 border-gray-200 cursor-not-allowed bg-gray-50' : 
                                                  'border-gray-200 hover:border-black text-gray-700 bg-white'}`}
                                        >
                                            {s}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                    </div>
                </div>
                
                <div className="p-4 border-t bg-gray-50 flex items-center justify-between gap-4 shrink-0">
                    <div className="flex flex-col">
                        <div className="flex items-baseline gap-2">
                            <span className="text-xl font-black text-gray-900 leading-none">{formatPrice(currentVariant ? currentVariant.price : product.price)}</span>
                            {(currentVariant ? currentVariant.mrp : product.mrp) > (currentVariant ? currentVariant.price : product.price) && (
                                <span className="text-xs font-semibold text-gray-400 line-through">{formatPrice(currentVariant ? currentVariant.mrp : product.mrp)}</span>
                            )}
                        </div>
                        {product.coupon_price && (
                            <div className="text-[10px] text-gray-500 mt-1 font-medium bg-green-50/50 px-1.5 py-0.5 rounded border border-green-100/50 inline-block w-max">
                                Best Price <span className="text-green-700 font-bold">{formatPrice(product.coupon_price)}</span> with coupon
                            </div>
                        )}
                    </div>
                    <div className="flex gap-2 flex-1 max-w-[360px]">
                        <Link 
                            href={`/product/${product.slug}`}
                            onClick={closeQuickView}
                            className="flex-1 bg-white border border-gray-300 text-gray-800 px-4 py-3 rounded-xl font-bold tracking-widest text-[10px] hover:bg-gray-50 text-center uppercase flex items-center justify-center whitespace-nowrap"
                        >
                            View Details
                        </Link>
                        <button 
                            onClick={handleAction}
                            disabled={!currentVariant || currentVariant.stock <= 0}
                            className="flex-1 bg-black text-white px-4 py-3 rounded-xl font-bold uppercase tracking-widest text-[10px] hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                        >
                            {action === 'buy' ? 'Buy Now' : 'Add to Cart'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
