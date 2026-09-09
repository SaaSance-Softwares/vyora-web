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

    return (
        <>
            <Head>
                <title>{category.name}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getCategorySchema())}
                </script>
            </Head>
            <ProductListing 
                title={category.name} 
                queryKey="category" 
                queryValue={category.slug} 
            />
        </>
    );
}
