import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import Layout from './Components/Layout';

let appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Vyora';
try {
    const el = document.getElementById('app');
    if (el && el.dataset.page) {
        const page = JSON.parse(el.dataset.page);
        if (page.props?.settings?.store_name) {
            appName = page.props.settings.store_name;
        }
    }
} catch (e) {}

createInertiaApp({
    title: (title) => title ? (title.includes(appName) ? title : `${title} - ${appName}`) : appName,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
        let page = pages[`./Pages/${name}.tsx`];
        if (!page) {
            // fallback to jsx if needed
            const pagesJsx = import.meta.glob('./Pages/**/*.jsx', { eager: true });
            page = pagesJsx[`./Pages/${name}.jsx`];
        }
        page.default.layout = page.default.layout || ((page) => <Layout>{page}</Layout>);
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);

        // Global PageView tracking for Inertia navigation
        import('@inertiajs/react').then(({ router }) => {
            router.on('navigate', (event) => {
                import('./lib/tracking').then(({ trackPageView }) => {
                    trackPageView(event.detail.page.url);
                });
            });
        });
    },
    progress: {
        color: '#4B5563',
    },
});
