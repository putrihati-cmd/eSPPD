const CACHE_NAME = 'esppd-v1';
const STATIC_CACHE = 'esppd-static-v1';
const DYNAMIC_CACHE = 'esppd-dynamic-v1';

// Resources to cache immediately
const STATIC_ASSETS = [
    '/',
    '/dashboard',
    '/offline',
    '/build/assets/app.css',
    '/build/assets/app.js',
    '/manifest.json',
];

// API routes to cache for offline access
const API_CACHE_ROUTES = [
    '/api/mobile/dashboard',
    '/api/mobile/sppd',
];

// Install event
self.addEventListener('install', event => {
    console.log('[ServiceWorker] Install');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[ServiceWorker] Pre-caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
    );
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', event => {
    console.log('[ServiceWorker] Activate');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Handle API requests
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Handle static assets
    if (request.destination === 'image' || 
        request.destination === 'script' || 
        request.destination === 'style') {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Handle navigation requests
    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    // Default: network first
    event.respondWith(networkFirst(request));
});

// Cache first strategy
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline');
        }
        return new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Background sync for offline data
self.addEventListener('sync', event => {
    console.log('[ServiceWorker] Sync event', event.tag);
    if (event.tag === 'sync-sppd') {
        event.waitUntil(syncPendingData());
    }
});

async function syncPendingData() {
    // Get pending data from IndexedDB
    // Send to server when online
    console.log('[ServiceWorker] Syncing pending data...');
}

// Push notification
self.addEventListener('push', event => {
    console.log('[ServiceWorker] Push received');
    
    let data = { title: 'e-SPPD', body: 'Anda memiliki notifikasi baru' };
    
    if (event.data) {
        data = event.data.json();
    }

    const options = {
        body: data.body,
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/dashboard'
        },
        actions: [
            { action: 'open', title: 'Lihat' },
            { action: 'close', title: 'Tutup' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', event => {
    console.log('[ServiceWorker] Notification click');
    event.notification.close();

    if (event.action === 'open' || !event.action) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/dashboard')
        );
    }
});
