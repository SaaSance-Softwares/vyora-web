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

    return (
        <main className="min-h-screen bg-gray-50">
            <Head title={page.title || 'Page'} />
            <PageRenderer content={content} layout={layout} settings={settings} />
        </main>
    );
}
