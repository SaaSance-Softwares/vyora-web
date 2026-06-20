import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { Head } from '@inertiajs/react';

export default function CategoryPage({ category }) {
    if (!category) return null;
    
    return (
        <>
            <Head title={category.name} />
            <ProductListing 
                title={category.name} 
                queryKey="category" 
                queryValue={category.slug} 
            />
        </>
    );
}
