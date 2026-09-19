import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { Head, usePage } from '@inertiajs/react';

export default function CategoryPage({ category }) {
    const { app_url } = usePage().props;
    const baseUrl = app_url || 'https://dopestyle.in';

    if (!category) return null;
    
    const getCategorySchema = () => {
        return {
            "@context": "https://schema.org",
            "@type": "CollectionPage",
            "name": category.name,
            "description": category.description || `Shop the latest ${category.name}`,
            "url": `${baseUrl}/category/${category.slug}`
        };
    };

    const getFaqSchema = () => {
        if (!category.faqs || category.faqs.length === 0) return null;
        return {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": category.faqs.map((faq: any) => ({
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
        <>
            <Head>
                <title>{category.meta_title || category.name}</title>
                <meta name="description" content={category.meta_description || category.description || `Shop the latest ${category.name}`} />
                {category.meta_keywords && <meta name="keywords" content={category.meta_keywords} />}
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getCategorySchema())}
                </script>
                {category.faqs && category.faqs.length > 0 && (
                    <script type="application/ld+json" head-key="faqld">
                        {JSON.stringify(getFaqSchema())}
                    </script>
                )}
            </Head>
            <ProductListing 
                title={category.name} 
                subtitle={category.aeo_use_case ? `Ideal for: ${category.aeo_use_case}` : undefined}
                bannerImage={category.banner_image_url}
                description={category.description}
                faqs={category.faqs}
                queryKey="category" 
                queryValue={category.slug} 
            />
        </>
    );
}
