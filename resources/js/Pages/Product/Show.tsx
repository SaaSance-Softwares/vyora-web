import React, { useEffect, useState } from 'react';
import { usePage, Head } from '@inertiajs/react';
import ProductDetailClient from "../../Components/product/ProductDetailClient";
import api from '@/lib/api';
import { FaqAccordion } from '@/Components/sections/FaqAccordion';

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
        const primaryImage = product.images?.length > 0 ? product.images[0].url : (product.image || 'https://dopestyle.in/logo.png');
        const price = product.variants?.length > 0 ? product.variants[0].price : (product.price || 0);
        const inStock = product.variants?.length > 0 ? (product.variants[0].stock > 0) : true;
        
        const schema: any = {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": product.name,
            "image": primaryImage,
            "description": product.short_description?.replace(/<[^>]*>?/gm, '') || product.name,
            "sku": product.variants?.length > 0 ? (product.variants[0].code || 'SKU-01') : 'SKU-01',
            "brand": {
                "@type": "Brand",
                "name": product.brand || "Dope Style"
            },
            "offers": {
                "@type": "Offer",
                "url": typeof window !== 'undefined' ? window.location.href : `https://dopestyle.in/product/${product.slug}`,
                "priceCurrency": "INR",
                "price": price,
                "itemCondition": "https://schema.org/NewCondition",
                "availability": inStock ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
            }
        };

        if (product.reviews_summary?.total_reviews > 0) {
            schema.aggregateRating = {
                "@type": "AggregateRating",
                "ratingValue": product.reviews_summary.average_rating,
                "reviewCount": product.reviews_summary.total_reviews
            };
        }

        return schema;
    };

    const getFaqSchema = () => {
        if (!product.faqs || product.faqs.length === 0) return null;
        return {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": product.faqs.map((faq: any) => ({
                "@type": "Question",
                "name": faq.question,
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": faq.answer
                }
            }))
        };
    };

    return (
        <div className="w-full pb-12">
            <Head>
                <title>{product.seo?.title || product.name}</title>
                <meta name="description" content={product.seo?.description || product.short_description?.replace(/<[^>]*>?/gm, '') || product.name} />
                {product.seo?.keywords && <meta name="keywords" content={product.seo.keywords} />}
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getProductSchema())}
                </script>
                {product.faqs && product.faqs.length > 0 && (
                    <script type="application/ld+json" head-key="faqld">
                        {JSON.stringify(getFaqSchema())}
                    </script>
                )}
            </Head>
            <ProductDetailClient product={product} policies={policies} coupons={coupons} />
            <FaqAccordion faqs={product.faqs} />
        </div>
    );
}
