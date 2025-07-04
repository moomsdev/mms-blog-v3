/**
 * Service Worker for MoomsDev Theme
 * 
 * Provides caching, offline support and performance optimizations
 * 
 * @version 1.0.0
 */

const CACHE_NAME = 'mooms-dev-v1.0.0';
const CACHE_VERSION = 1;

// Assets to cache immediately
const CRITICAL_ASSETS = [
    '/wp-content/themes/mooms_dev/dist/css/critical.min.css',
    '/wp-content/themes/mooms_dev/dist/js/critical.min.js',
];

// Assets to cache on first request
const CACHEABLE_ASSETS = [
    // CSS Files
    '/wp-content/themes/mooms_dev/dist/css/main.min.css',
    '/wp-content/themes/mooms_dev/dist/css/components.min.css',
    
    // JavaScript Files
    '/wp-content/themes/mooms_dev/dist/js/main.min.js',
    '/wp-content/themes/mooms_dev/dist/js/vendor.min.js',
    
    // Images (common icons, logos)
    '/wp-content/themes/mooms_dev/dist/images/logo.svg',
    '/wp-content/themes/mooms_dev/dist/images/favicon.ico',
];

// Cache strategies for different resource types
const CACHE_STRATEGIES = {
    // Cache first, fallback to network
    'cache-first': [
        /\.(?:css|js|woff2?|ttf|eot)$/,
        /\/wp-content\/themes\/mooms_dev\/dist\//,
    ],
    
    // Network first, fallback to cache
    'network-first': [
        /\/wp-json\//,
        /\/wp-admin\//,
        /\.php$/,
    ],
    
    // Stale while revalidate
    'stale-while-revalidate': [
        /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
        /\/wp-content\/uploads\//,
    ]
};

/**
 * Install Event - Cache critical assets
 */
self.addEventListener('install', event => {
    console.log('SW: Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('SW: Caching critical assets');
                return cache.addAll(CRITICAL_ASSETS);
            })
            .then(() => {
                console.log('SW: Critical assets cached');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('SW: Critical asset caching failed:', error);
            })
    );
});

/**
 * Activate Event - Cleanup old caches
 */
self.addEventListener('activate', event => {
    console.log('SW: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('SW: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('SW: Activated');
                return self.clients.claim();
            })
    );
});

/**
 * Fetch Event - Handle requests with appropriate caching strategy
 */
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip admin and preview pages
    if (url.pathname.includes('/wp-admin/') || 
        url.searchParams.has('preview') ||
        url.searchParams.has('customize_changeset_uuid')) {
        return;
    }
    
    // Apply appropriate caching strategy
    const strategy = getCacheStrategy(request.url);
    
    switch (strategy) {
        case 'cache-first':
            event.respondWith(cacheFirstStrategy(request));
            break;
            
        case 'network-first':
            event.respondWith(networkFirstStrategy(request));
            break;
            
        case 'stale-while-revalidate':
            event.respondWith(staleWhileRevalidateStrategy(request));
            break;
            
        default:
            // Default to network first for unknown resources
            event.respondWith(networkFirstStrategy(request));
    }
});

/**
 * Cache First Strategy - Best for static assets
 */
async function cacheFirstStrategy(request) {
    try {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        
        const response = await fetch(request);
        
        if (response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.error('SW: Cache first strategy failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

/**
 * Network First Strategy - Best for dynamic content
 */
async function networkFirstStrategy(request) {
    try {
        const response = await fetch(request);
        
        if (response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.log('SW: Network failed, trying cache:', request.url);
        
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        
        // Return offline page for HTML requests
        if (request.headers.get('accept').includes('text/html')) {
            return getOfflinePage();
        }
        
        return new Response('Offline', { status: 503 });
    }
}

/**
 * Stale While Revalidate Strategy - Best for images
 */
async function staleWhileRevalidateStrategy(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    // Fetch fresh version in background
    const fetchPromise = fetch(request).then(response => {
        if (response.status === 200) {
            cache.put(request, response.clone());
        }
        return response;
    });
    
    // Return cached version immediately, or wait for network
    return cached || fetchPromise;
}

/**
 * Determine cache strategy for a given URL
 */
function getCacheStrategy(url) {
    for (const [strategy, patterns] of Object.entries(CACHE_STRATEGIES)) {
        for (const pattern of patterns) {
            if (pattern.test(url)) {
                return strategy;
            }
        }
    }
    return 'network-first'; // Default strategy
}

/**
 * Get offline page
 */
async function getOfflinePage() {
    const offlineHtml = `
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Offline - MoomsDev</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                text-align: center; 
                padding: 50px; 
                background: #f8f9fa;
                color: #333;
            }
            .offline-container {
                max-width: 400px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .offline-icon {
                font-size: 48px;
                margin-bottom: 20px;
            }
            h1 { color: #e74c3c; margin-bottom: 10px; }
            p { margin-bottom: 20px; line-height: 1.6; }
            .retry-btn {
                background: #3498db;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }
            .retry-btn:hover { background: #2980b9; }
        </style>
    </head>
    <body>
        <div class="offline-container">
            <div class="offline-icon">📡</div>
            <h1>Không có kết nối mạng</h1>
            <p>Bạn đang offline. Vui lòng kiểm tra kết nối internet và thử lại.</p>
            <button class="retry-btn" onclick="window.location.reload()">
                Thử lại
            </button>
        </div>
    </body>
    </html>
    `;
    
    return new Response(offlineHtml, {
        headers: { 'Content-Type': 'text/html' }
    });
}

/**
 * Background Sync for failed requests
 */
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

/**
 * Handle background sync
 */
async function doBackgroundSync() {
    console.log('SW: Background sync triggered');
    // Handle any queued requests here
}

/**
 * Push notification support
 */
self.addEventListener('push', event => {
    if (!event.data) return;
    
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/wp-content/themes/mooms_dev/dist/images/icon-192x192.png',
        badge: '/wp-content/themes/mooms_dev/dist/images/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: data.data,
        actions: data.actions
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

/**
 * Handle notification clicks
 */
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action) {
        // Handle action clicks
        console.log('SW: Notification action clicked:', event.action);
    } else {
        // Handle notification click
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

console.log('SW: Service Worker loaded successfully'); 