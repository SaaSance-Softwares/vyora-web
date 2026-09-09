import React from 'react';
import { usePage, Head } from '@inertiajs/react';
import PageRenderer from "../Components/PageRenderer";

export default function Home({ page, content, layout }) {
    const { settings, app_url } = usePage().props;
    const baseUrl = app_url || 'https://dopestyle.in';
    const storeName = settings?.general?.store_name || 'Dope Style';

    if (!page || !content) {
        return (
            <div className="flex min-h-screen flex-col items-center justify-center p-24">
                <Head title="Welcome to Our Store" />
                <h1 className="text-4xl font-bold mb-4">Welcome to Our Store</h1>
                <p className="text-xl text-gray-600">We are setting things up. Please check back later!</p>
            </div>
        );
    }

    const getHomeSchema = () => {
        return {
            "@context": "https://schema.org",
            "@graph": [
                {
                    "@type": "WebSite",
                    "@id": `${baseUrl}/#website`,
                    "url": baseUrl,
                    "name": storeName,
                    "potentialAction": {
                        "@type": "SearchAction",
                        "target": `${baseUrl}/search?q={search_term_string}`,
                        "query-input": "required name=search_term_string"
                    }
                },
                {
                    "@type": "Organization",
                    "@id": `${baseUrl}/#organization`,
                    "name": storeName,
                    "url": baseUrl,
                    "logo": settings?.general?.favicon ? `${baseUrl}/${settings.general.favicon}` : `${baseUrl}/favicon.ico`
                }
            ]
        };
    };

    return (
        <main className="min-h-screen bg-gray-50">
            <Head>
                <title>{page.title || 'Home'}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getHomeSchema())}
                </script>
            </Head>
            <PageRenderer content={content} layout={layout} settings={settings} />
        </main>
    );
}
