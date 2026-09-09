import React from 'react';
import { usePage, Head } from '@inertiajs/react';
import PageRenderer from "../Components/PageRenderer";

export default function CmsPage({ page, content, layout }) {
    const { settings } = usePage().props;

    if (!page || !content) {
        return (
            <div className="min-h-[70vh] flex flex-col items-center justify-center">
                <h1 className="text-3xl font-black text-gray-900 tracking-tight">404 - Page Not Found</h1>
                <p className="text-gray-500 mt-4">The page you are looking for does not exist.</p>
            </div>
        );
    }

    const getPageSchema = () => {
        return {
            "@context": "https://schema.org",
            "@type": "WebPage",
            "name": page.title,
            "description": page.meta_description || page.title,
            "dateModified": page.updated_at
        };
    };

    return (
        <main className="min-h-screen bg-gray-50">
            <Head>
                <title>{page.title || 'Page'}</title>
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getPageSchema())}
                </script>
            </Head>
            <PageRenderer content={content} layout={layout} settings={settings} />
        </main>
    );
}
