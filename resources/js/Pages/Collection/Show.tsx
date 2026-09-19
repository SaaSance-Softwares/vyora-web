import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { Head, usePage } from '@inertiajs/react';

export default function CollectionPage({ collection }) {
    const { app_url } = usePage().props;
    const baseUrl = app_url || 'https://dopestyle.in';

    if (!collection) return null;
    
    const getCollectionSchema = () => {
        return {
            "@context": "https://schema.org",
            "@type": "CollectionPage",
            "name": collection.name,
            "description": collection.description || `Shop the latest ${collection.name}`,
            "url": `${baseUrl}/collection/${collection.slug}`
        };
    };

    const getFaqSchema = () => {
        if (!collection.faqs || collection.faqs.length === 0) return null;
        return {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": collection.faqs.map((faq: any) => ({
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
                <title>{collection.social_title || collection.name}</title>
                <meta name="description" content={collection.social_description || (collection.description ? collection.description.replace(/<[^>]*>?/gm, '') : `Shop the latest ${collection.name}`)} />
                {collection.meta_keywords && <meta name="keywords" content={collection.meta_keywords} />}
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getCollectionSchema())}
                </script>
                {collection.faqs && collection.faqs.length > 0 && (
                    <script type="application/ld+json" head-key="faqld">
                        {JSON.stringify(getFaqSchema())}
                    </script>
                )}
            </Head>
            <ProductListing 
                title={collection.name} 
                subtitle={collection.aeo_use_case ? `Ideal for: ${collection.aeo_use_case}` : undefined}
                bannerImage={collection.banner_image_url}
                description={collection.description}
                faqs={collection.faqs}
                queryKey="collection" 
                queryValue={collection.slug} 
            />
        </>
    );
}
