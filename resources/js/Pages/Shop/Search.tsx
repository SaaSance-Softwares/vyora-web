import React from 'react';
import { ProductListing } from '@/Components/product/ProductListing';
import { usePage, Head } from '@inertiajs/react';
import { SearchInput } from './SearchInput';

export default function SearchPage() {
    const { url } = usePage();
    const searchParams = new URLSearchParams(url.substring(url.indexOf('?')));
    const q = searchParams.get('q') || '';

    return (
        <>
            <Head title={`Search Results for "${q}"`} />
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="max-w-2xl mx-auto mb-12">
                    <h1 className="text-3xl font-black tracking-tight text-gray-900 mb-6 text-center">
                        {q ? `Search Results for "${q}"` : "What are you looking for?"}
                    </h1>
                    <SearchInput initialValue={q} />
                </div>

                {q ? (
                    <ProductListing 
                        title=""
                        baseEndpoint="/api/search" 
                        queryKey="q"
                        queryValue={q}
                    />
                ) : (
                    <div className="text-center text-gray-500 py-12">
                        Enter a search term above to find products.
                    </div>
                )}
            </div>
        </>
    );
}
