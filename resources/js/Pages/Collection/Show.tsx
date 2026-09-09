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

    return (
        <>
            <Head>
                <title>{collection.name}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getCollectionSchema())}
                </script>
            </Head>
            <ProductListing 
                title={collection.name} 
                queryKey="collection" 
                queryValue={collection.slug} 
            />
        </>
    );
}
