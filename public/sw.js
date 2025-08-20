const CACHE_NAME = "solelec-admin-v1.2";
const isDevelopment =
    location.hostname === "localhost" || location.hostname.includes(".test");

// URLs à mettre en cache (adaptation automatique dev/prod)
const urlsToCache = [
    "/manifest.json",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",
].concat(
    isDevelopment ? [] : ["/build/assets/app.css", "/build/assets/app.js"]
);

// Installation du Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(urlsToCache).catch((err) => {
                // Erreur silencieuse lors de la mise en cache
            });
        })
    );
    self.skipWaiting(); // Force l'activation immédiate
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
    self.clients.claim(); // Prend contrôle immédiatement
});

// Intercepter les requêtes réseau
self.addEventListener("fetch", (event) => {
    const request = event.request;

    // Ne pas mettre en cache les requêtes POST/PUT/DELETE (formulaires avec CSRF)
    if (request.method !== "GET") {
        return;
    }

    // Ne pas mettre en cache les pages admin (à cause du CSRF token)
    if (
        request.url.includes("/admin") &&
        !request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico)$/)
    ) {
        return;
    }

    // Cache seulement les assets statiques
    if (request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|json)$/)) {
        event.respondWith(
            caches.match(request).then((response) => {
                return (
                    response ||
                    fetch(request).then((fetchResponse) => {
                        // Cloner la réponse pour la mettre en cache
                        const responseClone = fetchResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                        return fetchResponse;
                    })
                );
            })
        );
    }
});
