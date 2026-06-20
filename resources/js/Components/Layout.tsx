import React, { useEffect } from 'react';
import { usePage, Head } from '@inertiajs/react';
import Navbar from './Navbar';
import AuthModal from './auth/AuthModal';
import QuickViewModal from './product/QuickViewModal';

// A mock context provider for Settings, since some components might still use it
export const SettingsContext = React.createContext({});
export const useSettings = () => React.useContext(SettingsContext);

export default function Layout({ children }: { children: React.ReactNode }) {
    const { settings } = usePage().props as any;

    const primary = settings?.primary_color || '#000000';
    const secondary = settings?.secondary_color || '#ffffff';
    const accent = settings?.accent_color || '#3b82f6';
    const headingFont = settings?.heading_font || 'Inter';
    const bodyFont = settings?.body_font || 'Inter';

    useEffect(() => {
        const fontFamilies = [...new Set([headingFont, bodyFont])]
            .map(f => `family=${f.replace(/ /g, '+')}:wght@400;500;600;700;800`)
            .join('&');
        const googleFontsUrl = `https://fonts.googleapis.com/css2?${fontFamilies}&display=swap`;
        
        const link = document.createElement('link');
        link.href = googleFontsUrl;
        link.rel = 'stylesheet';
        document.head.appendChild(link);

        return () => {
            document.head.removeChild(link);
        };
    }, [headingFont, bodyFont]);

    return (
        <SettingsContext.Provider value={settings}>
            <Head>
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            </Head>
            <div
                className="min-h-screen antialiased flex flex-col"
                style={{
                    '--primary': primary,
                    '--secondary': secondary,
                    '--accent': accent,
                    '--font-heading': `"${headingFont}", sans-serif`,
                    '--font-body': `"${bodyFont}", sans-serif`,
                    background: secondary,
                    color: primary,
                } as React.CSSProperties}
            >
                <Navbar settings={settings} />
                <main className="flex-grow">
                    {children}
                </main>
                <AuthModal />
                <QuickViewModal />
            </div>
        </SettingsContext.Provider>
    );
}
