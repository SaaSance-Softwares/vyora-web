import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { Head } from '@inertiajs/react';

export default function CollectionPage({ collection }) {
    if (!collection) return null;
    
    return (
        <>
            <Head title={collection.name} />
            <ProductListing 
                title={collection.name} 
                queryKey="collection" 
                queryValue={collection.slug} 
            />
        </>
    );
}
