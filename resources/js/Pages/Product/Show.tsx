import React, { useEffect, useState } from 'react';
import { usePage, Head } from '@inertiajs/react';
import ProductDetailClient from "../../Components/product/ProductDetailClient";
import api from '@/lib/api';

export default function ProductPage({ product }) {
    const { settings } = usePage().props;
    const policies = settings?.policies || {};
    const [coupons, setCoupons] = useState([]);

    useEffect(() => {
        api.get('/api/coupons/public')
            .then(res => setCoupons(res.data.product_coupons || []))
            .catch(err => console.error("Failed to load coupons", err));
    }, []);

    if (!product) {
        return (
            <div className="flex min-h-screen flex-col items-center justify-center p-24 text-center">
                <h1 className="text-3xl font-bold mb-4">Product Not Found</h1>
            </div>
        );
    }

    const getProductSchema = () => {
        const firstSku = product.skus && product.skus.length > 0 ? product.skus[0] : null;
        const baseUrl = settings?.app_url || '';
        const mainImage = product.images && product.images.length > 0 
            ? (product.images[0].image_path?.startsWith('http') ? product.images[0].image_path : `${baseUrl}${product.images[0].image_path}`) 
            : '';
        
        return {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": product.name,
            "image": mainImage ? [mainImage] : [],
            "description": product.short_description || product.name,
            "sku": firstSku ? firstSku.product_sku : product.id,
            "brand": {
                "@type": "Brand",
                "name": product.brand_name || "Dope Style"
            },
            "offers": {
                "@type": "Offer",
                "url": `${baseUrl}/product/${product.slug}`,
                "priceCurrency": "INR",
                "price": firstSku ? firstSku.price : (product.price || 0),
                "itemCondition": "https://schema.org/NewCondition",
                "availability": (firstSku && firstSku.stock > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
            }
        };
    };

    return (
        <div className="w-full pb-12">
            <Head>
                <title>{product.name}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getProductSchema())}
                </script>
            </Head>
            <ProductDetailClient product={product} policies={policies} coupons={coupons} />
        </div>
    );
}
