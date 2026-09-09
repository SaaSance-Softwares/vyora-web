import React from 'react';
import { Head } from '@inertiajs/react';

interface LegalPageProps {
    page: {
        title: string;
        content: string;
        meta_title?: string;
        meta_description?: string;
        updated_at: string;
    }
}

export default function LegalPage({ page }: LegalPageProps) {
    if (!page) {
        return (
            <div className="min-h-[70vh] flex flex-col items-center justify-center">
                <h1 className="text-3xl font-black text-gray-900 tracking-tight">404 - Page Not Found</h1>
                <p className="text-gray-500 mt-4">The policy page you are looking for does not exist.</p>
            </div>
        );
    }

    const formattedDate = new Date(page.updated_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const getArticleSchema = () => {
        return {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": page.title,
            "dateModified": page.updated_at,
            "author": {
                "@type": "Organization",
                "name": "Dope Style"
            }
        };
    };

    return (
        <main className="min-h-screen bg-white py-16 md:py-24">
            <Head>
                <title>{page.meta_title || page.title}</title>
                {page.meta_description && <meta name="description" content={page.meta_description} />}
                <script type="application/ld+json" head-key="jsonld">
                    {JSON.stringify(getArticleSchema())}
                </script>
            </Head>
            
            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <header className="mb-12 border-b border-gray-100 pb-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">
                        {page.title}
                    </h1>
                    <p className="text-sm font-semibold text-gray-500 uppercase tracking-widest">
                        Last Updated: {formattedDate}
                    </p>
                </header>

                <div 
                    className="prose prose-lg max-w-none text-gray-700 
                        prose-headings:font-bold prose-headings:text-gray-900 
                        prose-a:text-black prose-a:font-semibold hover:prose-a:text-gray-700
                        prose-strong:text-gray-900 prose-strong:font-bold
                        prose-p:leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: page.content }} 
                />
            </div>
        </main>
    );
}
