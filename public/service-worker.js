const CACHE_NAME = 'ahhc-portal-cache-v1';
const OFFLINE_PAGE = '/offline.html';
const PRECACHE_URLS = [
    '/',
    '/portal/dashboard?source=pwa',
    '/portal/manifest.json',
    '/offline.html',
    '/favicon.ico',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];
const SYNC_STORE_NAME = 'pwa-pending-requests';

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('ahhc-pwa-requests', 1);

        request.onupgradeneeded = function(event) {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(SYNC_STORE_NAME)) {
                db.createObjectStore(SYNC_STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };

        request.onsuccess = function(event) {
            resolve(event.target.result);
        };

        request.onerror = function(event) {
            reject(event.target.error);
        };
    });
}

function waitForTransaction(tx) {
    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onabort = tx.onerror = () => reject(tx.error || new Error('IndexedDB transaction failed'));
    });
}

async function storePendingRequest(request) {
    try {
        const cloned = request.clone();
        const body = await cloned.text();
        const headers = {};
        for (const [key, value] of cloned.headers.entries()) {
            headers[key] = value;
        }

        const db = await openDatabase();
        const tx = db.transaction(SYNC_STORE_NAME, 'readwrite');
        tx.objectStore(SYNC_STORE_NAME).add({
            url: cloned.url,
            method: cloned.method,
            headers,
            body,
            timestamp: Date.now()
        });
        return await waitForTransaction(tx);
    } catch (error) {
        return Promise.reject(error);
    }
}

async function getPendingRequests() {
    const db = await openDatabase();
    const tx = db.transaction(SYNC_STORE_NAME, 'readonly');
    return new Promise((resolve, reject) => {
        const request = tx.objectStore(SYNC_STORE_NAME).getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function clearPendingRequest(id) {
    const db = await openDatabase();
    const tx = db.transaction(SYNC_STORE_NAME, 'readwrite');
    tx.objectStore(SYNC_STORE_NAME).delete(id);
    return await waitForTransaction(tx);
}

self.addEventListener('install', event => {
    console.log('[SW] Install event');
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS)).then(() => {
            console.log('[SW] Precache complete');
        }).catch(err => {
            console.error('[SW] Precache failed', err);
        })
    );
});

self.addEventListener('activate', event => {
    console.log('[SW] Activate event');
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
    console.log('[SW] Claiming clients');
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method === 'POST' && request.headers.get('accept')?.includes('application/json')) {
        event.respondWith(
            fetch(request.clone())
                .catch(async () => {
                    await storePendingRequest(request);
                    if ('sync' in self.registration) {
                        await self.registration.sync.register('sync-pending-requests');
                    }
                    return new Response(JSON.stringify({
                        offline: true,
                        message: 'Your request has been queued and will sync when back online.'
                    }), {
                        status: 503,
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    return response.ok ? response : caches.match(OFFLINE_PAGE);
                })
                .catch(() => caches.match(OFFLINE_PAGE))
        );
        return;
    }

    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'font' || url.origin !== self.location.origin) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                const fetchPromise = fetch(request).then(response => {
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
                    }
                    return response;
                }).catch(() => cachedResponse);
                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    event.respondWith(
        caches.match(request).then(cachedResponse => cachedResponse || fetch(request).then(response => {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
            return response;
        }))
    );
});

self.addEventListener('sync', event => {
    if (event.tag === 'sync-pending-requests') {
        event.waitUntil(
            getPendingRequests().then(async requests => {
                for (const saved of requests) {
                    try {
                        const headers = new Headers(saved.headers || {});
                        const response = await fetch(saved.url, {
                            method: saved.method,
                            headers,
                            body: saved.body
                        });
                        if (response.ok) {
                            await clearPendingRequest(saved.id);
                        }
                    } catch (error) {
                        console.warn('PWA sync failed for request', saved.url, error);
                    }
                }
            })
        );
    }
});

self.addEventListener('push', event => {
    const payload = event.data?.json() || {};
    const title = payload.title || 'AHHC Portal';
    const options = {
        body: payload.body || 'You have a new notification from the portal.',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: payload.data || {},
        actions: payload.actions || [],
        tag: payload.tag || 'ahhc-portal',
        renotify: Boolean(payload.renotify),
        requireInteraction: Boolean(payload.requireInteraction),
        vibrate: payload.vibrate || [200, 100, 200],
        silent: Boolean(payload.silent)
    };

    const showNotificationPromise = self.registration.showNotification(title, options);
    const broadcastPromise = clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
        const message = {
            type: 'push-notification',
            notification: {
                title,
                body: options.body,
                data: options.data,
                timestamp: Date.now(),
            },
        };

        clientList.forEach(client => {
            client.postMessage(message);
        });
    });

    event.waitUntil(Promise.all([showNotificationPromise, broadcastPromise]));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            const urlToOpen = new URL(event.notification.data?.url || '/portal/dashboard', self.location.origin).href;
            for (const client of clientList) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

self.addEventListener('message', event => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});
