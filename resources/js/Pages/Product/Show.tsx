import React, { useEffect, useState } from 'react';
import { usePage, Head } from '@inertiajs/react';
import ProductDetailClient from "../../Components/product/ProductDetailClient";
import api from '@/lib/api';

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

    return (
        <div className="w-full pb-12">
            <Head>
                <title>{product.name}</title>
            </Head>
            <ProductDetailClient product={product} policies={policies} coupons={coupons} />
        </div>
    );
}
