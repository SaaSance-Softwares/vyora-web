// Minimal Service Worker to satisfy PWA requirements
const CACHE_NAME = 'vyora-admin-cache-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Basic pass-through fetch to satisfy the PWA installability requirement.
    // Since this is an admin panel, we don't want to aggressively cache API or Views.
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
