import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { renderToString } from 'react-dom/server';
import Layout from './Components/Layout';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => {
            const appName = page.props?.settings?.store_name || 'Vyora';
            return title ? (title.includes(appName) ? title : `${title} - ${appName}`) : appName;
        },
        resolve: (name) => {
            const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
            let pageModule = pages[`./Pages/${name}.tsx`];
            if (!pageModule) {
                const pagesJsx = import.meta.glob('./Pages/**/*.jsx', { eager: true });
                pageModule = pagesJsx[`./Pages/${name}.jsx`];
            }
            pageModule.default.layout = pageModule.default.layout || ((page) => <Layout>{page}</Layout>);
            return pageModule;
        },
        setup: ({ App, props }) => <App {...props} />,
    })
);
