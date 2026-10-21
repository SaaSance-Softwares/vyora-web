import React from 'react';
import { usePage, Head } from '@inertiajs/react';
import PageRenderer from "../Components/PageRenderer";

export default function Home({ page, content, layout }) {
    const { settings } = usePage().props as any;
    const storeName = settings?.store_name || settings?.general?.store_name || 'Dope Style';

    if (!page || !content) {
        return (
            <div className="flex min-h-screen flex-col items-center justify-center p-24">
                <Head title="Welcome to Our Store" />
                <h1 className="text-4xl font-bold mb-4">Welcome to Our Store</h1>
                <p className="text-xl text-gray-600">We are setting things up. Please check back later!</p>
            </div>
        );
    }

    return (
        <main className="min-h-screen bg-gray-50">
            <Head>
                <title>{page.meta_title || page.title ? `${page.meta_title || page.title}` : storeName}</title>
            </Head>
            <PageRenderer content={content} layout={layout} settings={settings} />
        </main>
    );
}
