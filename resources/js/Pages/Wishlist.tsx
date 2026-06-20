import { useState, useEffect } from 'react';
import { useWishlistStore } from '@/store/wishlist';
import { Link, Head } from '@inertiajs/react';
import { Heart, ArrowRight } from 'lucide-react';
import { ProductCard } from '@/Components/product/ProductCard';

export default function WishlistPage() {
    const wishlist = useWishlistStore();
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    if (!mounted) return <div className="min-h-[60vh]" />;

    if (wishlist.items.length === 0) return (
        <div className="max-w-sm mx-auto px-4 py-28 text-center min-h-[60vh] flex flex-col items-center justify-center">
            <Head title="Wishlist" />
            <Heart className="w-12 h-12 text-gray-200 mx-auto mb-5" />
            <h1 className="text-xl font-bold text-gray-900 mb-2">Your wishlist is empty</h1>
            <p className="text-sm text-gray-400 mb-8">Save items you love to view them later.</p>
            <Link href="/shop" className="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all">
                Browse Shop <ArrowRight size={15} />
            </Link>
        </div>
    );

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16 min-h-[60vh]">
            <Head title="Wishlist" />
            <div className="mb-10">
                <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <Heart size={28} className="text-red-500 fill-red-50" /> 
                    Your Wishlist
                </h1>
                <p className="text-sm text-gray-400 mt-2">
                    {wishlist.items.length} {wishlist.items.length === 1 ? 'item' : 'items'} saved for later
                </p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                {wishlist.items.map(item => (
                    <ProductCard 
                        key={item.productId} 
                        onRemove={() => wishlist.removeItem(item.productId)}
                        product={{
                            id: item.productId,
                            name: item.name,
                            slug: item.slug,
                            price: item.price,
                            mrp: item.mrp || item.price,
                            discount_percentage: item.discount_percentage || 0,
                            image: item.image,
                            video: item.video,
                            brand: item.brand,
                            category: item.category,
                        }} 
                    />
                ))}
            </div>
        </div>
    );
}
