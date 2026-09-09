import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { usePage, Head } from '@inertiajs/react';

export default function ShopPage() {
    const { url, app_url } = usePage();
    const searchParams = new URLSearchParams(url.substring(url.indexOf('?')));
    const category = searchParams.get('category');
    const collection = searchParams.get('collection');
    
    const baseUrl = (app_url as string) || 'https://dopestyle.in';
    const pageTitle = category || collection || "All Products";

    const getShopSchema = () => {
        return {
            "@context": "https://schema.org",
            "@type": "CollectionPage",
            "name": pageTitle,
            "url": `${baseUrl}${url}`
        };
    };

    return (
        <>
            <Head>
                <title>{`Shop - ${pageTitle}`}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getShopSchema())}
                </script>
            </Head>
            <ProductListing 
                title={pageTitle} 
                baseEndpoint="/api/products" 
            />
        </>
    );
}
