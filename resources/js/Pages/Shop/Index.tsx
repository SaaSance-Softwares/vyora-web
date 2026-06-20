import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { usePage, Head } from '@inertiajs/react';

export default function ShopPage() {
    const { url } = usePage();
    const searchParams = new URLSearchParams(url.substring(url.indexOf('?')));
    const category = searchParams.get('category');
    const collection = searchParams.get('collection');

    return (
        <>
            <Head title="Shop" />
            <ProductListing 
                title={category || collection || "All Products"} 
                baseEndpoint="/api/products" 
            />
        </>
    );
}
