const CACHE_NAME = "solelec-admin-v1.0";
const urlsToCache = [
    "/admin",
    "/manifest.json",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",
];

// Installation du Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(urlsToCache);
        })
    );
    self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Intercepter les requêtes réseau
self.addEventListener("fetch", (event) => {
    if (
        event.request.method === "GET" &&
        event.request.url.includes("/admin")
    ) {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            })
        );
    }
});
